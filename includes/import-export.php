<?php
/**
 * Einsatzlyzer – vollständiger Import/Export mit Bildern.
 *
 * @package Einsatzlyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const FFL_IMPEX_SCHEMA_VERSION = 6;
const FFL_IMPEX_FORMAT         = 'einsatzlyzer-backup';
const FFL_IMPEX_BATCH_SIZE     = 4;
const FFL_IMPEX_MAX_LOG_ITEMS  = 250;

add_action( 'admin_enqueue_scripts', 'ffl_impex_enqueue_assets' );
add_action( 'admin_post_ffl_export_backup', 'ffl_impex_handle_export_backup' );
add_action( 'admin_post_ffl_export_csv', 'ffl_impex_handle_export_csv' );
add_action( 'admin_post_ffl_import_upload', 'ffl_impex_handle_import_upload' );
add_action( 'admin_post_ffl_import_start', 'ffl_impex_handle_import_start' );
add_action( 'admin_post_ffl_import_batch', 'ffl_impex_handle_import_batch' );
add_action( 'admin_post_ffl_import_cancel', 'ffl_impex_handle_import_cancel' );
add_action( 'admin_post_ffl_import_rollback', 'ffl_impex_handle_import_rollback' );
add_action( 'admin_post_ffl_import_cleanup', 'ffl_impex_handle_import_cleanup' );
add_action( 'admin_post_ffl_import_log', 'ffl_impex_handle_import_log' );
add_action( 'admin_init', 'ffl_impex_cleanup_abandoned_sessions', 50 );
add_filter( 'post_row_actions', 'ffl_impex_add_row_action', 20, 2 );
add_filter( 'bulk_actions-edit-ffl_einsatz', 'ffl_impex_add_bulk_action' );
add_filter( 'handle_bulk_actions-edit-ffl_einsatz', 'ffl_impex_handle_bulk_action', 10, 3 );

/**
 * Admin-Dateien nur auf der Import-/Export-Seite laden.
 */
function ffl_impex_enqueue_assets( $hook ) {
    if ( 'ffl_einsatz_page_ffl_einsatz_impex' !== $hook ) {
        return;
    }

    wp_enqueue_style(
        'ffl-einsatzlyzer-impex-admin',
        FFL_EINSATZLYZER_URL . 'css/einsatzlyzer-impex-admin.css',
        array(),
        FFL_EINSATZLYZER_VERSION
    );
    wp_enqueue_script(
        'ffl-einsatzlyzer-impex-admin',
        FFL_EINSATZLYZER_URL . 'js/einsatzlyzer-impex-admin.js',
        array(),
        FFL_EINSATZLYZER_VERSION,
        true
    );
}

/**
 * Export eines einzelnen Einsatzes direkt aus der Beitragsliste.
 */
function ffl_impex_add_row_action( $actions, $post ) {
    if ( ! $post instanceof WP_Post || 'ffl_einsatz' !== $post->post_type || ! current_user_can( 'manage_options' ) ) {
        return $actions;
    }

    $url = wp_nonce_url(
        add_query_arg(
            array(
                'action'   => 'ffl_export_backup',
                'post_ids' => (int) $post->ID,
            ),
            admin_url( 'admin-post.php' )
        ),
        'ffl_export_backup'
    );
    $actions['ffl_export_backup'] = '<a href="' . esc_url( $url ) . '">Vollständig exportieren</a>';
    return $actions;
}

function ffl_impex_add_bulk_action( $actions ) {
    if ( current_user_can( 'manage_options' ) ) {
        $actions['ffl_export_selected'] = 'Vollständig exportieren';
    }
    return $actions;
}

function ffl_impex_handle_bulk_action( $redirect_to, $action, $post_ids ) {
    if ( 'ffl_export_selected' !== $action || empty( $post_ids ) ) {
        return $redirect_to;
    }

    return wp_nonce_url(
        add_query_arg(
            array(
                'action'   => 'ffl_export_backup',
                'post_ids' => implode( ',', array_map( 'absint', $post_ids ) ),
            ),
            admin_url( 'admin-post.php' )
        ),
        'ffl_export_backup'
    );
}

/**
 * Liefert alle exportierbaren Einsatz-Metafelder inklusive alter Linkfelder.
 */
function ffl_impex_export_meta( $post_id ) {
    $all    = get_post_meta( $post_id );
    $result = array();

    foreach ( $all as $key => $values ) {
        if ( 0 !== strpos( $key, '_ffl_' ) && '_wp_old_slug' !== $key ) {
            continue;
        }
        $result[ $key ] = array_map( 'maybe_unserialize', (array) $values );
    }

    return $result;
}

/**
 * Alle dauerhaften Einsatzlyzer-Einstellungen exportieren.
 * Laufzeit-, Import-Sitzungs- und reine Hinweiswerte werden bewusst ausgelassen.
 */
/**
 * Dokumentiert die seit Schema-Version 3 ausdrücklich abgesicherten Wetterdaten.
 * Die Liste wird in das Manifest geschrieben und dient Importen sowie Tests als
 * eindeutiger Vertrag für den vollständigen Sicherungsumfang.
 */
function ffl_impex_weather_backup_contract() {
    return array(
        'settings' => array(
            'ffl_station_name',
            'ffl_station_lat',
            'ffl_station_lon',
        ),
        'incident_meta' => array(
            '_ffl_lat',
            '_ffl_lon',
            '_ffl_weather_data',
            '_ffl_weather_source',
            '_ffl_weather_last_error',
            '_ffl_weather_last_error_time',
        ),
        'weather_payload_fields' => array(
            'time',
            'temperature',
            'precipitation',
            'weather_code',
            'wind_speed',
            'wind_direction',
            'wind_gusts',
            'latitude',
            'longitude',
            'fetched_at',
            'coordinate_source',
            'coordinate_label',
            'request_url',
            'timezone',
            'model_note',
        ),
    );
}


/**
 * Dokumentiert die seit Schema-Version 6 ausdrücklich abgesicherten
 * Entfernungs- und Routingdaten. Die vollständige Sicherung enthält damit
 * sowohl die globale Anzeigeeinstellung als auch alle gespeicherten Werte,
 * Koordinaten, Zeitstempel und Fehler je Einsatz.
 */
function ffl_impex_routing_backup_contract() {
    return array(
        'settings' => array(
            'ffl_distance_mode',
            'ffl_station_name',
            'ffl_station_lat',
            'ffl_station_lon',
        ),
        'incident_meta' => array(
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
        ),
        'providers' => array( 'OSRM / OpenStreetMap' ),
    );
}

function ffl_impex_export_plugin_settings() {
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'ffl\\_%' ESCAPE '\\\\'"
    );
    $excluded = array(
        'ffl_cache_notice_version',
        'ffl_show_cache_notice',
        'ffl_einsatzlyzer_version',
    );
    $settings = array();
    foreach ( (array) $rows as $row ) {
        $name = (string) $row->option_name;
        if ( in_array( $name, $excluded, true ) || 0 === strpos( $name, 'ffl_impex_session_' ) ) {
            continue;
        }
        $settings[ $name ] = maybe_unserialize( $row->option_value );
    }
    // Standardstandort immer ausdrücklich mitsichern – auch wenn eine Option
    // bislang noch nicht physisch in der WordPress-Datenbank angelegt wurde.
    $settings['ffl_station_name'] = get_option( 'ffl_station_name', '' );
    $settings['ffl_station_lat']  = get_option( 'ffl_station_lat', '' );
    $settings['ffl_station_lon']  = get_option( 'ffl_station_lon', '' );
    $settings['ffl_distance_mode'] = get_option( 'ffl_distance_mode', 'air' );

    ksort( $settings );
    return $settings;
}

/** Einstellungen aus einer vollständigen Sicherung wiederherstellen. */
function ffl_impex_import_plugin_settings( $state ) {
    if ( empty( $state['settings']['import_plugin_settings'] ) || empty( $state['dir'] ) ) {
        return 0;
    }
    $file = trailingslashit( $state['dir'] ) . 'einstellungen.json';
    if ( ! is_readable( $file ) ) {
        return 0;
    }
    $data = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $data ) ) {
        return 0;
    }
    $count = 0;
    foreach ( $data as $name => $value ) {
        if ( ! is_string( $name ) || 0 !== strpos( $name, 'ffl_' ) || 0 === strpos( $name, 'ffl_impex_session_' ) ) {
            continue;
        }
        if ( in_array( $name, array( 'ffl_einsatzlyzer_version', 'ffl_cache_notice_version', 'ffl_show_cache_notice' ), true ) ) {
            continue;
        }
        update_option( $name, $value, false );
        $count++;
    }
    return $count;
}

/**
 * Prüft nach dem Import, ob die ausdrücklich zugesicherten Wetter-Einstellungen
 * aus dem Sicherungsarchiv übernommen wurden. Alte Sicherungen bleiben erlaubt;
 * fehlende Angaben werden lediglich protokolliert.
 */
function ffl_impex_verify_weather_settings_restore( $state ) {
    if ( empty( $state['settings']['import_plugin_settings'] ) || empty( $state['dir'] ) ) {
        return;
    }

    $file = trailingslashit( $state['dir'] ) . 'einstellungen.json';
    if ( ! is_readable( $file ) ) {
        return;
    }

    $data = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $data ) ) {
        return;
    }

    foreach ( ffl_impex_weather_backup_contract()['settings'] as $option_name ) {
        if ( ! array_key_exists( $option_name, $data ) ) {
            ffl_impex_add_log(
                $state,
                'warning',
                sprintf( 'Ältere Sicherung: Die Wetter-Einstellung „%s“ war im Archiv nicht enthalten.', $option_name )
            );
            continue;
        }

        $restored = get_option( $option_name, null );
        if ( $restored != $data[ $option_name ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
            ffl_impex_add_log(
                $state,
                'warning',
                sprintf( 'Die Wetter-Einstellung „%s“ konnte nicht eindeutig bestätigt werden.', $option_name )
            );
        }
    }
}


/**
 * Prüft nach dem Import die ausdrücklich zugesicherten Routing-Einstellungen.
 * Alte Sicherungen bleiben erlaubt; fehlende Angaben werden nur protokolliert.
 */
function ffl_impex_verify_routing_settings_restore( $state ) {
    if ( empty( $state['settings']['import_plugin_settings'] ) || empty( $state['dir'] ) ) {
        return;
    }

    $file = trailingslashit( $state['dir'] ) . 'einstellungen.json';
    if ( ! is_readable( $file ) ) {
        return;
    }

    $data = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $data ) ) {
        return;
    }

    foreach ( ffl_impex_routing_backup_contract()['settings'] as $option_name ) {
        if ( ! array_key_exists( $option_name, $data ) ) {
            ffl_impex_add_log(
                $state,
                'warning',
                sprintf( 'Ältere Sicherung: Die Routing-Einstellung „%s“ war im Archiv nicht enthalten.', $option_name )
            );
            continue;
        }

        $restored = get_option( $option_name, null );
        if ( $restored != $data[ $option_name ] ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
            ffl_impex_add_log(
                $state,
                'warning',
                sprintf( 'Die Routing-Einstellung „%s“ konnte nicht eindeutig bestätigt werden.', $option_name )
            );
        }
    }
}

/** Kommentare eines Einsatzes vollständig sichern. */
function ffl_impex_export_comments( $post_id ) {
    $comments = get_comments( array( 'post_id' => $post_id, 'status' => 'all', 'orderby' => 'comment_ID', 'order' => 'ASC' ) );
    $result = array();
    $id_to_index = array();
    foreach ( $comments as $index => $comment ) {
        $id_to_index[ (int) $comment->comment_ID ] = $index + 1;
    }
    foreach ( $comments as $index => $comment ) {
        $meta = get_comment_meta( $comment->comment_ID );
        $result[] = array(
            'local_id'        => $index + 1,
            'parent_local_id' => isset( $id_to_index[ (int) $comment->comment_parent ] ) ? $id_to_index[ (int) $comment->comment_parent ] : 0,
            'author'          => $comment->comment_author,
            'author_email'    => $comment->comment_author_email,
            'author_url'      => $comment->comment_author_url,
            'author_ip'       => $comment->comment_author_IP,
            'date'            => $comment->comment_date,
            'date_gmt'        => $comment->comment_date_gmt,
            'content'         => $comment->comment_content,
            'karma'           => (int) $comment->comment_karma,
            'approved'        => $comment->comment_approved,
            'agent'           => $comment->comment_agent,
            'type'            => $comment->comment_type,
            'user_email'      => $comment->user_id ? (string) get_the_author_meta( 'user_email', $comment->user_id ) : '',
            'meta'            => array_map( function( $values ) { return array_map( 'maybe_unserialize', (array) $values ); }, $meta ),
        );
    }
    return $result;
}

