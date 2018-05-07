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

    // Detect and skip hidden system files.

    // Detect and skip duplicate files when creating a new torrent.

    // Passed array must have more than two elements.

    // Intercept error if a file does not exist.

    require_once CORE_DIR . 'php-class-torrent-read-write.php';

    $torrent = new Torrent( CORE_DIR );

    $torrent->save( 'test.torrent' );

?>