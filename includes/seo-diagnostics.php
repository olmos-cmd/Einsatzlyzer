<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * SEO, schema, image and cache diagnostics for Einsatzlyzer.
 */

function ffl_detect_seo_provider_key() {
    if ( defined( 'WPSEO_VERSION' ) ) return 'yoast';
    if ( defined( 'RANK_MATH_VERSION' ) ) return 'rank_math';
    if ( defined( 'AIOSEO_VERSION' ) ) return 'aioseo';
    if ( defined( 'SEOPRESS_VERSION' ) ) return 'seopress';
    return 'none';
}

function ffl_detect_seo_provider() {
    $labels = array(
        'yoast'     => 'Yoast SEO',
        'rank_math' => 'Rank Math',
        'aioseo'    => 'All in One SEO',
        'seopress'  => 'SEOPress',
        'none'      => ffl_lang( 'Kein SEO-Plugin erkannt', 'No SEO plugin detected' ),
    );
    return $labels[ ffl_detect_seo_provider_key() ];
}

function ffl_schema_provider_is_integrated( $provider = null ) {
    $provider = $provider ?: ffl_detect_seo_provider_key();
    return in_array( $provider, array( 'yoast', 'rank_math', 'aioseo', 'seopress' ), true );
}

function ffl_detect_cache_systems() {
    $systems = array();
    if ( defined( 'W3TC' ) || class_exists( 'W3TC\\Dispatcher' ) || function_exists( 'w3tc_flush_all' ) ) $systems[] = 'W3 Total Cache';
    if ( class_exists( 'LiteSpeed_Cache_API' ) ) $systems[] = 'LiteSpeed Cache';
    if ( class_exists( 'WP_Rocket' ) || function_exists( 'rocket_clean_domain' ) ) $systems[] = 'WP Rocket';
    if ( class_exists( 'autoptimizeCache' ) ) $systems[] = 'Autoptimize';
    if ( defined( 'SG_CACHEPRESS_VERSION' ) ) $systems[] = 'SiteGround Optimizer';
    return array_values( array_unique( $systems ) );
}

function ffl_get_image_quality_status( $post_id ) {
    $data   = ffl_get_social_image_data( $post_id );
    $width  = absint( $data['width'] ?? 0 );
    $height = absint( $data['height'] ?? 0 );
    $id     = absint( $data['id'] ?? 0 );
    $source = 'plugin';
    if ( $id && has_post_thumbnail( $post_id ) && get_post_thumbnail_id( $post_id ) === $id ) {
        $source = 'featured';
    } elseif ( $id && absint( get_option( 'ffl_default_image_id', 0 ) ) === $id ) {
        $source = 'default';
    } elseif ( $id ) {
        $source = 'gallery';
    }
    return array(
        'ok'     => $width >= 1200 && $height >= 675,
        'width'  => $width,
        'height' => $height,
        'source' => $source,
        'url'    => (string) ( $data['url'] ?? '' ),
        'id'     => $id,
    );
}

function ffl_get_incident_index_status( $post_id ) {
    $post      = get_post( $post_id );
    $permalink = get_permalink( $post_id );
    $issues    = array();
    if ( ! $post || 'ffl_einsatz' !== $post->post_type ) $issues[] = 'invalid';
    if ( $post && 'publish' !== $post->post_status ) $issues[] = 'not_published';
    if ( ! $permalink ) $issues[] = 'permalink';
    if ( get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ) ) $issues[] = 'noindex';
    $rank_robots = get_post_meta( $post_id, 'rank_math_robots', true );
    if ( ( is_array( $rank_robots ) && in_array( 'noindex', $rank_robots, true ) ) || 'noindex' === $rank_robots ) $issues[] = 'noindex';
    if ( get_post_meta( $post_id, '_aioseo_noindex', true ) ) $issues[] = 'noindex';
    if ( 'yes' === get_post_meta( $post_id, '_seopress_robots_index', true ) ) $issues[] = 'noindex';
    $canonical = $permalink;
    $custom_canonicals = array_filter( array_map( 'trim', array(
        (string) get_post_meta( $post_id, '_yoast_wpseo_canonical', true ),
        (string) get_post_meta( $post_id, 'rank_math_canonical_url', true ),
        (string) get_post_meta( $post_id, '_aioseo_custom_link', true ),
        (string) get_post_meta( $post_id, '_seopress_robots_canonical', true ),
    ) ) );
    foreach ( $custom_canonicals as $custom_canonical ) {
        if ( untrailingslashit( $custom_canonical ) !== untrailingslashit( $permalink ) ) { $issues[] = 'canonical'; break; }
    }
    return array( 'ok' => empty( $issues ), 'issues' => array_values( array_unique( $issues ) ), 'canonical' => $canonical );
}

