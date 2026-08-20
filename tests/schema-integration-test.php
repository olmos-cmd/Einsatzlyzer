<?php
/** Static regression fixture for schema integration. */
$fixture = array(
    array( '@type' => 'Event' ),
    array( '@type' => 'Article', 'about' => array( '@type' => 'Event' ) ),
    array( '@type' => 'BreadcrumbList', 'itemListElement' => array( array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home' ) ) ),
);
if ( 3 !== count( $fixture ) ) { fwrite( STDERR, "Fixture failed\n" ); exit( 1 ); }
echo "Schema integration fixture: OK\n";
