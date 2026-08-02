<?php
$code = file_get_contents(__DIR__ . '/../includes/vehicles-depesche.php');
$checks = array(
    'external upload form' => strpos($code, 'ffl-dispatch-upload-form') !== false,
    'external apply form' => strpos($code, 'ffl-dispatch-apply-form') !== false,
    'external cancel form' => strpos($code, 'ffl-dispatch-cancel-form') !== false,
    'post id preserved' => strpos($code, 'target_post_id') !== false,
    'post edit redirect' => strpos($code, "admin_url( 'post.php' )") !== false,
);
foreach ($checks as $name => $ok) {
    if (!$ok) { fwrite(STDERR, "FAILED: $name\n"); exit(1); }
}
echo "dispatch editor forms ok\n";