function ffl_get_incident_seo_status( $post_id ) {
    $post   = get_post( $post_id );
    $image  = ffl_get_image_quality_status( $post_id );
    $index  = ffl_get_incident_index_status( $post_id );
    $issues = array();
    if ( ! $post || 'ffl_einsatz' !== $post->post_type ) $issues[] = 'invalid';
    if ( ! trim( (string) get_the_title( $post_id ) ) ) $issues[] = 'title';
    if ( ! trim( (string) get_post_meta( $post_id, '_ffl_alarmzeit', true ) ) ) $issues[] = 'date';
    if ( ! trim( (string) get_post_meta( $post_id, '_ffl_einsatzort', true ) ) ) $issues[] = 'location';
    if ( ! trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ) ) $issues[] = 'content';
    if ( ! $image['ok'] ) $issues[] = 'image';
    $issues = array_merge( $issues, $index['issues'] );
    return array(
        'ok'        => empty( $issues ),
        'issues'    => array_values( array_unique( $issues ) ),
        'image'     => $image,
        'schema'    => ffl_detect_seo_provider_key() === 'none' ? 'Einsatzlyzer' : ffl_detect_seo_provider(),
        'canonical' => $index['canonical'],
        'index'     => $index,
    );
}

function ffl_issue_labels() {
    return array(
        'title'         => ffl_lang( 'Titel fehlt', 'Title missing' ),
        'date'          => ffl_lang( 'Alarmdatum fehlt', 'Alert date missing' ),
        'location'      => ffl_lang( 'Einsatzort fehlt', 'Incident location missing' ),
        'content'       => ffl_lang( 'Bericht fehlt', 'Report missing' ),
        'image'         => ffl_lang( 'Vorschaubild kleiner als 1200 × 675 Pixel', 'Preview image is smaller than 1200 × 675 pixels' ),
        'permalink'     => ffl_lang( 'Permalink fehlt', 'Permalink missing' ),
        'invalid'       => ffl_lang( 'Ungültiger Einsatz', 'Invalid incident' ),
        'not_published' => ffl_lang( 'Nicht veröffentlicht', 'Not published' ),
        'noindex'       => ffl_lang( 'Auf noindex gesetzt', 'Set to noindex' ),
        'canonical'     => ffl_lang( 'Abweichende Canonical-URL', 'Different canonical URL' ),
        'schema_live'   => ffl_lang( 'Live-Schema nicht bestätigt', 'Live schema not confirmed' ),
    );
}

add_action( 'add_meta_boxes_ffl_einsatz', function() {
    add_meta_box( 'ffl_seo_check', ffl_lang( 'SEO- und Bildprüfung', 'SEO and Image Check' ), 'ffl_render_seo_meta_box', 'ffl_einsatz', 'side', 'default' );
} );

function ffl_render_seo_meta_box( $post ) {
    $status = ffl_get_incident_seo_status( $post->ID );
    $labels = ffl_issue_labels();
    echo '<p><strong>' . ( $status['ok'] ? '✓ ' . esc_html( ffl_lang( 'SEO-Basis vollständig', 'SEO basics complete' ) ) : '⚠ ' . esc_html( ffl_lang( 'Prüfung erforderlich', 'Review required' ) ) ) . '</strong></p>';
    echo '<p>' . esc_html( ffl_lang( 'Schema-Quelle: ', 'Schema source: ' ) . $status['schema'] ) . '</p>';
    echo '<p>' . esc_html( sprintf( ffl_lang( 'Vorschaubild: %1$d × %2$d Pixel', 'Preview image: %1$d × %2$d pixels' ), $status['image']['width'], $status['image']['height'] ) ) . '</p>';
    if ( $status['issues'] ) {
        echo '<ul style="margin-left:18px;list-style:disc">';
        foreach ( $status['issues'] as $issue ) echo '<li>' . esc_html( $labels[ $issue ] ?? $issue ) . '</li>';
        echo '</ul>';
    }
}

function ffl_status_badge( $ok, $good, $bad, $title = '' ) {
    $color = $ok ? '#16803a' : '#b45309';
    return '<span title="' . esc_attr( $title ) . '" style="font-weight:700;color:' . esc_attr( $color ) . '">' . ( $ok ? '✓ ' . esc_html( $good ) : '⚠ ' . esc_html( $bad ) ) . '</span>';
}

add_action( 'manage_ffl_einsatz_posts_custom_column', function( $column, $post_id ) {
    if ( ! in_array( $column, array( 'ffl_content_check', 'ffl_image_check', 'ffl_schema_check', 'ffl_index_check' ), true ) ) return;
    $status = ffl_get_incident_seo_status( $post_id );
    $labels = ffl_issue_labels();
    if ( 'ffl_content_check' === $column ) {
        $keys = array_intersect( $status['issues'], array( 'title', 'date', 'location', 'content', 'invalid' ) );
        $text = implode( ', ', array_map( function( $key ) use ( $labels ) { return $labels[ $key ] ?? $key; }, $keys ) );
        echo ffl_status_badge( empty( $keys ), ffl_lang( 'Inhalt', 'Content' ), ffl_lang( 'Prüfen', 'Review' ), $text );
    } elseif ( 'ffl_image_check' === $column ) {
        $title = sprintf( '%d × %d px', $status['image']['width'], $status['image']['height'] );
        echo ffl_status_badge( $status['image']['ok'], ffl_lang( 'Bild', 'Image' ), ffl_lang( 'Bild', 'Image' ), $title );
    } elseif ( 'ffl_schema_check' === $column ) {
        $provider = $status['schema'];
        echo ffl_status_badge( ffl_schema_provider_is_integrated() || 'Einsatzlyzer' === $provider, ffl_lang( 'Schema', 'Schema' ), ffl_lang( 'Schema', 'Schema' ), $provider );
    } elseif ( 'ffl_index_check' === $column ) {
        $text = implode( ', ', array_map( function( $key ) use ( $labels ) { return $labels[ $key ] ?? $key; }, $status['index']['issues'] ) );
        echo ffl_status_badge( $status['index']['ok'], ffl_lang( 'Index', 'Index' ), ffl_lang( 'Prüfen', 'Review' ), $text );
    }
}, 20, 2 );