function ffl_impex_import_comments( $post_id, $entry, &$state ) {
    if ( empty( $state['settings']['import_comments'] ) || empty( $entry['comments'] ) || ! is_array( $entry['comments'] ) ) {
        return;
    }
    if ( ! empty( $state['settings']['strategy'] ) && 'update' === $state['settings']['strategy'] ) {
        $existing = get_comments( array( 'post_id' => $post_id, 'status' => 'all', 'fields' => 'ids' ) );
        foreach ( (array) $existing as $comment_id ) {
            wp_delete_comment( $comment_id, true );
        }
    }
    $map = array();
    foreach ( $entry['comments'] as $item ) {
        if ( ! is_array( $item ) ) { continue; }
        $parent = ! empty( $item['parent_local_id'] ) && isset( $map[ (int) $item['parent_local_id'] ] ) ? $map[ (int) $item['parent_local_id'] ] : 0;
        $user_id = 0;
        if ( ! empty( $item['user_email'] ) ) {
            $user = get_user_by( 'email', sanitize_email( $item['user_email'] ) );
            $user_id = $user ? (int) $user->ID : 0;
        }
        $comment_id = wp_insert_comment( wp_slash( array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => sanitize_text_field( $item['author'] ?? '' ),
            'comment_author_email' => sanitize_email( $item['author_email'] ?? '' ),
            'comment_author_url'   => esc_url_raw( $item['author_url'] ?? '' ),
            'comment_author_IP'    => sanitize_text_field( $item['author_ip'] ?? '' ),
            'comment_date'         => sanitize_text_field( $item['date'] ?? '' ),
            'comment_date_gmt'     => sanitize_text_field( $item['date_gmt'] ?? '' ),
            'comment_content'      => wp_kses_post( $item['content'] ?? '' ),
            'comment_karma'        => (int) ( $item['karma'] ?? 0 ),
            'comment_approved'     => sanitize_text_field( $item['approved'] ?? '1' ),
            'comment_agent'        => sanitize_text_field( $item['agent'] ?? '' ),
            'comment_type'         => sanitize_key( $item['type'] ?? 'comment' ),
            'comment_parent'       => $parent,
            'user_id'              => $user_id,
        ) ) );
        if ( $comment_id ) {
            $map[ (int) ( $item['local_id'] ?? 0 ) ] = (int) $comment_id;
            foreach ( (array) ( $item['meta'] ?? array() ) as $key => $values ) {
                foreach ( (array) $values as $value ) { add_comment_meta( $comment_id, sanitize_key( $key ), $value ); }
            }
        }
    }
}

/**
 * Stabile Einsatzkennung. Sie bleibt beim Export/Import erhalten.
 */
function ffl_impex_get_or_create_uuid( $post_id ) {
    $uuid = trim( (string) get_post_meta( $post_id, '_ffl_export_uuid', true ) );
    if ( ! wp_is_uuid( $uuid ) ) {
        $uuid = wp_generate_uuid4();
        update_post_meta( $post_id, '_ffl_export_uuid', $uuid );
    }
    return $uuid;
}

function ffl_impex_get_or_create_attachment_uuid( $attachment_id ) {
    $uuid = trim( (string) get_post_meta( $attachment_id, '_ffl_attachment_uuid', true ) );
    if ( ! wp_is_uuid( $uuid ) ) {
        $uuid = wp_generate_uuid4();
        update_post_meta( $attachment_id, '_ffl_attachment_uuid', $uuid );
    }
    return $uuid;
}

/**
 * Lokale Bilder aus dem Einsatzinhalt ermitteln.
 */
function ffl_impex_content_image_map( $content ) {
    $result = array();
    if ( '' === trim( (string) $content ) ) {
        return $result;
    }

    if ( ! preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
        return $result;
    }

    foreach ( array_unique( $matches[1] ) as $url ) {
        $attachment_id = attachment_url_to_postid( $url );
        if ( ! $attachment_id ) {
            $clean_url     = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $url );
            $attachment_id = attachment_url_to_postid( $clean_url );
        }
        if ( ! $attachment_id ) {
            continue;
        }
        if ( ! isset( $result[ $attachment_id ] ) ) {
            $result[ $attachment_id ] = array();
        }
        $result[ $attachment_id ][] = esc_url_raw( $url );
    }

    return $result;
}

/**
 * Bildliste für einen Einsatz: Titelbild, Galerie und im Bericht eingebettete Bilder.
 */
function ffl_impex_collect_images( $post_id, $options ) {
    $images = array();

    if ( ! empty( $options['include_featured'] ) && has_post_thumbnail( $post_id ) ) {
        $id = (int) get_post_thumbnail_id( $post_id );
        $images[ $id ] = array(
            'roles'         => array( 'featured' ),
            'gallery_order' => null,
            'content_urls'  => array(),
        );
    }

    if ( ! empty( $options['include_gallery'] ) ) {
        $gallery = (string) get_post_meta( $post_id, '_ffl_gallery_ids', true );
        $ids     = array_values( array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) );
        foreach ( $ids as $order => $id ) {
            if ( ! isset( $images[ $id ] ) ) {
                $images[ $id ] = array(
                    'roles'         => array(),
                    'gallery_order' => $order,
                    'content_urls'  => array(),
                );
            }
            $images[ $id ]['roles'][]       = 'gallery';
            $images[ $id ]['gallery_order'] = $order;
        }
    }

    if ( ! empty( $options['include_content_images'] ) ) {
        $post        = get_post( $post_id );
        $content_map = $post ? ffl_impex_content_image_map( $post->post_content ) : array();
        foreach ( $content_map as $id => $urls ) {
            if ( ! isset( $images[ $id ] ) ) {
                $images[ $id ] = array(
                    'roles'         => array(),
                    'gallery_order' => null,
                    'content_urls'  => array(),
                );
            }
            $images[ $id ]['roles'][]      = 'content';
            $images[ $id ]['content_urls'] = array_values( array_unique( array_merge( $images[ $id ]['content_urls'], $urls ) ) );
        }
    }

    foreach ( $images as &$image ) {
        $image['roles'] = array_values( array_unique( $image['roles'] ) );
    }
    unset( $image );

    return $images;
}


/** IDs aus CSV, Array oder Einzelwert normalisieren. */
function ffl_impex_normalize_id_list( $value ) {
    if ( is_string( $value ) ) {
        $value = preg_split( '/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
    }
    return array_values( array_filter( array_map( 'absint', (array) $value ) ) );
}

/**
 * Alle relevanten Daten eines Einsatzes für JSON und ZIP aufbereiten.
 */
function ffl_impex_export_entry( $post_id, $options, &$zip_files, &$checksums, &$warnings ) {
    $post = get_post( $post_id );
    if ( ! $post || 'ffl_einsatz' !== $post->post_type ) {
        return null;
    }

    $uuid   = ffl_impex_get_or_create_uuid( $post_id );
    $author = get_userdata( $post->post_author );
    $terms  = get_the_terms( $post_id, 'ffl_einsatzart' );
    $tax    = array();
    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $tax[] = array(
                'name'        => $term->name,
                'slug'        => $term->slug,
                'description' => $term->description,
            );
        }
    }

    $entry = array(
        'uuid'      => $uuid,
        'legacy_id' => (int) $post_id,
        'post'      => array(
            'title'          => $post->post_title,
            'slug'           => $post->post_name,
            'content'        => $post->post_content,
            'excerpt'        => $post->post_excerpt,
            'status'         => $post->post_status,
            'date'           => $post->post_date,
            'date_gmt'       => $post->post_date_gmt,
            'modified'       => $post->post_modified,
            'modified_gmt'   => $post->post_modified_gmt,
            'author_email'   => $author ? $author->user_email : '',
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'menu_order'     => (int) $post->menu_order,
            'password'       => $post->post_password,
            'parent'         => (int) $post->post_parent,
        ),
        'taxonomy'  => array(
            'ffl_einsatzart' => $tax,
        ),
        'meta'      => ffl_impex_export_meta( $post_id ),
        'relationships' => array(
            'related_manual'  => array_values( array_filter( array_map( 'ffl_impex_get_or_create_uuid', ffl_impex_normalize_id_list( get_post_meta( $post_id, '_ffl_related_manual', true ) ) ) ) ),
            'related_exclude' => array_values( array_filter( array_map( 'ffl_impex_get_or_create_uuid', ffl_impex_normalize_id_list( get_post_meta( $post_id, '_ffl_related_exclude', true ) ) ) ) ),
            'vehicles'        => array_values( array_filter( array_map( static function( $vehicle_id ) {
                $uuid = get_post_meta( (int) $vehicle_id, '_ffl_vehicle_uuid', true );
                if ( ! $uuid && 'ffl_fahrzeug' === get_post_type( (int) $vehicle_id ) ) {
                    $uuid = wp_generate_uuid4();
                    update_post_meta( (int) $vehicle_id, '_ffl_vehicle_uuid', $uuid );
                }
                return $uuid;
            }, array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_vehicle_ids', true ) ) ) ) ) ) ),
        ),
        'comments'  => ffl_impex_export_comments( $post_id ),
        'images'    => array(),
        'fingerprint' => ffl_impex_entry_fingerprint_from_post( $post_id ),
    );

    $image_map = ffl_impex_collect_images( $post_id, $options );
    $counter   = 0;
    foreach ( $image_map as $attachment_id => $roles ) {
        $file = get_attached_file( $attachment_id );
        if ( ! $file || ! is_readable( $file ) ) {
            $warnings[] = sprintf( 'Bild #%d bei „%s“ fehlt auf dem Server.', $attachment_id, $post->post_title );
            continue;
        }

        $counter++;
        $filename     = wp_basename( $file );
        $safe_name    = sanitize_file_name( $filename );
        $archive_path = 'bilder/' . sanitize_file_name( $uuid ) . '/' . str_pad( (string) $counter, 3, '0', STR_PAD_LEFT ) . '-' . $safe_name;
        $hash         = hash_file( 'sha256', $file );
        update_post_meta( $attachment_id, '_ffl_file_sha256', $hash );
        $attachment   = get_post( $attachment_id );
        $attachment_meta = get_post_meta( $attachment_id );
        $public_meta  = array();
        foreach ( $attachment_meta as $meta_key => $meta_values ) {
            if ( in_array( $meta_key, array( '_wp_attached_file', '_wp_attachment_metadata' ), true ) ) {
                continue;
            }
            $public_meta[ $meta_key ] = array_map( 'maybe_unserialize', (array) $meta_values );
        }

        $zip_files[] = array(
            'source'  => $file,
            'archive' => $archive_path,
        );
        $checksums[ $archive_path ] = $hash;

        $entry['images'][] = array(
            'attachment_uuid' => ffl_impex_get_or_create_attachment_uuid( $attachment_id ),
            'legacy_id'       => (int) $attachment_id,
            'file'            => $archive_path,
            'filename'        => $filename,
            'mime'            => get_post_mime_type( $attachment_id ),
            'size'            => (int) filesize( $file ),
            'sha256'          => $hash,
            'source_url'      => wp_get_attachment_url( $attachment_id ),
            'content_urls'    => array_values( array_unique( $roles['content_urls'] ) ),
            'roles'           => array_values( array_unique( $roles['roles'] ) ),
            'gallery_order'   => is_null( $roles['gallery_order'] ) ? null : (int) $roles['gallery_order'],
            'title'           => $attachment ? $attachment->post_title : pathinfo( $filename, PATHINFO_FILENAME ),
            'caption'         => $attachment ? $attachment->post_excerpt : '',
            'description'     => $attachment ? $attachment->post_content : '',
            'alt'             => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
            'meta'            => $public_meta,
        );
    }

    return $entry;
}

function ffl_impex_entry_fingerprint_from_post( $post_id ) {
    $parts = array(
        strtolower( trim( wp_strip_all_tags( get_the_title( $post_id ) ) ) ),
        trim( (string) get_post_meta( $post_id, '_ffl_alarmzeit', true ) ),
        strtolower( trim( (string) get_post_meta( $post_id, '_ffl_einsatzort', true ) ) ),
        trim( (string) get_post_meta( $post_id, '_ffl_manuelle_einsatznummer', true ) ),
    );
    return hash( 'sha256', implode( '|', $parts ) );
}

function ffl_impex_entry_fingerprint( $entry ) {
    if ( ! empty( $entry['fingerprint'] ) && preg_match( '/^[a-f0-9]{64}$/', $entry['fingerprint'] ) ) {
        return $entry['fingerprint'];
    }
    $meta = isset( $entry['meta'] ) && is_array( $entry['meta'] ) ? $entry['meta'] : array();
    $one  = static function( $key ) use ( $meta ) {
        if ( empty( $meta[ $key ] ) ) {
            return '';
        }
        $value = is_array( $meta[ $key ] ) ? reset( $meta[ $key ] ) : $meta[ $key ];
        return is_scalar( $value ) ? (string) $value : '';
    };
    $parts = array(
        strtolower( trim( wp_strip_all_tags( isset( $entry['post']['title'] ) ? $entry['post']['title'] : '' ) ) ),
        trim( $one( '_ffl_alarmzeit' ) ),
        strtolower( trim( $one( '_ffl_einsatzort' ) ) ),
        trim( $one( '_ffl_manuelle_einsatznummer' ) ),
    );
    return hash( 'sha256', implode( '|', $parts ) );
}

/**
 * Einsatz-IDs anhand der Exportauswahl bestimmen.
 */
function ffl_impex_query_export_ids( $request ) {
    if ( ! empty( $request['post_ids'] ) ) {
        $ids = array_values( array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $request['post_ids'] ) ) ) ) ) );
        return get_posts(
            array(
                'post_type'      => 'ffl_einsatz',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'post__in'       => $ids,
                'orderby'        => 'post__in',
            )
        );
    }

    $statuses = isset( $request['statuses'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $request['statuses'] ) ) : array( 'publish', 'draft', 'pending', 'private', 'future' );
    $allowed  = array( 'publish', 'draft', 'pending', 'private', 'future' );
    $statuses = array_values( array_intersect( $statuses, $allowed ) );
    if ( empty( $statuses ) ) {
        $statuses = array( 'publish' );
    }

    $args = array(
        'post_type'      => 'ffl_einsatz',
        'post_status'    => $statuses,
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => array( 'meta_value' => 'DESC', 'ID' => 'DESC' ),
        'meta_key'       => '_ffl_alarmzeit',
        'no_found_rows'  => true,
    );

    $scope = isset( $request['scope'] ) ? sanitize_key( wp_unslash( $request['scope'] ) ) : 'all';
    if ( 'year' === $scope ) {
        $year = absint( isset( $request['year'] ) ? $request['year'] : 0 );
        if ( $year >= 1900 && $year <= 2200 ) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_ffl_alarmzeit',
                    'value'   => (string) $year,
                    'compare' => 'LIKE',
                ),
            );
        }
    } elseif ( 'range' === $scope ) {
        $from = isset( $request['date_from'] ) ? sanitize_text_field( wp_unslash( $request['date_from'] ) ) : '';
        $to   = isset( $request['date_to'] ) ? sanitize_text_field( wp_unslash( $request['date_to'] ) ) : '';
        $meta_query = array( 'relation' => 'AND' );
        if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
            $meta_query[] = array(
                'key'     => '_ffl_alarmzeit',
                'value'   => $from . 'T00:00',
                'compare' => '>=',
                'type'    => 'CHAR',
            );
        }
        if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
            $meta_query[] = array(
                'key'     => '_ffl_alarmzeit',
                'value'   => $to . 'T23:59',
                'compare' => '<=',
                'type'    => 'CHAR',
            );
        }
        if ( count( $meta_query ) > 1 ) {
            $args['meta_query'] = $meta_query;
        }
    }

    return get_posts( $args );
}

