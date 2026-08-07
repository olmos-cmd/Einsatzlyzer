<?php
$root = dirname( __DIR__ );
$code = file_get_contents( $root . '/includes/import-export.php' );
$checks = array(
    'Schema version 7'           => false !== strpos( $code, 'FFL_IMPEX_SCHEMA_VERSION = 7' ),
    'Settings JSON export'       => false !== strpos( $code, "einstellungen.json" ),
    'All plugin settings'        => false !== strpos( $code, 'ffl_impex_export_plugin_settings' ),
    'Settings restore'           => false !== strpos( $code, 'ffl_impex_import_plugin_settings' ),
    'Stable relationship UUIDs'  => false !== strpos( $code, 'ffl_impex_resolve_relationships' ),
    'Comments export/import'     => false !== strpos( $code, 'ffl_impex_export_comments' ) && false !== strpos( $code, 'ffl_impex_import_comments' ),
    'All FFL incident metadata'  => false !== strpos( $code, 'ffl_impex_export_meta' ),
    'Attachment custom metadata' => false !== strpos( $code, "'_wp_attachment_metadata'" ),
);
foreach ( $checks as $label => $ok ) {
    echo ( $ok ? '[OK] ' : '[FAIL] ' ) . $label . PHP_EOL;
    if ( ! $ok ) { exit( 1 ); }
}