/** Build the canonical Einsatzlyzer article and breadcrumb nodes. */
function ffl_build_schema_nodes( $post_id ) {
    $post_id   = absint( $post_id );
    $permalink = get_permalink( $post_id );
    $title     = get_the_title( $post_id );
    $summary   = ffl_get_summary( $post_id, 36 );
    $style     = ffl_term_style( $post_id );
    $location  = ffl_meta_value( $post_id, '_ffl_einsatzort' );
    $image     = ffl_get_social_image_data( $post_id );
    $article   = array(
        '@type'            => 'NewsArticle',
        '@id'              => $permalink . '#article',
        'headline'         => $title,
        'description'      => $summary,
        'datePublished'    => get_the_date( DATE_W3C, $post_id ),
        'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
        'mainEntityOfPage' => array( '@id' => $permalink ),
        'articleSection'   => $style['name'],
    );
    if ( ! empty( $image['url'] ) ) {
        $article['image'] = array( '@type' => 'ImageObject', 'url' => $image['url'], 'width' => absint( $image['width'] ), 'height' => absint( $image['height'] ) );
    }
    if ( $location ) $article['contentLocation'] = array( '@type' => 'Place', 'name' => $location );
    $breadcrumb = array(
        '@type' => 'BreadcrumbList',
        '@id'   => $permalink . '#breadcrumb',
        'itemListElement' => array(
            array( '@type' => 'ListItem', 'position' => 1, 'name' => ffl_lang( 'Startseite', 'Home' ), 'item' => home_url( '/' ) ),
            array( '@type' => 'ListItem', 'position' => 2, 'name' => ffl_lang( 'Feuerwehr-Einsätze', 'Fire Department Incidents' ), 'item' => ffl_get_archive_url() ),
            array( '@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $permalink ),
        ),
    );
    return array( 'article' => $article, 'breadcrumb' => $breadcrumb );
}

function ffl_schema_node_types( $node ) {
    $type = $node['@type'] ?? '';
    return is_array( $type ) ? $type : array( $type );
}

function ffl_normalize_schema_graph( $graph, $post_id = 0 ) {
    if ( ! $post_id ) $post_id = get_queried_object_id();
    if ( ! $post_id || ! is_array( $graph ) ) return $graph;
    $nodes = ffl_build_schema_nodes( $post_id );
    $found_article = false;
    $found_breadcrumb = false;
    foreach ( $graph as $key => &$node ) {
        if ( ! is_array( $node ) ) continue;
        $types = ffl_schema_node_types( $node );
        if ( in_array( 'Event', $types, true ) ) { unset( $graph[ $key ] ); continue; }
        if ( isset( $node['about']['@type'] ) && 'Event' === $node['about']['@type'] ) unset( $node['about'] );
        if ( array_intersect( $types, array( 'Article', 'NewsArticle', 'BlogPosting' ) ) ) {
            $node = array_replace_recursive( $node, $nodes['article'] );
            $node['@type'] = 'NewsArticle';
            $found_article = true;
        }
        if ( in_array( 'BreadcrumbList', $types, true ) ) {
            $node = array_replace_recursive( $node, $nodes['breadcrumb'] );
            $node['itemListElement'] = $nodes['breadcrumb']['itemListElement'];
            $found_breadcrumb = true;
        }
    }
    unset( $node );
    if ( ! $found_article ) $graph[] = $nodes['article'];
    if ( ! $found_breadcrumb ) $graph[] = $nodes['breadcrumb'];
    return array_values( $graph );
}

add_filter( 'wpseo_schema_graph', function( $graph ) {
    return is_singular( 'ffl_einsatz' ) ? ffl_normalize_schema_graph( $graph ) : $graph;
}, 999 );

add_filter( 'rank_math/json_ld', function( $data ) {
    if ( ! is_singular( 'ffl_einsatz' ) || ! is_array( $data ) ) return $data;
    $keys = array_keys( $data );
    $is_assoc = $keys !== range( 0, count( $keys ) - 1 );
    if ( $is_assoc ) {
        $normalized = ffl_normalize_schema_graph( array_values( $data ) );
        $result = array();
        foreach ( $normalized as $index => $node ) $result[ 'Einsatzlyzer_' . $index ] = $node;
        return $result;
    }
    return ffl_normalize_schema_graph( $data );
}, 999, 2 );

add_filter( 'aioseo_schema_output', function( $graphs ) {
    return is_singular( 'ffl_einsatz' ) && is_array( $graphs ) ? ffl_normalize_schema_graph( $graphs ) : $graphs;
}, 999 );

function ffl_filter_seopress_article_schema( $data ) {
    if ( ! is_singular( 'ffl_einsatz' ) || ! is_array( $data ) ) return $data;
    return array_replace_recursive( $data, ffl_build_schema_nodes( get_queried_object_id() )['article'] );
}
add_filter( 'seopress_schemas_auto_article_json', 'ffl_filter_seopress_article_schema', 999 );
add_filter( 'seopress_pro_get_json_data_article', 'ffl_filter_seopress_article_schema', 999 );
add_filter( 'seopress_schemas_auto_event_json', function( $data ) { return is_singular( 'ffl_einsatz' ) ? array() : $data; }, 999 );
add_filter( 'seopress_pro_get_json_data_event', function( $data ) { return is_singular( 'ffl_einsatz' ) ? array() : $data; }, 999 );
add_filter( 'seopress_pro_breadcrumbs_json', function( $data ) {
    return is_singular( 'ffl_einsatz' ) ? ffl_build_schema_nodes( get_queried_object_id() )['breadcrumb'] : $data;
}, 999 );

/** Live inspection of rendered JSON-LD; cached to avoid slowing the incident list. */
function ffl_inspect_live_schema( $post_id, $force = false ) {
    $post_id = absint( $post_id );
    $key = 'ffl_schema_live_' . $post_id;
    if ( ! $force ) {
        $cached = get_transient( $key );
        if ( is_array( $cached ) ) return $cached;
    }
    $result = array( 'ok' => false, 'http' => 0, 'article' => 0, 'breadcrumbs' => 0, 'events' => 0, 'missing_items' => 0, 'image' => '' );
    $url = get_permalink( $post_id );
    if ( ! $url ) return $result;
    $response = wp_remote_get( $url, array( 'timeout' => 12, 'redirection' => 3, 'headers' => array( 'Cache-Control' => 'no-cache' ) ) );
    if ( is_wp_error( $response ) ) { $result['error'] = $response->get_error_message(); set_transient( $key, $result, 5 * MINUTE_IN_SECONDS ); return $result; }
    $result['http'] = wp_remote_retrieve_response_code( $response );
    $html = wp_remote_retrieve_body( $response );
    preg_match_all( '#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches );
    foreach ( $matches[1] ?? array() as $json ) {
        $decoded = json_decode( html_entity_decode( trim( $json ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
        if ( ! is_array( $decoded ) ) continue;
        $graph = isset( $decoded['@graph'] ) && is_array( $decoded['@graph'] ) ? $decoded['@graph'] : array( $decoded );
        foreach ( $graph as $node ) {
            if ( ! is_array( $node ) ) continue;
            $types = ffl_schema_node_types( $node );
            if ( array_intersect( $types, array( 'Article', 'NewsArticle', 'BlogPosting' ) ) ) {
                $result['article']++;
                if ( empty( $result['image'] ) && ! empty( $node['image'] ) ) $result['image'] = is_string( $node['image'] ) ? $node['image'] : ( $node['image']['url'] ?? '' );
            }
            if ( in_array( 'Event', $types, true ) || ( isset( $node['about']['@type'] ) && 'Event' === $node['about']['@type'] ) ) $result['events']++;
            if ( in_array( 'BreadcrumbList', $types, true ) ) {
                $result['breadcrumbs']++;
                foreach ( $node['itemListElement'] ?? array() as $item ) if ( empty( $item['item'] ) ) $result['missing_items']++;
            }
        }
    }
    $result['ok'] = 200 === $result['http'] && $result['article'] >= 1 && 1 === $result['breadcrumbs'] && 0 === $result['events'] && 0 === $result['missing_items'];
    set_transient( $key, $result, 12 * HOUR_IN_SECONDS );
    return $result;
}

add_action( 'admin_init', function() {
    $seen = (string) get_option( 'ffl_cache_notice_version', '' );
    if ( FFL_EINSATZLYZER_VERSION !== $seen ) {
        update_option( 'ffl_show_cache_notice', 1, false );
    }
} );

/** Whether the current admin screen belongs to Einsatzlyzer. */
function ffl_is_einsatzlyzer_admin_screen() {
    if ( ! function_exists( 'get_current_screen' ) ) {
        return false;
    }
    $screen = get_current_screen();
    if ( ! $screen ) {
        return false;
    }
    if ( 'ffl_einsatz' === (string) $screen->post_type ) {
        return true;
    }
    return false !== strpos( (string) $screen->id, 'ffl_einsatz' );
}

add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'manage_options' ) || ! ffl_is_einsatzlyzer_admin_screen() ) {
        return;
    }
    if ( isset( $_GET['cache-cleared'] ) ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( ffl_lang( 'Unterstützte WordPress-Caches wurden erfolgreich geleert.', 'Supported WordPress caches were cleared successfully.' ) ) . '</p></div>';
    }
    if ( ! get_option( 'ffl_show_cache_notice' ) ) {
        return;
    }

    $systems = ffl_detect_cache_systems();
    $referer = wp_get_referer();
    if ( ! $referer ) {
        $referer = admin_url( 'edit.php?post_type=ffl_einsatz' );
    }
    $clear = wp_nonce_url(
        add_query_arg(
            array(
                'action'      => 'ffl_clear_supported_caches',
                'redirect_to' => rawurlencode( $referer ),
            ),
            admin_url( 'admin-post.php' )
        ),
        'ffl_clear_supported_caches'
    );
    $later = wp_nonce_url(
        add_query_arg(
            array(
                'action'      => 'ffl_acknowledge_cache_notice',
                'redirect_to' => rawurlencode( $referer ),
            ),
            admin_url( 'admin-post.php' )
        ),
        'ffl_acknowledge_cache_notice'
    );

    echo '<div class="notice notice-info is-dismissible ffl-update-cache-notice"><p><strong>'
        . esc_html( sprintf( ffl_lang( 'Einsatzlyzer wurde auf Version %s aktualisiert.', 'Einsatzlyzer was updated to version %s.' ), FFL_EINSATZLYZER_VERSION ) )
        . '</strong><br>'
        . esc_html( ffl_lang( 'Damit alle Änderungen sichtbar werden, leere bitte einmal die unterstützten WordPress-Caches.', 'To make all changes visible, please clear the supported WordPress caches once.' ) );
    if ( $systems ) {
        echo '<br><small>' . esc_html( ffl_lang( 'Erkannte Cache-Plugins: ', 'Detected cache plugins: ' ) . implode( ', ', $systems ) ) . '</small>';
    }
    echo '</p><p><a class="button button-primary" href="' . esc_url( $clear ) . '">'
        . esc_html( ffl_lang( 'Unterstützte Caches leeren', 'Clear supported caches' ) )
        . '</a> <a class="button" href="' . esc_url( $later ) . '">'
        . esc_html( ffl_lang( 'Später', 'Later' ) )
        . '</a></p></div>';
} );

function ffl_finish_cache_notice() {
    update_option( 'ffl_cache_notice_version', FFL_EINSATZLYZER_VERSION, false );
    delete_option( 'ffl_show_cache_notice' );
}

function ffl_cache_notice_redirect_url( $fallback = '' ) {
    $fallback = $fallback ?: admin_url( 'edit.php?post_type=ffl_einsatz' );
    $redirect = isset( $_GET['redirect_to'] ) ? rawurldecode( sanitize_text_field( wp_unslash( $_GET['redirect_to'] ) ) ) : '';
    return wp_validate_redirect( $redirect, $fallback );
}

add_action( 'admin_post_ffl_acknowledge_cache_notice', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Insufficient permissions.' ) ) );
    }
    check_admin_referer( 'ffl_acknowledge_cache_notice' );
    ffl_finish_cache_notice();
    wp_safe_redirect( ffl_cache_notice_redirect_url() );
    exit;
} );

