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
        $url = get_post_meta( $post_id, '_ffl_link_' . $i, true );
        if ( $url ) {
            $links[] = $url;
        }
    }
    ?>
    <main class="ffl-single" style="--ffl-accent:<?php echo esc_attr( $style['color'] ); ?>;--ffl-hero-desktop-height:<?php echo esc_attr( ffl_get_single_hero_height() ); ?>px">
        <section class="ffl-single-hero <?php echo $preview_id ? 'has-image' : 'no-image'; ?>">
            <?php if ( $preview_id ) : ?>
                <div class="ffl-single-hero__image"><?php echo wp_get_attachment_image( $preview_id, 'full', false, array( 'fetchpriority' => 'high', 'alt' => get_the_title(), 'sizes' => '100vw' ) ); ?></div>
            <?php else : ?>
                <div class="ffl-single-hero__fallback" aria-hidden="true">
                    <span class="ffl-fallback-visual__grid"></span>
                    <span class="ffl-fallback-visual__rings"></span>
                    <span class="ffl-fallback-visual__signal ffl-fallback-visual__signal--<?php echo esc_attr( $visual_icon ); ?>"><?php echo ffl_icon( $visual_icon ); ?></span>
                    <span class="ffl-fallback-visual__label">Einsatz</span>
                    <span class="ffl-fallback-visual__number"><?php echo esc_html( ffl_get_einsatz_number( $post_id ) ); ?></span>
                </div>
            <?php endif; ?>
            <div class="ffl-single-hero__overlay"></div>
            <div class="ffl-shell ffl-single-hero__content">
                <a href="<?php echo esc_url( ffl_get_archive_url() ); ?>" class="ffl-back-link">← Alle Einsätze</a>
                <div class="ffl-single-hero__labels">
                    <span class="ffl-type-badge"><?php echo esc_html( $style['name'] ); ?></span>
                    <?php if ( $keyword ) : ?><span class="ffl-keyword-badge"><?php echo esc_html( $keyword ); ?></span><?php endif; ?>
                </div>
                <h1><?php the_title(); ?></h1>
                <div class="ffl-single-hero__meta">
                    <span><?php echo esc_html( wp_date( 'd. F Y', $timestamp ) ); ?></span>
                    <span>Einsatz <?php echo esc_html( ffl_get_einsatz_number( $post_id ) ); ?></span>
                    <?php if ( $alarm ) : ?><span><?php echo esc_html( wp_date( 'H:i', strtotime( $alarm ) ) ); ?> Uhr</span><?php endif; ?>
                </div>
            </div>
        </section>

        <nav class="ffl-mobile-jumpnav" aria-label="Abschnitte">
            <a href="#einsatzbericht">Bericht</a>
            <?php if ( $gallery_ids ) : ?><a href="#einsatzbilder">Bilder</a><?php endif; ?>
            <?php if ( $coords ) : ?><a href="#einsatzkarte">Karte</a><?php endif; ?>
            <button type="button" data-share-url="<?php echo esc_url( get_permalink() ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>">Teilen</button>
        </nav>

        <div class="ffl-shell ffl-single-layout">
            <div class="ffl-single-main">
                <section class="ffl-fact-grid" aria-label="Einsatzdetails">
                    <?php if ( $alarm ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small>Alarmierung</small><strong><?php echo esc_html( wp_date( 'd.m.Y · H:i', strtotime( $alarm ) ) ); ?> Uhr</strong></div></div>
                    <?php endif; ?>
                    <?php if ( $end ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small>Einsatzende</small><strong><?php echo esc_html( wp_date( 'd.m.Y · H:i', strtotime( $end ) ) ); ?> Uhr</strong></div></div>
                    <?php endif; ?>
                    <?php if ( $duration ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'clock' ); ?></span><div><small>Dauer</small><strong><?php echo esc_html( $duration ); ?></strong></div></div>
                    <?php endif; ?>
                    <?php if ( $location ) : ?>
                        <div class="ffl-fact"><span class="ffl-fact__icon"><?php echo ffl_icon( 'pin' ); ?></span><div><small>Einsatzort</small><strong><?php echo esc_html( $location ); ?></strong></div></div>
                    <?php endif; ?>
                </section>

                <?php if ( $summary ) : ?>
                    <section class="ffl-summary-box">
                        <span class="ffl-section-kicker">Kurz zusammengefasst</span>
                        <p><?php echo esc_html( $summary ); ?></p>
                    </section>
                <?php endif; ?>

                <article id="einsatzbericht" class="ffl-report">
                    <header class="ffl-section-heading"><span class="ffl-section-kicker">Einsatzbericht</span><h2>Was ist passiert?</h2></header>
                    <div class="ffl-report__content">
                        <?php echo $report_html ? wp_kses_post( $report_html ) : '<p>Zu diesem Einsatz liegt noch kein ausführlicher Bericht vor.</p>'; ?>
                    </div>
                </article>

                <?php if ( $timeline ) : ?>
                    <section class="ffl-timeline-section">
                        <header class="ffl-section-heading"><span class="ffl-section-kicker">Einsatzverlauf</span><h2>Der Einsatz in zeitlicher Folge</h2></header>
                        <ol class="ffl-timeline">
                            <?php foreach ( $timeline as $event ) : ?>
                                <li><time><?php echo esc_html( $event['time'] ); ?></time><div><?php echo esc_html( $event['event'] ); ?></div></li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if ( $gallery_ids ) : ?>
                    <section id="einsatzbilder" class="ffl-gallery-section">
                        <header class="ffl-section-heading ffl-section-heading--split"><div><span class="ffl-section-kicker">Einsatzbilder</span><h2>Impressionen vom Einsatz</h2></div><span class="ffl-photo-count"><?php echo esc_html( count( $gallery_ids ) ); ?> <?php echo count( $gallery_ids ) === 1 ? 'Bild' : 'Bilder'; ?></span></header>
                        <div class="ffl-gallery-grid <?php echo count( $gallery_ids ) === 1 ? 'is-single' : ''; ?>" data-gallery>
                            <?php foreach ( $gallery_ids as $index => $image_id ) :
                                $full    = wp_get_attachment_image_url( $image_id, 'full' );
                                $caption = wp_get_attachment_caption( $image_id );
                                if ( ! $full ) { continue; }
                                ?>
                                <button type="button" class="ffl-gallery-item <?php echo $index === 0 ? 'is-featured' : ''; ?>" data-gallery-item data-full="<?php echo esc_url( $full ); ?>" data-caption="<?php echo esc_attr( $caption ?: $image_credit ); ?>" aria-label="Bild <?php echo esc_attr( $index + 1 ); ?> öffnen">
                                    <?php echo wp_get_attachment_image( $image_id, $index === 0 ? 'large' : 'medium_large', false, array( 'loading' => 'lazy' ) ); ?>
                                    <?php if ( $index === 4 && count( $gallery_ids ) > 5 ) : ?><span class="ffl-gallery-more">+<?php echo esc_html( count( $gallery_ids ) - 5 ); ?> weitere</span><?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( $image_credit ) : ?><p class="ffl-image-credit">Bilder: <?php echo esc_html( $image_credit ); ?></p><?php endif; ?>
                    </section>
                <?php endif; ?>

                <?php if ( $coords ) : ?>
                    <section id="einsatzkarte" class="ffl-map-section">
                        <header class="ffl-section-heading ffl-section-heading--split"><div><span class="ffl-section-kicker">Einsatzort</span><h2>Wo der Einsatz stattfand</h2></div><?php if ( $coords['privacy'] === 'approx' ) : ?><span class="ffl-privacy-label">Position aus Datenschutzgründen angenähert</span><?php endif; ?></header>
                        <div class="ffl-single-map" data-single-map data-lat="<?php echo esc_attr( $coords['lat'] ); ?>" data-lon="<?php echo esc_attr( $coords['lon'] ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-location="<?php echo esc_attr( $location ); ?>" data-color="<?php echo esc_attr( $style['color'] ); ?>" data-icon="<?php echo esc_attr( $visual_icon ); ?>" tabindex="0" aria-label="Interaktive Karte des Einsatzortes"></div>
                        <div class="ffl-map-footer">
                            <div class="ffl-map-footer__meta">
                                <div><?php echo ffl_icon( 'pin' ); ?><span><?php echo esc_html( $location ); ?></span></div>
                                <?php if ( $distance ) : ?>
                                    <div class="ffl-distance-note"><?php echo ffl_icon( 'arrow' ); ?><span><?php echo esc_html( $distance['label'] ); ?> ab <?php echo esc_html( $distance['station_name'] ); ?></span></div>
                                <?php endif; ?>
                            </div>
                            <div class="ffl-map-footer__actions">
                                <?php if ( $map_url ) : ?><a href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer">In Karte öffnen →</a><?php endif; ?>
                                <?php if ( $osm_route_url ) : ?><a href="<?php echo esc_url( $osm_route_url ); ?>" target="_blank" rel="noopener noreferrer">Route in OpenStreetMap planen →</a><?php endif; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ( $links ) : ?>
                    <section class="ffl-links-section">
                        <header class="ffl-section-heading"><span class="ffl-section-kicker">Weitere Informationen</span><h2>Weiterführende Berichte</h2></header>
                        <div class="ffl-external-links">
                            <?php foreach ( $links as $index => $url ) : ?><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><span>Externer Bericht <?php echo esc_html( $index + 1 ); ?></span><?php echo ffl_icon( 'arrow' ); ?></a><?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <aside class="ffl-single-sidebar">
                <div class="ffl-sidebar-card">
                    <span class="ffl-section-kicker">Einsatzdetails</span>
                    <h2>Auf einen Blick</h2>
                    <dl>
                        <?php if ( $keyword ) : ?><div><dt>Alarmstichwort</dt><dd><?php echo esc_html( $keyword ); ?></dd></div><?php endif; ?>
                        <div><dt>Einsatzart</dt><dd><?php echo esc_html( $style['name'] ); ?></dd></div>
                        <?php if ( $leader ) : ?><div><dt>Einsatzleiter</dt><dd><?php echo esc_html( $leader ); ?></dd></div><?php endif; ?>
                        <?php if ( $personnel ) : ?><div><dt>Einsatzkräfte</dt><dd><?php echo esc_html( $personnel ); ?></dd></div><?php endif; ?>
                        <?php if ( $duration ) : ?><div><dt>Dauer</dt><dd><?php echo esc_html( $duration ); ?></dd></div><?php endif; ?>
                        <?php if ( $distance ) : ?><div><dt>Entfernung</dt><dd><?php echo esc_html( $distance['label'] ); ?><small>ab <?php echo esc_html( $distance['station_name'] ); ?></small></dd></div><?php endif; ?>
                    </dl>
                </div>

                <?php if ( $vehicles || $units || $organisations ) : ?>
                    <div class="ffl-sidebar-card">
                        <span class="ffl-section-kicker">Beteiligte Kräfte</span>
                        <?php if ( $vehicles ) : ?><h3>Fahrzeuge</h3><ul><?php foreach ( $vehicles as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <?php if ( $units ) : ?><h3>Feuerwehren &amp; Einheiten</h3><ul><?php foreach ( $units as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                        <?php if ( $organisations ) : ?><h3>Weitere Organisationen</h3><ul><?php foreach ( $organisations as $item ) : ?><li><?php echo esc_html( $item ); ?></li><?php endforeach; ?></ul><?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="ffl-sidebar-card ffl-share-card">
                    <span class="ffl-section-kicker">Weitergeben</span>
                    <h3>Einsatzbericht teilen</h3>
                    <div class="ffl-share-grid">
                        <a class="ffl-share-option ffl-share-option--whatsapp" href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Über WhatsApp teilen"><span>WA</span>WhatsApp</a>
                        <a class="ffl-share-option ffl-share-option--facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Auf Facebook teilen"><span>f</span>Facebook</a>
                        <a class="ffl-share-option ffl-share-option--email" href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&amp;body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="Per E-Mail teilen"><span>@</span>E-Mail</a>
                        <button type="button" class="ffl-share-option ffl-share-option--copy" data-copy-url="<?php echo esc_url( get_permalink() ); ?>"><span><?php echo ffl_icon( 'arrow' ); ?></span>Link kopieren</button>
                    </div>
                    <button type="button" class="ffl-button ffl-button--primary ffl-native-share" data-share-url="<?php echo esc_url( get_permalink() ); ?>" data-share-title="<?php echo esc_attr( get_the_title() ); ?>"><?php echo ffl_icon( 'share' ); ?> Weitere Teilen-Optionen</button>
                    <span class="ffl-copy-status" aria-live="polite"></span>
                </div>
            </aside>
        </div>

        <?php
        $related = new WP_Query(
            array(
                'post_type'      => 'ffl_einsatz',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'post__not_in'   => array( $post_id ),
                'meta_key'       => '_ffl_alarmzeit',
                'orderby'        => 'meta_value',
                'order'          => 'DESC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'ffl_einsatzart',
                        'field'    => 'slug',
                        'terms'    => $style['slug'],
                    ),
                ),
            )
        );
        if ( $related->have_posts() ) : ?>
            <section class="ffl-related">
                <div class="ffl-shell">
                    <header class="ffl-section-heading ffl-section-heading--split"><div><span class="ffl-section-kicker">Weitere Einsätze</span><h2>Das könnte ebenfalls interessieren</h2></div><a href="<?php echo esc_url( ffl_get_archive_url() ); ?>">Zum Einsatzarchiv →</a></header>
                    <div class="ffl-card-grid ffl-card-grid--related"><?php while ( $related->have_posts() ) : $related->the_post(); ffl_render_einsatz_card( get_the_ID() ); endwhile; ?></div>
                </div>
            </section>
        <?php endif; wp_reset_postdata(); ?>
    </main>

    <div class="ffl-lightbox" data-lightbox hidden aria-hidden="true">
        <button type="button" class="ffl-lightbox__close" data-lightbox-close aria-label="Galerie schließen">&times;</button>
        <button type="button" class="ffl-lightbox__nav ffl-lightbox__nav--prev" data-lightbox-prev aria-label="Vorheriges Bild">‹</button>
        <figure><img src="" alt=""><figcaption></figcaption></figure>
        <button type="button" class="ffl-lightbox__nav ffl-lightbox__nav--next" data-lightbox-next aria-label="Nächstes Bild">›</button>
        <div class="ffl-lightbox__count" data-lightbox-count></div>
    </div>
    <?php
endwhile;

ffl_render_manual_single_footer_template();
get_footer();
