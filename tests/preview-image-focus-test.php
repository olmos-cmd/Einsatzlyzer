<?php
/** Static regression checks for per-incident preview image focus (10.6.25). */
$root      = dirname( __DIR__ );
$main      = file_get_contents( $root . '/einsatz-manager.php' );
$admin_js  = file_get_contents( $root . '/js/einsatz-manager-admin.js' );
$admin_css = file_get_contents( $root . '/css/einsatzlyzer-admin.css' );
$impex     = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'version 10.6.25' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.25" ),
    'focus mode sanitizer' => false !== strpos( $main, 'function ffl_sanitize_preview_focus_mode' ),
    'focus percent sanitizer' => false !== strpos( $main, 'function ffl_sanitize_preview_focus_percent' ),
    'focus resolver' => false !== strpos( $main, 'function ffl_get_preview_focus' ),
    'default center focus' => false !== strpos( $main, "'mode' => \$mode" ) && false !== strpos( $main, "'css'  => sprintf( '%d%% %d%%'" ),
    'focus meta fields' => false !== strpos( $main, '_ffl_preview_focus_mode' ) && false !== strpos( $main, '_ffl_preview_focus_x' ) && false !== strpos( $main, '_ffl_preview_focus_y' ),
    'admin focus selector' => false !== strpos( $main, 'id="ffl_preview_focus_mode"' ) && false !== strpos( $main, "ffl_lang( 'Benutzerdefiniert', 'Custom' )" ),
    'full image picker' => false !== strpos( $main, 'data-preview-focus-picker' ) && false !== strpos( $main, 'data-preview-focus-source-image' ),
    'visible focus marker' => false !== strpos( $main, 'data-preview-focus-marker' ) && false !== strpos( $admin_css, '.ffl-preview-focus__marker' ),
    'separate exact card preview' => false !== strpos( $main, 'data-preview-focus-card' ) && false !== strpos( $main, 'data-preview-focus-card-image' ) && false !== strpos( $admin_css, 'aspect-ratio: 16 / 8.2' ),
    'pixel calculated crop preview' => false !== strpos( $admin_js, 'renderPreviewCard' ) && false !== strpos( $admin_js, 'const overflowX' ) && false !== strpos( $admin_js, 'const overflowY' ) && false !== strpos( $admin_js, 'image.style.left' ) && false !== strpos( $admin_js, 'image.style.top' ),
    'crop movement explanation' => false !== strpos( $main, 'data-preview-focus-effect' ) && false !== strpos( $admin_css, '.ffl-preview-focus__effect' ),
    'frontend focus css vars helper' => false !== strpos( $main, 'function ffl_get_preview_focus_css_vars' ),
    'archive direct focus layer' => false !== strpos( $main, 'class="ffl-card__focus-layer"' ) && false !== strpos( $main, 'background-position:%2$d%% %3$d%%' ) && false !== strpos( $main, 'data-focus-x=' ),
    'focus layer visible above img' => false !== strpos( file_get_contents( $root . '/css/einsatzlyzer.css' ), '.ffl-card__media.has-focus-layer .ffl-card__image' ) && false !== strpos( file_get_contents( $root . '/css/einsatzlyzer.css' ), 'opacity: 0' ),
    'homepage direct background focus' => false !== strpos( $main, 'background-position:%2$d%% %3$d%%;background-size:cover;background-repeat:no-repeat;' ),
    'targeted W3TC purge' => false !== strpos( $main, "function_exists( 'w3tc_flush_url' )" ) && false !== strpos( $main, 'ffl_purge_incident_preview_caches( $post_id )' ),
    'live admin preview js' => false !== strpos( $admin_js, 'updatePreviewFocus' ) && false !== strpos( $admin_js, 'positionFocusMarker' ),
    'pointer focal point editing' => false !== strpos( $admin_js, "previewFocusPicker.on('pointerdown'" ) && false !== strpos( $admin_js, "previewFocusPicker.on('pointermove'" ) && false !== strpos( $admin_js, 'focusFromPointer' ),
    'contained image coordinate mapping' => false !== strpos( $admin_js, 'getContainedImageBounds' ) && false !== strpos( $admin_js, 'naturalWidth' ) && false !== strpos( $admin_js, 'naturalHeight' ),
    'keyboard focus editing' => false !== strpos( $admin_js, "previewFocusPicker.on('keydown'" ) && false !== strpos( $admin_js, 'ArrowLeft' ),
    'range controls' => false !== strpos( $main, 'id="ffl_preview_focus_x_range"' ) && false !== strpos( $main, 'id="ffl_preview_focus_y_range"' ),
    'live gallery source sync' => false !== strpos( $admin_js, 'syncPreviewImageSource' ) && false !== strpos( $admin_js, 'data-preview-url' ),
    'featured image live sync' => false !== strpos( $admin_js, 'getFeaturedImageId' ) && false !== strpos( $admin_js, 'MutationObserver' ),
    'backup contract' => false !== strpos( $impex, 'function ffl_impex_preview_focus_backup_contract' ),
    'manifest contract' => false !== strpos( $impex, "'preview_focus' => ffl_impex_preview_focus_backup_contract()" ),
    'csv export fields' => false !== strpos( $impex, "'Vorschaubild-Fokus'" ) && false !== strpos( $impex, "'_ffl_preview_focus_mode'" ),
    'schema remains 7' => false !== strpos( $impex, 'FFL_IMPEX_SCHEMA_VERSION = 7' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
}
exit( in_array( false, $checks, true ) ? 1 : 0 );