function ffl_impex_export_options_from_request( $request ) {
    return array(
        'include_featured'       => ! empty( $request['include_featured'] ),
        'include_gallery'        => ! empty( $request['include_gallery'] ),
        'include_content_images' => ! empty( $request['include_content_images'] ),
    );
}

/**
 * Backup-ZIP erzeugen. Bei $target_file leer wird eine temporäre Datei genutzt.
 */
function ffl_impex_build_backup( $post_ids, $options, $target_file = '' ) {
    if ( ! class_exists( 'ZipArchive' ) ) {
        return new WP_Error( 'ffl_no_zip', 'Die PHP-Erweiterung ZipArchive ist auf diesem Server nicht verfügbar.' );
    }

    $post_ids = array_values( array_filter( array_map( 'absint', (array) $post_ids ) ) );
    if ( empty( $post_ids ) ) {
        return new WP_Error( 'ffl_empty_export', 'Es wurden keine Einsätze für den Export gefunden.' );
    }

    if ( '' === $target_file ) {
        $target_file = wp_tempnam( 'einsatzlyzer-export.zip' );
    }
    if ( ! $target_file ) {
        return new WP_Error( 'ffl_temp_failed', 'Die temporäre Exportdatei konnte nicht erstellt werden.' );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $target_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
        return new WP_Error( 'ffl_zip_open', 'Die ZIP-Datei konnte nicht erstellt werden.' );
    }

    $zip_files = array();
    $checksums = array();
    $warnings  = array();
    $entries   = array();

    foreach ( $post_ids as $post_id ) {
        $entry = ffl_impex_export_entry( $post_id, $options, $zip_files, $checksums, $warnings );
        if ( $entry ) {
            $entries[] = $entry;
        }
    }

    $vehicle_registry = function_exists( 'ffl_export_vehicle_registry' ) ? ffl_export_vehicle_registry( $zip_files, $checksums, $warnings ) : array();
    $entries_json = wp_json_encode( $entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $vehicles_json = wp_json_encode( $vehicle_registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $settings_json = wp_json_encode( ffl_impex_export_plugin_settings(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    if ( false === $entries_json ) {
        $zip->close();
        return new WP_Error( 'ffl_json_failed', 'Die Einsatzdaten konnten nicht als JSON erstellt werden.' );
    }

    $manifest = array(
        'format'         => FFL_IMPEX_FORMAT,
        'schema_version' => FFL_IMPEX_SCHEMA_VERSION,
        'plugin_version' => FFL_EINSATZLYZER_VERSION,
        'created_at'     => gmdate( 'c' ),
        'site_url'       => home_url( '/' ),
        'site_name'      => get_bloginfo( 'name' ),
        'locale'         => get_locale(),
        'counts'         => array(
            'einsatz' => count( $entries ),
            'bilder'  => count( $zip_files ),
            'fahrzeuge' => count( $vehicle_registry ),
        ),
        'options'        => array(
            'featured_images' => ! empty( $options['include_featured'] ),
            'gallery_images'  => ! empty( $options['include_gallery'] ),
            'content_images'  => ! empty( $options['include_content_images'] ),
            'plugin_settings' => true,
            'comments'        => true,
            'relationships'   => true,
            'vehicle_registry' => true,
            'dispatch_import'  => true,
        ),
        'data_contract'   => array(
            'complete_incident_meta' => true,
            'complete_plugin_settings' => true,
            'weather' => ffl_impex_weather_backup_contract(),
            'routing' => ffl_impex_routing_backup_contract(),
            'vehicles' => array( 'callsign', 'designation', 'station', 'scope', 'active', 'featured_image' ),
            'dispatch' => array( '_ffl_dispatch_control_hash', '_ffl_dispatch_source_file', '_ffl_dispatch_imported_at' ),
        ),
        'warnings'       => $warnings,
    );

    $manifest_json = wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $zip->addFromString( 'manifest.json', $manifest_json );
    $zip->addFromString( 'einsaetze.json', $entries_json );
    $zip->addFromString( 'einstellungen.json', $settings_json );
    $zip->addFromString( 'fahrzeuge.json', $vehicles_json );
    $checksums['manifest.json'] = hash( 'sha256', $manifest_json );
    $checksums['einsaetze.json'] = hash( 'sha256', $entries_json );
    $checksums['einstellungen.json'] = hash( 'sha256', $settings_json );
    $checksums['fahrzeuge.json'] = hash( 'sha256', $vehicles_json );

    foreach ( $zip_files as $file ) {
        if ( ! $zip->addFile( $file['source'], $file['archive'] ) ) {
            $warnings[] = 'Eine Bilddatei konnte nicht in das ZIP aufgenommen werden: ' . $file['source'];
        }
    }

    $checksum_json = wp_json_encode( $checksums, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
    $zip->addFromString( 'pruefsummen.json', $checksum_json );
    $zip->close();

    return array(
        'path'      => $target_file,
        'entries'   => count( $entries ),
        'images'    => count( $zip_files ),
        'warnings'  => $warnings,
        'size'      => is_file( $target_file ) ? filesize( $target_file ) : 0,
    );
}

function ffl_impex_stream_file( $path, $filename, $content_type ) {
    if ( ! is_readable( $path ) ) {
        wp_die( 'Die Exportdatei konnte nicht gelesen werden.' );
    }
    nocache_headers();
    header( 'Content-Type: ' . $content_type );
    header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
    header( 'Content-Length: ' . filesize( $path ) );
    header( 'X-Content-Type-Options: nosniff' );
    while ( ob_get_level() ) {
        ob_end_clean();
    }
    readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile
}

function ffl_impex_handle_export_backup() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_export_backup' );

    @set_time_limit( 0 );
    $ids     = ffl_impex_query_export_ids( $_REQUEST );
    $options = ffl_impex_export_options_from_request( $_REQUEST );
    if ( empty( $_REQUEST['scope'] ) && empty( $_REQUEST['post_ids'] ) ) {
        $options = array(
            'include_featured'       => true,
            'include_gallery'        => true,
            'include_content_images' => true,
        );
    }
    if ( ! empty( $_REQUEST['post_ids'] ) ) {
        $options = array(
            'include_featured'       => true,
            'include_gallery'        => true,
            'include_content_images' => true,
        );
    }

    $result = ffl_impex_build_backup( $ids, $options );
    if ( is_wp_error( $result ) ) {
        wp_die( esc_html( $result->get_error_message() ) );
    }

    $filename = 'einsatzlyzer-sicherung-' . wp_date( 'Y-m-d-His' ) . '.zip';
    ffl_impex_stream_file( $result['path'], $filename, 'application/zip' );
    @unlink( $result['path'] );
    exit;
}

/** CSV für Excel: Semikolon und UTF-8-BOM. */
function ffl_impex_handle_export_csv() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_export_csv' );

    $ids = ffl_impex_query_export_ids( $_REQUEST );
    $filename = 'einsatzlyzer-daten-' . wp_date( 'Y-m-d-His' ) . '.csv';
    nocache_headers();
    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
    header( 'X-Content-Type-Options: nosniff' );
    while ( ob_get_level() ) {
        ob_end_clean();
    }

    $out = fopen( 'php://output', 'w' );
    fwrite( $out, "\xEF\xBB\xBF" );
    fputcsv(
        $out,
        array( 'UUID', 'ID', 'Status', 'Titel', 'URL', 'Alarmierung', 'Einsatzende', 'Einsatznummer', 'Alarmstichwort', 'Einsatzart', 'Einsatzort', 'Breitengrad', 'Längengrad', 'Kurzfassung', 'Einsatzbericht', 'Fahrzeuge', 'Einheiten', 'Organisationen', 'Einsatzkräfte', 'Bildquelle', 'Beitragsbild', 'Galeriebilder' ),
        ';'
    );

    foreach ( $ids as $post_id ) {
        $terms = wp_get_post_terms( $post_id, 'ffl_einsatzart', array( 'fields' => 'names' ) );
        $gallery_urls = array();
        $gallery = (string) get_post_meta( $post_id, '_ffl_gallery_ids', true );
        foreach ( array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) as $attachment_id ) {
            $url = wp_get_attachment_url( $attachment_id );
            if ( $url ) {
                $gallery_urls[] = $url;
            }
        }
        fputcsv(
            $out,
            array(
                ffl_impex_get_or_create_uuid( $post_id ),
                $post_id,
                get_post_status( $post_id ),
                get_the_title( $post_id ),
                get_permalink( $post_id ),
                get_post_meta( $post_id, '_ffl_alarmzeit', true ),
                get_post_meta( $post_id, '_ffl_endezeit', true ),
                get_post_meta( $post_id, '_ffl_manuelle_einsatznummer', true ),
                get_post_meta( $post_id, '_ffl_alarmstichwort', true ),
                is_wp_error( $terms ) ? '' : implode( ', ', $terms ),
                get_post_meta( $post_id, '_ffl_einsatzort', true ),
                get_post_meta( $post_id, '_ffl_lat', true ),
                get_post_meta( $post_id, '_ffl_lon', true ),
                get_post_meta( $post_id, '_ffl_kurzfassung', true ),
                ffl_get_report_raw( $post_id ),
                get_post_meta( $post_id, '_ffl_fahrzeuge', true ),
                get_post_meta( $post_id, '_ffl_einheiten', true ),
                get_post_meta( $post_id, '_ffl_organisationen', true ),
                get_post_meta( $post_id, '_ffl_einsatzkraefte', true ),
                get_post_meta( $post_id, '_ffl_bildquelle', true ),
                get_the_post_thumbnail_url( $post_id, 'full' ),
                implode( ' | ', $gallery_urls ),
            ),
            ';'
        );
    }
    fclose( $out );
    exit;
}

/**
 * Import-Sitzungen sicher in nicht automatisch geladenen Optionen speichern.
 */
function ffl_impex_session_key( $token ) {
    return 'ffl_impex_session_' . sanitize_key( $token );
}

function ffl_impex_session_get( $token ) {
    $state = get_option( ffl_impex_session_key( $token ), array() );
    if ( ! is_array( $state ) || empty( $state['owner'] ) || (int) $state['owner'] !== get_current_user_id() ) {
        return array();
    }
    return $state;
}

function ffl_impex_session_save( $token, $state ) {
    $key = ffl_impex_session_key( $token );
    if ( false === get_option( $key, false ) ) {
        add_option( $key, $state, '', false );
    } else {
        update_option( $key, $state, false );
    }
}

function ffl_impex_session_delete( $token ) {
    delete_option( ffl_impex_session_key( $token ) );
}

function ffl_impex_import_root() {
    $uploads = wp_upload_dir();
    $root    = trailingslashit( $uploads['basedir'] ) . 'einsatzlyzer-imports';
    if ( ! is_dir( $root ) ) {
        wp_mkdir_p( $root );
        @file_put_contents( trailingslashit( $root ) . 'index.php', "<?php\n// Silence is golden.\n" );
        @file_put_contents( trailingslashit( $root ) . '.htaccess', "Deny from all\n" );
    }
    return $root;
}

function ffl_impex_recursive_delete( $path ) {
    if ( ! $path || ! file_exists( $path ) ) {
        return;
    }
    if ( is_file( $path ) || is_link( $path ) ) {
        @unlink( $path );
        return;
    }
    $items = scandir( $path );
    if ( is_array( $items ) ) {
        foreach ( $items as $item ) {
            if ( '.' === $item || '..' === $item ) {
                continue;
            }
            ffl_impex_recursive_delete( trailingslashit( $path ) . $item );
        }
    }
    @rmdir( $path );
}

/**
 * ZIP sicher prüfen und ohne Pfadtraversal entpacken.
 */
function ffl_impex_validate_and_extract( $zip_path, $token ) {
    if ( ! class_exists( 'ZipArchive' ) ) {
        return new WP_Error( 'ffl_no_zip', 'Die PHP-Erweiterung ZipArchive ist nicht verfügbar.' );
    }

    $zip = new ZipArchive();
    if ( true !== $zip->open( $zip_path ) ) {
        return new WP_Error( 'ffl_invalid_zip', 'Die hochgeladene Datei ist kein lesbares ZIP-Archiv.' );
    }
    if ( $zip->numFiles < 2 || $zip->numFiles > 10000 ) {
        $zip->close();
        return new WP_Error( 'ffl_zip_count', 'Das ZIP-Archiv enthält eine unplausible Anzahl an Dateien.' );
    }

    $allowed_image_ext = array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif' );
    $total_size        = 0;
    $names             = array();
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $stat = $zip->statIndex( $i );
        $name = isset( $stat['name'] ) ? (string) $stat['name'] : '';
        $name = str_replace( '\\', '/', $name );
        if ( '' === $name || 0 === strpos( $name, '/' ) || false !== strpos( $name, '../' ) || false !== strpos( $name, '/..' ) ) {
            $zip->close();
            return new WP_Error( 'ffl_zip_path', 'Das ZIP enthält einen unsicheren Dateipfad.' );
        }
        if ( '/' === substr( $name, -1 ) ) {
            continue;
        }
        $allowed = in_array( $name, array( 'manifest.json', 'einsaetze.json', 'einstellungen.json', 'fahrzeuge.json', 'pruefsummen.json' ), true );
        if ( 0 === strpos( $name, 'bilder/' ) || 0 === strpos( $name, 'fahrzeugbilder/' ) ) {
            $allowed = in_array( strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ), $allowed_image_ext, true );
        }
        if ( ! $allowed ) {
            $zip->close();
            return new WP_Error( 'ffl_zip_filetype', 'Nicht erlaubte Datei im Archiv: ' . esc_html( $name ) );
        }
        $total_size += isset( $stat['size'] ) ? (int) $stat['size'] : 0;
        if ( $total_size > 5 * GB_IN_BYTES ) {
            $zip->close();
            return new WP_Error( 'ffl_zip_too_large', 'Der entpackte Inhalt wäre größer als 5 GB.' );
        }
        $names[] = $name;
    }

    foreach ( array( 'manifest.json', 'einsaetze.json' ) as $required ) {
        if ( ! in_array( $required, $names, true ) ) {
            $zip->close();
            return new WP_Error( 'ffl_zip_missing', 'Im Archiv fehlt ' . $required . '.' );
        }
    }

    $dir = trailingslashit( ffl_impex_import_root() ) . sanitize_key( $token );
    ffl_impex_recursive_delete( $dir );
    wp_mkdir_p( $dir );

    foreach ( $names as $name ) {
        $target = trailingslashit( $dir ) . $name;
        wp_mkdir_p( dirname( $target ) );
        $in  = $zip->getStream( $name );
        $out = fopen( $target, 'wb' );
        if ( ! $in || ! $out ) {
            if ( is_resource( $in ) ) {
                fclose( $in );
            }
            if ( is_resource( $out ) ) {
                fclose( $out );
            }
            $zip->close();
            ffl_impex_recursive_delete( $dir );
            return new WP_Error( 'ffl_extract_failed', 'Eine Datei konnte nicht sicher entpackt werden.' );
        }
        stream_copy_to_stream( $in, $out );
        fclose( $in );
        fclose( $out );
    }
    $zip->close();

    $manifest = json_decode( (string) file_get_contents( trailingslashit( $dir ) . 'manifest.json' ), true );
    $entries  = json_decode( (string) file_get_contents( trailingslashit( $dir ) . 'einsaetze.json' ), true );
    if ( ! is_array( $manifest ) || FFL_IMPEX_FORMAT !== ( isset( $manifest['format'] ) ? $manifest['format'] : '' ) ) {
        ffl_impex_recursive_delete( $dir );
        return new WP_Error( 'ffl_manifest_format', 'Das Archiv ist keine Einsatzlyzer-Sicherung.' );
    }
    if ( empty( $manifest['schema_version'] ) || (int) $manifest['schema_version'] > FFL_IMPEX_SCHEMA_VERSION ) {
        ffl_impex_recursive_delete( $dir );
        return new WP_Error( 'ffl_schema_newer', 'Diese Sicherung wurde mit einem neueren, nicht unterstützten Exportformat erstellt.' );
    }
    if ( ! is_array( $entries ) ) {
        ffl_impex_recursive_delete( $dir );
        return new WP_Error( 'ffl_entries_invalid', 'Die Einsatzdaten sind beschädigt.' );
    }

    $checksum_errors = array();
    $checksum_file   = trailingslashit( $dir ) . 'pruefsummen.json';
    if ( is_readable( $checksum_file ) ) {
        $checksums = json_decode( (string) file_get_contents( $checksum_file ), true );
        if ( is_array( $checksums ) ) {
            foreach ( $checksums as $name => $expected ) {
                $file = trailingslashit( $dir ) . ltrim( str_replace( '\\', '/', $name ), '/' );
                if ( ! is_readable( $file ) || ! hash_equals( strtolower( (string) $expected ), strtolower( hash_file( 'sha256', $file ) ) ) ) {
                    $checksum_errors[] = $name;
                }
            }
        }
    }
    if ( ! empty( $checksum_errors ) ) {
        ffl_impex_recursive_delete( $dir );
        return new WP_Error( 'ffl_checksum', 'Prüfsummenfehler bei: ' . implode( ', ', array_slice( $checksum_errors, 0, 8 ) ) );
    }

    return array(
        'dir'      => $dir,
        'manifest' => $manifest,
        'entries'  => $entries,
    );
}