add_action( 'admin_post_ffl_clear_supported_caches', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Insufficient permissions.' ) ) );
    }
    check_admin_referer( 'ffl_clear_supported_caches' );
    if ( function_exists( 'w3tc_flush_all' ) ) w3tc_flush_all();
    if ( function_exists( 'rocket_clean_domain' ) ) rocket_clean_domain();
    if ( class_exists( 'LiteSpeed_Cache_API' ) ) LiteSpeed_Cache_API::purge_all();
    if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
    ffl_finish_cache_notice();
    // Nach dem Leeren immer zur Einsatzübersicht zurückkehren. So landet der
    // Benutzer unabhängig von Referer, Diagnose- oder Einstellungsseite an
    // einer eindeutigen Stelle im Einsatzlyzer.
    $redirect = add_query_arg( 'cache-cleared', 1, admin_url( 'edit.php?post_type=ffl_einsatz' ) );
    wp_safe_redirect( $redirect );
    exit;
} );

add_action( 'admin_post_ffl_recalculate_seo_status', function() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Insufficient permissions.' ) ) );
    check_admin_referer( 'ffl_recalculate_seo_status' );
    global $wpdb;
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ffl_schema_live_%' OR option_name LIKE '_transient_timeout_ffl_schema_live_%'" );
    wp_safe_redirect( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_einsatz_diagnose', 'recalculated' => 1 ), admin_url( 'edit.php' ) ) ); exit;
} );

