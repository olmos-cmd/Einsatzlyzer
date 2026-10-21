<?php
/** Static regression checks for archive SEO heading/text and incident image ALT logic. */
$root = dirname( __DIR__ );
$main = file_get_contents( $root . '/einsatz-manager.php' );
$single = file_get_contents( $root . '/single-ffl_einsatz.php' );
$css = file_get_contents( $root . '/css/einsatzlyzer.css' );
$checks = array(
    'version 10.6.25' => false !== strpos( $main, "FFL_EINSATZLYZER_VERSION', '10.6.25" ),
    'configurable archive heading setting' => false !== strpos( $main, "'ffl_archive_heading'" ),
    'archive heading always h1' => false !== strpos( $main, '<h1 class="ffl-archive-title"' ) && false === strpos( $main, '<h2 class="ffl-archive-title"' ),
    'optional archive seo setting' => false !== strpos( $main, "'ffl_archive_seo_enabled'" ) && false !== strpos( $main, "'ffl_archive_seo_text'" ),
    'seo text below results' => false !== strpos( $main, 'class="ffl-archive-seo"' ),
    'manual alt preservation' => false !== strpos( $main, "_wp_attachment_image_alt" ) && false !== strpos( $main, 'ffl_get_incident_image_alt' ),
    'archive card uses alt helper' => false !== strpos( $main, "'alt' => ffl_get_incident_image_alt( \$preview_id, \$post_id )" ),
    'single hero uses alt helper' => false !== strpos( $single, "'alt' => ffl_get_incident_image_alt( \$preview_id, \$post_id )" ),
    'gallery uses alt helper' => false !== strpos( $single, "'alt' => ffl_get_incident_image_alt( \$image_id, \$post_id )" ),
    'compact archive hero' => false !== strpos( $css, 'min-height: 340px;' ),
);
foreach ( $checks as $label => $passed ) {
    echo ( $passed ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
}
exit( in_array( false, $checks, true ) ? 1 : 0 );
