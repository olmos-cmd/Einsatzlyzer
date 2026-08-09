<?php
/**
 * Fahrzeugverwaltung und sicherer Einsatzdepeschen-Import.
 *
 * @package Einsatzlyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'ffl_register_vehicle_post_type' );
add_action( 'admin_menu', 'ffl_register_vehicle_and_dispatch_menus', 20 );
add_action( 'add_meta_boxes_ffl_fahrzeug', 'ffl_add_vehicle_metabox' );
add_action( 'save_post_ffl_fahrzeug', 'ffl_save_vehicle' );
add_filter( 'manage_ffl_fahrzeug_posts_columns', 'ffl_vehicle_columns' );
add_action( 'manage_ffl_fahrzeug_posts_custom_column', 'ffl_vehicle_column_content', 10, 2 );
add_action( 'add_meta_boxes_ffl_einsatz', 'ffl_add_dispatch_metabox' );
add_action( 'admin_post_ffl_dispatch_upload', 'ffl_dispatch_handle_upload' );
add_action( 'admin_post_ffl_dispatch_apply', 'ffl_dispatch_handle_apply' );
add_action( 'admin_post_ffl_dispatch_cancel', 'ffl_dispatch_handle_cancel' );
add_action( 'admin_post_ffl_vehicle_registry_import', 'ffl_vehicle_registry_import_handler' );
add_action( 'admin_notices', 'ffl_dispatch_admin_notice' );
add_action( 'admin_footer-post.php', 'ffl_dispatch_render_editor_forms' );
add_action( 'admin_footer-post-new.php', 'ffl_dispatch_render_editor_forms' );
add_action( 'edit_form_after_title', 'ffl_dispatch_render_edit_screen_preview' );
add_action( 'admin_init', 'ffl_dispatch_migrate_confidential_numbers' );
add_action( 'restrict_manage_posts', 'ffl_vehicle_admin_filters' );
add_action( 'pre_get_posts', 'ffl_vehicle_admin_apply_filters' );
add_action( 'admin_head-edit.php', 'ffl_vehicle_admin_list_styles' );

/** Registriert die zentrale Fahrzeugverwaltung. */
function ffl_register_vehicle_post_type() {
    register_post_type(
        'ffl_fahrzeug',
        array(
            'labels' => array(
                'name'          => ffl_lang( 'Fahrzeuge', 'Vehicles' ),
                'singular_name' => ffl_lang( 'Fahrzeug', 'Vehicle' ),
                'add_new_item'  => ffl_lang( 'Fahrzeug hinzufügen', 'Add Vehicle' ),
                'edit_item'     => ffl_lang( 'Fahrzeug bearbeiten', 'Edit Vehicle' ),
                'search_items'  => ffl_lang( 'Fahrzeuge durchsuchen', 'Search Vehicles' ),
                'not_found'     => ffl_lang( 'Keine Fahrzeuge gefunden', 'No vehicles found' ),
            ),
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => false,
            'show_in_rest'    => true,
            'supports'        => array( 'title', 'thumbnail' ),
            'capability_type' => 'post',
            'map_meta_cap'    => true,
        )
    );

    foreach ( array( '_ffl_vehicle_callsign', '_ffl_vehicle_municipality', '_ffl_vehicle_station', '_ffl_vehicle_scope', '_ffl_vehicle_active', '_ffl_vehicle_year', '_ffl_vehicle_chassis', '_ffl_vehicle_body' ) as $key ) {
        register_post_meta(
            'ffl_fahrzeug',
            $key,
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback'     => static function() { return current_user_can( 'edit_posts' ); },
            )
        );
    }
}

/** Ob der optionale Depeschen-Import aktiviert ist. */
function ffl_dispatch_import_enabled() {
    return 1 === (int) get_option( 'ffl_dispatch_enabled', 1 );
}

/** Menüpunkte Fahrzeuge und Depesche importieren. */
function ffl_register_vehicle_and_dispatch_menus() {
    add_submenu_page(
        'edit.php?post_type=ffl_einsatz',
        ffl_lang( 'Fahrzeuge', 'Vehicles' ),
        ffl_lang( 'Fahrzeuge', 'Vehicles' ),
        'edit_posts',
        'edit.php?post_type=ffl_fahrzeug'
    );
    add_submenu_page(
        'edit.php?post_type=ffl_einsatz',
        ffl_lang( 'Fahrzeuge importieren', 'Import Vehicles' ),
        ffl_lang( 'Fahrzeuge importieren', 'Import Vehicles' ),
        'edit_posts',
        'ffl_vehicle_import',
        'ffl_render_vehicle_import_page'
    );
    if ( ffl_dispatch_import_enabled() ) {
        add_submenu_page(
            'edit.php?post_type=ffl_einsatz',
            ffl_lang( 'Depesche importieren', 'Import Dispatch PDF' ),
            ffl_lang( 'Depesche importieren', 'Import Dispatch PDF' ),
            'edit_posts',
            'ffl_dispatch_import',
            'ffl_render_dispatch_import_page'
        );
    }
}

function ffl_add_vehicle_metabox() {
    add_meta_box( 'ffl_vehicle_details', ffl_lang( 'Fahrzeugdaten', 'Vehicle Data' ), 'ffl_render_vehicle_metabox', 'ffl_fahrzeug', 'normal', 'high' );
}

function ffl_normalize_callsign( $value ) {
    $value = strtoupper( trim( (string) $value ) );
    $value = preg_replace( '/^(FL\s+[A-Z]{2,4}\s+|FLO\s+[A-Z]{2,4}\s+)/', '', $value );
    $value = preg_replace( '/\s+/', '', $value );
    return preg_replace( '/[^A-Z0-9\-]/', '', $value );
}

function ffl_render_vehicle_metabox( $post ) {
    wp_nonce_field( 'ffl_save_vehicle', 'ffl_vehicle_nonce' );
    $callsign = get_post_meta( $post->ID, '_ffl_vehicle_callsign', true );
    $municipality = get_post_meta( $post->ID, '_ffl_vehicle_municipality', true );
    $station  = get_post_meta( $post->ID, '_ffl_vehicle_station', true );
    $year     = get_post_meta( $post->ID, '_ffl_vehicle_year', true );
    $chassis  = get_post_meta( $post->ID, '_ffl_vehicle_chassis', true );
    $body     = get_post_meta( $post->ID, '_ffl_vehicle_body', true );
    $scope    = get_post_meta( $post->ID, '_ffl_vehicle_scope', true ) ?: 'own';
    $active   = get_post_meta( $post->ID, '_ffl_vehicle_active', true );
    if ( '' === $active ) $active = '1';
    ?>
    <div class="ffl-admin-fields ffl-admin-fields--two">
        <label><span><?php echo esc_html( ffl_lang( 'Funkrufname', 'Call Sign' ) ); ?></span><input type="text" name="ffl_vehicle_callsign" value="<?php echo esc_attr( $callsign ); ?>" placeholder="z. B. FL LER 12-41-4"></label>
        <label><span><?php echo esc_html( ffl_lang( 'Gemeinde', 'Municipality' ) ); ?></span><input type="text" name="ffl_vehicle_municipality" value="<?php echo esc_attr( $municipality ); ?>" placeholder="z. B. Jümme oder Uplengen"></label>
        <label><span><?php echo esc_html( ffl_lang( 'Ortswehr / Standort', 'Station' ) ); ?></span><input type="text" name="ffl_vehicle_station" value="<?php echo esc_attr( $station ); ?>" placeholder="z. B. Lammertsfehn"></label>
        <label><span><?php echo esc_html( ffl_lang( 'Zuordnung', 'Assignment' ) ); ?></span><select name="ffl_vehicle_scope"><option value="own" <?php selected( $scope, 'own' ); ?>><?php echo esc_html( ffl_lang( 'Eigenes Fahrzeug', 'Own vehicle' ) ); ?></option><option value="external" <?php selected( $scope, 'external' ); ?>><?php echo esc_html( ffl_lang( 'Externes Fahrzeug / Einheit', 'External vehicle / unit' ) ); ?></option></select></label>
        <label><span><?php echo esc_html( ffl_lang( 'Status', 'Status' ) ); ?></span><select name="ffl_vehicle_active"><option value="1" <?php selected( $active, '1' ); ?>><?php echo esc_html( ffl_lang( 'Aktiv', 'Active' ) ); ?></option><option value="0" <?php selected( $active, '0' ); ?>><?php echo esc_html( ffl_lang( 'Außer Dienst', 'Out of service' ) ); ?></option></select></label>
        <label><span><?php echo esc_html( ffl_lang( 'Baujahr (optional)', 'Year (optional)' ) ); ?></span><input type="number" min="1900" max="2100" name="ffl_vehicle_year" value="<?php echo esc_attr( $year ); ?>"></label>
        <label><span><?php echo esc_html( ffl_lang( 'Fahrgestell (optional)', 'Chassis (optional)' ) ); ?></span><input type="text" name="ffl_vehicle_chassis" value="<?php echo esc_attr( $chassis ); ?>"></label>
        <label><span><?php echo esc_html( ffl_lang( 'Aufbau (optional)', 'Body (optional)' ) ); ?></span><input type="text" name="ffl_vehicle_body" value="<?php echo esc_attr( $body ); ?>"></label>
    </div>
    <p class="description"><?php echo esc_html( ffl_lang( 'Der Beitragstitel ist die Fahrzeugbezeichnung, zum Beispiel TSF, LF 10 oder MTF. Ein Kennzeichen wird bewusst nicht gespeichert.', 'The post title is the vehicle designation, for example TSF, LF 10 or MTF. Registration plates are intentionally not stored.' ) ); ?></p>
    <?php
}

