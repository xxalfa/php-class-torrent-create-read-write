<?php

    //-------------------------------------------------
    // HEAD
    //-------------------------------------------------

    error_reporting( -1 );

    ini_set( 'html_errors', 1 );

    ini_set( 'display_errors', 1 );

    define( 'CORE_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

    header( 'Content-Type:text/html' );

    //-------------------------------------------------
    // GET LIST OF TORRENTS
    //-------------------------------------------------

    if ( isset( $_REQUEST[ 'GET_LIST_OF_TORRENTS' ] ) )
    {
        is_readable( CORE_DIR ) or die ( 'Dir is not readable.' );
        
        $list_of_files = array();

        $iterator = new RecursiveDirectoryIterator( CORE_DIR, FilesystemIterator::SKIP_DOTS );

        $iterator = new RecursiveIteratorIterator( $iterator );

        foreach ( $iterator as $object )
        {
            if ( $object->getExtension() != 'torrent' ): continue; endif;

            require_once CORE_DIR . 'php-class-torrent-create-read-write.php';

            $torrent = new Torrent( $object->getPathname() );

            $torrent->info[ 'pieces' ] = '';

            $torrent->filename = $object->getFilename();

            $list_of_files[] = $torrent;
        }

        header( 'Content-Type:application/json' );

        echo json_encode( $list_of_files );

        exit;
    }

?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#" moznomarginboxes mozdisallowselectionprint>
<title>TORRENT MANAGER</title>

<meta charset="utf-8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">

<link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAAVlpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDUuNC4wIj4KICAgPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4KICAgICAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgICAgICAgICAgeG1sbnM6dGlmZj0iaHR0cDovL25zLmFkb2JlLmNvbS90aWZmLzEuMC8iPgogICAgICAgICA8dGlmZjpPcmllbnRhdGlvbj4xPC90aWZmOk9yaWVudGF0aW9uPgogICAgICA8L3JkZjpEZXNjcmlwdGlvbj4KICAgPC9yZGY6UkRGPgo8L3g6eG1wbWV0YT4KTMInWQAAAvlJREFUWAm1lTuLVEEQhXd9rKIIRhoo6Ao+MDEwMREE0cTIxEgEMfc/bGRoYGpkJGZiJP4GQRHRyAdoZiAGKr7Pd7u/oXe4c2fcmT1wuruqq05V9713ZmlpY1juSevz9YTN77LQ1kjdq2QN3CvWJo3bqu6NzH8rWQP3irUJoyfcHu2XoQ2wxgeMKdaCR094PboU/1XJGh8wplgLHD0ZBV6EFP1dyRqfxY2Na3FQ/FokPT1zu2YPGFusBYyeiLf9eUhRTm8Drtmb+4ugGNxSxTjNzhBcDdsT20DrIwaQQy4NoaVuluvBBoGQwCE8yybFPHHbgD5ihkAN6y1TfBwE7AoPhEcqD2Y+HV4IKdqXF/do70nWT8MP4ZvKj5m/hn/CERDaHV4JT4SHwtXwcLg3XAlbDBU3ri/mRzY/h+/Ct+H78HX4IFxaC0nqI93yrSPA3BfT52tz0OiLwbfGs+CqgMXwLXeeMvMS+WZX99RpPIdigJnmqMGjtnb3COzyf05qzqxzq81j7+DpLsdSqA3UN+/calILWLu7EhyXQj+nNmFRxdGmBuAxrIOOi/Hy0lF0EU2ogSbawFrFakY3zsX3LaQJb2Qjt2AuWudCYI1i9Yz+r5/N3qeQwkOf0qTGzEEDLaB2sQbGHXXvVmYK/KzzpGJ9fnPQAGoWq458i30gGayWafS7UM2ZJn9L1FBzXXJfAyRyfXwiJ2u0YtWcaTIHDbTQ1DcoYNC+RH0JuV6fZ99VT/KZgwZaQO1iZZx0AwQcC/ewmAAK85b7pk8I6zTQAjM1YFPHS05XoE2kMN82Pq4WsvZ7z7IDPpoDaqldvBmHvkmTKCgoQg78Ht4Jwc3Qt9wY/OaqhW8qPO3DRHpaT6d9N3tHGyXW+MbjtNECaherZzRgJXuvQgsyw/vhqVB4G9rsEWO8DaCFJrBGscZGN/fH71uM2KPwTBPLc2+fJ2t8glhybAQtNIE1ijUw3s7e4/B8EzNeuNnqluONkIsGWnNhXHia2LRGR/n/ALNkVj/jnaViAAAAAElFTkSuQmCC" rel="icon" type="image/x-icon">
<style type="text/css">
    
    *
    {
        margin:0;
        padding:0;
        text-decoration:none;
        text-rendering:auto;
        box-sizing:border-box;
        font-family:monospace;
    }
    header
    {
        position:absolute;
        top:0;
        left:0;
        width:100%;
        min-height:40px;
        padding-top:10px;
        padding-left:10px;
        background:green;
    }
    footer
    {
        position:absolute;
        bottom:0;
        left:0;
        width:100%;
        min-height:40px;
        padding-top:10px;
        padding-left:10px;
        background:green;
    }
    .DIALOG_MESSAGE,
    .TORRENT_MANAGER
    {
        position:absolute;
        top:0;
        left:0;
        right:0;
        bottom:0;
        margin:auto;
        width:500px;
        height:500px;
        background:#41403F;
        z-index:2000;
        color:white;
    }
    .DIALOG_MESSAGE header,
    .DIALOG_MESSAGE footer,
    .TORRENT_MANAGER header,
    .TORRENT_MANAGER footer
    {
        z-index:3000;
    }
    .DIALOG_MESSAGE section,
    .TORRENT_MANAGER section
    {
        overflow-y:auto;
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:100%;
        padding:10px;
        padding-top:50px;
        padding-bottom:50px;
        background:#41403F;
        z-index:2000;
    }

</style>

<div></div>

<script type="text/javascript">

    function AJAX_REQUEST_HANDLING() { var request = null; try { request = new XMLHttpRequest(); } catch ( error ) { try { request = new ActiveXObject( 'Msxml2.XMLHTTP' ); } catch ( error ) { try { request = new ActiveXObject( 'Microsoft.XMLHTTP' ); } catch ( error ) { return false; } } } return request; };

    var HTTP_REQUEST = AJAX_REQUEST_HANDLING();

    window.onerror = function( ERROR_MESSAGE, SOURCE_URL, LINE_NUMBER, COLUMN_NUMBER ) { alert( ERROR_MESSAGE + ' on line ' + LINE_NUMBER + ':' + COLUMN_NUMBER ); };

    function UNIQID() { return Math.random().toString( 36 ).substr( 2, 10 ).toUpperCase(); }

</script>
<script type="text/javascript">
    
    var VA1 = document.createElement( 'div' );

    VA1.className = 'TORRENT_MANAGER';

    var VA2 = document.createElement( 'header' );

    VA1.appendChild( VA2 );

    var VA2 = document.createElement( 'section' );

    VA1.appendChild( VA2 );

    var VA2 = document.createElement( 'footer' );

    VA1.appendChild( VA2 );

    document.querySelector( 'body' ).appendChild( VA1 );

    // List of torrent files. EDIT DELETE

    // Files or folders to be merged into a torrent.

    HTTP_REQUEST.open( 'get', 'torrent-manager.php?GET_LIST_OF_TORRENTS', true );

    HTTP_REQUEST.addEventListener( 'load', EVENT_LOAD__LIST_OF_FILES, false );

    function EVENT_LOAD__LIST_OF_FILES( event )
    {
        var VA1 = new Array();

        try { VA1 = JSON.parse( HTTP_REQUEST.responseText ); } catch ( error ) { return false; }

        for ( var VA2 = 0; VA2 < VA1.length; VA2++ )
        {
            document.querySelector( '.TORRENT_MANAGER section' ).innerHTML += VA1[ VA2 ].filename;
        }
    }

    HTTP_REQUEST.send();

    //-------------------------------------------------
    // DIALOG
    //-------------------------------------------------

    function Dialog()
    {
        this.MB_OK = 0; // OK (Default)

        this.MB_OKCANCEL = 1; // OK | Cancel

        this.MB_ABORTRETRYIGNORE = 2; // Abort | Retry | Ignore

        this.MB_YESNOCANCEL = 3; // Yes | No | Cancel

        this.MB_YESNO = 4; // Yes | No

        this.MB_RETRYCANCEL = 5; // Retry | Cancel


        this.MB_ICONNONE = 0; // None.

        this.MB_ICONSTOP = 16; // Stop.

        this.MB_ICONQUESTION = 32; // Question.

        this.MB_ICONEXCLAMATION = 48; // Exclamation.

        this.MB_ICONINFORMATION = 64; // Information. (Default)


        this.MB_DEFBUTTON1 = 0; // The first button from the left. (Default)

        this.MB_DEFBUTTON2 = 256; // The second button from the left.

        this.MB_DEFBUTTON3 = 512; // The third button from the left.


        this.IDOK = 1; // The OK button.

        this.IDCANCEL = 2; // The Cancel button.

        this.IDABORT = 3; // The Abort button.

        this.IDRETRY = 4; // The Retry button.

        this.IDIGNORE = 5; // The Ignore button.

        this.IDYES = 6; // The Yes button.

        this.IDNO = 7; // The No button.
    }

    Dialog.prototype.Message = function( /* string */ Title, /* string */ Text, /* number */ Type = this.MB_OK, /* number */ Icon = this.MB_ICONINFORMATION, /* number */ DefaultButton = this.MB_DEFBUTTON1 )
    {
        var DM1 = document.createElement( 'div' );

        DM1.setAttribute( 'class', 'DIALOG_MESSAGE' );

        DM1.setAttribute( 'identifier', UNIQID() );

        var HE1 = document.createElement( 'header' );

        HE1.innerHTML = Title;

        DM1.appendChild( HE1 );

        var SE1 = document.createElement( 'section' );

        SE1.innerHTML = Text;

        DM1.appendChild( SE1 );

        var FO1 = document.createElement( 'footer' );

        var BT1 = document.createElement( 'button' );

        BT1.setAttribute( 'class', 'DIALOG_MESSAGE__BUTTON' );

        BT1.setAttribute( 'type', 'button' );

        BT1.innerHTML = 'test';

        BT1.addEventListener( 'click', EVENT_CLICK__DIALOG_MESSAGE, false );

        FO1.appendChild( BT1 );

        DM1.appendChild( FO1 );

        document.querySelector( 'body' ).appendChild( DM1 );
    };

    new Dialog().Message( 'aaa', 'bbb' );

    new Dialog().Message( 'ccc', 'ddd' );

    function EVENT_CLICK__DIALOG_MESSAGE( event )
    {
        // alert( this.parentNode.parentNode.getAttribute( 'identifier' ) );

        document.querySelector( '[identifier="' + this.parentNode.parentNode.getAttribute( 'identifier' ) + '"]' ).outerHTML = '';
    }

</script>