function ffl_render_diagnostics_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $seo        = ffl_detect_seo_provider();
    $provider   = ffl_detect_seo_provider_key();
    $caches     = ffl_detect_cache_systems();
    $default_id = absint( get_option( 'ffl_default_image_id', 0 ) );
    $default    = $default_id ? wp_get_attachment_image_src( $default_id, 'full' ) : false;

    $allowed_per_page = array( 25, 50, 100, -1 );
    $requested_per_page = isset( $_GET['diag_per_page'] ) && 'all' === $_GET['diag_per_page'] ? -1 : absint( $_GET['diag_per_page'] ?? 50 );
    $per_page = in_array( $requested_per_page, $allowed_per_page, true ) ? $requested_per_page : 50;
    $page     = max( 1, absint( $_GET['diag_page'] ?? 1 ) );
    $search   = sanitize_text_field( wp_unslash( $_GET['diag_search'] ?? '' ) );
    $filter   = sanitize_key( $_GET['diag_filter'] ?? 'all' );
    $allowed_filters = array( 'all', 'ready', 'almost', 'incomplete', 'missing_weather', 'missing_image', 'missing_location', 'seo', 'schema' );
    if ( ! in_array( $filter, $allowed_filters, true ) ) $filter = 'all';

    $all_ids = get_posts( array(
        'post_type'      => 'ffl_einsatz',
        'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'orderby'        => 'date',
        'order'          => 'DESC',
        's'              => $search,
        'no_found_rows'  => true,
    ) );

    $summary = array( 'ready' => 0, 'almost' => 0, 'incomplete' => 0, 'total' => count( $all_ids ) );
    $filtered_ids = array();
    foreach ( $all_ids as $id ) {
        $quality = function_exists( 'ffl_admin_quality_status' ) ? ffl_admin_quality_status( $id ) : array( 'state' => ffl_get_incident_seo_status( $id )['ok'] ? 'ready' : 'almost', 'checks' => array() );
        $summary[ $quality['state'] ]++;
        $checks = array();
        foreach ( $quality['checks'] as $check ) $checks[ $check['key'] ] = ! empty( $check['ok'] );
        $matches = 'all' === $filter
            || $quality['state'] === $filter
            || ( 'missing_weather' === $filter && empty( $checks['weather'] ) )
            || ( 'missing_image' === $filter && empty( $checks['image'] ) )
            || ( 'missing_location' === $filter && empty( $checks['location'] ) )
            || ( 'seo' === $filter && empty( $checks['seo'] ) )
            || ( 'schema' === $filter && empty( $checks['schema'] ) );
        if ( $matches ) $filtered_ids[] = $id;
    }

    $filtered_total = count( $filtered_ids );
    $total_pages = -1 === $per_page ? 1 : max( 1, (int) ceil( $filtered_total / $per_page ) );
    if ( $page > $total_pages ) $page = $total_pages;
    $page_ids = -1 === $per_page ? $filtered_ids : array_slice( $filtered_ids, ( $page - 1 ) * $per_page, $per_page );
    $first = $filtered_total ? ( -1 === $per_page ? 1 : ( ( $page - 1 ) * $per_page + 1 ) ) : 0;
    $last  = $filtered_total ? ( -1 === $per_page ? $filtered_total : min( $page * $per_page, $filtered_total ) ) : 0;

    $sample_id = absint( $_GET['schema_post_id'] ?? 0 );
    if ( ! $sample_id && $page_ids ) $sample_id = (int) reset( $page_ids );
    $live = $sample_id ? ffl_inspect_live_schema( $sample_id, isset( $_GET['force_live'] ) ) : array();
    $base_url = admin_url( 'edit.php?post_type=ffl_einsatz&page=ffl_einsatz_diagnose' );
    ?>
    <div class="wrap ffl-diagnostics"><h1><?php echo esc_html( ffl_lang( 'Einsatzlyzer Diagnose', 'Einsatzlyzer Diagnostics' ) ); ?></h1>
    <style>
    .ffl-diag-summary{display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:12px;max-width:1050px;margin:16px 0}.ffl-diag-card{background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:14px;text-decoration:none;color:#1d2327}.ffl-diag-card strong{display:block;font-size:24px;line-height:1.1}.ffl-diag-card.ready{border-left:5px solid #16803a}.ffl-diag-card.almost{border-left:5px solid #d97706}.ffl-diag-card.incomplete{border-left:5px solid #b91c1c}.ffl-diag-toolbar{display:flex;gap:10px;align-items:end;flex-wrap:wrap;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:12px;max-width:1050px;margin:14px 0}.ffl-diag-toolbar label{display:flex;flex-direction:column;gap:4px;font-weight:600}.ffl-diag-toolbar input[type=search]{min-width:260px}.ffl-diag-result-head{max-width:1050px;display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin:20px 0 8px}.ffl-diag-result-head h2{margin:0}.ffl-diag-quality summary{cursor:pointer}.ffl-diag-quality ul{margin:8px 0 0 18px}.ffl-diag-state{font-weight:700}.ffl-diag-state.ready{color:#16803a}.ffl-diag-state.almost{color:#b45309}.ffl-diag-state.incomplete{color:#b91c1c}@media(max-width:782px){.ffl-diag-summary{grid-template-columns:1fr 1fr}.ffl-diag-toolbar>*{width:100%}.ffl-diag-toolbar input[type=search],.ffl-diag-toolbar select,.ffl-diag-toolbar .button{width:100%;max-width:none}.ffl-diag-table thead{display:none}.ffl-diag-table,.ffl-diag-table tbody,.ffl-diag-table tr,.ffl-diag-table td{display:block;width:100%}.ffl-diag-table tr{margin-bottom:12px;border:1px solid #dcdcde;background:#fff}.ffl-diag-table td{box-sizing:border-box;border:0!important;padding:8px 12px}.ffl-diag-table td:before{content:attr(data-label);display:block;font-weight:700;margin-bottom:3px}}
    </style>
    <?php if ( isset( $_GET['recalculated'] ) ) : ?><div class="notice notice-success"><p><?php echo esc_html( ffl_lang( 'Zwischengespeicherte Prüfergebnisse wurden gelöscht.', 'Cached diagnostic results were cleared.' ) ); ?></p></div><?php endif; ?>
    <table class="widefat striped" style="max-width:1050px"><tbody>
    <tr><th><?php echo esc_html( ffl_lang( 'Plugin-Version', 'Plugin version' ) ); ?></th><td><?php echo esc_html( FFL_EINSATZLYZER_VERSION ); ?></td></tr>
    <tr><th><?php echo esc_html( ffl_lang( 'SEO-Anbieter', 'SEO provider' ) ); ?></th><td><?php echo esc_html( $seo ); ?></td></tr>
    <tr><th><?php echo esc_html( ffl_lang( 'Schema-Verantwortung', 'Schema responsibility' ) ); ?></th><td><?php echo esc_html( 'none' === $provider ? 'Einsatzlyzer' : sprintf( ffl_lang( '%s, durch Einsatzlyzer bereinigt und ergänzt', '%s, normalized and extended by Einsatzlyzer' ), $seo ) ); ?></td></tr>
    <tr><th><?php echo esc_html( ffl_lang( 'Erkannte Caches', 'Detected caches' ) ); ?></th><td><?php echo esc_html( $caches ? implode( ', ', $caches ) : ffl_lang( 'Keine automatisch erkannt', 'None automatically detected' ) ); ?></td></tr>
    <tr><th><?php echo esc_html( ffl_lang( 'Standardbild', 'Default image' ) ); ?></th><td><?php echo $default ? esc_html( $default[1] . ' × ' . $default[2] . ' px ' . ( ( $default[1] >= 1200 && $default[2] >= 675 ) ? '✓' : '⚠ ' . ffl_lang( 'mindestens 1200 × 675 empfohlen', 'at least 1200 × 675 recommended' ) ) ) : esc_html( ffl_lang( 'Nicht ausgewählt – internes Fallback ist für Google möglicherweise zu klein.', 'Not selected – the internal fallback may be too small for Google.' ) ); ?></td></tr>
    <tr><th><?php echo esc_html( ffl_lang( 'Live-Schema-Stichprobe', 'Live schema sample' ) ); ?></th><td><?php if ( $live ) echo esc_html( ( $live['ok'] ? '✓ ' : '⚠ ' ) . sprintf( ffl_lang( '%1$d Artikel · %2$d Breadcrumbs · %3$d Events · %4$d fehlende item-Felder · HTTP %5$d', '%1$d articles · %2$d breadcrumbs · %3$d events · %4$d missing item fields · HTTP %5$d' ), $live['article'], $live['breadcrumbs'], $live['events'], $live['missing_items'], $live['http'] ) ); ?></td></tr>
    </tbody></table>
    <p><a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ffl_clear_supported_caches' ), 'ffl_clear_supported_caches' ) ); ?>"><?php echo esc_html( ffl_lang( 'Unterstützte Caches leeren', 'Clear supported caches' ) ); ?></a> <a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ffl_recalculate_seo_status' ), 'ffl_recalculate_seo_status' ) ); ?>"><?php echo esc_html( ffl_lang( 'Status neu berechnen', 'Recalculate status' ) ); ?></a> <a class="button" href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_einsatz_diagnose', 'schema_post_id' => $sample_id, 'force_live' => 1 ), admin_url( 'edit.php' ) ) ); ?>"><?php echo esc_html( ffl_lang( 'Live-Schema erneut prüfen', 'Recheck live schema' ) ); ?></a> <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffl_einsatz' ) ); ?>"><?php echo esc_html( ffl_lang( 'Status aller Einsätze öffnen', 'Open status of all incidents' ) ); ?></a></p>

    <div class="ffl-diag-summary">
      <a class="ffl-diag-card" href="<?php echo esc_url( add_query_arg( 'diag_filter', 'all', $base_url ) ); ?>"><strong><?php echo esc_html( $summary['total'] ); ?></strong><?php echo esc_html( ffl_lang( 'Einsätze geprüft', 'Incidents checked' ) ); ?></a>
      <a class="ffl-diag-card ready" href="<?php echo esc_url( add_query_arg( 'diag_filter', 'ready', $base_url ) ); ?>"><strong><?php echo esc_html( $summary['ready'] ); ?></strong><?php echo esc_html( ffl_lang( 'Bereit', 'Ready' ) ); ?></a>
      <a class="ffl-diag-card almost" href="<?php echo esc_url( add_query_arg( 'diag_filter', 'almost', $base_url ) ); ?>"><strong><?php echo esc_html( $summary['almost'] ); ?></strong><?php echo esc_html( ffl_lang( 'Fast fertig', 'Almost ready' ) ); ?></a>
      <a class="ffl-diag-card incomplete" href="<?php echo esc_url( add_query_arg( 'diag_filter', 'incomplete', $base_url ) ); ?>"><strong><?php echo esc_html( $summary['incomplete'] ); ?></strong><?php echo esc_html( ffl_lang( 'Unvollständig', 'Incomplete' ) ); ?></a>
    </div>

    <form method="get" class="ffl-diag-toolbar">
      <input type="hidden" name="post_type" value="ffl_einsatz"><input type="hidden" name="page" value="ffl_einsatz_diagnose">
      <label><?php echo esc_html( ffl_lang( 'Suche', 'Search' ) ); ?><input type="search" name="diag_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( ffl_lang( 'Titel, Einsatznummer oder Ort', 'Title, incident number or location' ) ); ?>"></label>
      <label><?php echo esc_html( ffl_lang( 'Schnellfilter', 'Quick filter' ) ); ?><select name="diag_filter">
      <?php $filter_labels = array( 'all'=>ffl_lang('Alle','All'), 'ready'=>ffl_lang('Bereit','Ready'), 'almost'=>ffl_lang('Fast fertig','Almost ready'), 'incomplete'=>ffl_lang('Unvollständig','Incomplete'), 'missing_weather'=>ffl_lang('Wetter fehlt','Weather missing'), 'missing_image'=>ffl_lang('Bild fehlt','Image missing'), 'missing_location'=>ffl_lang('Ort fehlt','Location missing'), 'seo'=>ffl_lang('SEO-Probleme','SEO issues'), 'schema'=>ffl_lang('Schema-Probleme','Schema issues') ); foreach ( $filter_labels as $key=>$label ) echo '<option value="'.esc_attr($key).'" '.selected($filter,$key,false).'>'.esc_html($label).'</option>'; ?>
      </select></label>
      <label><?php echo esc_html( ffl_lang( 'Anzeigen', 'Show' ) ); ?><select name="diag_per_page"><option value="25" <?php selected( $per_page, 25 ); ?>>25</option><option value="50" <?php selected( $per_page, 50 ); ?>>50</option><option value="100" <?php selected( $per_page, 100 ); ?>>100</option><option value="all" <?php selected( $per_page, -1 ); ?>><?php echo esc_html( ffl_lang( 'Alle', 'All' ) ); ?></option></select></label>
      <button class="button button-primary" type="submit"><?php echo esc_html( ffl_lang( 'Anwenden', 'Apply' ) ); ?></button>
      <a class="button" href="<?php echo esc_url( $base_url ); ?>"><?php echo esc_html( ffl_lang( 'Zurücksetzen', 'Reset' ) ); ?></a>
    </form>

    <div class="ffl-diag-result-head"><h2><?php echo esc_html( ffl_lang( 'Diagnoseergebnisse', 'Diagnostic results' ) ); ?></h2><strong><?php echo esc_html( sprintf( ffl_lang( 'Seite %1$d von %2$d · Einsätze %3$d–%4$d von %5$d', 'Page %1$d of %2$d · Incidents %3$d–%4$d of %5$d' ), $page, $total_pages, $first, $last, $filtered_total ) ); ?></strong></div>
    <table class="widefat striped ffl-diag-table" style="max-width:1050px"><thead><tr><th><?php echo esc_html( ffl_lang( 'Einsatz', 'Incident' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Datum', 'Date' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Qualität', 'Quality' ) ); ?></th><th><?php echo esc_html( ffl_lang( 'Fehlende Punkte', 'Missing items' ) ); ?></th></tr></thead><tbody>
    <?php if ( ! $page_ids ) : ?><tr><td colspan="4"><?php echo esc_html( ffl_lang( 'Für diese Auswahl wurden keine Einsätze gefunden.', 'No incidents were found for this selection.' ) ); ?></td></tr><?php endif; ?>
    <?php foreach ( $page_ids as $id ) : $quality = ffl_admin_quality_status( $id ); $missing=array_filter($quality['checks'],static function($c){return empty($c['ok']);}); ?>
      <tr><td data-label="<?php echo esc_attr( ffl_lang('Einsatz','Incident') ); ?>"><a href="<?php echo esc_url( get_edit_post_link( $id ) ); ?>"><strong><?php echo esc_html( get_the_title( $id ) ?: sprintf( ffl_lang('Einsatz #%d','Incident #%d'), $id ) ); ?></strong></a><br><small>#<?php echo esc_html( $id ); ?> · <?php echo esc_html( get_post_status( $id ) ); ?></small></td>
      <td data-label="<?php echo esc_attr( ffl_lang('Datum','Date') ); ?>"><?php echo esc_html( get_post_meta( $id, '_ffl_alarmzeit', true ) ?: get_the_date( 'd.m.Y H:i', $id ) ); ?></td>
      <td data-label="<?php echo esc_attr( ffl_lang('Qualität','Quality') ); ?>"><span class="ffl-diag-state <?php echo esc_attr($quality['state']); ?>"><?php echo esc_html( array('ready'=>ffl_lang('Bereit','Ready'),'almost'=>ffl_lang('Fast fertig','Almost ready'),'incomplete'=>ffl_lang('Unvollständig','Incomplete'))[$quality['state']] ); ?></span><br><small><?php echo esc_html( sprintf( ffl_lang('%d von %d erfüllt','%d of %d complete'),$quality['passed'],$quality['total']) ); ?></small></td>
      <td data-label="<?php echo esc_attr( ffl_lang('Fehlende Punkte','Missing items') ); ?>"><details class="ffl-diag-quality"><summary><?php echo esc_html( $missing ? sprintf( ffl_lang('%d Punkte anzeigen','Show %d items'), count($missing) ) : ffl_lang('Alles vollständig','Everything complete') ); ?></summary><?php if($missing): ?><ul><?php foreach($missing as $check): ?><li>✗ <?php echo esc_html($check['label']); ?></li><?php endforeach; ?></ul><?php endif; ?></details></td></tr>
    <?php endforeach; ?></tbody></table>
    <?php if ( $total_pages > 1 ) { $pagination_args=array('post_type'=>'ffl_einsatz','page'=>'ffl_einsatz_diagnose','diag_search'=>$search,'diag_filter'=>$filter,'diag_per_page'=>(-1===$per_page?'all':$per_page)); echo '<div class="tablenav"><div class="tablenav-pages">'.wp_kses_post(paginate_links(array('base'=>add_query_arg(array_merge($pagination_args,array('diag_page'=>'%#%')),admin_url('edit.php')),'format'=>'','current'=>$page,'total'=>$total_pages,'prev_text'=>'← '.ffl_lang('Zurück','Previous'),'next_text'=>ffl_lang('Weiter','Next').' →'))).'</div></div>'; }
    ?>
    <p class="description"><?php echo esc_html( ffl_lang( 'nginx-, Hoster- und CDN-Caches können nicht zuverlässig durch WordPress erkannt oder gelöscht werden.', 'nginx, hosting and CDN caches cannot be reliably detected or cleared by WordPress.' ) ); ?></p></div><?php
}