function ffl_impex_find_existing( $entry ) {
    $uuid = isset( $entry['uuid'] ) ? sanitize_text_field( $entry['uuid'] ) : '';
    if ( wp_is_uuid( $uuid ) ) {
        $ids = get_posts(
            array(
                'post_type'      => 'ffl_einsatz',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_ffl_export_uuid',
                'meta_value'     => $uuid,
                'no_found_rows'  => true,
            )
        );
        if ( $ids ) {
            return (int) reset( $ids );
        }
    }

    $fingerprint = ffl_impex_entry_fingerprint( $entry );
    $ids = get_posts(
        array(
            'post_type'      => 'ffl_einsatz',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_key'       => '_ffl_import_fingerprint',
            'meta_value'     => $fingerprint,
            'no_found_rows'  => true,
        )
    );
    if ( $ids ) {
        return (int) reset( $ids );
    }

    global $wpdb;
    $title = trim( isset( $entry['post']['title'] ) ? wp_strip_all_tags( $entry['post']['title'] ) : '' );
    $meta  = isset( $entry['meta'] ) && is_array( $entry['meta'] ) ? $entry['meta'] : array();
    $alarm = ! empty( $meta['_ffl_alarmzeit'] ) ? (string) reset( $meta['_ffl_alarmzeit'] ) : '';
    $place = ! empty( $meta['_ffl_einsatzort'] ) ? (string) reset( $meta['_ffl_einsatzort'] ) : '';
    if ( '' !== $title && ( '' !== $alarm || '' !== $place ) ) {
        $candidate_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ffl_einsatz' AND post_title = %s AND post_status <> 'trash' LIMIT 10",
                $title
            )
        );
        foreach ( $candidate_ids as $candidate_id ) {
            if ( $alarm && $alarm !== (string) get_post_meta( $candidate_id, '_ffl_alarmzeit', true ) ) {
                continue;
            }
            if ( $place && strtolower( trim( $place ) ) !== strtolower( trim( (string) get_post_meta( $candidate_id, '_ffl_einsatzort', true ) ) ) ) {
                continue;
            }
            return (int) $candidate_id;
        }
    }

    return 0;
}

function ffl_impex_preview_entries( $entries ) {
    $preview = array();
    $counts  = array( 'new' => 0, 'duplicate' => 0, 'images' => 0, 'missing_images' => 0 );
    foreach ( $entries as $index => $entry ) {
        if ( ! is_array( $entry ) ) {
            continue;
        }
        $existing = ffl_impex_find_existing( $entry );
        $images   = isset( $entry['images'] ) && is_array( $entry['images'] ) ? count( $entry['images'] ) : 0;
        $counts['images'] += $images;
        if ( $existing ) {
            $counts['duplicate']++;
        } else {
            $counts['new']++;
        }
        if ( count( $preview ) < 30 ) {
            $preview[] = array(
                'index'      => $index,
                'title'      => isset( $entry['post']['title'] ) ? $entry['post']['title'] : 'Unbenannter Einsatz',
                'alarm'      => ! empty( $entry['meta']['_ffl_alarmzeit'] ) ? reset( $entry['meta']['_ffl_alarmzeit'] ) : '',
                'images'     => $images,
                'existing'   => $existing,
                'existing_title' => $existing ? get_the_title( $existing ) : '',
            );
        }
    }
    return array( 'preview' => $preview, 'counts' => $counts );
}