function ffl_save_vehicle( $post_id ) {
    if ( ! isset( $_POST['ffl_vehicle_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_nonce'] ) ), 'ffl_save_vehicle' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    update_post_meta( $post_id, '_ffl_vehicle_callsign', sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_callsign'] ?? '' ) ) );
    update_post_meta( $post_id, '_ffl_vehicle_municipality', sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_municipality'] ?? '' ) ) );
    update_post_meta( $post_id, '_ffl_vehicle_station', sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_station'] ?? '' ) ) );
    update_post_meta( $post_id, '_ffl_vehicle_scope', in_array( $_POST['ffl_vehicle_scope'] ?? '', array( 'own', 'external' ), true ) ? $_POST['ffl_vehicle_scope'] : 'own' );
    update_post_meta( $post_id, '_ffl_vehicle_active', '0' === ( $_POST['ffl_vehicle_active'] ?? '1' ) ? '0' : '1' );
    update_post_meta( $post_id, '_ffl_vehicle_year', preg_replace( '/[^0-9]/', '', (string) ( $_POST['ffl_vehicle_year'] ?? '' ) ) );
    update_post_meta( $post_id, '_ffl_vehicle_chassis', sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_chassis'] ?? '' ) ) );
    update_post_meta( $post_id, '_ffl_vehicle_body', sanitize_text_field( wp_unslash( $_POST['ffl_vehicle_body'] ?? '' ) ) );
}

function ffl_vehicle_columns( $columns ) {
    return array(
        'cb'       => $columns['cb'] ?? '<input type="checkbox">',
        'thumbnail'=> ffl_lang( 'Bild', 'Image' ),
        'title'    => ffl_lang( 'Fahrzeug', 'Vehicle' ),
        'callsign' => ffl_lang( 'Funkrufname', 'Call Sign' ),
        'municipality' => ffl_lang( 'Gemeinde', 'Municipality' ),
        'station'  => ffl_lang( 'Ortswehr', 'Station' ),
        'scope'    => ffl_lang( 'Zuordnung', 'Assignment' ),
        'active'   => ffl_lang( 'Status', 'Status' ),
        'date'     => $columns['date'] ?? ffl_lang( 'Datum', 'Date' ),
    );
}

function ffl_vehicle_column_content( $column, $post_id ) {
    if ( 'thumbnail' === $column ) echo get_the_post_thumbnail( $post_id, array( 54, 40 ) );
    if ( 'callsign' === $column ) echo esc_html( get_post_meta( $post_id, '_ffl_vehicle_callsign', true ) );
    if ( 'municipality' === $column ) echo esc_html( get_post_meta( $post_id, '_ffl_vehicle_municipality', true ) );
    if ( 'station' === $column ) echo esc_html( get_post_meta( $post_id, '_ffl_vehicle_station', true ) );
    if ( 'scope' === $column ) echo esc_html( 'external' === get_post_meta( $post_id, '_ffl_vehicle_scope', true ) ? ffl_lang( 'Extern', 'External' ) : ffl_lang( 'Eigen', 'Own' ) );
    if ( 'active' === $column ) echo esc_html( '0' === get_post_meta( $post_id, '_ffl_vehicle_active', true ) ? ffl_lang( 'Außer Dienst', 'Out of service' ) : ffl_lang( 'Aktiv', 'Active' ) );
}

/** Liefert Fahrzeugzuordnungen nach normalisiertem Funkrufnamen. */
function ffl_get_vehicle_registry() {
    $ids = get_posts( array( 'post_type' => 'ffl_fahrzeug', 'post_status' => array( 'publish', 'draft', 'private' ), 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
    $out = array();
    foreach ( $ids as $id ) {
        $callsign = ffl_normalize_callsign( get_post_meta( $id, '_ffl_vehicle_callsign', true ) );
        if ( ! $callsign ) continue;
        $out[ $callsign ] = array(
            'id'       => (int) $id,
            'title'    => get_the_title( $id ),
            'callsign' => get_post_meta( $id, '_ffl_vehicle_callsign', true ),
            'municipality' => get_post_meta( $id, '_ffl_vehicle_municipality', true ),
            'station'  => get_post_meta( $id, '_ffl_vehicle_station', true ),
            'year'     => get_post_meta( $id, '_ffl_vehicle_year', true ),
            'chassis'  => get_post_meta( $id, '_ffl_vehicle_chassis', true ),
            'body'     => get_post_meta( $id, '_ffl_vehicle_body', true ),
            'scope'    => get_post_meta( $id, '_ffl_vehicle_scope', true ) ?: 'own',
            'active'   => '0' !== get_post_meta( $id, '_ffl_vehicle_active', true ),
        );
    }
    return $out;
}


/** Formatiert einen zentral verwalteten Fahrzeugeintrag für Einsatzberichte. */
function ffl_vehicle_display_label( $vehicle ) {
    $title = trim( (string) ( $vehicle['title'] ?? '' ) );
    $station = trim( (string) ( $vehicle['station'] ?? '' ) );
    $callsign = trim( (string) ( $vehicle['callsign'] ?? '' ) );
    $label = $title;
    if ( $station ) $label .= ' – ' . $station;
    if ( $callsign ) $label .= ' (' . $callsign . ')';
    return trim( $label );
}

/** Rendert die anklickbare Fahrzeugauswahl im Einsatzeditor. */
function ffl_render_incident_vehicle_picker( $post_id, $manual_value = '' ) {
    $vehicles = array_values( ffl_get_vehicle_registry() );
    usort( $vehicles, static function( $a, $b ) {
        $a_own = 'own' === ( $a['scope'] ?? '' ) ? 0 : 1;
        $b_own = 'own' === ( $b['scope'] ?? '' ) ? 0 : 1;
        if ( $a_own !== $b_own ) return $a_own <=> $b_own;
        return strnatcasecmp( ( $a['municipality'] ?? '' ) . ' ' . ( $a['station'] ?? '' ) . ' ' . ( $a['title'] ?? '' ), ( $b['municipality'] ?? '' ) . ' ' . ( $b['station'] ?? '' ) . ' ' . ( $b['title'] ?? '' ) );
    } );
    $selected = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_vehicle_ids', true ) ) ) );
    $municipalities = array_values( array_unique( array_filter( array_map( static function( $v ) { return trim( (string) ( $v['municipality'] ?? '' ) ); }, $vehicles ) ) ) );
    sort( $municipalities, SORT_NATURAL | SORT_FLAG_CASE );
    ?>
    <div class="ffl-vehicle-picker" data-vehicle-picker>
        <div class="ffl-vehicle-picker__top">
            <label class="ffl-admin-field-full"><span><?php echo esc_html( ffl_lang( 'Fahrzeug suchen', 'Search vehicle' ) ); ?></span><input type="search" class="ffl-vehicle-picker__search" placeholder="<?php echo esc_attr( ffl_lang( 'Funkrufname, Fahrzeug, Gemeinde oder Ortswehr', 'Call sign, vehicle, municipality or station' ) ); ?>"></label>
            <div class="ffl-vehicle-picker__filters" role="group" aria-label="<?php echo esc_attr( ffl_lang( 'Fahrzeugfilter', 'Vehicle filters' ) ); ?>">
                <button type="button" class="button is-active" data-vehicle-filter="default"><?php echo esc_html( ffl_lang( 'Eigene & ausgewählte', 'Own & selected' ) ); ?></button>
                <button type="button" class="button" data-vehicle-filter="all"><?php echo esc_html( ffl_lang( 'Alle', 'All' ) ); ?></button>
                <?php foreach ( $municipalities as $municipality ) : ?>
                    <button type="button" class="button" data-vehicle-filter="municipality" data-value="<?php echo esc_attr( strtolower( $municipality ) ); ?>"><?php echo esc_html( $municipality ); ?></button>
                <?php endforeach; ?>
                <button type="button" class="button" data-vehicle-filter="selected"><?php echo esc_html( ffl_lang( 'Ausgewählt', 'Selected' ) ); ?> <span data-selected-count><?php echo count( $selected ); ?></span></button>
            </div>
        </div>

        <div class="ffl-vehicle-picker__selected" data-selected-panel>
            <div class="ffl-vehicle-picker__selected-head"><strong><?php echo esc_html( ffl_lang( 'Ausgewählte Fahrzeuge', 'Selected vehicles' ) ); ?></strong><span data-selected-summary><?php echo esc_html( sprintf( ffl_lang( '%d ausgewählt', '%d selected' ), count( $selected ) ) ); ?></span></div>
            <div class="ffl-vehicle-picker__chips" data-selected-chips></div>
        </div>

        <div class="ffl-vehicle-picker__list">
            <?php if ( ! $vehicles ) : ?>
                <p class="description"><?php echo esc_html( ffl_lang( 'Noch keine Fahrzeuge angelegt. Nutzen Sie zuerst den Menüpunkt „Fahrzeuge“.', 'No vehicles created yet. Use the “Vehicles” menu first.' ) ); ?></p>
            <?php else : foreach ( $vehicles as $vehicle ) :
                if ( empty( $vehicle['active'] ) ) continue;
                $title = trim( (string) ( $vehicle['title'] ?? '' ) );
                $callsign = trim( (string) ( $vehicle['callsign'] ?? '' ) );
                $municipality = trim( (string) ( $vehicle['municipality'] ?? '' ) );
                $station = trim( (string) ( $vehicle['station'] ?? '' ) );
                $scope = 'external' === ( $vehicle['scope'] ?? '' ) ? 'external' : 'own';
                $label = ffl_vehicle_display_label( $vehicle );
                $search = strtolower( implode( ' ', array_filter( array( $label, $title, $callsign, $municipality, $station ) ) ) );
                $is_selected = in_array( (int) $vehicle['id'], $selected, true );
                ?>
                <label class="ffl-vehicle-picker__item" data-search="<?php echo esc_attr( $search ); ?>" data-scope="<?php echo esc_attr( $scope ); ?>" data-municipality="<?php echo esc_attr( strtolower( $municipality ) ); ?>" data-title="<?php echo esc_attr( $title ); ?>" data-callsign="<?php echo esc_attr( $callsign ); ?>" data-station="<?php echo esc_attr( $station ); ?>">
                    <input type="checkbox" name="ffl_vehicle_ids[]" value="<?php echo esc_attr( $vehicle['id'] ); ?>" <?php checked( $is_selected ); ?>>
                    <span class="ffl-vehicle-picker__content">
                        <span class="ffl-vehicle-picker__headline"><strong><?php echo esc_html( $title ); ?></strong><?php if ( $callsign ) : ?><code><?php echo esc_html( $callsign ); ?></code><?php endif; ?></span>
                        <small><?php echo esc_html( implode( ' · ', array_filter( array( $municipality, $station ) ) ) ); ?></small>
                        <em class="ffl-vehicle-picker__badge ffl-vehicle-picker__badge--<?php echo esc_attr( $scope ); ?>"><?php echo esc_html( 'external' === $scope ? ffl_lang( 'Extern', 'External' ) : ffl_lang( 'Eigene Wehr', 'Own station' ) ); ?></em>
                    </span>
                </label>
            <?php endforeach; endif; ?>
        </div>
        <p class="ffl-vehicle-picker__empty" data-empty-message hidden><?php echo esc_html( ffl_lang( 'Keine passenden Fahrzeuge gefunden.', 'No matching vehicles found.' ) ); ?></p>
        <label class="ffl-admin-field-full"><span><?php echo esc_html( ffl_lang( 'Zusätzliche freie Fahrzeugangaben', 'Additional manual vehicle entries' ) ); ?></span><textarea name="ffl_fahrzeuge_manual" rows="2" placeholder="<?php echo esc_attr( ffl_lang( 'Nur für Fahrzeuge, die noch nicht in der Verwaltung stehen.', 'Only for vehicles not yet stored in the registry.' ) ); ?>"><?php echo esc_textarea( $manual_value ); ?></textarea></label>
        <p class="description"><?php echo esc_html( ffl_lang( 'Standardmäßig werden nur eigene und bereits ausgewählte Fahrzeuge angezeigt. Weitere Fahrzeuge erscheinen über Suche, Gemeinde-Filter oder „Alle“.', 'By default, only own and already selected vehicles are shown. Use search, municipality filters, or “All” for more.' ) ); ?></p>
    </div>
    <script>
    (function(){
        const root = document.currentScript.previousElementSibling;
        if (!root || !root.matches('[data-vehicle-picker]')) return;
        const search = root.querySelector('.ffl-vehicle-picker__search');
        const items = Array.from(root.querySelectorAll('.ffl-vehicle-picker__item'));
        const buttons = Array.from(root.querySelectorAll('[data-vehicle-filter]'));
        const chips = root.querySelector('[data-selected-chips]');
        const summary = root.querySelector('[data-selected-summary]');
        const countNodes = root.querySelectorAll('[data-selected-count]');
        const empty = root.querySelector('[data-empty-message]');
        let filter = 'default';
        let value = '';
        function checked(item){ const input=item.querySelector('input[type="checkbox"]'); return !!(input && input.checked); }
        function updateChips(){
            const selected = items.filter(checked);
            chips.innerHTML = '';
            selected.forEach(function(item){
                const input=item.querySelector('input');
                const chip=document.createElement('button');
                chip.type='button'; chip.className='ffl-vehicle-picker__chip';
                const title=item.dataset.title || ''; const call=item.dataset.callsign || '';
                chip.textContent=title + (call ? ' · ' + call : '') + ' ×';
                chip.addEventListener('click', function(){ input.checked=false; update(); });
                chips.appendChild(chip);
            });
            const text = selected.length + ' <?php echo esc_js( ffl_lang( 'ausgewählt', 'selected' ) ); ?>';
            summary.textContent = text; countNodes.forEach(function(n){ n.textContent=selected.length; });
            root.querySelector('[data-selected-panel]').classList.toggle('is-empty', !selected.length);
        }
        function visibleByFilter(item){
            if (filter === 'all') return true;
            if (filter === 'selected') return checked(item);
            if (filter === 'municipality') return item.dataset.municipality === value;
            return item.dataset.scope === 'own' || checked(item);
        }
        function update(){
            const q=(search.value || '').trim().toLowerCase(); let shown=0;
            items.forEach(function(item){
                const visible = (q ? item.dataset.search.includes(q) : visibleByFilter(item));
                item.hidden=!visible; if(visible) shown++;
                item.classList.toggle('is-selected', checked(item));
            });
            empty.hidden = shown !== 0;
            updateChips();
        }
        search.addEventListener('input', function(){ if(this.value.trim()){ filter='all'; buttons.forEach(b=>b.classList.remove('is-active')); } update(); });
        buttons.forEach(function(button){ button.addEventListener('click', function(){
            filter=this.dataset.vehicleFilter; value=this.dataset.value || ''; search.value='';
            buttons.forEach(b=>b.classList.toggle('is-active', b===this)); update();
        }); });
        items.forEach(function(item){ item.querySelector('input').addEventListener('change', update); });
        update();
    })();
    </script>
    <?php
}

/** Filter oberhalb der Fahrzeugverwaltung. */
function ffl_vehicle_admin_filters( $post_type ) {
    if ( 'ffl_fahrzeug' !== $post_type ) return;
    global $wpdb;
    $municipalities = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_ffl_vehicle_municipality' AND meta_value<>'' ORDER BY meta_value" );
    $stations = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_ffl_vehicle_station' AND meta_value<>'' ORDER BY meta_value" );
    $current_m = sanitize_text_field( wp_unslash( $_GET['ffl_vehicle_municipality'] ?? '' ) );
    $current_s = sanitize_text_field( wp_unslash( $_GET['ffl_vehicle_station'] ?? '' ) );
    $current_scope = sanitize_key( $_GET['ffl_vehicle_scope'] ?? '' );
    $current_active = sanitize_key( $_GET['ffl_vehicle_active'] ?? '' );
    echo '<select name="ffl_vehicle_municipality"><option value="">' . esc_html( ffl_lang( 'Alle Gemeinden', 'All municipalities' ) ) . '</option>';
    foreach ( $municipalities as $m ) printf( '<option value="%s" %s>%s</option>', esc_attr( $m ), selected( $current_m, $m, false ), esc_html( $m ) );
    echo '</select><select name="ffl_vehicle_station"><option value="">' . esc_html( ffl_lang( 'Alle Ortswehren', 'All stations' ) ) . '</option>';
    foreach ( $stations as $st ) printf( '<option value="%s" %s>%s</option>', esc_attr( $st ), selected( $current_s, $st, false ), esc_html( $st ) );
    echo '</select><select name="ffl_vehicle_scope"><option value="">' . esc_html( ffl_lang( 'Eigene und externe', 'Own and external' ) ) . '</option><option value="own" ' . selected( $current_scope, 'own', false ) . '>' . esc_html( ffl_lang( 'Eigene Wehr', 'Own station' ) ) . '</option><option value="external" ' . selected( $current_scope, 'external', false ) . '>' . esc_html( ffl_lang( 'Extern', 'External' ) ) . '</option></select>';
    echo '<select name="ffl_vehicle_active"><option value="">' . esc_html( ffl_lang( 'Alle Status', 'All statuses' ) ) . '</option><option value="1" ' . selected( $current_active, '1', false ) . '>' . esc_html( ffl_lang( 'Aktiv', 'Active' ) ) . '</option><option value="0" ' . selected( $current_active, '0', false ) . '>' . esc_html( ffl_lang( 'Außer Dienst', 'Out of service' ) ) . '</option></select>';
}

/** Wendet Fahrzeugfilter auf die Listenabfrage an. */
function ffl_vehicle_admin_apply_filters( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'ffl_fahrzeug' !== $query->get( 'post_type' ) ) return;
    $map = array(
        'ffl_vehicle_municipality' => '_ffl_vehicle_municipality',
        'ffl_vehicle_station' => '_ffl_vehicle_station',
        'ffl_vehicle_scope' => '_ffl_vehicle_scope',
        'ffl_vehicle_active' => '_ffl_vehicle_active',
    );
    $meta_query = array();
    foreach ( $map as $request_key => $meta_key ) {
        if ( isset( $_GET[ $request_key ] ) && '' !== (string) $_GET[ $request_key ] ) {
            $meta_query[] = array( 'key' => $meta_key, 'value' => sanitize_text_field( wp_unslash( $_GET[ $request_key ] ) ) );
        }
    }
    if ( $meta_query ) { $meta_query['relation'] = 'AND'; $query->set( 'meta_query', $meta_query ); }
}

/** Mobile Kartenansicht für die Fahrzeugverwaltung. */
function ffl_vehicle_admin_list_styles() {
    $screen = get_current_screen();
    if ( ! $screen || 'edit-ffl_fahrzeug' !== $screen->id ) return;
    ?>
    <style>
    .post-type-ffl_fahrzeug .wp-list-table .column-thumbnail{width:72px}.post-type-ffl_fahrzeug .wp-list-table .column-callsign{width:130px}.post-type-ffl_fahrzeug .wp-list-table .column-scope,.post-type-ffl_fahrzeug .wp-list-table .column-active{width:95px}
    @media(max-width:782px){
      .post-type-ffl_fahrzeug .tablenav.top{height:auto}.post-type-ffl_fahrzeug .tablenav.top .actions{display:grid;grid-template-columns:1fr;gap:8px;width:100%;padding:0}.post-type-ffl_fahrzeug .tablenav.top select,.post-type-ffl_fahrzeug .tablenav.top .button{width:100%;max-width:none}
      .post-type-ffl_fahrzeug .wp-list-table{border:0;background:transparent}.post-type-ffl_fahrzeug .wp-list-table thead,.post-type-ffl_fahrzeug .wp-list-table tfoot{display:none}.post-type-ffl_fahrzeug .wp-list-table tbody{display:grid;gap:12px}.post-type-ffl_fahrzeug .wp-list-table tr{display:grid;grid-template-columns:42px 1fr;position:relative;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:14px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
      .post-type-ffl_fahrzeug .wp-list-table td{display:block!important;width:auto!important;border:0;padding:3px 0 3px 0}.post-type-ffl_fahrzeug .wp-list-table .check-column{grid-row:1/8;padding-top:3px!important}.post-type-ffl_fahrzeug .wp-list-table .column-title{grid-column:2;font-size:18px;padding-top:0}.post-type-ffl_fahrzeug .wp-list-table .column-thumbnail{grid-column:2}.post-type-ffl_fahrzeug .wp-list-table .column-thumbnail img{width:100%;max-width:220px;height:auto;border-radius:8px}
      .post-type-ffl_fahrzeug .wp-list-table td:not(.check-column):not(.column-title):not(.column-thumbnail):before{display:inline-block;min-width:105px;font-weight:600;color:#50575e}.post-type-ffl_fahrzeug .wp-list-table .column-callsign:before{content:'Funkrufname:'}.post-type-ffl_fahrzeug .wp-list-table .column-municipality:before{content:'Gemeinde:'}.post-type-ffl_fahrzeug .wp-list-table .column-station:before{content:'Ortswehr:'}.post-type-ffl_fahrzeug .wp-list-table .column-scope:before{content:'Zuordnung:'}.post-type-ffl_fahrzeug .wp-list-table .column-active:before{content:'Status:'}.post-type-ffl_fahrzeug .wp-list-table .column-date{display:none!important}.post-type-ffl_fahrzeug .wp-list-table .row-actions{position:static;display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.post-type-ffl_fahrzeug .wp-list-table .toggle-row{display:none}
    }
    </style>
    <?php
}

/** Seite für eine eigenständige Fahrzeug-Importdatei. */
function ffl_render_vehicle_import_page() {
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Insufficient permissions.' ) ) );
    $count = isset( $_GET['imported'] ) ? absint( $_GET['imported'] ) : null;
    ?>
    <div class="wrap"><h1><?php echo esc_html( ffl_lang( 'Fahrzeuge importieren', 'Import Vehicles' ) ); ?></h1>
    <?php if ( null !== $count ) : ?><div class="notice notice-success"><p><?php echo esc_html( sprintf( ffl_lang( '%d Fahrzeuge wurden importiert oder aktualisiert.', '%d vehicles were imported or updated.' ), $count ) ); ?></p></div><?php endif; ?>
    <div class="card" style="max-width:820px"><h2><?php echo esc_html( ffl_lang( 'Einsatzlyzer-Fahrzeugdatei', 'Einsatzlyzer vehicle file' ) ); ?></h2>
    <p><?php echo esc_html( ffl_lang( 'Die JSON-Datei legt Fahrzeuge anhand des Funkrufnamens an oder aktualisiert vorhandene Einträge. Einsatzberichte werden dabei nicht verändert.', 'The JSON file creates or updates vehicles by call sign. Incident reports are not changed.' ) ); ?></p>
    <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="ffl_vehicle_registry_import"><?php wp_nonce_field( 'ffl_vehicle_registry_import' ); ?>
        <input type="file" name="vehicle_registry" accept="application/json,.json" required>
        <?php submit_button( ffl_lang( 'Fahrzeugdatei importieren', 'Import vehicle file' ) ); ?>
    </form></div></div>
    <?php
}

/** Importiert eine eigenständige Fahrzeug-JSON-Datei. */
function ffl_vehicle_registry_import_handler() {
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Insufficient permissions.' ) ) );
    check_admin_referer( 'ffl_vehicle_registry_import' );
    if ( empty( $_FILES['vehicle_registry']['tmp_name'] ) || ! is_uploaded_file( $_FILES['vehicle_registry']['tmp_name'] ) ) wp_die( esc_html( ffl_lang( 'Keine gültige Datei hochgeladen.', 'No valid file uploaded.' ) ) );
    $payload = json_decode( (string) file_get_contents( $_FILES['vehicle_registry']['tmp_name'] ), true );
    if ( ! is_array( $payload ) || 'einsatzlyzer-vehicles' !== ( $payload['format'] ?? '' ) || ! is_array( $payload['vehicles'] ?? null ) ) wp_die( esc_html( ffl_lang( 'Ungültiges Einsatzlyzer-Fahrzeugformat.', 'Invalid Einsatzlyzer vehicle format.' ) ) );
    $count = 0;
    foreach ( $payload['vehicles'] as $item ) {
        if ( ! is_array( $item ) ) continue;
        $callsign = sanitize_text_field( $item['callsign'] ?? '' );
        $normalized = ffl_normalize_callsign( $callsign );
        if ( ! $normalized ) continue;
        $existing = 0;
        foreach ( ffl_get_vehicle_registry() as $key => $vehicle ) if ( $key === $normalized ) { $existing = (int) $vehicle['id']; break; }
        $postarr = array( 'post_type' => 'ffl_fahrzeug', 'post_status' => 'publish', 'post_title' => sanitize_text_field( $item['title'] ?? $callsign ) );
        if ( $existing ) $postarr['ID'] = $existing;
        $id = wp_insert_post( $postarr, true );
        if ( is_wp_error( $id ) ) continue;
        if ( ! get_post_meta( $id, '_ffl_vehicle_uuid', true ) ) update_post_meta( $id, '_ffl_vehicle_uuid', sanitize_text_field( $item['uuid'] ?? wp_generate_uuid4() ) );
        update_post_meta( $id, '_ffl_vehicle_callsign', $callsign );
        update_post_meta( $id, '_ffl_vehicle_municipality', sanitize_text_field( $item['municipality'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_station', sanitize_text_field( $item['station'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_scope', 'own' === ( $item['scope'] ?? '' ) ? 'own' : 'external' );
        update_post_meta( $id, '_ffl_vehicle_active', '0' === (string) ( $item['active'] ?? '1' ) ? '0' : '1' );
        update_post_meta( $id, '_ffl_vehicle_year', sanitize_text_field( $item['year'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_chassis', sanitize_text_field( $item['chassis'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_body', sanitize_text_field( $item['body'] ?? '' ) );
        $count++;
    }
    wp_safe_redirect( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_vehicle_import', 'imported' => $count ), admin_url( 'edit.php' ) ) );
    exit;
}

function ffl_add_dispatch_metabox() {
    if ( ! ffl_dispatch_import_enabled() ) return;
    add_meta_box( 'ffl_dispatch_import_box', ffl_lang( 'Einsatzdepesche importieren', 'Import Dispatch PDF' ), 'ffl_render_dispatch_metabox', 'ffl_einsatz', 'side', 'high' );
}

function ffl_render_dispatch_metabox( $post ) {
    if ( ! ffl_dispatch_import_enabled() ) return;
    ?>
    <p><?php echo esc_html( ffl_lang( 'PDF einlesen, prüfen und nur ausgewählte Angaben übernehmen. Bestehende Daten werden nicht automatisch überschrieben.', 'Read a PDF, review it and apply only selected values. Existing data is never overwritten automatically.' ) ); ?></p>
    <input form="ffl-dispatch-upload-form" type="file" name="dispatch_pdf" accept="application/pdf,.pdf" required style="max-width:100%">
    <p><button form="ffl-dispatch-upload-form" class="button button-primary" type="submit"><?php echo esc_html( ffl_lang( 'Depesche prüfen', 'Review Dispatch' ) ); ?></button></p>
    <p><a href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_dispatch_import' ), admin_url( 'edit.php' ) ) ); ?>"><?php echo esc_html( ffl_lang( 'Importzentrale öffnen', 'Open Import Center' ) ); ?></a></p>
    <?php
}


function ffl_dispatch_render_editor_forms() {
    if ( ! ffl_dispatch_import_enabled() ) return;
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || 'ffl_einsatz' !== $screen->post_type || 'post' !== $screen->base ) return;

    $post_id = absint( $_GET['post'] ?? 0 );
    if ( ! $post_id && isset( $GLOBALS['post']->ID ) ) $post_id = absint( $GLOBALS['post']->ID );
    ?>
    <form id="ffl-dispatch-upload-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none">
        <input type="hidden" name="action" value="ffl_dispatch_upload">
        <input type="hidden" name="target_post_id" value="<?php echo esc_attr( $post_id ); ?>">
        <?php wp_nonce_field( 'ffl_dispatch_upload' ); ?>
    </form>
    <?php
    $token = sanitize_key( $_GET['dispatch_token'] ?? '' );
    $state = $token ? get_transient( 'ffl_dispatch_' . $token ) : false;
    if ( $token && is_array( $state ) ) : ?>
        <form id="ffl-dispatch-apply-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none">
            <input type="hidden" name="action" value="ffl_dispatch_apply">
            <input type="hidden" name="dispatch_token" value="<?php echo esc_attr( $token ); ?>">
            <?php wp_nonce_field( 'ffl_dispatch_apply_' . $token ); ?>
        </form>
        <form id="ffl-dispatch-cancel-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none">
            <input type="hidden" name="action" value="ffl_dispatch_cancel">
            <input type="hidden" name="dispatch_token" value="<?php echo esc_attr( $token ); ?>">
            <?php wp_nonce_field( 'ffl_dispatch_cancel_' . $token ); ?>
        </form>
    <?php endif;
}

function ffl_render_dispatch_import_page() {
    if ( ! ffl_dispatch_import_enabled() ) { wp_die( esc_html( ffl_lang( 'Der Depeschen-Import ist in den Einsatzlyzer-Einstellungen ausgeschaltet.', 'Dispatch PDF import is disabled in the Einsatzlyzer settings.' ) ) ); }
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) );
    $token = sanitize_key( $_GET['dispatch_token'] ?? '' );
    $data  = $token ? get_transient( 'ffl_dispatch_' . $token ) : false;
    ?>
    <div class="wrap"><h1><?php echo esc_html( ffl_lang( 'Einsatzdepesche importieren', 'Import Dispatch PDF' ) ); ?></h1>
    <?php if ( ! is_array( $data ) ) : ?>
        <div class="card" style="max-width:900px"><h2><?php echo esc_html( ffl_lang( 'PDF auswählen', 'Select PDF' ) ); ?></h2><p><?php echo esc_html( ffl_lang( 'Die Depesche wird zuerst nur analysiert. Vor dem Speichern sehen Sie einen Vergleich und entscheiden bei jeder Abweichung.', 'The dispatch is analyzed first. Before saving, you see a comparison and decide on every difference.' ) ); ?></p>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="ffl_dispatch_upload"><?php wp_nonce_field( 'ffl_dispatch_upload' ); ?><p><input type="file" name="dispatch_pdf" accept="application/pdf,.pdf" required></p><p><button class="button button-primary"><?php echo esc_html( ffl_lang( 'Depesche prüfen', 'Review Dispatch' ) ); ?></button></p></form></div>
    <?php else : ffl_render_dispatch_preview( $token, $data ); endif; ?>
    </div>
    <?php
}

/** Extrahiert PDF-Text zuerst rein in PHP; pdftotext bleibt optionaler Fallback. */
function ffl_dispatch_extract_pdf_text( $path ) {
    if ( ! is_readable( $path ) ) return new WP_Error( 'ffl_dispatch_read', ffl_lang( 'Die PDF-Datei konnte nicht gelesen werden.', 'The PDF could not be read.' ) );

    require_once FFL_EINSATZLYZER_DIR . 'includes/simple-pdf-text.php';
    try {
        $parser = new FFL_Simple_PDF_Text();
        $text = trim( (string) $parser->extract_file( $path ) );
        if ( '' !== $text ) return preg_replace( "/\r\n?/", "\n", $text );
    } catch ( Throwable $error ) {
        // Optionaler System-Fallback wird anschließend versucht.
    }

    $binary = function_exists( 'shell_exec' ) ? trim( (string) @shell_exec( 'command -v pdftotext 2>/dev/null' ) ) : '';
    if ( $binary && function_exists( 'exec' ) ) {
        $txt = wp_tempnam( 'einsatzdepesche.txt' );
        if ( $txt ) {
            $cmd = escapeshellarg( $binary ) . ' -layout -enc UTF-8 ' . escapeshellarg( $path ) . ' ' . escapeshellarg( $txt ) . ' 2>&1';
            @exec( $cmd, $output, $code );
            if ( 0 === (int) $code && is_readable( $txt ) ) {
                $text = trim( (string) file_get_contents( $txt ) );
                @unlink( $txt );
                if ( '' !== $text ) return preg_replace( "/\r\n?/", "\n", $text );
            }
            @unlink( $txt );
        }
    }

    return new WP_Error( 'ffl_dispatch_extract', ffl_lang( 'Die PDF enthält keinen maschinenlesbaren Text oder verwendet eine nicht unterstützte Codierung. Eingescannte PDFs benötigen Texterkennung (OCR).', 'The PDF contains no machine-readable text or uses an unsupported encoding. Scanned PDFs require OCR.' ) );
}

function ffl_dispatch_parse_datetime( $date, $time ) {
    $dt = DateTime::createFromFormat( 'd.m.Y H:i:s', trim( $date . ' ' . $time ), wp_timezone() );
    return $dt ? $dt->format( 'Y-m-d\TH:i' ) : '';
}

function ffl_dispatch_dms_to_decimal( $degrees, $minutes, $seconds, $direction ) {
    $value = (float) $degrees + (float) $minutes / 60 + (float) str_replace( ',', '.', $seconds ) / 3600;
    return in_array( strtoupper( $direction ), array( 'S', 'W' ), true ) ? -$value : $value;
}

/** Parser für KRLO-Einsatzdepeschen. */
function ffl_dispatch_parse_text( $text ) {
    $data = array( 'raw_text' => $text, 'resources' => array(), 'matched_vehicles' => array(), 'unknown_resources' => array() );
    $patterns = array(
        'control_number' => '/Einsatznummer:\s*([A-Z0-9\-]+)/iu',
        'start'          => '/Einsatzbeginn:\s*(\d{2}\.\d{2}\.\d{4})\s+(\d{2}:\d{2}:\d{2})/iu',
        'type'           => '/Einsatzart:\s*([^\n\r]+)/iu',
        'keyword'        => '/Einsatzstichwort:\s*([^\n\r]+)/iu',
        'selection'      => '/Auswahl:\s*([^\n\r]+)/iu',
        'emergency'      => '/Notsituation:\s*([^\n\r]+)/iu',
    );
    foreach ( $patterns as $key => $pattern ) if ( preg_match( $pattern, $text, $m ) ) $data[ $key ] = trim( $m[1] );
    if ( preg_match( $patterns['start'], $text, $m ) ) $data['start_datetime'] = ffl_dispatch_parse_datetime( $m[1], $m[2] );

    if ( preg_match( '/B\s*=\s*(\d+)°\s*(\d+)\'\s*([\d,.]+)"\s*,\s*L\s*=\s*(\d+)°\s*(\d+)\'\s*([\d,.]+)"/u', $text, $m ) ) {
        $data['lat'] = ffl_dispatch_dms_to_decimal( $m[1], $m[2], $m[3], 'N' );
        $data['lon'] = ffl_dispatch_dms_to_decimal( $m[4], $m[5], $m[6], 'E' );
    }

    $lines = array_values( array_filter( array_map( 'trim', explode( "\n", $text ) ), static function( $v ) { return '' !== $v; } ) );
    $address = array(); $in_address = false;
    foreach ( $lines as $line ) {
        if ( preg_match( '/^Einsatzort$/iu', $line ) ) { $in_address = true; continue; }
        if ( $in_address && preg_match( '/^(Anrufer:|Einsatzverlauf|Ressourcen)/iu', $line ) ) { $in_address = false; }
        if ( $in_address ) {
            $clean = preg_replace( '/^(Art:|Adresse:)\s*/iu', '', $line );
            if ( preg_match( '/^(Deutschland|Niedersachsen|Weser-Ems|Leer)$/iu', $clean ) ) continue;
            if ( preg_match( '/^B\s*=|^Telefonnummer:/iu', $clean ) ) continue;
            if ( '' !== $clean && ! in_array( $clean, $address, true ) ) $address[] = $clean;
        }
    }
    $data['location'] = implode( ', ', array_slice( $address, -4 ) );

    $registry = ffl_get_vehicle_registry(); $times = array();
    $line_count = count( $lines );
    for ( $index = 0; $index < $line_count; $index++ ) {
        $line = $lines[ $index ];
        if ( ! preg_match_all( '/\b(?:FL\s+[A-Z]{2,4}\s+)?(\d{2}-\d{1,2}-\d{1,2})\b/u', $line, $matches ) ) continue;

        preg_match_all( '/\b(\d{2}:\d{2}:\d{2})\b/', $line, $tm );
        $resource_times = $tm[1] ?? array();

        // Pure-PHP extraction may place the status times on following lines.
        for ( $lookahead = $index + 1; $lookahead < $line_count; $lookahead++ ) {
            if ( preg_match( '/^\d{2}:\d{2}:\d{2}$/', $lines[ $lookahead ] ) ) {
                $resource_times[] = $lines[ $lookahead ];
                continue;
            }
            break;
        }
        $resource_times = array_values( array_unique( $resource_times ) );
        foreach ( $resource_times as $t ) $times[] = $t;

        foreach ( array_unique( $matches[1] ?? array() ) as $raw_callsign ) {
            $callsign = ffl_normalize_callsign( $raw_callsign );
            if ( '' === $callsign ) continue;
            $data['resources'][] = array( 'raw' => $line, 'callsign' => $callsign, 'times' => $resource_times );
            if ( isset( $registry[ $callsign ] ) && $registry[ $callsign ]['active'] ) {
                $data['matched_vehicles'][ $callsign ] = $registry[ $callsign ];
                unset( $data['unknown_resources'][ $callsign ] );
            } elseif ( ! isset( $data['matched_vehicles'][ $callsign ] ) ) {
                $data['unknown_resources'][ $callsign ] = $callsign;
            }
        }
    }
    if ( ! empty( $data['start_datetime'] ) && $times ) {
        $date = substr( $data['start_datetime'], 0, 10 ); $latest = '';
        foreach ( $times as $time ) {
            $candidate = $date . 'T' . substr( $time, 0, 5 );
            if ( $candidate >= $data['start_datetime'] && $candidate > $latest ) $latest = $candidate;
        }
        $data['end_datetime'] = $latest;
    }
    $all = array(); $own = array(); $ids = array();
    foreach ( $data['matched_vehicles'] as $vehicle ) {
        $label = trim( $vehicle['title'] . ( $vehicle['station'] ? ' – ' . $vehicle['station'] : '' ) . ( $vehicle['callsign'] ? ' (' . $vehicle['callsign'] . ')' : '' ) );
        if ( '' !== $label ) $all[] = $label;
        if ( 'own' === $vehicle['scope'] && '' !== $label ) $own[] = $label;
        if ( ! empty( $vehicle['id'] ) ) $ids[] = absint( $vehicle['id'] );
    }
    $data['matched_vehicle_text'] = implode( ', ', array_unique( $all ) );
    $data['matched_vehicle_ids']  = array_values( array_unique( array_filter( $ids ) ) );
    $data['own_vehicle_text']     = implode( ', ', array_unique( $own ) );
    return $data;
}


/** Maskiert eine vertrauliche Leitstellenkennung für die interne Vorschau. */
function ffl_dispatch_mask_control_number( $value ) {
    $value = preg_replace( '/[^A-Z0-9\-]/i', '', (string) $value );
    if ( '' === $value ) return '';
    $length = strlen( $value );
    if ( $length <= 6 ) return str_repeat( '•', max( 1, $length - 2 ) ) . substr( $value, -2 );
    return substr( $value, 0, 3 ) . str_repeat( '•', max( 4, $length - 7 ) ) . substr( $value, -4 );
}

/** Speichert ausschließlich einen nicht rückrechenbaren Prüfwert, niemals die Leitstellenkennung selbst. */
function ffl_dispatch_control_hash( $value ) {
    $value = strtoupper( trim( (string) $value ) );
    return '' === $value ? '' : hash_hmac( 'sha256', $value, wp_salt( 'auth' ) );
}

/** Entfernt einmalig ältere, im Klartext gespeicherte Leitstellenkennungen. */
function ffl_dispatch_migrate_confidential_numbers() {
    if ( get_option( 'ffl_dispatch_confidential_migration', '' ) === '10.6.4' ) return;
    $ids = get_posts( array(
        'post_type'      => 'ffl_einsatz',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => array( array( 'key' => '_ffl_leitstellen_einsatznummer', 'compare' => 'EXISTS' ) ),
    ) );
    foreach ( $ids as $id ) {
        $raw = (string) get_post_meta( $id, '_ffl_leitstellen_einsatznummer', true );
        if ( '' !== $raw ) update_post_meta( $id, '_ffl_dispatch_control_hash', ffl_dispatch_control_hash( $raw ) );
        delete_post_meta( $id, '_ffl_leitstellen_einsatznummer' );
    }
    update_option( 'ffl_dispatch_confidential_migration', '10.6.4', false );
}

/** Zeigt die Vorschau direkt im geöffneten Einsatz statt auf der Beitragsübersicht. */
function ffl_dispatch_render_edit_screen_preview( $post ) {
    if ( ! ffl_dispatch_import_enabled() ) return;
    if ( ! $post instanceof WP_Post || 'ffl_einsatz' !== $post->post_type ) return;
    $token = sanitize_key( $_GET['dispatch_token'] ?? '' );
    if ( ! $token ) return;
    $state = get_transient( 'ffl_dispatch_' . $token );
    if ( ! is_array( $state ) || (int) ( $state['target_post_id'] ?? 0 ) !== (int) $post->ID ) return;
    echo '<div class="ffl-dispatch-inline-preview" style="margin:16px 0 24px">';
    ffl_render_dispatch_preview( $token, $state );
    echo '</div>';
}

function ffl_dispatch_handle_upload() {
    if ( ! ffl_dispatch_import_enabled() ) wp_die( esc_html( ffl_lang( 'Der Depeschen-Import ist ausgeschaltet.', 'Dispatch PDF import is disabled.' ) ) );
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) );
    check_admin_referer( 'ffl_dispatch_upload' );
    if ( empty( $_FILES['dispatch_pdf']['tmp_name'] ) || ! is_uploaded_file( $_FILES['dispatch_pdf']['tmp_name'] ) ) wp_die( esc_html( ffl_lang( 'Bitte eine PDF auswählen.', 'Please select a PDF.' ) ) );
    if ( 'pdf' !== strtolower( pathinfo( sanitize_file_name( $_FILES['dispatch_pdf']['name'] ), PATHINFO_EXTENSION ) ) ) wp_die( esc_html( ffl_lang( 'Es sind nur PDF-Dateien erlaubt.', 'Only PDF files are allowed.' ) ) );
    $text = ffl_dispatch_extract_pdf_text( $_FILES['dispatch_pdf']['tmp_name'] );
    if ( is_wp_error( $text ) ) wp_die( esc_html( $text->get_error_message() ) );
    $parsed = ffl_dispatch_parse_text( $text );
    $target = absint( $_POST['target_post_id'] ?? 0 );
    if ( $target && 'ffl_einsatz' !== get_post_type( $target ) ) $target = 0;
    $token = strtolower( wp_generate_password( 24, false, false ) );
    set_transient( 'ffl_dispatch_' . $token, array( 'parsed' => $parsed, 'target_post_id' => $target, 'source_name' => sanitize_file_name( $_FILES['dispatch_pdf']['name'] ), 'created_at' => time() ), HOUR_IN_SECONDS );
    if ( $target ) {
        wp_safe_redirect( add_query_arg( array( 'post' => $target, 'action' => 'edit', 'dispatch_token' => $token ), admin_url( 'post.php' ) ) );
    } else {
        wp_safe_redirect( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_dispatch_import', 'dispatch_token' => $token ), admin_url( 'edit.php' ) ) );
    }
    exit;
}

function ffl_dispatch_existing_values( $post_id ) {
    return array(
        'start_datetime' => get_post_meta( $post_id, '_ffl_alarmzeit', true ),
        'end_datetime'   => get_post_meta( $post_id, '_ffl_endezeit', true ),
        'keyword'        => get_post_meta( $post_id, '_ffl_alarmstichwort', true ),
        'location'       => get_post_meta( $post_id, '_ffl_einsatzort', true ),
        'lat'            => get_post_meta( $post_id, '_ffl_lat', true ),
        'lon'            => get_post_meta( $post_id, '_ffl_lon', true ),
        'vehicles'       => get_post_meta( $post_id, '_ffl_fahrzeuge', true ),
        'vehicle_ids'     => array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_ffl_vehicle_ids', true ) ) ) ),
    );
}

function ffl_dispatch_similarity_warning( $post_id, $parsed ) {
    if ( ! $post_id ) return '';
    $existing = ffl_dispatch_existing_values( $post_id ); $points = 0; $checks = 0;
    foreach ( array( 'start_datetime', 'keyword', 'location' ) as $key ) {
        if ( empty( $existing[ $key ] ) || empty( $parsed[ $key ] ) ) continue;
        $checks++;
        $left = strtolower( preg_replace( '/\s+/', ' ', (string) $existing[ $key ] ) );
        $right= strtolower( preg_replace( '/\s+/', ' ', (string) $parsed[ $key ] ) );
        if ( 'start_datetime' === $key ? substr( $left, 0, 10 ) === substr( $right, 0, 10 ) : ( false !== strpos( $left, $right ) || false !== strpos( $right, $left ) ) ) $points++;
    }
    if ( $checks >= 2 && $points < 2 ) return ffl_lang( 'Diese Depesche passt wahrscheinlich nicht zum geöffneten Einsatz. Datum, Stichwort oder Ort weichen deutlich ab.', 'This dispatch probably does not belong to the open incident. Date, keyword or location differ significantly.' );
    return '';
}

function ffl_render_dispatch_preview( $token, $data ) {
    $parsed = $data['parsed']; $target = absint( $data['target_post_id'] ?? 0 ); $existing = $target ? ffl_dispatch_existing_values( $target ) : array(); $warning = ffl_dispatch_similarity_warning( $target, $parsed );
    $fields = array(
        'start_datetime' => ffl_lang( 'Einsatzbeginn', 'Incident Start' ),
        'end_datetime'   => ffl_lang( 'Einsatzende', 'Incident End' ),
        'keyword'        => ffl_lang( 'Einsatzstichwort', 'Incident Keyword' ),
        'location'       => ffl_lang( 'Einsatzort', 'Incident Location' ),
        'lat'            => ffl_lang( 'Breitengrad', 'Latitude' ),
        'lon'            => ffl_lang( 'Längengrad', 'Longitude' ),
        'vehicles'       => ffl_lang( 'Eigene Fahrzeuge', 'Own Vehicles' ),
    );
    $parsed['vehicles'] = $parsed['matched_vehicle_text'] ?? '';
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    $inside_editor = $screen && 'post' === $screen->base && 'ffl_einsatz' === $screen->post_type;
    $apply_form_attr = $inside_editor ? ' form="ffl-dispatch-apply-form"' : '';
    $masked_control = ffl_dispatch_mask_control_number( $parsed['control_number'] ?? '' );
    ?>
    <?php if ( $masked_control ) : ?><div class="notice notice-warning inline"><p><strong><?php echo esc_html( ffl_lang( 'Vertrauliche Leitstellenkennung erkannt:', 'Confidential control-center identifier detected:' ) ); ?></strong> <code><?php echo esc_html( $masked_control ); ?></code><br><?php echo esc_html( ffl_lang( 'Die Kennung wird weder in den Einsatz übernommen noch im Frontend, in Jahresberichten oder in öffentlichen Exporten ausgegeben. Gespeichert wird ausschließlich ein nicht rückrechenbarer Prüfwert.', 'The identifier is not imported into the incident and is never exposed in the frontend, annual reports or public exports. Only a non-reversible verification hash is stored.' ) ); ?></p></div><?php endif; ?>
    <?php if ( $warning ) : ?><div class="notice notice-error inline"><p><strong><?php echo esc_html( $warning ); ?></strong></p></div><?php endif; ?>
    <div class="card" style="max-width:1100px"><h2><?php echo esc_html( ffl_lang( 'Importvorschau', 'Import Preview' ) ); ?></h2><p><strong><?php echo esc_html( $data['source_name'] ); ?></strong><?php if ( $target ) : ?> → <a href="<?php echo esc_url( get_edit_post_link( $target ) ); ?>"><?php echo esc_html( get_the_title( $target ) ); ?></a><?php endif; ?></p>
    <?php if ( ! $inside_editor ) : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="ffl_dispatch_apply"><input type="hidden" name="dispatch_token" value="<?php echo esc_attr( $token ); ?>"><?php wp_nonce_field( 'ffl_dispatch_apply_' . $token ); ?><?php endif; ?>
    <table class="widefat striped"><thead><tr><th><?php echo esc_html( ffl_lang( 'Feld', 'Field' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Vorhanden', 'Existing' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Aus Depesche', 'From Dispatch' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Aktion', 'Action' ) ); ?></th></tr></thead><tbody>
    <?php foreach ( $fields as $key => $label ) : $old = (string) ( $existing[ $key ] ?? '' ); $new = (string) ( $parsed[ $key ] ?? '' ); $same = '' !== $old && '' !== $new && trim( $old ) === trim( $new ); ?>
        <tr><th><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $old ?: '—' ); ?></td><td><?php echo esc_html( $new ?: '—' ); ?></td><td><select<?php echo $apply_form_attr; ?> name="field_action[<?php echo esc_attr( $key ); ?>]" <?php disabled( '' === $new ); ?>><option value="skip"><?php echo esc_html( ffl_lang( 'Nicht übernehmen', 'Do not import' ) ); ?></option><option value="apply" <?php selected( '' === $old || $same ); ?>><?php echo esc_html( $same ? ffl_lang( 'Identisch', 'Identical' ) : ffl_lang( 'Übernehmen / ersetzen', 'Apply / replace' ) ); ?></option><?php if ( 'vehicles' === $key && $old && $new ) : ?><option value="append"><?php echo esc_html( ffl_lang( 'Ergänzen', 'Append' ) ); ?></option><?php endif; ?></select></td></tr>
    <?php endforeach; ?></tbody></table>
    <?php if ( ! empty( $parsed['matched_vehicles'] ) ) : ?><h3><?php echo esc_html( ffl_lang( 'Erkannte Fahrzeuge', 'Recognized Vehicles' ) ); ?></h3><ul class="ffl-dispatch-vehicle-list"><?php foreach ( $parsed['matched_vehicles'] as $vehicle ) : ?><li><strong><?php echo esc_html( $vehicle['title'] ); ?></strong><?php if ( ! empty( $vehicle['station'] ) ) : ?> – <?php echo esc_html( $vehicle['station'] ); ?><?php endif; ?><?php if ( ! empty( $vehicle['callsign'] ) ) : ?> <code><?php echo esc_html( $vehicle['callsign'] ); ?></code><?php endif; ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ( ! empty( $parsed['unknown_resources'] ) ) : ?><h3><?php echo esc_html( ffl_lang( 'Nicht zugeordnete Funkrufnamen', 'Unassigned Call Signs' ) ); ?></h3><p><?php echo esc_html( implode( ', ', array_keys( $parsed['unknown_resources'] ) ) ); ?></p><p class="description"><?php echo esc_html( ffl_lang( 'Diese Funkrufnamen werden nicht als eigene Fahrzeuge übernommen. Legen Sie sie bei Bedarf zuerst unter „Fahrzeuge“ an.', 'These call signs are not imported as own vehicles. Add them under “Vehicles” first if required.' ) ); ?></p><?php endif; ?>
    <?php if ( $warning ) : ?><label><input<?php echo $apply_form_attr; ?> type="checkbox" name="confirm_mismatch" value="1"> <strong><?php echo esc_html( ffl_lang( 'Ich habe die Abweichungen geprüft und möchte trotzdem fortfahren.', 'I reviewed the differences and still want to continue.' ) ); ?></strong></label><?php endif; ?>
    <p><button<?php echo $apply_form_attr; ?> class="button button-primary" name="import_mode" value="existing" <?php disabled( ! $target ); ?>><?php echo esc_html( ffl_lang( 'In geöffneten Einsatz übernehmen', 'Apply to Open Incident' ) ); ?></button> <button<?php echo $apply_form_attr; ?> class="button" name="import_mode" value="new"><?php echo esc_html( ffl_lang( 'Als neuen Entwurf anlegen', 'Create as New Draft' ) ); ?></button></p><?php if ( ! $inside_editor ) : ?></form><?php endif; ?>
    <?php if ( $inside_editor ) : ?><button form="ffl-dispatch-cancel-form" class="button-link-delete" type="submit"><?php echo esc_html( ffl_lang( 'Import abbrechen', 'Cancel Import' ) ); ?></button><?php else : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="ffl_dispatch_cancel"><input type="hidden" name="dispatch_token" value="<?php echo esc_attr( $token ); ?>"><?php wp_nonce_field( 'ffl_dispatch_cancel_' . $token ); ?><button class="button-link-delete"><?php echo esc_html( ffl_lang( 'Import abbrechen', 'Cancel Import' ) ); ?></button></form><?php endif; ?></div>
    <?php
}

function ffl_dispatch_handle_apply() {
    if ( ! ffl_dispatch_import_enabled() ) wp_die( esc_html( ffl_lang( 'Der Depeschen-Import ist ausgeschaltet.', 'Dispatch PDF import is disabled.' ) ) );
    if ( ! current_user_can( 'edit_posts' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) );
    $token = sanitize_key( $_POST['dispatch_token'] ?? '' ); check_admin_referer( 'ffl_dispatch_apply_' . $token );
    $state = get_transient( 'ffl_dispatch_' . $token ); if ( ! is_array( $state ) ) wp_die( esc_html( ffl_lang( 'Die Importvorschau ist abgelaufen.', 'The import preview has expired.' ) ) );
    $parsed = $state['parsed']; $mode = sanitize_key( $_POST['import_mode'] ?? '' ); $target = absint( $state['target_post_id'] ?? 0 ); $warning = ffl_dispatch_similarity_warning( $target, $parsed );
    if ( 'existing' === $mode && ( ! $target || 'ffl_einsatz' !== get_post_type( $target ) ) ) wp_die( esc_html( ffl_lang( 'Der Zieleinsatz ist nicht verfügbar.', 'The target incident is unavailable.' ) ) );
    if ( 'existing' === $mode && $warning && empty( $_POST['confirm_mismatch'] ) ) wp_die( esc_html( ffl_lang( 'Bitte bestätigen Sie zuerst die deutlichen Abweichungen.', 'Please confirm the significant differences first.' ) ) );
    if ( 'new' === $mode ) {
        $title = trim( ( $parsed['keyword'] ?? '' ) . ( ! empty( $parsed['emergency'] ) ? ' – ' . $parsed['emergency'] : '' ) );
        $target = wp_insert_post( array( 'post_type' => 'ffl_einsatz', 'post_status' => 'draft', 'post_title' => $title ?: ffl_lang( 'Importierte Einsatzdepesche', 'Imported Dispatch' ) ), true );
        if ( is_wp_error( $target ) ) wp_die( esc_html( $target->get_error_message() ) );
    }
    $actions = array_map( 'sanitize_key', (array) ( $_POST['field_action'] ?? array() ) );
    $map = array( 'start_datetime' => '_ffl_alarmzeit', 'end_datetime' => '_ffl_endezeit', 'keyword' => '_ffl_alarmstichwort', 'location' => '_ffl_einsatzort', 'lat' => '_ffl_lat', 'lon' => '_ffl_lon', 'vehicles' => '_ffl_fahrzeuge' );
    foreach ( $map as $field => $meta ) {
        $action = $actions[ $field ] ?? 'skip'; $value = 'vehicles' === $field ? ( $parsed['matched_vehicle_text'] ?? '' ) : ( $parsed[ $field ] ?? '' ); if ( 'skip' === $action || '' === (string) $value ) continue;
        if ( 'append' === $action ) { $old = (string) get_post_meta( $target, $meta, true ); $parts = array_filter( array_map( 'trim', preg_split( '/[,\n;]+/', $old . ',' . $value ) ) ); $value = implode( ', ', array_unique( $parts ) ); }
        update_post_meta( $target, $meta, sanitize_textarea_field( (string) $value ) );
    }
    $vehicle_action = $actions['vehicles'] ?? 'skip';
    if ( 'skip' !== $vehicle_action && ! empty( $parsed['matched_vehicle_ids'] ) ) {
        $vehicle_ids = array_map( 'absint', (array) $parsed['matched_vehicle_ids'] );
        if ( 'append' === $vehicle_action ) {
            $existing_ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $target, '_ffl_vehicle_ids', true ) ) ) );
            $vehicle_ids = array_values( array_unique( array_merge( $existing_ids, $vehicle_ids ) ) );
        }
        update_post_meta( $target, '_ffl_vehicle_ids', implode( ',', array_values( array_unique( array_filter( $vehicle_ids ) ) ) ) );
    }
    if ( ! empty( $parsed['control_number'] ) ) {
        update_post_meta( $target, '_ffl_dispatch_control_hash', ffl_dispatch_control_hash( $parsed['control_number'] ) );
    }
    delete_post_meta( $target, '_ffl_leitstellen_einsatznummer' );
    update_post_meta( $target, '_ffl_dispatch_source_file', ffl_lang( 'Einsatzdepesche (PDF)', 'Dispatch PDF' ) );
    update_post_meta( $target, '_ffl_dispatch_imported_at', current_time( 'mysql' ) );
    delete_transient( 'ffl_dispatch_' . $token );
    set_transient( 'ffl_dispatch_notice_' . get_current_user_id(), ffl_lang( 'Die Einsatzdepesche wurde übernommen.', 'The dispatch was imported.' ), MINUTE_IN_SECONDS );
    wp_safe_redirect( get_edit_post_link( $target, 'url' ) ); exit;
}

function ffl_dispatch_handle_cancel() {
    $token = sanitize_key( $_POST['dispatch_token'] ?? '' );
    check_admin_referer( 'ffl_dispatch_cancel_' . $token );
    $state = get_transient( 'ffl_dispatch_' . $token );
    $target = is_array( $state ) ? absint( $state['target_post_id'] ?? 0 ) : 0;
    delete_transient( 'ffl_dispatch_' . $token );
    if ( $target && 'ffl_einsatz' === get_post_type( $target ) ) {
        wp_safe_redirect( get_edit_post_link( $target, 'url' ) );
    } else {
        wp_safe_redirect( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_dispatch_import' ), admin_url( 'edit.php' ) ) );
    }
    exit;
}

function ffl_dispatch_admin_notice() {
    $key = 'ffl_dispatch_notice_' . get_current_user_id(); $msg = get_transient( $key ); if ( ! $msg ) return; delete_transient( $key ); echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
}

/** Exportiert alle zentral verwalteten Fahrzeuge für die Vollsicherung. */
function ffl_export_vehicle_registry( &$zip_files = array(), &$checksums = array(), &$warnings = array() ) {
    $ids = get_posts( array( 'post_type' => 'ffl_fahrzeug', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'no_found_rows' => true ) );
    $items = array();
    foreach ( $ids as $id ) {
        $uuid = get_post_meta( $id, '_ffl_vehicle_uuid', true );
        if ( ! $uuid ) { $uuid = wp_generate_uuid4(); update_post_meta( $id, '_ffl_vehicle_uuid', $uuid ); }
        $item = array(
            'uuid'       => $uuid,
            'title'      => get_the_title( $id ),
            'status'     => get_post_status( $id ),
            'callsign'   => get_post_meta( $id, '_ffl_vehicle_callsign', true ),
            'municipality' => get_post_meta( $id, '_ffl_vehicle_municipality', true ),
            'station'    => get_post_meta( $id, '_ffl_vehicle_station', true ),
            'scope'      => get_post_meta( $id, '_ffl_vehicle_scope', true ) ?: 'own',
            'active'     => get_post_meta( $id, '_ffl_vehicle_active', true ),
            'year'       => get_post_meta( $id, '_ffl_vehicle_year', true ),
            'chassis'    => get_post_meta( $id, '_ffl_vehicle_chassis', true ),
            'body'       => get_post_meta( $id, '_ffl_vehicle_body', true ),
            'image'      => null,
        );
        $attachment_id = get_post_thumbnail_id( $id );
        if ( $attachment_id ) {
            $file = get_attached_file( $attachment_id );
            if ( $file && is_readable( $file ) ) {
                $filename = wp_basename( $file );
                $archive  = 'fahrzeugbilder/' . sanitize_file_name( $uuid ) . '-' . sanitize_file_name( $filename );
                $hash     = hash_file( 'sha256', $file );
                $zip_files[] = array( 'source' => $file, 'archive' => $archive );
                $checksums[ $archive ] = $hash;
                $attachment = get_post( $attachment_id );
                $item['image'] = array(
                    'file'        => $archive,
                    'filename'    => $filename,
                    'mime'        => get_post_mime_type( $attachment_id ),
                    'sha256'      => $hash,
                    'title'       => $attachment ? $attachment->post_title : pathinfo( $filename, PATHINFO_FILENAME ),
                    'caption'     => $attachment ? $attachment->post_excerpt : '',
                    'description' => $attachment ? $attachment->post_content : '',
                    'alt'         => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
                    'roles'       => array( 'vehicle_featured' ),
                    'content_urls'=> array(),
                    'meta'        => array(),
                );
            } else {
                $warnings[] = sprintf( 'Fahrzeugbild bei „%s“ fehlt auf dem Server.', get_the_title( $id ) );
            }
        }
        $items[] = $item;
    }
    return $items;
}

/** Importiert Fahrzeugdatensätze aus einer Vollsicherung ohne Dubletten. */
function ffl_import_vehicle_registry_file( $dir, &$state = array() ) {
    $file = trailingslashit( $dir ) . 'fahrzeuge.json';
    if ( ! is_readable( $file ) ) return 0;
    $items = json_decode( (string) file_get_contents( $file ), true ); if ( ! is_array( $items ) ) return 0;
    $count = 0;
    foreach ( $items as $item ) {
        if ( ! is_array( $item ) ) continue;
        $callsign = sanitize_text_field( $item['callsign'] ?? '' ); $normalized = ffl_normalize_callsign( $callsign ); $existing = 0;
        foreach ( ffl_get_vehicle_registry() as $key => $vehicle ) if ( $normalized && $key === $normalized ) { $existing = $vehicle['id']; break; }
        $postarr = array( 'post_type' => 'ffl_fahrzeug', 'post_title' => sanitize_text_field( $item['title'] ?? $callsign ), 'post_status' => in_array( $item['status'] ?? '', array( 'publish', 'draft', 'private' ), true ) ? $item['status'] : 'publish' );
        if ( $existing ) $postarr['ID'] = $existing;
        $id = wp_insert_post( $postarr, true ); if ( is_wp_error( $id ) ) continue;
        update_post_meta( $id, '_ffl_vehicle_uuid', sanitize_text_field( $item['uuid'] ?? wp_generate_uuid4() ) );
        update_post_meta( $id, '_ffl_vehicle_callsign', $callsign );
        update_post_meta( $id, '_ffl_vehicle_municipality', sanitize_text_field( $item['municipality'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_station', sanitize_text_field( $item['station'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_scope', 'external' === ( $item['scope'] ?? '' ) ? 'external' : 'own' );
        update_post_meta( $id, '_ffl_vehicle_active', '0' === (string) ( $item['active'] ?? '1' ) ? '0' : '1' );
        update_post_meta( $id, '_ffl_vehicle_year', sanitize_text_field( $item['year'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_chassis', sanitize_text_field( $item['chassis'] ?? '' ) );
        update_post_meta( $id, '_ffl_vehicle_body', sanitize_text_field( $item['body'] ?? '' ) );
        if ( ! empty( $item['image']['file'] ) && function_exists( 'ffl_impex_import_attachment' ) ) {
            $attachment_id = ffl_impex_import_attachment( $item['image'], $id, $dir, $state );
            if ( $attachment_id && ! is_wp_error( $attachment_id ) ) set_post_thumbnail( $id, $attachment_id );
        }
        if ( ! isset( $state['vehicle_uuid_map'] ) || ! is_array( $state['vehicle_uuid_map'] ) ) $state['vehicle_uuid_map'] = array();
        $vehicle_uuid = sanitize_text_field( $item['uuid'] ?? '' );
        if ( $vehicle_uuid ) $state['vehicle_uuid_map'][ $vehicle_uuid ] = (int) $id;
        $count++;
    }
    return $count;
}
