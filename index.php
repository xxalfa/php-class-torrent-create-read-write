<?php

    //-------------------------------------------------
    // HEAD
    //-------------------------------------------------

    error_reporting( -1 );

    ini_set( 'html_errors', 1 );

    ini_set( 'display_errors', 1 );

    define( 'CORE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

    // header( 'Content-Type:text/plain' );

    //-------------------------------------------------
    // TODO
    //-------------------------------------------------

    // Detect and skip duplicate files.

    // Passed array must have more than two elements.

    // Intercept error if a file does not exist.

    require_once 'Torrent.php';

    $torrent = new Torrent( CORE_DIR );

    $torrent->save( 'test.torrent' );

?>