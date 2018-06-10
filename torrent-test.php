<?php

    //-------------------------------------------------
    // HEAD
    //-------------------------------------------------

    error_reporting( -1 );

    ini_set( 'html_errors', 0 );

    ini_set( 'display_errors', 1 );

    define( 'CORE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

    header( 'Content-Type:text/plain' );

    //-------------------------------------------------
    // TODO
    //-------------------------------------------------

    // Detect and skip duplicate files when creating a new torrent.

    // Passed array must have more than two elements.

    // Intercept error if a file does not exist.

    require_once CORE_DIR . 'php-class-torrent-create-read-write.php';

    // $torrent = new Torrent( CORE_DIR );

    // $torrent = new Torrent( array( 'LICENSE', 'LICENSE' ) );

    $torrent = new Torrent();

    // $torrent->convert_a_wildcard_to_regex_pattern( array( '*test*', '*was*' ) );

    $torrent->include( '*test*' );

    // $torrent->include( '*test*,*mana*' );

    // $torrent->include( '*test*', '*mana*' );

    // $torrent->include( array( '*test*', '*mana*' ) );

    $torrent->single()->file();

    // $torrent->single()->folder( '*' );

    // $torrent->save( CORE_DIR . 'test.torrent' );

?>