function ffl_impex_handle_import_upload() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_upload' );

    if ( empty( $_FILES['ffl_import_file']['tmp_name'] ) || ! is_uploaded_file( $_FILES['ffl_import_file']['tmp_name'] ) ) {
        ffl_impex_redirect_error( 'Bitte eine Einsatzlyzer-ZIP auswählen.' );
    }
    if ( ! empty( $_FILES['ffl_import_file']['size'] ) && (int) $_FILES['ffl_import_file']['size'] > 2 * GB_IN_BYTES ) {
        ffl_impex_redirect_error( 'Die hochgeladene ZIP-Datei ist größer als 2 GB.' );
    }

    $name = sanitize_file_name( wp_unslash( $_FILES['ffl_import_file']['name'] ) );
    if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
        ffl_impex_redirect_error( 'Es sind ausschließlich ZIP-Sicherungen erlaubt.' );
    }

    $token  = strtolower( wp_generate_password( 24, false, false ) );
    $result = ffl_impex_validate_and_extract( $_FILES['ffl_import_file']['tmp_name'], $token );
    if ( is_wp_error( $result ) ) {
        ffl_impex_redirect_error( $result->get_error_message() );
    }

    $analysis = ffl_impex_preview_entries( $result['entries'] );
    $state    = array(
        'owner'       => get_current_user_id(),
        'token'       => $token,
        'created_at'  => time(),
        'status'      => 'preview',
        'source_name' => $name,
        'dir'         => $result['dir'],
        'manifest'    => $result['manifest'],
        'total'       => count( $result['entries'] ),
        'preview'     => $analysis['preview'],
        'counts'      => $analysis['counts'],
        'position'    => 0,
        'stats'       => array(
            'created'         => 0,
            'updated'         => 0,
            'copied'          => 0,
            'skipped'         => 0,
            'images_created'  => 0,
            'images_reused'   => 0,
            'warnings'        => 0,
            'errors'          => 0,
        ),
        'logs'        => array(),
        'created_posts'       => array(),
        'created_attachments' => array(),
        'snapshots'           => array(),
        'backup'              => array(),
        'uuid_map'            => array(),
    );
    ffl_impex_session_save( $token, $state );
    wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
    exit;
}

function ffl_impex_redirect_error( $message ) {
    $key = 'ffl_impex_error_' . get_current_user_id();
    set_transient( $key, sanitize_text_field( $message ), 2 * MINUTE_IN_SECONDS );
    wp_safe_redirect( ffl_impex_admin_url() );
    exit;
}

function ffl_impex_admin_url( $args = array() ) {
    return add_query_arg( array_merge( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_einsatz_impex' ), $args ), admin_url( 'edit.php' ) );
}

function ffl_impex_read_entries( $state ) {
    $file = ! empty( $state['dir'] ) ? trailingslashit( $state['dir'] ) . 'einsaetze.json' : '';
    if ( ! is_readable( $file ) ) {
        return new WP_Error( 'ffl_entries_missing', 'Die temporären Importdaten wurden nicht gefunden.' );
    }
    $entries = json_decode( (string) file_get_contents( $file ), true );
    return is_array( $entries ) ? $entries : new WP_Error( 'ffl_entries_invalid', 'Die Importdaten sind beschädigt.' );
}

function ffl_impex_handle_import_start() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_start' );
    $token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( empty( $state ) || 'preview' !== $state['status'] ) {
        ffl_impex_redirect_error( 'Die Importvorschau ist nicht mehr verfügbar.' );
    }

    $strategy = isset( $_POST['strategy'] ) ? sanitize_key( wp_unslash( $_POST['strategy'] ) ) : 'skip';
    if ( ! in_array( $strategy, array( 'skip', 'update', 'copy' ), true ) ) {
        $strategy = 'skip';
    }
    $status_mode = isset( $_POST['status_mode'] ) ? sanitize_key( wp_unslash( $_POST['status_mode'] ) ) : 'preserve';
    if ( ! in_array( $status_mode, array( 'preserve', 'draft', 'publish' ), true ) ) {
        $status_mode = 'preserve';
    }

    $state['settings'] = array(
        'strategy'       => $strategy,
        'status_mode'    => $status_mode,
        'import_images'  => ! empty( $_POST['import_images'] ),
        'backup_before'  => ! empty( $_POST['backup_before'] ),
        'keep_slugs'     => ! empty( $_POST['keep_slugs'] ),
        'import_plugin_settings' => ! empty( $_POST['import_plugin_settings'] ),
        'import_comments'        => ! empty( $_POST['import_comments'] ),
    );
    $state['status']   = 'running';
    $state['position'] = 0;
    $state['started_at'] = time();

    if ( $state['settings']['backup_before'] ) {
        @set_time_limit( 0 );
        $ids = get_posts(
            array(
                'post_type'      => 'ffl_einsatz',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            )
        );
        if ( $ids ) {
            $uploads = wp_upload_dir();
            $backup_dir = trailingslashit( $uploads['basedir'] ) . 'einsatzlyzer-backups';
            wp_mkdir_p( $backup_dir );
            @file_put_contents( trailingslashit( $backup_dir ) . 'index.php', "<?php\n// Silence is golden.\n" );
            $backup_path = trailingslashit( $backup_dir ) . 'einsatzlyzer-vor-import-' . gmdate( 'Ymd-His' ) . '-' . substr( $token, 0, 8 ) . '.zip';
            $backup = ffl_impex_build_backup(
                $ids,
                array( 'include_featured' => true, 'include_gallery' => true, 'include_content_images' => true ),
                $backup_path
            );
            if ( is_wp_error( $backup ) ) {
                ffl_impex_add_log( $state, 'warning', 'Das automatische Vorab-Backup konnte nicht erstellt werden: ' . $backup->get_error_message() );
            } else {
                $state['backup'] = $backup;
                ffl_impex_add_log( $state, 'info', 'Vorab-Backup mit ' . $backup['entries'] . ' Einsätzen wurde erstellt.' );
            }
        }
    }

    ffl_impex_session_save( $token, $state );
    wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
    exit;
}

function ffl_impex_add_log( &$state, $type, $message ) {
    if ( ! isset( $state['logs'] ) || ! is_array( $state['logs'] ) ) {
        $state['logs'] = array();
    }
    $state['logs'][] = array(
        'time'    => current_time( 'H:i:s' ),
        'type'    => sanitize_key( $type ),
        'message' => wp_strip_all_tags( (string) $message ),
    );
    if ( count( $state['logs'] ) > FFL_IMPEX_MAX_LOG_ITEMS ) {
        $state['logs'] = array_slice( $state['logs'], -FFL_IMPEX_MAX_LOG_ITEMS );
    }
    if ( 'warning' === $type ) {
        $state['stats']['warnings']++;
    } elseif ( 'error' === $type ) {
        $state['stats']['errors']++;
    }
}

function ffl_impex_snapshot_post( $post_id ) {
    $post = get_post( $post_id, ARRAY_A );
    if ( ! $post ) {
        return array();
    }
    $meta = get_post_meta( $post_id );
    foreach ( $meta as $key => &$values ) {
        $values = array_map( 'maybe_unserialize', (array) $values );
    }
    unset( $values );
    $terms = wp_get_object_terms( $post_id, 'ffl_einsatzart', array( 'fields' => 'slugs' ) );
    return array(
        'post'  => $post,
        'meta'  => $meta,
        'terms' => is_wp_error( $terms ) ? array() : $terms,
    );
}

function ffl_impex_restore_snapshot( $post_id, $snapshot ) {
    if ( empty( $snapshot['post'] ) ) {
        return false;
    }
    $post_data       = $snapshot['post'];
    $post_data['ID'] = $post_id;
    unset( $post_data['filter'] );
    $result = wp_update_post( wp_slash( $post_data ), true );
    if ( is_wp_error( $result ) ) {
        return false;
    }
    $current_meta = get_post_meta( $post_id );
    foreach ( array_keys( $current_meta ) as $key ) {
        delete_post_meta( $post_id, $key );
    }
    foreach ( (array) $snapshot['meta'] as $key => $values ) {
        foreach ( (array) $values as $value ) {
            add_post_meta( $post_id, $key, $value );
        }
    }
    wp_set_object_terms( $post_id, (array) $snapshot['terms'], 'ffl_einsatzart', false );
    clean_post_cache( $post_id );
    return true;
}

function ffl_impex_sanitize_post_status( $status ) {
    $allowed = array( 'publish', 'draft', 'pending', 'private', 'future' );
    return in_array( $status, $allowed, true ) ? $status : 'draft';
}

function ffl_impex_resolve_author( $email ) {
    if ( $email && is_email( $email ) ) {
        $user = get_user_by( 'email', $email );
        if ( $user ) {
            return (int) $user->ID;
        }
    }
    return get_current_user_id();
}

function ffl_impex_import_meta( $post_id, $entry, $import_images, $is_update ) {
    $meta = isset( $entry['meta'] ) && is_array( $entry['meta'] ) ? $entry['meta'] : array();

    if ( $is_update ) {
        $current = get_post_meta( $post_id );
        foreach ( array_keys( $current ) as $key ) {
            if ( 0 !== strpos( $key, '_ffl_' ) && '_wp_old_slug' !== $key ) {
                continue;
            }
            if ( ! $import_images && '_ffl_gallery_ids' === $key ) {
                continue;
            }
            delete_post_meta( $post_id, $key );
        }
    }

    foreach ( $meta as $key => $values ) {
        if ( 0 !== strpos( $key, '_ffl_' ) && '_wp_old_slug' !== $key ) {
            continue;
        }
        if ( '_ffl_gallery_ids' === $key || '_ffl_export_uuid' === $key || '_ffl_import_fingerprint' === $key ) {
            continue;
        }
        delete_post_meta( $post_id, $key );
        foreach ( (array) $values as $value ) {
            add_post_meta( $post_id, $key, $value );
        }
    }
}

function ffl_impex_import_terms( $post_id, $entry ) {
    $term_slugs = array();
    $terms      = isset( $entry['taxonomy']['ffl_einsatzart'] ) && is_array( $entry['taxonomy']['ffl_einsatzart'] ) ? $entry['taxonomy']['ffl_einsatzart'] : array();
    foreach ( $terms as $term_data ) {
        $name = sanitize_text_field( isset( $term_data['name'] ) ? $term_data['name'] : '' );
        $slug = sanitize_title( isset( $term_data['slug'] ) ? $term_data['slug'] : $name );
        if ( '' === $name ) {
            continue;
        }
        $term = term_exists( $slug, 'ffl_einsatzart' );
        if ( ! $term ) {
            $term = wp_insert_term(
                $name,
                'ffl_einsatzart',
                array(
                    'slug'        => $slug,
                    'description' => sanitize_textarea_field( isset( $term_data['description'] ) ? $term_data['description'] : '' ),
                )
            );
        }
        if ( ! is_wp_error( $term ) ) {
            $term_slugs[] = $slug;
        }
    }
    wp_set_object_terms( $post_id, $term_slugs, 'ffl_einsatzart', false );
}

function ffl_impex_find_attachment_by_hash( $hash, $filename ) {
    if ( $hash ) {
        $ids = get_posts(
            array(
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_ffl_file_sha256',
                'meta_value'     => $hash,
                'no_found_rows'  => true,
            )
        );
        if ( $ids ) {
            return (int) reset( $ids );
        }
    }

    global $wpdb;
    $basename = wp_basename( $filename );
    if ( '' === $basename ) {
        return 0;
    }
    $like = '%' . $wpdb->esc_like( $basename );
    $candidates = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 25",
            $like
        )
    );
    foreach ( $candidates as $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        if ( $file && is_readable( $file ) && hash_equals( strtolower( $hash ), strtolower( hash_file( 'sha256', $file ) ) ) ) {
            update_post_meta( $attachment_id, '_ffl_file_sha256', $hash );
            return (int) $attachment_id;
        }
    }
    return 0;
}

