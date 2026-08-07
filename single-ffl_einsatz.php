<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
ffl_render_page_header();

while ( have_posts() ) :
    the_post();
    $post_id       = get_the_ID();
    $style         = ffl_term_style( $post_id );
    $timestamp     = ffl_get_alarm_timestamp( $post_id );
    $alarm         = ffl_meta_value( $post_id, '_ffl_alarmzeit' );
    $end           = ffl_meta_value( $post_id, '_ffl_endezeit' );
    $location      = ffl_meta_value( $post_id, '_ffl_einsatzort' );
    $leader        = ffl_meta_value( $post_id, '_ffl_einsatzleiter' );
    $keyword       = ffl_meta_value( $post_id, '_ffl_alarmstichwort' );
    $personnel     = ffl_meta_value( $post_id, '_ffl_einsatzkraefte' );
    $summary       = ffl_get_summary( $post_id, 44 );
    $duration      = ffl_get_duration( $post_id );
    $vehicles      = ffl_parse_list( ffl_meta_value( $post_id, '_ffl_fahrzeuge' ) );
    $units         = ffl_parse_list( ffl_meta_value( $post_id, '_ffl_einheiten' ) );
    $organisations = ffl_parse_list( ffl_meta_value( $post_id, '_ffl_organisationen' ) );
    $timeline      = ffl_parse_timeline( ffl_meta_value( $post_id, '_ffl_timeline' ) );
    $weather       = ffl_get_weather_data( $post_id );
    $gallery_ids   = ffl_get_gallery_ids( $post_id );
    $preview_id    = ffl_get_preview_image_id( $post_id );
    $visual_icon   = ffl_get_visual_icon( $post_id );
    $image_credit  = ffl_meta_value( $post_id, '_ffl_bildquelle' );
    $coords        = ffl_get_public_coordinates( $post_id );
    $distance      = $coords ? ffl_get_einsatz_distance( $post_id, $coords ) : null;
    $map_url       = $coords ? ffl_get_external_map_url( $coords, $location ?: get_the_title() ) : '';
    $osm_route_url = $coords ? ffl_get_osm_route_url( $coords ) : '';
    $report_html   = ffl_get_report_html( $post_id );
    $links         = array();
    for ( $i = 1; $i <= 5; $i++ ) {
        $url    = get_post_meta( $post_id, '_ffl_link_' . $i, true );
        $source = trim( (string) get_post_meta( $post_id, '_ffl_link_source_' . $i, true ) );
        if ( $url ) {
            $links[] = array( 'url' => $url, 'source' => $source );
        }
    }
    $print_classes = array( 'ffl-single' );
    foreach ( array( 'logo', 'images', 'weather', 'timeline', 'vehicles', 'internal' ) as $print_option ) {
        $print_classes[] = (int) get_option( 'ffl_print_' . $print_option, 1 ) ? 'ffl-print-' . $print_option . '-on' : 'ffl-print-' . $print_option . '-off';
    }
    ?>
    <main class="<?php echo esc_attr( implode( ' ', $print_classes ) ); ?>" style="--ffl-accent:<?php echo esc_attr( $style['color'] ); ?>;--ffl-hero-desktop-height:<?php echo esc_attr( ffl_get_single_hero_height() ); ?>px">
        <div class="ffl-print-header">
            <?php $print_logo_id = absint( get_theme_mod( 'custom_logo', 0 ) ); ?>
            <?php if ( $print_logo_id ) : ?><?php echo wp_get_attachment_image( $print_logo_id, 'medium', false, array( 'alt' => get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ) ) ); ?><?php endif; ?>
            <strong><?php echo esc_html( get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ) ); ?></strong>
        </div>
        <section class="ffl-single-hero <?php echo $preview_id ? 'has-image' : 'no-image'; ?>">
            <?php if ( $preview_id ) : ?>
                <div class="ffl-single-hero__image"><?php echo wp_get_attachment_image( $preview_id, 'full', false, array( 'fetchpriority' => 'high', 'alt' => get_the_title(), 'sizes' => '100vw' ) ); ?></div>
            <?php else : ?>
                <div class="ffl-single-hero__fallback" aria-hidden="true">
                    <span class="ffl-fallback-visual__grid"></span>
                    <span class="ffl-fallback-visual__rings"></span>
                    <span class="ffl-fallback-visual__signal ffl-fallback-visual__signal--<?php echo esc_attr( $visual_icon ); ?>"><?php echo ffl_icon( $visual_icon ); ?></span>
                    <span class="ffl-fallback-visual__label"><?php echo esc_html( ffl_lang( 'Einsatz', 'Incident' ) ); ?></span>
                    <span class="ffl-fallback-visual__number"><?php echo esc_html( ffl_get_einsatz_number( $post_id ) ); ?></span>
                </div>
            <?php endif; ?>
            <div class="ffl-single-hero__overlay"></div>
            <div class="ffl-shell ffl-single-hero__content">
                <a href="<?php echo esc_url( ffl_get_archive_url() ); ?>" class="ffl-back-link">← <?php echo esc_html( ffl_lang( 'Alle Einsätze', 'All Incidents' ) ); ?></a>
                <div class="ffl-single-hero__labels">
                    <span class="ffl-type-badge"><?php echo esc_html( ffl_term_display_name( $style['name'] ) ); ?></span>
                    <?php if ( $keyword ) : ?><span class="ffl-keyword-badge"><?php echo esc_html( $keyword ); ?></span><?php endif; ?>
                </div>
                <h1><?php the_title(); ?></h1>
                <div class="ffl-single-hero__meta">
                    <span><?php echo esc_html( ffl_archive_date( $timestamp ) ); ?></span>
                    <span><?php echo esc_html( ffl_lang( 'Einsatz', 'Incident' ) ); ?> <?php echo esc_html( ffl_get_einsatz_number( $post_id ) ); ?></span>
                    <?php if ( $alarm ) : ?><span><?php echo esc_html( wp_date( 'H:i', strtotime( $alarm ) ) ); ?><?php echo 'de' === ffl_get_plugin_language() ? ' Uhr' : ''; ?></span><?php endif; ?>
                </div>
            </div>
        </section>

        <nav class="ffl-mobile-jumpnav" aria-label="<?php echo esc_attr( ffl_lang( 'Abschnitte', 'Sections' ) ); ?>">
            <a href="#einsatzbericht"><?php echo esc_html( ffl_lang( 'Bericht', 'Report' ) ); ?></a>
            <?php if ( $gallery_ids ) : ?><a href="#einsatzbilder"><?php echo esc_html( ffl_lang( 'Bilder', 'Images' ) ); ?></a><?php endif; ?>
            <?php if ( $coords ) : ?><a href="#einsatzkarte"><?php echo esc_html( ffl_lang( 'Karte', 'Map' ) ); ?></a><?php endif; ?>
            <button type="button" data-share-url="<?php echo esc_url( get_permalink() ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo esc_html( ffl_lang( 'Teilen', 'Share' ) ); ?></button>
            <button type="button" onclick="window.print()"><?php echo esc_html( ffl_lang( 'Drucken', 'Print' ) ); ?></button>
        </nav>

        <div class="ffl-shell ffl-single-layout">
            <div class="ffl-single-main">
                <section class="ffl-fact-grid" aria-label="<?php echo esc_attr( ffl_lang( 'Einsatzdetails', 'Incident Details' ) ); ?>">
                    <?php if ( $alarm ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small><?php echo esc_html( ffl_lang( 'Alarmierung', 'Alert' ) ); ?></small><strong><?php echo esc_html( wp_date( 'd.m.Y · H:i', strtotime( $alarm ) ) ); ?><?php echo 'de' === ffl_get_plugin_language() ? ' Uhr' : ''; ?></strong></div></div>
                    <?php endif; ?>
                    <?php if ( $end ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small><?php echo esc_html( ffl_lang( 'Einsatzende', 'Incident End' ) ); ?></small><strong><?php echo esc_html( wp_date( 'd.m.Y · H:i', strtotime( $end ) ) ); ?><?php echo 'de' === ffl_get_plugin_language() ? ' Uhr' : ''; ?></strong></div></div>
                    <?php endif; ?>
                    <?php if ( $duration ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small><?php echo esc_html( ffl_lang( 'Dauer', 'Duration' ) ); ?></small><strong><?php echo esc_html( $duration ); ?></strong></div></div>
                    <?php endif; ?>
                    <?php if ( $location ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'pin' ); ?></span><div><small><?php echo esc_html( ffl_lang( 'Einsatzort', 'Incident Location' ) ); ?></small><strong><?php echo esc_html( $location ); ?></strong></div></div>
                    <?php endif; ?>
                </section>

                <?php if ( $summary ) : ?>
                    <section class="ffl-summary-box">
                        <span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Kurz zusammengefasst', 'Summary' ) ); ?></span>
                        <p><?php echo esc_html( $summary ); ?></p>
                    </section>
                <?php endif; ?>

                <article id="einsatzbericht" class="ffl-report">
                    <header class="ffl-section-heading"><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Einsatzbericht', 'Incident Report' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Was ist passiert?', 'What happened?' ) ); ?></h2></header>
                    <div class="ffl-report__content">
                        <?php echo $report_html ? wp_kses_post( $report_html ) : '<p>' . esc_html( ffl_lang( 'Zu diesem Einsatz liegt noch kein ausführlicher Bericht vor.', 'No detailed report is available for this incident yet.' ) ) . '</p>'; ?>
                    </div>
                </article>

                <?php if ( $timeline ) : ?>
                    <section class="ffl-timeline-section">
                        <header class="ffl-section-heading"><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Einsatzverlauf', 'Incident Timeline' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Der Einsatz in zeitlicher Folge', 'Incident chronology' ) ); ?></h2></header>
                        <ol class="ffl-timeline">
                            <?php foreach ( $timeline as $event ) : ?>
                                <li><time><?php echo esc_html( $event['time'] ); ?></time><div><?php echo esc_html( $event['event'] ); ?></div></li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if ( $weather ) : ?>
                    <section class="ffl-weather-section" id="einsatzwetter">
                        <header class="ffl-section-heading"><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Historische Wetterlage', 'Historical Weather' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Wetter zum Einsatzzeitpunkt', 'Weather at the Incident Time' ) ); ?></h2></header>
                        <div class="ffl-weather-card">
                            <strong><?php echo esc_html( ffl_weather_code_label( $weather['weather_code'] ?? -1 ) ); ?></strong>
                            <dl>
                                <div><dt><?php echo esc_html( ffl_lang( 'Temperatur', 'Temperature' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( (float) ( $weather['temperature'] ?? 0 ), 1 ) ); ?> °C</dd></div>
                                <div><dt><?php echo esc_html( ffl_lang( 'Niederschlag', 'Precipitation' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( (float) ( $weather['precipitation'] ?? 0 ), 1 ) ); ?> mm</dd></div>
                                <div><dt><?php echo esc_html( ffl_lang( 'Wind', 'Wind' ) ); ?></dt><dd><?php echo esc_html( ffl_wind_direction_label( (float) ( $weather['wind_direction'] ?? 0 ) ) . ' · ' . number_format_i18n( (float) ( $weather['wind_speed'] ?? 0 ), 1 ) ); ?> km/h</dd></div>
                                <div><dt><?php echo esc_html( ffl_lang( 'Böen', 'Gusts' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( (float) ( $weather['wind_gusts'] ?? 0 ), 1 ) ); ?> km/h</dd></div>
                            </dl>
                            <?php if ( 'default_location' === ( $weather['coordinate_source'] ?? 'incident' ) ) : ?>
                                <div class="ffl-weather-source">
                                    <p><strong><?php echo esc_html( ffl_lang( 'Historische Wetterdaten von Open-Meteo für den Einsatzzeitpunkt.', 'Historical weather data from Open-Meteo for the incident time.' ) ); ?></strong></p>
                                    <p><strong><?php echo esc_html( ffl_lang( 'Bezugsort:', 'Reference location:' ) ); ?></strong> <?php echo esc_html( ffl_lang( 'Feuerwehrhaus / Standardstandort', 'Fire station / default location' ) . ' · ' . ( $weather['coordinate_label'] ?? ffl_lang( 'Standardstandort', 'Default location' ) ) ); ?></p>
                                    <p><?php echo esc_html( ffl_lang( 'Rekonstruierte Rasterdaten, keine Messung direkt an der Einsatzstelle.', 'Reconstructed grid data, not a direct measurement at the incident location.' ) ); ?></p>
                                </div>
                            <?php else : ?>
                                <div class="ffl-weather-source">
                                    <p><strong><?php echo esc_html( ffl_lang( 'Datenquelle:', 'Data source:' ) ); ?></strong> Open-Meteo · <strong><?php echo esc_html( ffl_lang( 'Bezugsort:', 'Reference location:' ) ); ?></strong> <?php echo esc_html( ffl_lang( 'Einsatzstelle', 'Incident location' ) ); ?></p>
                                    <p><?php echo esc_html( ffl_lang( 'Rekonstruierte historische Rasterdaten, keine direkte Messung vor Ort.', 'Reconstructed historical grid data, not a direct measurement on site.' ) ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( $gallery_ids ) : ?>
                    <section id="einsatzbilder" class="ffl-gallery-section">
                        <header class="ffl-section-heading ffl-section-heading--split ffl-gallery-heading"><h2><?php echo esc_html( ffl_lang( 'Einsatzbilder', 'Incident Images' ) ); ?></h2><span class="ffl-photo-count"><?php echo esc_html( count( $gallery_ids ) ); ?> <?php echo esc_html( count( $gallery_ids ) === 1 ? ffl_lang( 'Bild', 'Image' ) : ffl_lang( 'Bilder', 'Images' ) ); ?></span></header>
                        <div class="ffl-gallery-grid <?php echo count( $gallery_ids ) === 1 ? 'is-single' : ''; ?>" data-gallery>
                            <?php foreach ( $gallery_ids as $index => $image_id ) :
                                $full    = wp_get_attachment_image_url( $image_id, 'full' );
                                $caption = wp_get_attachment_caption( $image_id );
                                if ( ! $full ) { continue; }
                                ?>
                                <button type="button" class="ffl-gallery-item <?php echo $index === 0 ? 'is-featured' : ''; ?>" data-gallery-item data-full="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( $caption ?: $image_credit ); ?>" aria-label="<?php echo esc_attr( sprintf( ffl_lang( 'Bild %d öffnen', 'Open image %d' ), $index + 1 ) ); ?>">
                                    <?php echo wp_get_attachment_image( $image_id, $index === 0 ? 'large' : 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
                                    <?php if ( $index === 4 && count( $gallery_ids ) > 5 ) : ?><span class="ffl-gallery-more">+<?php echo esc_html( count( $gallery_ids ) - 5 ); ?> <?php echo esc_html( ffl_lang( 'weitere', 'more' ) ); ?></span><?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( $image_credit ) : ?><p class="ffl-image-credit"><?php echo esc_html( ffl_lang( 'Bilder:', 'Images:' ) ); ?> <?php echo esc_html( $image_credit ); ?></p><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ( $coords ) : ?>
                    <section id="einsatzkarte" class="ffl-map-section">
                        <header class="ffl-section-heading ffl-section-heading--split"><div><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Einsatzort', 'Incident Location' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Wo der Einsatz stattfand', 'Location of the Incident' ) ); ?></h2></div><?php if ( $coords['privacy'] === 'approx' ) : ?><span class="ffl-privacy-label"><?php echo esc_html( ffl_lang( 'Position aus Datenschutzgründen angenähert', 'Position approximated for privacy reasons' ) ); ?></span><?php endif; ?></header>
                        <div class="ffl-single-map" data-single-map data-lat="<?php echo esc_attr( $coords['lat'] ); ?>" data-lon="<?php echo esc_attr( $coords['lon'] ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-location="<?php echo esc_attr( $location ); ?>" data-color="<?php echo esc_attr( $style['color'] ); ?>" data-icon="<?php echo esc_attr( $visual_icon ); ?>" tabindex="0" aria-label="<?php echo esc_attr( ffl_lang( 'Interaktive Karte des Einsatzortes', 'Interactive Map of the Incident Location' ) ); ?>"></div>
                        <div class="ffl-map-footer">
                            <div class="ffl-map-footer__meta">
                                <div><?php echo ffl_icon( 'pin' ); ?><span><?php echo esc_html( $location ); ?></span></div>
                                <?php if ( $distance ) : ?>
                                    <div class="ffl-distance-note"><?php echo ffl_icon( 'arrow' ); ?><span><?php echo esc_html( $distance['label'] ); ?> <?php echo esc_html( ffl_lang( 'ab', 'from' ) ); ?> <?php echo esc_html( $distance['station_name'] ); ?></span></div>
                                <?php endif; ?>
                            </div>
                            <div class="ffl-map-footer__actions">
                                <?php if ( $map_url ) : ?><a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( ffl_lang( 'In Karte öffnen', 'Open in Map' ) ); ?> →</a><?php endif; ?>
                                <?php if ( $osm_route_url ) : ?><a href="<?php echo esc_url( $osm_route_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( ffl_lang( 'Route in OpenStreetMap planen', 'Plan Route in OpenStreetMap' ) ); ?> →</a><?php endif; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( $links ) : ?>
                    <section class="ffl-links-section">
                        <header class="ffl-section-heading"><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Weitere Informationen', 'Additional Information' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Weiterführende Berichte', 'Further Information' ) ); ?></h2></header>
                        <div class="ffl-external-links">
                            <?php foreach ( $links as $index => $link ) : ?>
                                <a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="ffl-external-link__text">
                                        <strong><?php echo esc_html( $link['source'] ?: sprintf( ffl_lang( 'Externer Bericht %d', 'External Report %d' ), $index + 1 ) ); ?></strong>
                                        <?php if ( $link['source'] ) : ?><small><?php echo esc_html( ffl_lang( 'Weiterführender Bericht', 'Further report' ) ); ?></small><?php endif; ?>
                                    </span>
                                    <?php echo ffl_icon( 'arrow' ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="ffl-single-sidebar">
                <div class="ffl-sidebar-card">
                    <span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Einsatzdetails', 'Incident Details' ) ); ?></span>
                    <h2><?php echo esc_html( ffl_lang( 'Auf einen Blick', 'At a Glance' ) ); ?></h2>
                    <dl>
                        <?php if ( $keyword ) : ?><div><dt><?php echo esc_html( ffl_lang( 'Alarmstichwort', 'Alert Keyword' ) ); ?></dt><dd><?php echo esc_html( $keyword ); ?></dd></div><?php endif; ?>
                        <div><dt><?php echo esc_html( ffl_lang( 'Einsatzart', 'Incident Type' ) ); ?></dt><dd><?php echo esc_html( ffl_term_display_name( $style['name'] ) ); ?></dd></div>
                        <?php if ( $leader ) : ?><div class="ffl-print-internal-detail"><dt><?php echo esc_html( ffl_lang( 'Einsatzleiter', 'Incident Commander' ) ); ?></dt><dd><?php echo esc_html( $leader ); ?></dd></div><?php endif; ?>
                        <?php if ( $personnel ) : ?><div class="ffl-print-internal-detail"><dt><?php echo esc_html( ffl_lang( 'Einsatzkräfte', 'Personnel' ) ); ?></dt><dd><?php echo esc_html( $personnel ); ?></dd></div><?php endif; ?>
                        <?php if ( $duration ) : ?><div><dt><?php echo esc_html( ffl_lang( 'Dauer', 'Duration' ) ); ?></dt><dd><?php echo esc_html( $duration ); ?></dd></div><?php endif; ?>
                        <?php if ( $distance ) : ?><div><dt><?php echo esc_html( ffl_lang( 'Entfernung', 'Distance' ) ); ?></dt><dd><?php echo esc_html( $distance['label'] ); ?><small><?php echo esc_html( ffl_lang( 'ab', 'from' ) ); ?> <?php echo esc_html( $distance['station_name'] ); ?></small></dd></div><?php endif; ?>
                    </dl>
                </div>

                <?php if ( $vehicles || $units || $organisations ) : ?>
                    <div class="ffl-sidebar-card ffl-print-forces">
                        <span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Beteiligte Kräfte', 'Units Involved' ) ); ?></span>
                        <?php if ( $vehicles ) : ?><h3><?php echo esc_html( ffl_lang( 'Fahrzeuge', 'Vehicles' ) ); ?></h3><ul><?php foreach ( $vehicles as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <?php if ( $units ) : ?><h3><?php echo esc_html( ffl_lang( 'Feuerwehren & Einheiten', 'Fire Departments & Units' ) ); ?></h3><ul><?php foreach ( $units as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <?php if ( $organisations ) : ?><h3><?php echo esc_html( ffl_lang( 'Weitere Organisationen', 'Other Organizations' ) ); ?></h3><ul><?php foreach ( $organisations as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="ffl-sidebar-card ffl-print-card">
                    <span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Druckansicht', 'Print View' ) ); ?></span>
                    <h3><?php echo esc_html( ffl_lang( 'Einsatzübersicht drucken', 'Print Incident Summary' ) ); ?></h3>
                    <p><?php echo esc_html( ffl_lang( 'Navigation, interaktive Karte und Teilen-Funktionen werden beim Drucken ausgeblendet.', 'Navigation, interactive map and sharing controls are hidden when printing.' ) ); ?></p>
                    <button type="button" class="ffl-button ffl-button--secondary" onclick="window.print()"><?php echo esc_html( ffl_lang( 'Einsatz drucken', 'Print Incident' ) ); ?></button>
                </div>

                <div class="ffl-sidebar-card ffl-share-card">
                    <span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Weitergeben', 'Share' ) ); ?></span>
                    <h3><?php echo esc_html( ffl_lang( 'Einsatzbericht teilen', 'Share Incident Report' ) ); ?></h3>
                    <div class="ffl-share-grid">
                        <a class="ffl-share-option ffl-share-option--whatsapp" href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ffl_lang( 'Über WhatsApp teilen', 'Share via WhatsApp' ) ); ?>"><span>WA</span>WhatsApp</a>
                        <a class="ffl-share-option ffl-share-option--facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ffl_lang( 'Auf Facebook teilen', 'Share on Facebook' ) ); ?>"><span>f</span>Facebook</a>
                        <a class="ffl-share-option ffl-share-option--email" href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&amp;body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="<?php echo esc_attr( ffl_lang( 'Per E-Mail teilen', 'Share by Email' ) ); ?>"><span>@</span>E-Mail</a>
                        <button type="button" class="ffl-share-option ffl-share-option--copy" data-copy-url="<?php echo esc_url( get_permalink() ); ?>"><span><?php echo ffl_icon( 'arrow' ); ?></span><?php echo esc_html( ffl_lang( 'Link kopieren', 'Copy Link' ) ); ?></button>
                    </div>
                    <button type="button" class="ffl-button ffl-button--primary ffl-native-share" data-share-url="<?php echo esc_url( get_permalink() ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo ffl_icon( 'share' ); ?> <?php echo esc_html( ffl_lang( 'Weitere Teilen-Optionen', 'More Sharing Options' ) ); ?></button>
                    <span class="ffl-copy-status" aria-live="polite"></span>
                </div>
            </aside>
        </div>

        <?php
        $related_ids = ffl_related_incident_ids( $post_id, 3 );
        $related = new WP_Query( array( 'post_type' => 'ffl_einsatz', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__in' => $related_ids ?: array( 0 ), 'orderby' => 'post__in' ) );
        if ( $related->have_posts() ) : ?>
            <section class="ffl-related">
                <div class="ffl-shell">
                    <header class="ffl-section-heading ffl-section-heading--split"><div><span class="ffl-section-kicker"><?php echo esc_html( ffl_lang( 'Weitere Einsätze', 'More Incidents' ) ); ?></span><h2><?php echo esc_html( ffl_lang( 'Das könnte ebenfalls interessieren', 'You May Also Be Interested In' ) ); ?></h2></div><a href="<?php echo esc_url( ffl_get_archive_url() ); ?>"><?php echo esc_html( ffl_lang( 'Zum Einsatzarchiv', 'Go to Incident Archive' ) ); ?> →</a></header>
                    <div class="ffl-card-grid ffl-card-grid--related"><?php while ( $related->have_posts() ) : $related->the_post(); ffl_render_einsatz_card( get_the_ID() ); endwhile; ?></div>
                </div>
            </section>
        <?php endif; wp_reset_postdata(); ?>
    </main>

    <div class="ffl-lightbox" data-lightbox hidden aria-hidden="true">
        <button type="button" class="ffl-lightbox__close" data-lightbox-close aria-label="<?php echo esc_attr( ffl_lang( 'Galerie schließen', 'Close Gallery' ) ); ?>">&times;</button>
        <button type="button" class="ffl-lightbox__nav ffl-lightbox__nav--prev" data-lightbox-prev aria-label="<?php echo esc_attr( ffl_lang( 'Vorheriges Bild', 'Previous Image' ) ); ?>">‹</button>
        <figure><img src="" alt=""><figcaption></figcaption></figure>
        <button type="button" class="ffl-lightbox__nav ffl-lightbox__nav--next" data-lightbox-next aria-label="<?php echo esc_attr( ffl_lang( 'Nächstes Bild', 'Next Image' ) ); ?>">›</button>
        <div class="ffl-lightbox__count" data-lightbox-count></div>
    </div>
    <?php
endwhile;

ffl_render_manual_single_footer_template();
get_footer();
