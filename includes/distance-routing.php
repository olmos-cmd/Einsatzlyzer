<?php
/**
 * Distance and routing module for Einsatzlyzer.
 *
 * Uses local Haversine calculations for straight-line distance and the public
 * OSRM service (OpenStreetMap data) for road distance and estimated driving time.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ffl_distance_mode_options() {
    return array(
        'none'          => ffl_lang( 'Nicht anzeigen', 'Do not display' ),
        'air'           => ffl_lang( 'Luftlinie', 'Straight-line distance' ),
        'road'          => ffl_lang( 'Fahrstrecke', 'Driving distance' ),
        'time'          => ffl_lang( 'Fahrzeit', 'Driving time' ),
        'road_time'     => ffl_lang( 'Fahrstrecke und Fahrzeit', 'Driving distance and time' ),
        'air_road_time' => ffl_lang( 'Luftlinie, Fahrstrecke und Fahrzeit', 'Straight-line distance, driving distance and time' ),
    );
}

function ffl_distance_mode_needs_route( $mode = null ) {
    $mode = null === $mode ? ffl_get_distance_mode() : (string) $mode;
    return in_array( $mode, array( 'road', 'time', 'road_time', 'air_road_time' ), true );
}

function ffl_distance_route_meta_keys() {
    return array(
        '_ffl_route_air_km',
        '_ffl_route_road_km',
        '_ffl_route_duration_min',
        '_ffl_route_provider',
        '_ffl_route_calculated_at',
        '_ffl_route_start_lat',
        '_ffl_route_start_lon',
        '_ffl_route_target_lat',
        '_ffl_route_target_lon',
        '_ffl_route_input_hash',
        '_ffl_route_last_error',
        '_ffl_route_last_error_time',
    );
}

function ffl_distance_input_hash( $station, $coords ) {
    return hash( 'sha256', implode( '|', array(
        number_format( (float) $station['lat'], 7, '.', '' ),
        number_format( (float) $station['lon'], 7, '.', '' ),
        number_format( (float) $coords['lat'], 7, '.', '' ),
        number_format( (float) $coords['lon'], 7, '.', '' ),
        (string) ( $coords['privacy'] ?? 'exact' ),
    ) ) );
}

function ffl_get_saved_route_data( $post_id, $station = null, $coords = null ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return null;
    }
    $station = is_array( $station ) ? $station : ffl_get_station_coordinates();
    $coords  = is_array( $coords ) ? $coords : ffl_get_public_coordinates( $post_id );
    if ( ! $station || ! $coords ) {
        return null;
    }

    $stored_hash = (string) get_post_meta( $post_id, '_ffl_route_input_hash', true );
    if ( ! hash_equals( ffl_distance_input_hash( $station, $coords ), $stored_hash ) ) {
        return null;
    }

    $road_km      = get_post_meta( $post_id, '_ffl_route_road_km', true );
    $duration_min = get_post_meta( $post_id, '_ffl_route_duration_min', true );
    if ( '' === (string) $road_km || '' === (string) $duration_min ) {
        return null;
    }

    return array(
        'road_km'       => round( (float) $road_km, 1 ),
        'duration_min'  => max( 0, (int) round( (float) $duration_min ) ),
        'provider'      => (string) get_post_meta( $post_id, '_ffl_route_provider', true ),
        'calculated_at' => (string) get_post_meta( $post_id, '_ffl_route_calculated_at', true ),
    );
}

function ffl_calculate_and_store_route( $post_id, $force = false ) {
    $post_id = absint( $post_id );
    if ( ! $post_id || 'ffl_einsatz' !== get_post_type( $post_id ) ) {
        return new WP_Error( 'invalid_incident', ffl_lang( 'Ungültiger Einsatz.', 'Invalid incident.' ) );
    }

    $station = ffl_get_station_coordinates();
    $coords  = ffl_get_public_coordinates( $post_id );
    if ( ! $station ) {
        return new WP_Error( 'missing_station', ffl_lang( 'Koordinaten des Feuerwehrhauses fehlen.', 'Fire station coordinates are missing.' ) );
    }
    if ( ! $coords ) {
        return new WP_Error( 'missing_target', ffl_lang( 'Der Einsatz besitzt keine öffentlich verwendbaren Koordinaten.', 'The incident has no publicly usable coordinates.' ) );
    }

    if ( ! $force ) {
        $cached = ffl_get_saved_route_data( $post_id, $station, $coords );
        if ( $cached ) {
            return array_merge( $cached, array( 'cached' => true ) );
        }
    }

    $air_exact_km = ffl_haversine_distance_km( $station['lat'], $station['lon'], $coords['lat'], $coords['lon'] );
    $air_km       = round( $air_exact_km, 1 );

    // Sehr nahe Einsatzstellen können vom Routingdienst auf denselben Straßenpunkt
    // wie das Feuerwehrhaus eingerastet werden. Dann liefert OSRM eine 0-m-Route.
    // Unter 100 Metern verwenden wir deshalb bewusst eine lokale Näherung und
    // vermeiden eine unnötige externe Routinganfrage.
    if ( $air_exact_km <= 0.1 ) {
        $values = array(
            '_ffl_route_air_km'          => round( $air_exact_km, 3 ),
            '_ffl_route_road_km'         => round( $air_exact_km, 3 ),
            '_ffl_route_duration_min'    => 0,
            '_ffl_route_provider'        => ffl_lang( 'Lokale Näherung (unter 100 m)', 'Local approximation (under 100 m)' ),
            '_ffl_route_calculated_at'   => current_time( 'mysql' ),
            '_ffl_route_start_lat'       => (float) $station['lat'],
            '_ffl_route_start_lon'       => (float) $station['lon'],
            '_ffl_route_target_lat'      => (float) $coords['lat'],
            '_ffl_route_target_lon'      => (float) $coords['lon'],
            '_ffl_route_input_hash'      => ffl_distance_input_hash( $station, $coords ),
            '_ffl_route_last_error'      => '',
            '_ffl_route_last_error_time' => '',
        );
        foreach ( $values as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }
        return array(
            'air_km'       => round( $air_exact_km, 3 ),
            'road_km'      => round( $air_exact_km, 3 ),
            'duration_min' => 0,
            'provider'     => $values['_ffl_route_provider'],
            'cached'       => false,
            'near_station' => true,
        );
    }

    $url    = sprintf(
        'https://router.project-osrm.org/route/v1/driving/%1$s,%2$s;%3$s,%4$s?overview=false&steps=false&alternatives=false',
        rawurlencode( (string) (float) $station['lon'] ),
        rawurlencode( (string) (float) $station['lat'] ),
        rawurlencode( (string) (float) $coords['lon'] ),
        rawurlencode( (string) (float) $coords['lat'] )
    );

    $response = wp_remote_get( $url, array(
        'timeout'     => 20,
        'redirection' => 3,
        'headers'     => array( 'Accept' => 'application/json' ),
        'user-agent'  => 'Einsatzlyzer/' . FFL_EINSATZLYZER_VERSION . '; ' . home_url( '/' ),
    ) );

    if ( is_wp_error( $response ) ) {
        $message = $response->get_error_message();
        update_post_meta( $post_id, '_ffl_route_last_error', $message );
        update_post_meta( $post_id, '_ffl_route_last_error_time', current_time( 'mysql' ) );
        return new WP_Error( 'routing_request_failed', $message );
    }

    $status = (int) wp_remote_retrieve_response_code( $response );
    $data   = json_decode( wp_remote_retrieve_body( $response ), true );
    if ( 200 !== $status || ! is_array( $data ) || 'Ok' !== ( $data['code'] ?? '' ) || empty( $data['routes'][0] ) ) {
        $message = ffl_lang( 'Der Routingdienst lieferte keine Route.', 'The routing service did not return a route.' );
        if ( ! empty( $data['message'] ) ) {
            $message .= ' ' . sanitize_text_field( $data['message'] );
        }
        update_post_meta( $post_id, '_ffl_route_last_error', $message );
        update_post_meta( $post_id, '_ffl_route_last_error_time', current_time( 'mysql' ) );
        return new WP_Error( 'no_route', $message );
    }

    $route        = $data['routes'][0];
    $road_km      = round( (float) ( $route['distance'] ?? 0 ) / 1000, 1 );
    $duration_min = max( 1, (int) round( (float) ( $route['duration'] ?? 0 ) / 60 ) );
    if ( $road_km <= 0 || $duration_min <= 0 ) {
        $message = ffl_lang( 'Die berechnete Route ist ungültig.', 'The calculated route is invalid.' );
        update_post_meta( $post_id, '_ffl_route_last_error', $message );
        update_post_meta( $post_id, '_ffl_route_last_error_time', current_time( 'mysql' ) );
        return new WP_Error( 'invalid_route', $message );
    }

    $values = array(
        '_ffl_route_air_km'         => $air_km,
        '_ffl_route_road_km'        => $road_km,
        '_ffl_route_duration_min'   => $duration_min,
        '_ffl_route_provider'       => 'OSRM / OpenStreetMap',
        '_ffl_route_calculated_at'  => current_time( 'mysql' ),
        '_ffl_route_start_lat'      => (float) $station['lat'],
        '_ffl_route_start_lon'      => (float) $station['lon'],
        '_ffl_route_target_lat'     => (float) $coords['lat'],
        '_ffl_route_target_lon'     => (float) $coords['lon'],
        '_ffl_route_input_hash'     => ffl_distance_input_hash( $station, $coords ),
        '_ffl_route_last_error'     => '',
        '_ffl_route_last_error_time'=> '',
    );
    foreach ( $values as $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }

    return array(
        'air_km'       => $air_km,
        'road_km'      => $road_km,
        'duration_min' => $duration_min,
        'provider'     => 'OSRM / OpenStreetMap',
        'cached'       => false,
    );
}

function ffl_route_queue_ids( $mode ) {
    $mode = sanitize_key( $mode );
    if ( ! in_array( $mode, array( 'missing', 'all', 'errors' ), true ) ) {
        $mode = 'missing';
    }
    $ids = get_posts( array(
        'post_type'      => 'ffl_einsatz',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ) );

    return array_values( array_filter( array_map( 'absint', $ids ), static function( $post_id ) use ( $mode ) {
        $coords = ffl_get_public_coordinates( $post_id );
        if ( ! $coords ) {
            return false;
        }
        if ( 'all' === $mode ) {
            return true;
        }
        if ( 'errors' === $mode ) {
            return '' !== trim( (string) get_post_meta( $post_id, '_ffl_route_last_error', true ) );
        }
        return ! ffl_get_saved_route_data( $post_id, ffl_get_station_coordinates(), $coords );
    } ) );
}

add_action( 'wp_ajax_ffl_route_queue', 'ffl_ajax_route_queue' );
function ffl_ajax_route_queue() {
    check_ajax_referer( 'ffl_distance_routing', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ), 403 );
    }
    if ( ! ffl_get_station_coordinates() ) {
        wp_send_json_error( array( 'message' => ffl_lang( 'Bitte zuerst die Koordinaten des Feuerwehrhauses speichern.', 'Please save the fire station coordinates first.' ) ) );
    }
    $ids = ffl_route_queue_ids( $_POST['mode'] ?? 'missing' );
    wp_send_json_success( array( 'ids' => $ids, 'total' => count( $ids ) ) );
}

add_action( 'wp_ajax_ffl_route_calculate', 'ffl_ajax_route_calculate' );
function ffl_ajax_route_calculate() {
    check_ajax_referer( 'ffl_distance_routing', 'nonce' );
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array( 'message' => ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ), 403 );
    }
    $force   = ! empty( $_POST['force'] );
    $result  = ffl_calculate_and_store_route( $post_id, $force );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array(
            'post_id'  => $post_id,
            'title'    => get_the_title( $post_id ),
            'edit_url' => get_edit_post_link( $post_id, 'raw' ),
            'message'  => $result->get_error_message(),
        ) );
    }
    wp_send_json_success( array(
        'post_id'  => $post_id,
        'title'    => get_the_title( $post_id ),
        'edit_url' => get_edit_post_link( $post_id, 'raw' ),
        'result'   => $result,
        'html'    => ffl_route_admin_status_html( $post_id ),
        'message' => ffl_lang( 'Fahrstrecke und Fahrzeit wurden gespeichert.', 'Driving distance and time were saved.' ),
    ) );
}

add_action( 'wp_ajax_ffl_route_delete_single', 'ffl_ajax_route_delete_single' );
function ffl_ajax_route_delete_single() {
    check_ajax_referer( 'ffl_distance_routing', 'nonce' );
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array( 'message' => ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ), 403 );
    }
    foreach ( ffl_distance_route_meta_keys() as $key ) {
        delete_post_meta( $post_id, $key );
    }
    wp_send_json_success( array(
        'html'    => ffl_route_admin_status_html( $post_id ),
        'message' => ffl_lang( 'Gespeicherte Entfernungswerte wurden gelöscht.', 'Stored distance values were deleted.' ),
    ) );
}

function ffl_route_admin_status_html( $post_id ) {
    $post_id = absint( $post_id );
    $station = ffl_get_station_coordinates();
    $coords  = ffl_get_public_coordinates( $post_id );
    $saved   = $station && $coords ? ffl_get_saved_route_data( $post_id, $station, $coords ) : null;
    $error   = trim( (string) get_post_meta( $post_id, '_ffl_route_last_error', true ) );
    $error_time = trim( (string) get_post_meta( $post_id, '_ffl_route_last_error_time', true ) );
    $air = '';
    if ( $station && $coords ) {
        $air = round( ffl_haversine_distance_km( $station['lat'], $station['lon'], $coords['lat'], $coords['lon'] ), 1 );
    }
    ob_start();
    ?>
    <div class="ffl-single-route-status__grid">
      <div><span><?php echo esc_html( ffl_lang( 'Luftlinie', 'Straight-line distance' ) ); ?></span><strong><?php echo '' !== (string) $air ? esc_html( number_format_i18n( $air, 1 ) . ' km' ) : '—'; ?></strong></div>
      <div><span><?php echo esc_html( ffl_lang( 'Fahrstrecke', 'Driving distance' ) ); ?></span><strong><?php echo $saved ? esc_html( $saved['road_km'] < 0.1 ? ffl_lang( 'unter 0,1 km', 'under 0.1 km' ) : number_format_i18n( $saved['road_km'], 1 ) . ' km' ) : '—'; ?></strong></div>
      <div><span><?php echo esc_html( ffl_lang( 'Fahrzeit', 'Driving time' ) ); ?></span><strong><?php echo $saved ? esc_html( $saved['duration_min'] <= 0 ? ffl_lang( 'unter 1 Minute', 'under 1 minute' ) : sprintf( ffl_lang( 'ca. %d Minuten', 'about %d minutes' ), $saved['duration_min'] ) ) : '—'; ?></strong></div>
    </div>
    <?php if ( $saved ) : ?>
      <p class="description"><strong><?php echo esc_html( ffl_lang( 'Zuletzt berechnet:', 'Last calculated:' ) ); ?></strong> <?php echo esc_html( $saved['calculated_at'] ?: '—' ); ?> · <?php echo esc_html( $saved['provider'] ?: 'OSRM / OpenStreetMap' ); ?></p>
    <?php elseif ( $error ) : ?>
      <div class="notice notice-error inline"><p><strong><?php echo esc_html( ffl_lang( 'Letzter Fehler:', 'Last error:' ) ); ?></strong> <?php echo esc_html( $error ); ?><?php echo $error_time ? ' · ' . esc_html( $error_time ) : ''; ?></p></div>
    <?php else : ?>
      <p class="description"><?php echo esc_html( ffl_lang( 'Für diesen Einsatz sind noch keine Fahrstrecke und Fahrzeit gespeichert.', 'No driving distance or time is stored for this incident yet.' ) ); ?></p>
    <?php endif; ?>
    <details class="ffl-single-route-details">
      <summary><?php echo esc_html( ffl_lang( 'Verwendete Koordinaten anzeigen', 'Show coordinates used' ) ); ?></summary>
      <p><strong><?php echo esc_html( ffl_lang( 'Start:', 'Start:' ) ); ?></strong> <?php echo $station ? esc_html( $station['name'] . ' · ' . $station['lat'] . ', ' . $station['lon'] ) : esc_html( ffl_lang( 'Feuerwehrhaus-Koordinaten fehlen', 'Fire station coordinates are missing' ) ); ?><br>
      <strong><?php echo esc_html( ffl_lang( 'Ziel:', 'Destination:' ) ); ?></strong> <?php echo $coords ? esc_html( $coords['lat'] . ', ' . $coords['lon'] ) : esc_html( ffl_lang( 'Einsatzkoordinaten fehlen oder sind nicht öffentlich nutzbar', 'Incident coordinates are missing or not publicly usable' ) ); ?></p>
    </details>
    <?php
    return (string) ob_get_clean();
}

function ffl_render_single_route_admin_panel( $post_id ) {
    $post_id = absint( $post_id );
    if ( ! $post_id ) {
        return;
    }
    $nonce = wp_create_nonce( 'ffl_distance_routing' );
    ?>
    <section class="ffl-admin-panel ffl-admin-panel--wide ffl-single-route-panel">
      <div class="ffl-admin-panel__heading"><span class="dashicons dashicons-location-alt"></span><div><h3><?php echo esc_html( ffl_lang( 'Fahrstrecke und Fahrzeit', 'Driving distance and time' ) ); ?></h3><p><?php echo esc_html( ffl_lang( 'Berechnet die Straßenroute vom hinterlegten Feuerwehrhaus zur Einsatzstelle. Die Werte werden im Einsatz gespeichert.', 'Calculates the road route from the configured fire station to the incident location. Values are stored with the incident.' ) ); ?></p></div></div>
      <div id="ffl-single-route-status"><?php echo ffl_route_admin_status_html( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
      <div class="ffl-single-route-actions">
        <button type="button" class="button button-primary" id="ffl-single-route-calculate" data-post-id="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( get_post_meta( $post_id, '_ffl_route_road_km', true ) !== '' ? ffl_lang( 'Neu berechnen', 'Recalculate' ) : ffl_lang( 'Fahrstrecke jetzt berechnen', 'Calculate driving distance now' ) ); ?></button>
        <button type="button" class="button" id="ffl-single-route-delete" data-post-id="<?php echo esc_attr( $post_id ); ?>"><?php echo esc_html( ffl_lang( 'Gespeicherte Werte löschen', 'Delete stored values' ) ); ?></button>
        <span id="ffl-single-route-message" aria-live="polite"></span>
      </div>
    </section>
    <style>
      .ffl-single-route-status__grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin:12px 0}.ffl-single-route-status__grid>div{padding:12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:8px}.ffl-single-route-status__grid span{display:block;color:#50575e;font-size:12px;margin-bottom:3px}.ffl-single-route-status__grid strong{font-size:16px}.ffl-single-route-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:12px}.ffl-single-route-details{margin-top:10px}.ffl-single-route-details summary{cursor:pointer;font-weight:600}@media(max-width:600px){.ffl-single-route-status__grid{grid-template-columns:1fr}.ffl-single-route-actions .button{width:100%}#ffl-single-route-message{display:block;width:100%}}
    </style>
    <script>
    (function(){
      const calc=document.getElementById('ffl-single-route-calculate');
      const del=document.getElementById('ffl-single-route-delete');
      const status=document.getElementById('ffl-single-route-status');
      const message=document.getElementById('ffl-single-route-message');
      if(!calc||!del||!status)return;
      const nonce=<?php echo wp_json_encode( $nonce ); ?>;
      const ajaxUrl=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
      const wait=<?php echo wp_json_encode( ffl_lang( 'Bitte warten …', 'Please wait …' ) ); ?>;
      const failed=<?php echo wp_json_encode( ffl_lang( 'Anfrage fehlgeschlagen.', 'Request failed.' ) ); ?>;
      const request=(action,force)=>{
        calc.disabled=true;del.disabled=true;message.textContent=wait;
        fetch(ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:new URLSearchParams({action,nonce,post_id:calc.dataset.postId,force:force?1:0})})
          .then(r=>r.json()).then(r=>{if(!r.success)throw new Error((r.data&&r.data.message)||failed);if(r.data&&r.data.html)status.innerHTML=r.data.html;message.textContent=(r.data&&r.data.message)||'';if(action==='ffl_route_calculate')calc.textContent=<?php echo wp_json_encode( ffl_lang( 'Neu berechnen', 'Recalculate' ) ); ?>;})
          .catch(e=>{message.textContent=e.message||failed;})
          .finally(()=>{calc.disabled=false;del.disabled=false;});
      };
      calc.addEventListener('click',()=>request('ffl_route_calculate',true));
      del.addEventListener('click',()=>request('ffl_route_delete_single',false));
    })();
    </script>
    <?php
}

function ffl_render_distance_settings_modal() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'ffl_einsatz' !== $screen->post_type || 'ffl_einsatz_einstellungen' !== sanitize_key( $_GET['page'] ?? '' ) ) {
        return;
    }
    $options = ffl_distance_mode_options();
    $mode    = ffl_get_distance_mode();
    $nonce   = wp_create_nonce( 'ffl_distance_routing' );
    ?>
    <div id="ffl-distance-modal" class="ffl-distance-modal" hidden>
      <div class="ffl-distance-modal__backdrop" data-ffl-distance-close></div>
      <section class="ffl-distance-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ffl-distance-title">
        <button type="button" class="ffl-distance-modal__close" data-ffl-distance-close aria-label="<?php echo esc_attr( ffl_lang( 'Schließen', 'Close' ) ); ?>">×</button>
        <h2 id="ffl-distance-title"><?php echo esc_html( ffl_lang( 'Entfernung zum Einsatzort', 'Distance to Incident Location' ) ); ?></h2>
        <p><?php echo esc_html( ffl_lang( 'Lege fest, welche Entfernungsangaben im Einsatzbericht erscheinen. Fahrstrecke und Fahrzeit werden einmalig über OSRM auf Basis von OpenStreetMap-Daten berechnet und anschließend im Einsatz gespeichert.', 'Choose which distance information appears in incident reports. Driving distance and time are calculated once through OSRM using OpenStreetMap data and then stored with the incident.' ) ); ?></p>
        <label class="ffl-distance-modal__label" for="ffl-distance-mode-modal"><?php echo esc_html( ffl_lang( 'Anzeige im Einsatzbericht', 'Display in incident report' ) ); ?></label>
        <select id="ffl-distance-mode-modal" name="ffl_distance_mode" form="ffl-settings-form">
          <?php foreach ( $options as $value => $label ) : ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?>
        </select>
        <div class="ffl-distance-provider"><strong><?php echo esc_html( ffl_lang( 'Routinganbieter:', 'Routing provider:' ) ); ?></strong> OSRM / OpenStreetMap</div>

        <div class="ffl-distance-batch">
          <h3><?php echo esc_html( ffl_lang( 'Alle Einsätze aktualisieren', 'Update all incidents' ) ); ?></h3>
          <p><?php echo esc_html( ffl_lang( 'Veröffentlichte Einsätze werden einzeln verarbeitet, damit kein Server-Timeout entsteht.', 'Published incidents are processed one at a time to prevent server timeouts.' ) ); ?></p>
          <div class="ffl-distance-batch__actions">
            <button type="button" class="button button-primary ffl-route-start" data-mode="missing"><?php echo esc_html( ffl_lang( 'Nur fehlende berechnen', 'Calculate missing only' ) ); ?></button>
            <button type="button" class="button ffl-route-start" data-mode="all"><?php echo esc_html( ffl_lang( 'Alle neu berechnen', 'Recalculate all' ) ); ?></button>
            <button type="button" class="button ffl-route-start" data-mode="errors"><?php echo esc_html( ffl_lang( 'Fehler erneut versuchen', 'Retry errors' ) ); ?></button>
          </div>
          <div id="ffl-route-runtime" class="ffl-route-runtime" hidden>
            <progress id="ffl-route-progress" value="0" max="1"></progress>
            <p id="ffl-route-progress-text" aria-live="polite"></p>
            <p id="ffl-route-current" class="description"></p>
            <div><button type="button" class="button" id="ffl-route-pause"><?php echo esc_html( ffl_lang( 'Pausieren', 'Pause' ) ); ?></button> <button type="button" class="button" id="ffl-route-resume" disabled><?php echo esc_html( ffl_lang( 'Fortsetzen', 'Resume' ) ); ?></button> <button type="button" class="button" id="ffl-route-stop"><?php echo esc_html( ffl_lang( 'Beenden', 'Stop' ) ); ?></button></div>
          </div>
          <div id="ffl-route-errors" class="ffl-route-errors" hidden></div>
        </div>
        <div class="ffl-distance-modal__footer">
          <button type="submit" form="ffl-settings-form" class="button button-primary"><?php echo esc_html( ffl_lang( 'Auswahl speichern', 'Save selection' ) ); ?></button>
          <button type="button" class="button" data-ffl-distance-close><?php echo esc_html( ffl_lang( 'Schließen', 'Close' ) ); ?></button>
        </div>
      </section>
    </div>
    <style>
      .ffl-distance-modal{position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px}.ffl-distance-modal[hidden]{display:none}.ffl-distance-modal__backdrop{position:absolute;inset:0;background:rgba(20,27,38,.65)}.ffl-distance-modal__dialog{position:relative;width:min(720px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:14px;padding:26px;box-shadow:0 24px 70px rgba(0,0,0,.28)}.ffl-distance-modal__close{position:absolute;right:14px;top:10px;border:0;background:transparent;font-size:30px;cursor:pointer}.ffl-distance-modal__label{display:block;font-weight:700;margin:18px 0 7px}.ffl-distance-modal select{width:100%;max-width:none}.ffl-distance-provider{margin:12px 0;padding:11px 13px;background:#f0f6fc;border-radius:8px}.ffl-distance-batch{margin-top:22px;padding-top:18px;border-top:1px solid #dcdcde}.ffl-distance-batch__actions{display:flex;flex-wrap:wrap;gap:8px}.ffl-route-runtime{margin-top:16px;padding:14px;background:#f6f7f7;border-radius:9px}.ffl-route-runtime progress{width:100%;height:18px}.ffl-route-errors{margin-top:12px;padding:12px;background:#fcf0f1;border-left:4px solid #d63638}.ffl-distance-modal__footer{display:flex;gap:8px;justify-content:flex-end;margin-top:22px}.ffl-distance-summary{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.ffl-distance-summary strong{padding:5px 10px;background:#f0f6fc;border-radius:999px}@media(max-width:600px){.ffl-distance-modal{padding:8px;align-items:flex-end}.ffl-distance-modal__dialog{padding:20px 16px;border-radius:14px 14px 0 0;max-height:94vh}.ffl-distance-batch__actions .button{width:100%}.ffl-distance-modal__footer{flex-direction:column}.ffl-distance-modal__footer .button{width:100%}}
    </style>
    <script>
    (function(){
      const modal=document.getElementById('ffl-distance-modal');
      const open=document.getElementById('ffl-distance-open');
      if(!modal||!open)return;
      const nonce=<?php echo wp_json_encode( $nonce ); ?>;
      const ajaxUrl=<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
      const i18n={
        processed:<?php echo wp_json_encode( ffl_lang( 'verarbeitet', 'processed' ) ); ?>,
        success:<?php echo wp_json_encode( ffl_lang( 'erfolgreich', 'successful' ) ); ?>,
        errors:<?php echo wp_json_encode( ffl_lang( 'Fehler', 'errors' ) ); ?>,
        none:<?php echo wp_json_encode( ffl_lang( 'Für diese Auswahl gibt es keine zu bearbeitenden Einsätze.', 'There are no incidents to process for this selection.' ) ); ?>,
        genericError:<?php echo wp_json_encode( ffl_lang( 'Fehler', 'Error' ) ); ?>,
        networkError:<?php echo wp_json_encode( ffl_lang( 'Netzwerkfehler', 'Network error' ) ); ?>
      };
      const close=()=>{modal.hidden=true;document.body.style.overflow=''};
      open.addEventListener('click',()=>{modal.hidden=false;document.body.style.overflow='hidden'});
      modal.querySelectorAll('[data-ffl-distance-close]').forEach(el=>el.addEventListener('click',close));
      document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)close()});
      const runtime=document.getElementById('ffl-route-runtime'), progress=document.getElementById('ffl-route-progress'), text=document.getElementById('ffl-route-progress-text'), current=document.getElementById('ffl-route-current'), errorsBox=document.getElementById('ffl-route-errors'), pause=document.getElementById('ffl-route-pause'), resume=document.getElementById('ffl-route-resume'), stop=document.getElementById('ffl-route-stop');
      let state={ids:[],index:0,success:0,errors:[],running:false,paused:false,stopped:false,force:false};
      const request=(data)=>fetch(ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:new URLSearchParams(data)}).then(r=>r.json());
      const esc=(value)=>String(value??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));
      function render(){progress.max=Math.max(1,state.ids.length);progress.value=state.index;text.textContent=`${state.index} / ${state.ids.length} ${i18n.processed} · ${state.success} ${i18n.success} · ${state.errors.length} ${i18n.errors}`;if(state.errors.length){errorsBox.hidden=false;errorsBox.innerHTML='<strong>'+esc(i18n.errors)+':</strong><ul>'+state.errors.map(e=>{const label='#'+e.id+(e.title?' · '+e.title:'');const linked=e.url?'<a href="'+esc(e.url)+'">'+esc(label)+'</a>':esc(label);return '<li>'+linked+' — '+esc(e.message)+'</li>';}).join('')+'</ul>';}else errorsBox.hidden=true;}
      function next(){if(!state.running||state.paused||state.stopped)return;if(state.index>=state.ids.length){state.running=false;current.textContent='';render();return;}const id=state.ids[state.index];current.textContent='#'+id+' …';request({action:'ffl_route_calculate',nonce,post_id:id,force:state.force?1:0}).then(res=>{if(res.success)state.success++;else state.errors.push({id,title:(res.data&&res.data.title)||'',url:(res.data&&res.data.edit_url)||'',message:(res.data&&res.data.message)||i18n.genericError});}).catch(()=>state.errors.push({id,title:'',url:'',message:i18n.networkError})).finally(()=>{state.index++;render();setTimeout(next,1200);});}
      modal.querySelectorAll('.ffl-route-start').forEach(btn=>btn.addEventListener('click',()=>{const mode=btn.dataset.mode;runtime.hidden=false;errorsBox.hidden=true;state={ids:[],index:0,success:0,errors:[],running:true,paused:false,stopped:false,force:mode==='all'};pause.disabled=false;resume.disabled=true;request({action:'ffl_route_queue',nonce,mode}).then(res=>{if(!res.success)throw new Error((res.data&&res.data.message)||i18n.genericError);state.ids=res.data.ids||[];if(!state.ids.length){state.running=false;text.textContent=i18n.none;return;}render();next();}).catch(err=>{state.running=false;text.textContent=err.message;});}));
      pause.addEventListener('click',()=>{if(state.running){state.paused=true;pause.disabled=true;resume.disabled=false}});resume.addEventListener('click',()=>{if(state.running&&state.paused){state.paused=false;pause.disabled=false;resume.disabled=true;next()}});stop.addEventListener('click',()=>{state.stopped=true;state.running=false;current.textContent=''});
    })();
    </script>
    <?php
}
add_action( 'admin_footer', 'ffl_render_distance_settings_modal' );