function ffl_impex_import_attachment( $image, $post_id, $dir, &$state ) {
    $relative = isset( $image['file'] ) ? ltrim( str_replace( '\\', '/', $image['file'] ), '/' ) : '';
    $file     = $relative ? trailingslashit( $dir ) . $relative : '';
    if ( ! $file || ! is_readable( $file ) ) {
        ffl_impex_add_log( $state, 'warning', 'Bilddatei fehlt: ' . ( $relative ? $relative : 'unbekannt' ) );
        return 0;
    }

    $hash = isset( $image['sha256'] ) ? strtolower( sanitize_text_field( $image['sha256'] ) ) : hash_file( 'sha256', $file );
    if ( ! preg_match( '/^[a-f0-9]{64}$/', $hash ) || ! hash_equals( $hash, strtolower( hash_file( 'sha256', $file ) ) ) ) {
        ffl_impex_add_log( $state, 'warning', 'Bild mit fehlerhafter Prüfsumme übersprungen: ' . wp_basename( $file ) );
        return 0;
    }

    $filename = sanitize_file_name( isset( $image['filename'] ) ? $image['filename'] : wp_basename( $file ) );
    $existing = ffl_impex_find_attachment_by_hash( $hash, $filename );
    if ( $existing ) {
        $state['stats']['images_reused']++;
        return $existing;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam( $filename );
    if ( ! $tmp || ! copy( $file, $tmp ) ) {
        ffl_impex_add_log( $state, 'warning', 'Temporäre Bilddatei konnte nicht erstellt werden: ' . $filename );
        return 0;
    }
    $file_array = array(
        'name'     => $filename,
        'tmp_name' => $tmp,
    );
    $attachment_id = media_handle_sideload(
        $file_array,
        $post_id,
        sanitize_text_field( isset( $image['description'] ) ? $image['description'] : '' ),
        array(
            'post_title'   => sanitize_text_field( isset( $image['title'] ) ? $image['title'] : pathinfo( $filename, PATHINFO_FILENAME ) ),
            'post_excerpt' => sanitize_textarea_field( isset( $image['caption'] ) ? $image['caption'] : '' ),
        )
    );
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp );
        ffl_impex_add_log( $state, 'warning', 'Bild „' . $filename . '“ konnte nicht importiert werden: ' . $attachment_id->get_error_message() );
        return 0;
    }

    update_post_meta( $attachment_id, '_ffl_file_sha256', $hash );
    if ( ! empty( $image['attachment_uuid'] ) && wp_is_uuid( $image['attachment_uuid'] ) ) {
        update_post_meta( $attachment_id, '_ffl_attachment_uuid', $image['attachment_uuid'] );
    } else {
        ffl_impex_get_or_create_attachment_uuid( $attachment_id );
    }
    update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( isset( $image['alt'] ) ? $image['alt'] : '' ) );
    if ( ! empty( $image['meta'] ) && is_array( $image['meta'] ) ) {
        foreach ( $image['meta'] as $key => $values ) {
            if ( in_array( $key, array( '_wp_attached_file', '_wp_attachment_metadata' ), true ) ) {
                continue;
            }
            delete_post_meta( $attachment_id, $key );
            foreach ( (array) $values as $value ) {
                add_post_meta( $attachment_id, $key, $value );
            }
        }
        update_post_meta( $attachment_id, '_ffl_file_sha256', $hash );
    }

    $state['stats']['images_created']++;
    $state['created_attachments'][] = (int) $attachment_id;
    return (int) $attachment_id;
}

function ffl_impex_import_images( $post_id, $entry, $state_dir, &$state, $is_update ) {
    $images = isset( $entry['images'] ) && is_array( $entry['images'] ) ? $entry['images'] : array();
    if ( empty( $images ) ) {
        if ( $is_update ) {
            delete_post_thumbnail( $post_id );
            delete_post_meta( $post_id, '_ffl_gallery_ids' );
        }
        return array();
    }

    $featured_id = 0;
    $gallery     = array();
    $replacements = array();

    foreach ( $images as $image ) {
        if ( ! is_array( $image ) ) {
            continue;
        }
        $attachment_id = ffl_impex_import_attachment( $image, $post_id, $state_dir, $state );
        if ( ! $attachment_id ) {
            continue;
        }
        $roles = isset( $image['roles'] ) ? (array) $image['roles'] : array();
        if ( in_array( 'featured', $roles, true ) && ! $featured_id ) {
            $featured_id = $attachment_id;
        }
        if ( in_array( 'gallery', $roles, true ) ) {
            $order = is_numeric( isset( $image['gallery_order'] ) ? $image['gallery_order'] : null ) ? (int) $image['gallery_order'] : count( $gallery );
            $gallery[ $order ] = $attachment_id;
        }
        $new_url = wp_get_attachment_url( $attachment_id );
        if ( $new_url ) {
            if ( ! empty( $image['source_url'] ) ) {
                $replacements[ (string) $image['source_url'] ] = $new_url;
            }
            foreach ( isset( $image['content_urls'] ) ? (array) $image['content_urls'] : array() as $old_url ) {
                $replacements[ (string) $old_url ] = $new_url;
            }
        }
    }

    if ( $featured_id ) {
        set_post_thumbnail( $post_id, $featured_id );
    } elseif ( $is_update ) {
        delete_post_thumbnail( $post_id );
    }
    if ( $gallery ) {
        ksort( $gallery, SORT_NUMERIC );
        update_post_meta( $post_id, '_ffl_gallery_ids', implode( ',', array_values( array_unique( $gallery ) ) ) );
    } elseif ( $is_update ) {
        delete_post_meta( $post_id, '_ffl_gallery_ids' );
    }

    return $replacements;
}

function ffl_impex_import_entry( $entry, &$state ) {
    if ( ! is_array( $entry ) || empty( $entry['post'] ) || ! is_array( $entry['post'] ) ) {
        ffl_impex_add_log( $state, 'warning', 'Ein ungültiger Datensatz wurde übersprungen.' );
        $state['stats']['skipped']++;
        return;
    }

    $title    = sanitize_text_field( isset( $entry['post']['title'] ) ? $entry['post']['title'] : '' );
    $existing = ffl_impex_find_existing( $entry );
    $strategy = $state['settings']['strategy'];
    if ( $existing && 'skip' === $strategy ) {
        $state['stats']['skipped']++;
        ffl_impex_add_log( $state, 'info', 'Vorhandener Einsatz übersprungen: ' . $title );
        return;
    }

    $is_update = $existing && 'update' === $strategy;
    $is_copy   = $existing && 'copy' === $strategy;
    if ( $is_update && empty( $state['snapshots'][ $existing ] ) ) {
        $state['snapshots'][ $existing ] = ffl_impex_snapshot_post( $existing );
    }

    $status = ffl_impex_sanitize_post_status( isset( $entry['post']['status'] ) ? $entry['post']['status'] : 'draft' );
    if ( 'draft' === $state['settings']['status_mode'] ) {
        $status = 'draft';
    } elseif ( 'publish' === $state['settings']['status_mode'] ) {
        $status = 'publish';
    }

    $post_data = array(
        'post_type'      => 'ffl_einsatz',
        'post_title'     => $title ? $title : 'Importierter Einsatz',
        'post_content'   => wp_kses_post( isset( $entry['post']['content'] ) ? (string) $entry['post']['content'] : '' ),
        'post_excerpt'   => sanitize_textarea_field( isset( $entry['post']['excerpt'] ) ? (string) $entry['post']['excerpt'] : '' ),
        'post_status'    => $status,
        'post_author'    => ffl_impex_resolve_author( isset( $entry['post']['author_email'] ) ? $entry['post']['author_email'] : '' ),
        'comment_status' => in_array( isset( $entry['post']['comment_status'] ) ? $entry['post']['comment_status'] : 'closed', array( 'open', 'closed' ), true ) ? $entry['post']['comment_status'] : 'closed',
        'ping_status'    => in_array( isset( $entry['post']['ping_status'] ) ? $entry['post']['ping_status'] : 'closed', array( 'open', 'closed' ), true ) ? $entry['post']['ping_status'] : 'closed',
        'menu_order'     => isset( $entry['post']['menu_order'] ) ? (int) $entry['post']['menu_order'] : 0,
        'post_password'  => (string) ( $entry['post']['password'] ?? '' ),
    );
    if ( ! empty( $entry['post']['date'] ) && false !== strtotime( $entry['post']['date'] ) ) {
        $post_data['post_date'] = $entry['post']['date'];
    }
    if ( ! empty( $entry['post']['date_gmt'] ) && false !== strtotime( $entry['post']['date_gmt'] ) ) {
        $post_data['post_date_gmt'] = $entry['post']['date_gmt'];
    }
    if ( ! empty( $entry['post']['slug'] ) && ! ( $is_update && ! empty( $state['settings']['keep_slugs'] ) ) ) {
        $post_data['post_name'] = sanitize_title( $entry['post']['slug'] );
    }

    if ( $is_update ) {
        $post_data['ID'] = $existing;
        $post_id = wp_update_post( wp_slash( $post_data ), true );
    } else {
        if ( $is_copy ) {
            $post_data['post_status'] = 'draft';
            $post_data['post_title'] .= ' (Kopie)';
            unset( $post_data['post_name'] );
        }
        $post_id = wp_insert_post( wp_slash( $post_data ), true );
    }

    if ( is_wp_error( $post_id ) ) {
        ffl_impex_add_log( $state, 'error', 'Einsatz „' . $title . '“ konnte nicht importiert werden: ' . $post_id->get_error_message() );
        return;
    }

    if ( ! $is_update ) {
        $state['created_posts'][] = (int) $post_id;
    }

    ffl_impex_import_meta( $post_id, $entry, $state['settings']['import_images'], $is_update );
    ffl_impex_import_terms( $post_id, $entry );

    $uuid = isset( $entry['uuid'] ) && wp_is_uuid( $entry['uuid'] ) ? $entry['uuid'] : wp_generate_uuid4();
    if ( $is_copy ) {
        update_post_meta( $post_id, '_ffl_import_source_uuid', $uuid );
        $uuid = wp_generate_uuid4();
    }
    update_post_meta( $post_id, '_ffl_export_uuid', $uuid );
    update_post_meta( $post_id, '_ffl_import_fingerprint', ffl_impex_entry_fingerprint( $entry ) );
    $state['uuid_map'][ $uuid ] = (int) $post_id;
    ffl_impex_import_comments( $post_id, $entry, $state );

    $replacements = array();
    if ( $state['settings']['import_images'] ) {
        $replacements = ffl_impex_import_images( $post_id, $entry, $state['dir'], $state, $is_update );
    }
    if ( $replacements ) {
        $content = (string) get_post_field( 'post_content', $post_id );
        $updated = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
        if ( $updated !== $content ) {
            wp_update_post( wp_slash( array( 'ID' => $post_id, 'post_content' => $updated ) ) );
        }
    }
    update_post_meta( $post_id, '_ffl_einsatzbericht', wp_kses_post( (string) get_post_field( 'post_content', $post_id ) ) );

    if ( $is_update ) {
        $state['stats']['updated']++;
        ffl_impex_add_log( $state, 'success', 'Aktualisiert: ' . $title );
    } elseif ( $is_copy ) {
        $state['stats']['copied']++;
        ffl_impex_add_log( $state, 'success', 'Als Entwurfskopie importiert: ' . $title );
    } else {
        $state['stats']['created']++;
        ffl_impex_add_log( $state, 'success', 'Neu importiert: ' . $title );
    }
}

function ffl_impex_resolve_relationships( $entries, &$state ) {
    $map = isset( $state['uuid_map'] ) && is_array( $state['uuid_map'] ) ? $state['uuid_map'] : array();
    foreach ( (array) $entries as $entry ) {
        $uuid = isset( $entry['uuid'] ) ? (string) $entry['uuid'] : '';
        if ( ! $uuid || empty( $map[ $uuid ] ) ) { continue; }
        $post_id = (int) $map[ $uuid ];
        foreach ( array( 'related_manual' => '_ffl_related_manual', 'related_exclude' => '_ffl_related_exclude' ) as $source => $meta_key ) {
            $ids = array();
            foreach ( (array) ( $entry['relationships'][ $source ] ?? array() ) as $related_uuid ) {
                if ( isset( $map[ $related_uuid ] ) ) { $ids[] = (int) $map[ $related_uuid ]; }
            }
            update_post_meta( $post_id, $meta_key, implode( ',', array_values( array_unique( $ids ) ) ) );
        }
        $vehicle_ids = array();
        $vehicle_map = isset( $state['vehicle_uuid_map'] ) && is_array( $state['vehicle_uuid_map'] ) ? $state['vehicle_uuid_map'] : array();
        foreach ( (array) ( $entry['relationships']['vehicles'] ?? array() ) as $vehicle_uuid ) {
            if ( isset( $vehicle_map[ $vehicle_uuid ] ) ) $vehicle_ids[] = (int) $vehicle_map[ $vehicle_uuid ];
        }
        if ( $vehicle_ids ) {
            update_post_meta( $post_id, '_ffl_vehicle_ids', implode( ',', array_values( array_unique( $vehicle_ids ) ) ) );
        }
    }
}

