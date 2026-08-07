<?php
$root = dirname(__DIR__);
$code = file_get_contents($root . '/includes/import-export.php');
if ($code === false) { fwrite(STDERR, "import-export.php missing\n"); exit(1); }
if (strpos($code, 'FFL_IMPEX_SCHEMA_VERSION = 7') === false) { fwrite(STDERR, "schema version 7 missing\n"); exit(1); }
if (strpos($code, 'ffl_impex_external_links_backup_contract') === false) { fwrite(STDERR, "external link backup contract missing\n"); exit(1); }
for ($i=1; $i<=5; $i++) {
    foreach (['_ffl_link_' . $i, '_ffl_link_source_' . $i] as $field) {
        // Dynamic construction is accepted when the contract function contains both prefixes.
        if (strpos($code, "'_ffl_link_' . \$i") === false || strpos($code, "'_ffl_link_source_' . \$i") === false) {
            fwrite(STDERR, "link/source contract fields missing\n"); exit(1);
        }
    }
}
if (strpos($code, "'external_links' => ffl_impex_external_links_backup_contract()") === false) { fwrite(STDERR, "manifest contract missing\n"); exit(1); }
echo "external-links-backup-test: OK\n";
