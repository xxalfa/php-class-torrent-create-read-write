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

</style>

<div></div>

<script type="text/javascript">

    function AJAX_REQUEST_HANDLING() { var request = null; try { request = new XMLHttpRequest(); } catch ( error ) { try { request = new ActiveXObject( 'Msxml2.XMLHTTP' ); } catch ( error ) { try { request = new ActiveXObject( 'Microsoft.XMLHTTP' ); } catch ( error ) { return false; } } } return request; };

    var HTTP_REQUEST = AJAX_REQUEST_HANDLING();

    window.onerror = function( ERROR_MESSAGE, SOURCE_URL, LINE_NUMBER, COLUMN_NUMBER ) { alert( ERROR_MESSAGE + ' on line ' + LINE_NUMBER + ':' + COLUMN_NUMBER ); };

</script>
<script type="text/javascript">
    

</script>