function ffl_impex_handle_import_batch() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_batch' );
    $token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( empty( $state ) || 'running' !== $state['status'] ) {
        ffl_impex_redirect_error( 'Der Import kann nicht fortgesetzt werden.' );
    }

    @set_time_limit( 60 );
    $entries = ffl_impex_read_entries( $state );
    if ( is_wp_error( $entries ) ) {
        $state['status'] = 'error';
        ffl_impex_add_log( $state, 'error', $entries->get_error_message() );
        ffl_impex_session_save( $token, $state );
        wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
        exit;
    }

    $start = (int) $state['position'];
    $end   = min( count( $entries ), $start + FFL_IMPEX_BATCH_SIZE );
    for ( $index = $start; $index < $end; $index++ ) {
        try {
            ffl_impex_import_entry( $entries[ $index ], $state );
        } catch ( Throwable $throwable ) {
            ffl_impex_add_log( $state, 'error', 'Unerwarteter Fehler bei Datensatz ' . ( $index + 1 ) . ': ' . $throwable->getMessage() );
            $state['status'] = 'error';
            break;
        }
        $state['position'] = $index + 1;
    }

    if ( 'running' === $state['status'] && $state['position'] >= count( $entries ) ) {
        $vehicle_count = function_exists( 'ffl_import_vehicle_registry_file' ) ? ffl_import_vehicle_registry_file( $state['dir'], $state ) : 0;
        if ( $vehicle_count ) {
            ffl_impex_add_log( $state, 'success', $vehicle_count . ' Fahrzeuge wurden wiederhergestellt.' );
        }
        ffl_impex_resolve_relationships( $entries, $state );
        $settings_count = ffl_impex_import_plugin_settings( $state );
        if ( $settings_count ) {
            ffl_impex_add_log( $state, 'success', $settings_count . ' Plugin-Einstellungen wurden wiederhergestellt.' );
        }
        ffl_impex_verify_weather_settings_restore( $state );
        ffl_impex_verify_routing_settings_restore( $state );
        $state['status']      = 'done';
        $state['finished_at'] = time();
        ffl_impex_add_log( $state, 'success', 'Import einschließlich aller Einsatzdaten, Fahrzeuge, Funkrufnamen, Verknüpfungen, Kommentare, Bilder und Einstellungen abgeschlossen.' );
        flush_rewrite_rules( false );
    }
    ffl_impex_session_save( $token, $state );
    wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
    exit;
}

function ffl_impex_handle_import_cancel() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_cancel' );
    $token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( $state ) {
        $state['status'] = 'cancelled';
        ffl_impex_add_log( $state, 'warning', 'Import wurde vom Benutzer angehalten.' );
        ffl_impex_session_save( $token, $state );
    }
    wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
    exit;
}

function ffl_impex_handle_import_rollback() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_rollback' );
    $token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( empty( $state ) ) {
        ffl_impex_redirect_error( 'Die Import-Sitzung wurde nicht gefunden.' );
    }

    @set_time_limit( 0 );
    foreach ( array_reverse( array_unique( array_map( 'absint', (array) $state['created_posts'] ) ) ) as $post_id ) {
        wp_delete_post( $post_id, true );
    }
    foreach ( (array) $state['snapshots'] as $post_id => $snapshot ) {
        ffl_impex_restore_snapshot( (int) $post_id, $snapshot );
    }
    foreach ( array_reverse( array_unique( array_map( 'absint', (array) $state['created_attachments'] ) ) ) as $attachment_id ) {
        wp_delete_attachment( $attachment_id, true );
    }
    $state['status'] = 'rolled_back';
    ffl_impex_add_log( $state, 'success', 'Alle durch diese Sitzung angelegten oder aktualisierten Inhalte wurden zurückgesetzt.' );
    ffl_impex_session_save( $token, $state );
    flush_rewrite_rules( false );
    wp_safe_redirect( ffl_impex_admin_url( array( 'ffl_import_session' => $token ) ) );
    exit;
}

function ffl_impex_handle_import_cleanup() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_cleanup' );
    $token = isset( $_POST['token'] ) ? sanitize_key( wp_unslash( $_POST['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( $state && ! empty( $state['dir'] ) ) {
        ffl_impex_recursive_delete( $state['dir'] );
    }
    ffl_impex_session_delete( $token );
    wp_safe_redirect( ffl_impex_admin_url() );
    exit;
}


function ffl_impex_handle_import_log() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Keine Berechtigung.' );
    }
    check_admin_referer( 'ffl_import_log' );
    $token = isset( $_GET['token'] ) ? sanitize_key( wp_unslash( $_GET['token'] ) ) : '';
    $state = ffl_impex_session_get( $token );
    if ( empty( $state ) ) {
        wp_die( 'Die Import-Sitzung wurde nicht gefunden.' );
    }

    $lines   = array();
    $lines[] = 'Einsatzlyzer Importprotokoll';
    $lines[] = 'Datei: ' . ( isset( $state['source_name'] ) ? $state['source_name'] : '' );
    $lines[] = 'Status: ' . ffl_impex_status_label( isset( $state['status'] ) ? $state['status'] : '' );
    $lines[] = 'Fortschritt: ' . (int) $state['position'] . ' von ' . (int) $state['total'];
    $lines[] = str_repeat( '-', 70 );
    foreach ( (array) $state['logs'] as $log ) {
        $lines[] = '[' . ( isset( $log['time'] ) ? $log['time'] : '' ) . '] [' . strtoupper( isset( $log['type'] ) ? $log['type'] : 'INFO' ) . '] ' . ( isset( $log['message'] ) ? $log['message'] : '' );
    }
    $content = implode( "\r\n", $lines ) . "\r\n";

    nocache_headers();
    header( 'Content-Type: text/plain; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="einsatzlyzer-importprotokoll-' . gmdate( 'Ymd-His' ) . '.txt"' );
    header( 'Content-Length: ' . strlen( $content ) );
    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit;
}

/** Verwaiste Sitzungen und entpackte Dateien nach drei Tagen entfernen. */
function ffl_impex_cleanup_abandoned_sessions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $last = (int) get_transient( 'ffl_impex_cleanup_ran' );
    if ( $last && ( time() - $last ) < 12 * HOUR_IN_SECONDS ) {
        return;
    }
    set_transient( 'ffl_impex_cleanup_ran', time(), 12 * HOUR_IN_SECONDS );

    global $wpdb;
    $like = $wpdb->esc_like( 'ffl_impex_session_' ) . '%';
    $rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );
    foreach ( (array) $rows as $row ) {
        $state = maybe_unserialize( $row->option_value );
        if ( ! is_array( $state ) || empty( $state['created_at'] ) || ( time() - (int) $state['created_at'] ) <= 3 * DAY_IN_SECONDS ) {
            continue;
        }
        if ( ! empty( $state['dir'] ) ) {
            ffl_impex_recursive_delete( $state['dir'] );
        }
        delete_option( $row->option_name );
    }

    $root = ffl_impex_import_root();
    foreach ( glob( trailingslashit( $root ) . '*' ) as $path ) {
        if ( is_dir( $path ) && filemtime( $path ) && ( time() - filemtime( $path ) ) > 3 * DAY_IN_SECONDS ) {
            ffl_impex_recursive_delete( $path );
        }
    }
}

function ffl_impex_backup_url( $backup ) {
    if ( empty( $backup['path'] ) ) {
        return '';
    }
    $uploads = wp_upload_dir();
    $path    = wp_normalize_path( $backup['path'] );
    $base    = wp_normalize_path( $uploads['basedir'] );
    if ( 0 !== strpos( $path, $base ) ) {
        return '';
    }
    return trailingslashit( $uploads['baseurl'] ) . ltrim( substr( $path, strlen( $base ) ), '/' );
}

/**
 * Moderne Import-/Export-Seite.
 */
function ffl_impex_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $token = isset( $_GET['ffl_import_session'] ) ? sanitize_key( wp_unslash( $_GET['ffl_import_session'] ) ) : '';
    $state = $token ? ffl_impex_session_get( $token ) : array();
    $error_key = 'ffl_impex_error_' . get_current_user_id();
    $error = get_transient( $error_key );
    if ( $error ) {
        delete_transient( $error_key );
    }
    $current_year = (int) wp_date( 'Y' );
    ?>
    <div class="wrap ffl-impex-wrap">
        <div class="ffl-impex-brand"><img src="<?php echo esc_url( FFL_EINSATZLYZER_URL . 'images/branding/einsatzlyzer-logo.png' ); ?>" alt="Einsatzlyzer"><div><h1>Einsätze sichern, übertragen und wiederherstellen</h1><p>Import und Export</p></div></div>
        <p class="ffl-impex-lead">Die vollständige ZIP-Sicherung enthält Einsatzdaten, Fahrzeuge, Funkrufnamen, Depeschen-Zuordnungen, Beitragsbilder, Galerien, eingebettete Bilder, Bildtexte und Prüfsummen. CSV ist zusätzlich für Excel-Auswertungen gedacht.</p>

        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
        <?php endif; ?>

        <?php if ( $state ) : ?>
            <?php ffl_impex_render_session( $state ); ?>
        <?php else : ?>
            <div class="ffl-impex-grid">
                <section class="ffl-impex-card ffl-impex-card--export">
                    <div class="ffl-impex-card__icon"><span class="dashicons dashicons-download"></span></div>
                    <div>
                        <h2>Vollständige Sicherung</h2>
                        <p>Erzeugt eine eigenständige ZIP-Datei mit allen Einsatzdaten, Fahrzeugen, Funkrufnamen, Einstellungen und den echten Originalbildern.</p>
                    </div>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-form">
                        <input type="hidden" name="action" value="ffl_export_backup">
                        <?php wp_nonce_field( 'ffl_export_backup' ); ?>
                        <?php ffl_impex_render_export_filters( $current_year ); ?>
                        <fieldset>
                            <legend>Bilder einschließen</legend>
                            <label><input type="checkbox" name="include_featured" value="1" checked> Beitragsbilder</label>
                            <label><input type="checkbox" name="include_gallery" value="1" checked> Galeriebilder</label>
                            <label><input type="checkbox" name="include_content_images" value="1" checked> Im Bericht eingebettete Bilder</label>
                        </fieldset>
                        <?php submit_button( 'Vollständige Sicherung erstellen', 'primary button-hero', 'submit', false ); ?>
                    </form>
                </section>

                <section class="ffl-impex-card ffl-impex-card--csv">
                    <div class="ffl-impex-card__icon"><span class="dashicons dashicons-media-spreadsheet"></span></div>
                    <div>
                        <h2>CSV für Excel</h2>
                        <p>Semikolon-getrennt, UTF-8 und mit allen Einsatzfeldern sowie Bild-URLs.</p>
                    </div>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-form">
                        <input type="hidden" name="action" value="ffl_export_csv">
                        <?php wp_nonce_field( 'ffl_export_csv' ); ?>
                        <?php ffl_impex_render_export_filters( $current_year ); ?>
                        <?php submit_button( 'CSV herunterladen', 'secondary', 'submit', false ); ?>
                    </form>
                </section>

                <section class="ffl-impex-card ffl-impex-card--import">
                    <div class="ffl-impex-card__icon"><span class="dashicons dashicons-upload"></span></div>
                    <div>
                        <h2>Einsatzlyzer-Sicherung importieren</h2>
                        <p>Das Archiv wird zuerst vollständig geprüft. Es werden noch keine Inhalte verändert.</p>
                    </div>
                    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-form">
                        <input type="hidden" name="action" value="ffl_import_upload">
                        <?php wp_nonce_field( 'ffl_import_upload' ); ?>
                        <label class="ffl-impex-file">
                            <span class="dashicons dashicons-portfolio"></span>
                            <strong>ZIP-Sicherung auswählen</strong>
                            <input type="file" name="ffl_import_file" accept=".zip,application/zip" required>
                            <small>Nur ZIP-Dateien, die mit Einsatzlyzer erstellt wurden.</small>
                        </label>
                        <?php submit_button( 'Archiv prüfen und Vorschau öffnen', 'primary', 'submit', false ); ?>
                    </form>
                </section>
            </div>

            <div class="ffl-impex-note">
                <span class="dashicons dashicons-shield"></span>
                <div><strong>Sicherheitsprinzip</strong><p>Importe laufen in kleinen Paketen. Doppelte Einsätze werden vorab erkannt, Bilder anhand ihrer SHA-256-Prüfsumme wiederverwendet und Änderungen können innerhalb der Sitzung zurückgesetzt werden.</p></div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function ffl_impex_render_export_filters( $current_year ) {
    ?>
    <fieldset class="ffl-impex-scope" data-ffl-scope>
        <legend>Auswahl</legend>
        <label><input type="radio" name="scope" value="all" checked> Alle Einsätze</label>
        <label><input type="radio" name="scope" value="year"> Jahr <input type="number" name="year" value="<?php echo esc_attr( $current_year ); ?>" min="1900" max="2200"></label>
        <label><input type="radio" name="scope" value="range"> Zeitraum</label>
        <div class="ffl-impex-date-range"><input type="date" name="date_from" aria-label="Von"> <span>bis</span> <input type="date" name="date_to" aria-label="Bis"></div>
    </fieldset>
    <fieldset>
        <legend>Status</legend>
        <label><input type="checkbox" name="statuses[]" value="publish" checked> Veröffentlicht</label>
        <label><input type="checkbox" name="statuses[]" value="draft" checked> Entwürfe</label>
        <label><input type="checkbox" name="statuses[]" value="pending" checked> Ausstehend</label>
        <label><input type="checkbox" name="statuses[]" value="private" checked> Privat</label>
        <label><input type="checkbox" name="statuses[]" value="future" checked> Geplant</label>
    </fieldset>
    <?php
}

function ffl_impex_render_session( $state ) {
    $token  = $state['token'];
    $status = $state['status'];
    $done   = (int) $state['position'];
    $total  = max( 1, (int) $state['total'] );
    $percent = min( 100, round( $done / $total * 100 ) );
    ?>
    <section class="ffl-impex-session">
        <div class="ffl-impex-session__head">
            <div>
                <span class="ffl-impex-eyebrow">Import-Sitzung</span>
                <h2><?php echo esc_html( $state['source_name'] ); ?></h2>
            </div>
            <span class="ffl-impex-status ffl-impex-status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ffl_impex_status_label( $status ) ); ?></span>
        </div>

        <?php if ( 'preview' === $status ) : ?>
            <?php ffl_impex_render_preview( $state ); ?>
        <?php else : ?>
            <div class="ffl-impex-progress" aria-label="Importfortschritt">
                <div class="ffl-impex-progress__bar"><span style="width:<?php echo esc_attr( $percent ); ?>%"></span></div>
                <strong><?php echo esc_html( $done ); ?> von <?php echo esc_html( $total ); ?> Einsätzen · <?php echo esc_html( $percent ); ?> %</strong>
            </div>
            <?php ffl_impex_render_stats( $state ); ?>
            <?php ffl_impex_render_logs( $state ); ?>

            <?php if ( 'running' === $status ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ffl-impex-auto-batch" data-auto-submit="1">
                    <input type="hidden" name="action" value="ffl_import_batch">
                    <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
                    <?php wp_nonce_field( 'ffl_import_batch' ); ?>
                    <noscript><?php submit_button( 'Nächstes Paket importieren', 'primary', 'submit', false ); ?></noscript>
                </form>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-inline-form">
                    <input type="hidden" name="action" value="ffl_import_cancel">
                    <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
                    <?php wp_nonce_field( 'ffl_import_cancel' ); ?>
                    <?php submit_button( 'Import anhalten', 'secondary', 'submit', false ); ?>
                </form>
            <?php endif; ?>

            <?php if ( in_array( $status, array( 'done', 'error', 'cancelled' ), true ) && ( ! empty( $state['created_posts'] ) || ! empty( $state['snapshots'] ) ) ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-inline-form" onsubmit="return confirm('Alle Änderungen dieser Import-Sitzung wirklich zurücksetzen?');">
                    <input type="hidden" name="action" value="ffl_import_rollback">
                    <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
                    <?php wp_nonce_field( 'ffl_import_rollback' ); ?>
                    <?php submit_button( 'Änderungen dieser Sitzung zurücksetzen', 'delete', 'submit', false ); ?>
                </form>
            <?php endif; ?>

            <?php $backup_url = ffl_impex_backup_url( isset( $state['backup'] ) ? $state['backup'] : array() ); ?>
            <?php if ( $backup_url ) : ?>
                <p><a class="button" href="<?php echo esc_url( $backup_url ); ?>"><span class="dashicons dashicons-download"></span> Vorab-Backup herunterladen</a></p>
            <?php endif; ?>

            <?php if ( ! empty( $state['logs'] ) ) : ?>
                <p><a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ffl_import_log', 'token' => $token ), admin_url( 'admin-post.php' ) ), 'ffl_import_log' ) ); ?>"><span class="dashicons dashicons-media-text"></span> Importprotokoll herunterladen</a></p>
            <?php endif; ?>

            <?php if ( in_array( $status, array( 'done', 'error', 'cancelled', 'rolled_back' ), true ) ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-inline-form">
                    <input type="hidden" name="action" value="ffl_import_cleanup">
                    <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">
                    <?php wp_nonce_field( 'ffl_import_cleanup' ); ?>
                    <?php submit_button( 'Sitzung abschließen und temporäre Dateien löschen', 'primary', 'submit', false ); ?>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
}

function ffl_impex_status_label( $status ) {
    $labels = array(
        'preview'     => 'Vorschau',
        'running'     => 'Import läuft',
        'done'        => 'Abgeschlossen',
        'error'       => 'Fehler',
        'cancelled'   => 'Angehalten',
        'rolled_back' => 'Zurückgesetzt',
    );
    return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
}

function ffl_impex_render_preview( $state ) {
    $counts   = $state['counts'];
    $manifest = $state['manifest'];
    ?>
    <div class="ffl-impex-summary">
        <div><strong><?php echo esc_html( $state['total'] ); ?></strong><span>Einsätze gefunden</span></div>
        <div><strong><?php echo esc_html( $counts['new'] ); ?></strong><span>voraussichtlich neu</span></div>
        <div><strong><?php echo esc_html( $counts['duplicate'] ); ?></strong><span>bereits vorhanden</span></div>
        <div><strong><?php echo esc_html( $counts['images'] ); ?></strong><span>Bilder im Archiv</span></div>
    </div>
    <div class="ffl-impex-origin">
        <strong>Quelle:</strong> <?php echo esc_html( isset( $manifest['site_name'] ) ? $manifest['site_name'] : 'Unbekannte Website' ); ?> ·
        Export <?php echo esc_html( isset( $manifest['created_at'] ) ? wp_date( 'd.m.Y H:i', strtotime( $manifest['created_at'] ) ) . ' Uhr' : 'ohne Datum' ); ?> ·
        Format <?php echo esc_html( isset( $manifest['schema_version'] ) ? $manifest['schema_version'] : '?' ); ?>
    </div>

    <div class="ffl-impex-table-wrap">
        <table class="widefat striped">
            <thead><tr><th>Einsatz</th><th>Alarmierung</th><th>Bilder</th><th>Erkennung</th></tr></thead>
            <tbody>
            <?php foreach ( (array) $state['preview'] as $item ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $item['title'] ); ?></strong></td>
                    <td><?php echo esc_html( $item['alarm'] ? wp_date( 'd.m.Y H:i', strtotime( $item['alarm'] ) ) . ' Uhr' : '—' ); ?></td>
                    <td><?php echo esc_html( $item['images'] ); ?></td>
                    <td><?php if ( $item['existing'] ) : ?><span class="ffl-impex-pill ffl-impex-pill--duplicate">Vorhanden: <?php echo esc_html( $item['existing_title'] ); ?></span><?php else : ?><span class="ffl-impex-pill ffl-impex-pill--new">Neu</span><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ( $state['total'] > count( $state['preview'] ) ) : ?><p class="description">Vorschau zeigt die ersten <?php echo esc_html( count( $state['preview'] ) ); ?> Einsätze.</p><?php endif; ?>
    </div>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ffl-impex-start-form">
        <input type="hidden" name="action" value="ffl_import_start">
        <input type="hidden" name="token" value="<?php echo esc_attr( $state['token'] ); ?>">
        <?php wp_nonce_field( 'ffl_import_start' ); ?>
        <div class="ffl-impex-choice-grid">
            <fieldset>
                <legend>Vorhandene Einsätze</legend>
                <label><input type="radio" name="strategy" value="skip" checked> <strong>Überspringen</strong><small>Sicherste Einstellung; vorhandene Inhalte bleiben unverändert.</small></label>
                <label><input type="radio" name="strategy" value="update"> <strong>Aktualisieren</strong><small>Daten, Taxonomie und Bilder aus der Sicherung übernehmen.</small></label>
                <label><input type="radio" name="strategy" value="copy"> <strong>Als Kopie importieren</strong><small>Erstellt bei Treffern einen neuen Entwurf.</small></label>
            </fieldset>
            <fieldset>
                <legend>Status nach Import</legend>
                <label><input type="radio" name="status_mode" value="preserve" checked> Status aus Sicherung beibehalten</label>
                <label><input type="radio" name="status_mode" value="draft"> Alles als Entwurf</label>
                <label><input type="radio" name="status_mode" value="publish"> Alles veröffentlichen</label>
            </fieldset>
            <fieldset>
                <legend>Optionen</legend>
                <label><input type="checkbox" name="import_images" value="1" checked> Bilder importieren und zuordnen</label>
                <label><input type="checkbox" name="backup_before" value="1" checked> Vorher vollständiges Backup erstellen</label>
                <label><input type="checkbox" name="keep_slugs" value="1"> Bestehende Links bei Aktualisierungen beibehalten</label>
                <label><input type="checkbox" name="import_plugin_settings" value="1" checked> Alle Einsatzlyzer-Einstellungen wiederherstellen</label>
                <label><input type="checkbox" name="import_comments" value="1" checked> Kommentare und Kommentar-Metadaten importieren</label>
            </fieldset>
        </div>
        <div class="ffl-impex-actions">
            <?php submit_button( 'Import starten', 'primary button-hero', 'submit', false ); ?>
            <a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ffl_import_cleanup', 'token' => $state['token'] ), admin_url( 'admin-post.php' ) ), 'ffl_import_cleanup' ) ); ?>">Abbrechen und Dateien löschen</a>
        </div>
    </form>
    <?php
}

function ffl_impex_render_stats( $state ) {
    $stats = $state['stats'];
    ?>
    <div class="ffl-impex-summary ffl-impex-summary--stats">
        <div><strong><?php echo esc_html( $stats['created'] ); ?></strong><span>neu</span></div>
        <div><strong><?php echo esc_html( $stats['updated'] ); ?></strong><span>aktualisiert</span></div>
        <div><strong><?php echo esc_html( $stats['copied'] ); ?></strong><span>Kopien</span></div>
        <div><strong><?php echo esc_html( $stats['skipped'] ); ?></strong><span>übersprungen</span></div>
        <div><strong><?php echo esc_html( $stats['images_created'] ); ?></strong><span>Bilder neu</span></div>
        <div><strong><?php echo esc_html( $stats['images_reused'] ); ?></strong><span>Bilder wiederverwendet</span></div>
    </div>
    <?php
}

function ffl_impex_render_logs( $state ) {
    if ( empty( $state['logs'] ) ) {
        return;
    }
    ?>
    <details class="ffl-impex-log" <?php echo in_array( $state['status'], array( 'error', 'cancelled' ), true ) ? 'open' : ''; ?>>
        <summary>Importprotokoll anzeigen (<?php echo esc_html( count( $state['logs'] ) ); ?> Einträge)</summary>
        <div class="ffl-impex-log__items">
            <?php foreach ( array_reverse( $state['logs'] ) as $log ) : ?>
                <div class="ffl-impex-log__item ffl-impex-log__item--<?php echo esc_attr( $log['type'] ); ?>"><time><?php echo esc_html( $log['time'] ); ?></time><span><?php echo esc_html( $log['message'] ); ?></span></div>
            <?php endforeach; ?>
        </div>
    </details>
    <?php
}
