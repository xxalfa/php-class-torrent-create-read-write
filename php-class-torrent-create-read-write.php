<?php

declare( strict_types = 1 );

/**
 * Application: Class Torrent Create Read Write
 *
 * @package   BitTorrent
 * @category  file sharing
 * @version   0.0.5
 * @author    Adrien Gibrat <adrien.gibrat@gmail.com>
 * @copyleft  2010 - Just use it!
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License version 3
 * @link
 * @tester    Jeong, Anton, dokcharlie, official testers ;) Thanks for your precious feedback
 */
class Torrent
{
    /**
     * Used to record the application in detail.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const TRACE = 1;

    /**
     * Used to circle errors.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const DEBUG = 2;

    /**
     * Used to record general information related to program flow.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const INFORMATION = 3;

    /**
     * Used to record events that do not disturb the program flow.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const WARNING = 4;

    /**
     * Used to detect errors.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const ERROR = 5;

    /**
     * Used to record special errors.
     *
     * @since 0.0.12 (2018-07-01)
     *
     * @var <integer>
     */
    const FATAL = 6;

    /**
     * Contains the list of all events recorded during processing.
     *
     * @since 0.0.12 (2018-07-01)
     * @access private
     *
     * @var <array>
     */
    protected $list_of_events = array();

    /**
     * Used for recording and displaying events.
     *
     * @since 0.0.12 (2018-07-01)
     * @access protected
     * @static
     *
     * @param Exception
     *
     * @return <string> Return the message.
     */
    protected static function reporting( $exception = null )
    {
        // return ( array_unshift( self::$list_of_errors, $exception ) && $message ) ? $exception->getMessage() : false;

        if ( is_null( $exception ) )
        {
            foreach ( $this->list_of_events as $event )
            {
                echo '<br>' . $event->getMessage() . ' in <b>' . $event->getFile() . '</b> on line <b>' . $event->getLine() . '</b> [Code:' . $event->getCode() . ']';
            }
        }
        else
        {
            array_push( $this->list_of_events, $exception )

            return $exception->getMessage();
        }
    }

    /**
     * Default http timeout.
     *
     * @since 0.0.3
     * @access public
     *
     * @var <float>
     */
    const TIMEOUT = 30;

    /**
     * Read and decode torrent file/data OR build a torrent from source folder/files
     *
     * Supported signatures:
     *
     * - Torrent(); // get an instance (useful to scrape and check errors)
     * - Torrent( string $torrent ); // analyze a torrent file
     * - Torrent( string $torrent, string $announce );
     * - Torrent( string $torrent, array $metadata );
     * - Torrent( string $file_or_folder ); // create a torrent file
     * - Torrent( string $file_or_folder, string $announce_url, [ int $piece_length ] );
     * - Torrent( string $file_or_folder, array $metadata, [ int $piece_length ] );
     * - Torrent( array $files_list );
     * - Torrent( array $files_list, string $announce_url, [ int $piece_length ] );
     * - Torrent( array $files_list, array $metadata, [ int $piece_length ] );.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <string|array> torrent to read or source folder/files (optional, to get an instance)
     * @param <string|array> announce url or meta informations (optional)
     * @param <integer> piece length (optional)
     */
    public function __construct( $data = null, $metadata = [], $piece_length = 256 )
    {
        if ( is_null( $data ) )
        {
            return false;
        }

        if ( $piece_length < 32 || $piece_length > 4096 )
        {
            self::reporting( new Exception( 'Invalid piece length, must be between 32 and 4096.', self::ERROR ) );

            return false;
        }

        if ( is_string( $metadata ) )
        {
            $metadata = [ 'announce' => $metadata ];
        }

        if ( $this->build_the_torrent_info( $data, $piece_length * 1024 ) )
        {
            $this->metadata();
        }
        else
        {
            $metadata = array_merge( $metadata, $this->decode( $data ) );
        }

        foreach ( $metadata as $key => $value )
        {
            $this->{ trim( $key ) } = $value;
        }
    }

    /**
     * Convert the current Torrent instance in torrent format.
     *
     * @since 0.0.3
     * @access public
     *
     * @return <string> encoded torrent data
     */
    public function __toString()
    {
        return $this->encode( $this );
    }

/* ------------------------------------------------------------------------- */
/* GETTERS AND SETTERS
/* ------------------------------------------------------------------------- */

    /**
     * Getter and setter of torrent announce url / list.
     *
     * If the argument is a string, announce url is added to announce list (or set as announce if announce is not set)
     * If the argument is an array/object, set announce url (with first url) and list (if array has more than one url), tiered list supported
     * If the argument is false announce url & list are unset.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|false|string|array> announce url / list, reset all if false (optional, if omitted it's a getter)
     *
     * @return <string|array|null> announce url / list or null if not set
     */
    public function announce( $announce = null )
    {
        if ( is_null( $announce ) )
        {
            return ! isset( $this->{ 'announce-list' } ) ? isset( $this->announce ) ? $this->announce : null : $this->{ 'announce-list' };
        }

        $this->metadata();

        if ( isset( $this->announce ) && is_string( $announce ) )
        {
            return $this->{ 'announce-list' } = self::announce_list( isset( $this->{ 'announce-list' } ) ? $this->{ 'announce-list' } : $this->announce, $announce );
        }

        unset( $this->{ 'announce-list' } );

        if ( is_array( $announce ) || is_object( $announce ) )
        {
            if ( ( $this->announce = self::get_first_announce( $announce ) ) && count( $announce ) > 1 )
            {
                return $this->{ 'announce-list' } = self::announce_list( $announce );
            }
            else
            {
                return $this->announce;
            }
        }

        if ( ! isset( $this->announce ) && $announce )
        {
            return $this->announce = (string) $announce;
        }

        unset( $this->announce );
    }

    /**
     * Getter and setter of torrent creation date.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|integer> timestamp (optional, if omitted it's a getter)
     *
     * @return <integer|null> timestamp or null if not set
     */
    public function creation_date( $timestamp = null )
    {
        return is_null( $timestamp ) ? isset( $this->{ 'creation date' } ) ? $this->{ 'creation date' } : null : $this->metadata( $this->{ 'creation date' } = (integer) $timestamp );
    }

    /**
     * Getter and setter of torrent comment.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string> comment (optional, if omitted it's a getter)
     *
     * @return <null|string> comment or null if not set
     */
    public function comment( $comment = null )
    {
        return is_null( $comment ) ? isset( $this->comment ) ? $this->comment : null : $this->metadata( $this->comment = (string) $comment );
    }

    /**
     * Getter and setter of torrent name.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string> name (optional, if omitted it's a getter)
     *
     * @return <null|string> name or null if not set
     */
    public function name( $name = null )
    {
        return is_null( $name ) ? isset( $this->info[ 'name' ] ) ? $this->info[ 'name' ] : null : $this->metadata( $this->info[ 'name' ] = (string) $name );
    }

    /**
     * Getter and setter of private flag.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|bool> is private or not (optional, if omitted it's a getter)
     *
     * @return <boolean> private flag
     */
    public function is_private( $private = null )
    {
        return is_null( $private ) ? ! empty( $this->info[ 'private' ] ) : $this->metadata( $this->info[ 'private' ] = $private ? 1 : 0 );
    }

    /**
     * Getter and setter of torrent source.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string> source (optional, if omitted it's a getter)
     *
     * @return <null|string> source or null if not set
     */
    public function source( $source = null )
    {
        return is_null( $source ) ? isset( $this->info[ 'source' ] ) ? $this->info[ 'source' ] : null : $this->metadata( $this->info[ 'source' ] = (string) $source );
    }

    /**
     * Getter and setter of webseed( s ) url list (GetRight implementation).
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string|array> webseed or webseeds mirror list (optional, if omitted it's a getter)
     *
     * @return <string|array|null> webseed( s ) or null if not set
     */
    public function url_list( $urls = null )
    {
        return is_null( $urls ) ? isset( $this->{ 'url-list' } ) ? $this->{ 'url-list' } : null : $this->metadata( $this->{ 'url-list' } = is_string( $urls ) ? $urls : (array) $urls );
    }

    /**
     * Getter and setter of httpseed( s ) url list (BitTornado implementation).
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string|array> httpseed or httpseeds mirror list (optional, if omitted it's a getter)
     *
     * @return <array|null> httpseed( s ) or null if not set
     */
    public function httpseeds( $urls = null )
    {
        return is_null( $urls ) ? isset( $this->httpseeds ) ? $this->httpseeds : null : $this->metadata( $this->httpseeds = (array) $urls );
    }

/* ------------------------------------------------------------------------- */
/* BIT TORRENT -> ANALYZE
/* ------------------------------------------------------------------------- */

    /**
     * Get piece length.
     *
     * @since 0.0.3
     * @access public
     *
     * @return <integer> piece length or null if not set
     */
    public function piece_length()
    {
        return isset( $this->info[ 'piece length' ] ) ? $this->info[ 'piece length' ] : null;
    }

    /**
     * Compute hash info.
     *
     * @since 0.0.3
     * @access public
     *
     * @return <string> hash info or null if info not set
     */
    public function info_hash()
    {
        return isset( $this->info ) ? sha1( self::encode( $this->info ) ) : null;
    }

    /**
     * List torrent content.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <integer|null> size precision (optional, if omitted returns sizes in bytes)
     *
     * @return <array> files and size( s ) list, files as keys and sizes as values
     */
    public function content( $precision = null )
    {
        $files = [];

        if ( isset( $this->info[ 'files' ] ) && is_array( $this->info[ 'files' ] ) )
        {
            foreach ( $this->info[ 'files' ] as $file )
            {
                $files[ self::path( $file[ 'path' ], $this->info[ 'name' ] ) ] = $precision ? self::format( $file[ 'length' ], $precision ) : $file[ 'length' ];
            }
        }
        else if ( isset( $this->info[ 'name' ] ) )
        {
            $files[ $this->info[ 'name' ] ] = $precision ? self::format( $this->info[ 'length' ], $precision ) : $this->info[ 'length' ];
        }

        return $files;
    }

    /**
     * List torrent content pieces and offset( s ).
     *
     * @since 0.0.3
     * @access public
     *
     * @return <array> files and pieces/offset( s ) list, files as keys and pieces/offset( s ) as values
     */
    public function offset()
    {
        $files = [];

        $size = 0;

        if ( isset( $this->info[ 'files' ] ) && is_array( $this->info[ 'files' ] ) )
        {
            foreach ( $this->info[ 'files' ] as $file )
            {
                $files[ self::path( $file[ 'path' ], $this->info[ 'name' ] ) ] = [ 'startpiece' => floor( $size / $this->info[ 'piece length' ] ), 'offset' => fmod( $size, $this->info[ 'piece length' ] ), 'size' => $size += $file[ 'length' ], 'endpiece' => floor( $size / $this->info[ 'piece length' ] ) ];
            }
        }
        else if ( isset( $this->info[ 'name' ] ) )
        {
            $files[ $this->info[ 'name' ] ] = [ 'startpiece' => 0, 'offset' => 0, 'size' => $this->info[ 'length' ], 'endpiece' => floor( $this->info[ 'length' ] / $this->info[ 'piece length' ] ) ];
        }

        return $files;
    }

    /**
     * Sum torrent content size.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <integer|null> size precision (optional, if omitted returns size in bytes)
     *
     * @return <integer|string> files size
     */
    public function size( $precision = null )
    {
        $size = 0;

        if ( isset( $this->info[ 'files' ] ) && is_array( $this->info[ 'files' ] ) )
        {
            foreach ( $this->info[ 'files' ] as $file )
            {
                $size += $file[ 'length' ];
            }
        }
        else if ( isset( $this->info[ 'name' ] ) )
        {
            $size = $this->info[ 'length' ];
        }

        return is_null( $precision ) ? $size : self::format( $size, $precision );
    }

    /**
     * Request torrent statistics from scrape page USING CURL!
     *
     * @since 0.0.3
     * @access public
     *
     * @param <string|array> announce or scrape page url (optional, to request an alternative tracker BUT required for static call)
     * @param <string> torrent hash info (optional, required ONLY for static call)
     * @param <float> read timeout in seconds (optional, default to self::TIMEOUT 30s)
     *
     * @return <array> tracker torrent statistics
     */
    public function scrape( $announce = null, $info_hash = null, $timeout = self::TIMEOUT )
    {
        $packed_hash = urlencode( pack( 'H*', $info_hash ? $info_hash : $this->info_hash() ) );

        $handles = $scrape = [];

        if ( function_exists( 'curl_multi_init' ) == false )
        {
            self::reporting( new Exception( 'Install CURL with "curl_multi_init" enabled.', self::ERROR ) );

            return false;
        }

        $curl = curl_multi_init();

        foreach ( (array) ( $announce ? $announce : $this->announce() ) as $tier )
        {
            foreach ( (array) $tier as $tracker )
            {
                $tracker = str_ireplace( [ 'udp://', '/announce', ':80/' ], [ 'http://', '/scrape', '/' ], $tracker );

                if ( isset( $handles[ $tracker ] ) )
                {
                    continue;
                }

                $handles[ $tracker ] = curl_init( $tracker . '?info_hash=' . $packed_hash );

                curl_setopt( $handles[ $tracker ], CURLOPT_RETURNTRANSFER, true );

                curl_setopt( $handles[ $tracker ], CURLOPT_TIMEOUT, $timeout );

                curl_multi_add_handle( $curl, $handles[ $tracker ] );
            }
        }

        do
        {
            while ( CURLM_CALL_MULTI_PERFORM == ( $state = curl_multi_exec( $curl, $running ) ) );

            if ( CURLM_OK != $state )
            {
                continue;
            }

            while ( $done = curl_multi_info_read( $curl ) )
            {
                $info = curl_getinfo( $done[ 'handle' ] );

                $tracker = explode( '?', $info[ 'url' ], 2 );

                $tracker = array_shift( $tracker );

                if ( empty( $info[ 'http_code' ] ) )
                {
                    $scrape[ $tracker ] = self::reporting( new Exception( 'Tracker request timeout (' . $timeout . 's).', self::ERROR ) );

                    continue;
                }
                else if ( $info[ 'http_code' ] != 200 )
                {
                    $scrape[ $tracker ] = self::reporting( new Exception( 'Tracker request failed (' . $info[ 'http_code' ] . ' code).', self::ERROR ) );

                    continue;
                }

                $data = curl_multi_getcontent( $done[ 'handle' ] );

                $stats = self::decode_data( $data );

                curl_multi_remove_handle( $curl, $done[ 'handle' ] );

                $scrape[ $tracker ] = empty( $stats[ 'files' ] ) ? self::reporting( new Exception( 'Empty scrape data.', self::ERROR ) ) : array_shift( $stats[ 'files' ] ) + ( empty( $stats[ 'flags' ] ) ? [] : $stats[ 'flags' ] );
            }
        }
        while ( $running );

        curl_multi_close( $curl );

        return $scrape;
    }

/* ------------------------------------------------------------------------- */
/* SEND
/* ------------------------------------------------------------------------- */

    /**
     * Send torrent file to client.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <null|string> name of the file (optional).
     */
    public function send( $filename = null )
    {
        $data = $this->encode( $this );

        header( 'Content-type: application/x-bittorrent' );

        header( 'Content-Length: ' . strlen( $data ) );

        header( 'Content-Disposition: attachment; filename="' . ( is_null( $filename ) ? $this->info[ 'name' ] . '.torrent' : $filename ) . '"' );

        echo $data;

        exit;
    }

    /**
     * Get magnet link.
     *
     * @since 0.0.3
     * @access public
     *
     * @param <boolean> html encode ampersand, default true (optional).
     *
     * @return <string> magnet link
     */
    public function magnet( $html = true )
    {
        $ampersand = $html ? '&amp;' : '&';

        return sprintf( 'magnet:?xt=urn:btih:%2$s%1$sdn=%3$s%1$sxl=%4$d%1$str=%5$s', $ampersand, $this->info_hash(), urlencode( $this->name() ), $this->size(), implode( $ampersand . 'tr=', self::untier( $this->announce() ) ) );
    }

/* ------------------------------------------------------------------------- */
/* BIT TORRENT -> ENCODE
/* ------------------------------------------------------------------------- */

    /**
     * Encode torrent data.
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <mixed> data to encode
     *
     * @return <string> torrent encoded data
     */
    public static function encode( $mixed )
    {
        switch ( gettype( $mixed ) )
        {
            case 'integer':
            case 'double':
            {
                return self::encode_integer( $mixed );
            }
            case 'object':
            {
                $mixed = get_object_vars( $mixed );

                // no break
            }
            case 'array':
            {
                return self::encode_array( $mixed );
            }
            default:
            {
                return self::encode_string( (string) $mixed );
            }
        }
    }

    /**
     * Encode torrent string.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> string to encode
     *
     * @return <string> encoded string
     */
    private static function encode_string( $string )
    {
        return strlen( $string ) . ':' . $string;
    }

    /**
     * Encode torrent integer.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <integer> integer to encode
     *
     * @return <string> encoded integer
     */
    private static function encode_integer( $integer )
    {
        return 'i' . $integer . 'e';
    }

    /**
     * Encode torrent dictionary or list.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <array> array to encode
     *
     * @return <string> encoded dictionary or list
     */
    private static function encode_array( $array )
    {
        if ( self::is_list( $array ) )
        {
            $return = 'l';

            foreach ( $array as $value )
            {
                $return .= self::encode( $value );
            }
        }
        else
        {
            ksort( $array, SORT_STRING );

            $return = 'd';

            foreach ( $array as $key => $value )
            {
                $return .= self::encode( strval( $key ) ) . self::encode( $value );
            }
        }

        return $return . 'e';
    }

/* ------------------------------------------------------------------------- */
/* BIT TORRENT -> DECODE
/* ------------------------------------------------------------------------- */

    /**
     * Decode torrent data or file.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <string> data or file path to decode
     *
     * @return <array> decoded torrent data
     */
    protected static function decode( $string )
    {
        $data = is_file( $string ) || self::is_url_callable( $string ) ? self::try_to_load_the_file_content( $string ) : $string;

        return (array) self::decode_data( $data );
    }

    /**
     * Decode torrent data.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> data to decode
     *
     * @return <array> decoded torrent data
     */
    private static function decode_data( &$data )
    {
        switch ( self::get_first_char( $data ) )
        {
            case 'i':
            {
                $data = substr( $data, 1 );

                return self::decode_integer( $data );
            }
            case 'l':
            {
                $data = substr( $data, 1 );

                return self::decode_list( $data );
            }
            case 'd':
            {
                $data = substr( $data, 1 );

                return self::decode_dictionary( $data );
            }
            default:
            {
                return self::decode_string( $data );
            }
        }
    }

    /**
     * Decode torrent dictionary.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> data to decode
     *
     * @return <array> decoded dictionary
     */
    private static function decode_dictionary( &$data )
    {
        $dictionary = [];

        $previous = null;

        while ( ( $char = self::get_first_char( $data ) ) != 'e' )
        {
            if ( $char === false )
            {
                self::reporting( new Exception( 'Unterminated dictionary.', self::ERROR ) );

                return false;
            }

            if ( ctype_digit( $char ) == false )
            {
                self::reporting( new Exception( 'Invalid dictionary key.', self::ERROR ) );

                return false;
            }

            $key = self::decode_string( $data );

            if ( isset( $dictionary[ $key ] ) )
            {
                self::reporting( new Exception( 'Duplicate dictionary key.', self::ERROR ) );

                return false;
            }

            if ( $key < $previous )
            {
                self::reporting( new Exception( 'Missorted dictionary key.', self::ERROR ) );
            }

            $dictionary[ $key ] = self::decode_data( $data );

            $previous = $key;
        }

        $data = substr( $data, 1 );

        return $dictionary;
    }

    /**
     * Decode torrent list.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> data to decode
     *
     * @return <array> decoded list
     */
    private static function decode_list( &$data )
    {
        $list = [];

        while ( ( $char = self::get_first_char( $data ) ) != 'e' )
        {
            if ( $char === false )
            {
                self::reporting( new Exception( 'Unterminated list.', self::ERROR ) );

                return false;
            }

            $list[] = self::decode_data( $data );
        }

        $data = substr( $data, 1 );

        return $list;
    }

    /**
     * Decode torrent string.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> data to decode
     *
     * @return <string> decoded string
     */
    private static function decode_string( &$data )
    {
        if ( self::get_first_char( $data ) === '0' && substr( $data, 1, 1 ) != ':' )
        {
            self::reporting( new Exception( 'Invalid string length, leading zero.', self::ERROR ) );

            return false;
        }

        if ( ( $colon = @ strpos( $data, ':' ) ) == false  )
        {
            self::reporting( new Exception( 'Invalid string length, colon not found.', self::ERROR ) );

            return false;
        }

        $length = intval( substr( $data, 0, $colon ) );

        if ( $length + $colon + 1 > strlen( $data ) )
        {
            self::reporting( new Exception( 'Invalid string, input too short for string length.', self::ERROR ) );

            return false;
        }

        $string = substr( $data, $colon + 1, $length );

        $data = substr( $data, $colon + $length + 1 );

        return $string;
    }

    /**
     * Decode torrent integer.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> data to decode
     *
     * @return <integer> decoded integer
     */
    private static function decode_integer( &$data )
    {
        $start = 0;

        $end = strpos( $data, 'e' );

        if ( $end === 0 )
        {
            self::reporting( new Exception( 'Empty integer.', self::ERROR ) );
        }

        if ( '-' == self::get_first_char( $data ) )
        {
            ++$start;
        }

        if ( substr( $data, $start, 1 ) == '0' && $end > $start + 1 )
        {
            self::reporting( new Exception( 'Leading zero in integer.', self::ERROR ) );
        }

        if ( ctype_digit( substr( $data, $start, $start ? $end - 1 : $end ) ) == false )
        {
            self::reporting( new Exception( 'Non-digit characters in integer.', self::ERROR ) );
        }

        $integer = substr( $data, 0, $end );

        $data = substr( $data, $end + 1 );

        return 0 + $integer;
    }

/* ------------------------------------------------------------------------- */
/* INTERNAL HELPERS
/* ------------------------------------------------------------------------- */

    /**
     * Build the torrent info.
     *
     * @since 0.0.3
     * @access protected
     *
     * @param <string|array> source folder/files path
     * @param <integer> piece length
     *
     * @return <array|bool> torrent info or false if data isn't folder/files
     */
    protected function build_the_torrent_info( $data, $piece_length )
    {
        self::reporting( new Exception( 'Build the torrent info.', self::DEBUG ) );

        if ( is_null( $data ) )
        {
            return false;
        }
        else if ( is_array( $data ) && self::is_list( $data ) )
        {
            return $this->info = $this->build_the_torrent_info_from_multiple_files( $data, $piece_length );
        }
        else if ( is_dir( $data ) )
        {
            return $this->info = $this->build_the_torrent_info_from_folder_content( $data, $piece_length );
        }
        else if ( ( is_file( $data ) || self::is_url_callable( $data ) ) && ! self::is_torrent( $data ) )
        {
            return $this->info = $this->build_the_torrent_info_from_a_single_file( $data, $piece_length );
        }
        else
        {
            return false;
        }
    }

    /**
     * Set torrent creator and creation date.
     *
     * @since 0.0.11 (2018-07-01)
     * @access protected
     *
     * @param any param
     *
     * @return any param
     */
    protected function metadata( $value = null )
    {
        $this->{ 'created by' } = empty( $this->{ 'created by' } ) ? 'Torrent RW PHP Class - http://github.com/adriengibrat/torrent-rw' : $this->{ 'created by' };

        $this->{ 'creation date' } = empty( $this->{ 'creation date' } ) ? time() : $this->{ 'creation date' };

        return $value;
    }

    /**
     * Build announce list.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <string|array> announce url / list
     * @param <string|array> announce url / list to add (optionnal)
     *
     * @return <array> announce list (list of arrays)
     */
    protected static function announce_list( $announce, $merge = [] )
    {
        return array_map( function( $a ) { return (array) $a; }, array_merge( (array) $announce, (array) $merge ) );
    }

    /**
     * Get the first announce url in a list.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <array> announce list (array of arrays if tiered trackers)
     *
     * @return <string> first announce url
     */
    protected static function get_first_announce( $announce )
    {
        while ( is_array( $announce ) )
        {
            $announce = reset( $announce );
        }

        return $announce;
    }

    /**
     * Helper to pack data hash.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <string> data
     *
     * @return <string> packed data hash
     */
    protected static function pack( &$data )
    {
        return pack( 'H*', sha1( $data ) ) . ( $data = null );
    }

    /**
     * Helper to build file path.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <array> file path
     * @param <string> base folder
     *
     * @return <string> real file path
     */
    protected static function path( $path, $folder )
    {
        array_unshift( $path, $folder );

        return join( DIRECTORY_SEPARATOR, $path );
    }

    /**
     * Helper to explode file path.
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @param <string> file path
     *
     * @return <array> file path
     */
    protected static function path_explode( $path )
    {
        return explode( DIRECTORY_SEPARATOR, $path );
    }

    /**
     * Helper to test if an array is a list.
     *
     *  array to test
     *
     * @since 0.0.3
     * @access protected
     * @static
     *
     * @return <boolean> is the array a list or not
     */
    protected static function is_list( $array )
    {
        foreach ( array_keys( $array ) as $key )
        {
            if ( ! is_int( $key ) )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Build pieces depending on piece length from a file handler.
     *
     * @since 0.0.3
     * @access private
     *
     * @param <ressource> file handle
     * @param <integer> piece length
     * @param <boolean> is last piece
     *
     * @return <string> pieces
     */
    private function pieces( $handle, $piece_length, $last = true )
    {
        static $piece, $length;

        if ( empty( $length ) )
        {
            $length = $piece_length;
        }

        $pieces = null;

        while ( ! feof( $handle ) )
        {
            if ( ( $length = strlen( $piece .= fread( $handle, $length ) ) ) == $piece_length )
            {
                $pieces .= self::pack( $piece );
            }
            else if ( ( $length = $piece_length - $length ) < 0 )
            {
                self::reporting( new Exception( 'Invalid piece length!', self::ERROR ) );

                return false;
            }
        }

        fclose( $handle );

        return $pieces . ( $last && $piece ? self::pack( $piece ) : null );
    }

    /**
     * Build the torrent info from single file.
     *
     * @since 0.0.4
     * @access private
     *
     * @param <string> file path
     * @param <integer> piece length
     *
     * @return <array> torrent info
     */
    private function build_the_torrent_info_from_a_single_file( $file, $piece_length )
    {
        self::reporting( new Exception( 'Build the torrent info from single file.', self::DEBUG ) );

        if ( ( $handle = self::try_to_open_the_file( $file, $size = self::determine_the_file_size( $file ) ) ) == false )
        {
            self::reporting( new Exception( 'Failed to open file: "' . $file . '".', self::ERROR ) );

            return false;
        }

        if ( self::is_url( $file ) )
        {
            $this->url_list( $file );
        }

        $path = self::path_explode( $file );

        return [ 'length' => $size, 'name' => end( $path ), 'piece length' => $piece_length, 'pieces' => $this->pieces( $handle, $piece_length ) ];
    }

    /**
     * Build the torrent info from multiple files.
     *
     * @since 0.0.4
     * @access private
     *
     * @param <array> file list
     * @param <integer> piece length
     *
     * @return <array> torrent info
     */
    private function build_the_torrent_info_from_multiple_files( $files, $piece_length )
    {
        self::reporting( new Exception( 'Build the torrent info from multiple files.', self::DEBUG ) );

        sort( $files );

        usort( $files, function( $a, $b ) { return strrpos( $a, DIRECTORY_SEPARATOR ) - strrpos( $b, DIRECTORY_SEPARATOR ); } );

        $first = current( $files );

        if ( ! self::is_url( $first ) )
        {
            $files = array_map( 'realpath', $files );
        }
        else
        {
            $this->url_list( dirname( $first ) . DIRECTORY_SEPARATOR );
        }

        $files_path = array_map( 'self::path_explode', $files );

        $root = call_user_func_array( 'array_intersect_assoc', $files_path );

        $pieces = null;

        $info_files = [];

        $count = count( $files ) - 1;

        foreach ( $files as $index => $file )
        {
            if ( ( $handle = self::try_to_open_the_file( $file, $filesize = self::determine_the_file_size( $file ) ) ) == false )
            {
                self::reporting( new Exception( 'Failed to open file: "' . $file . '" discarded.' ), self::ERROR );

                continue;
            }

            $pieces .= $this->pieces( $handle, $piece_length, $count == $index );

            $info_files[] = [ 'length' => $filesize, 'path' => array_diff_assoc( $files_path[ $index ], $root ) ];
        }

        return [ 'files' => $info_files, 'name' => end( $root ), 'piece length' => $piece_length, 'pieces' => $pieces ];
    }

    /**
     * Build torrent info from folder content.
     *
     * @since 0.0.4
     * @access private
     *
     * @param <string> folder path
     * @param <integer> piece length
     *
     * @return <array> torrent info
     */
    private function build_the_torrent_info_from_folder_content( $dir, $piece_length )
    {
        return $this->build_the_torrent_info_from_multiple_files( self::searches_a_folder_for_substructures_and_files( $dir ), $piece_length );
    }

    /**
     * Helper to return the first char of encoded data.
     *
     * @since 0.0.3
     * @access private
     * @static
     *
     * @param <string> encoded data
     *
     * @return <string|bool> first char of encoded data or false if empty data
     */
    private static function get_first_char( $data )
    {
        return empty( $data ) ? false : substr( $data, 0, 1 );
    }

/* ------------------------------------------------------------------------- */
/* PUBLIC HELPERS
/* ------------------------------------------------------------------------- */

    /**
     * Helper to open file to read (even bigger than 2Gb, linux only).
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <string> file path
     * @param <integer|float> file size (optional)
     *
     * @return <resource|bool> file handle or false if error
     */
    public static function try_to_open_the_file( $file, $size = null )
    {
        self::reporting( new Exception( 'Try to open the file "' . $file . '".', self::DEBUG ) );

        if ( ( is_null( $size ) ? self::determine_the_file_size( $file ) : $size ) <= 2 * pow( 1024, 3 ) )
        {
            return fopen( $file, 'r' );
        }
        else if ( PHP_OS != 'Linux' )
        {
            self::reporting( new Exception( 'File size is greater than 2GB. This is only supported under Linux.', self::ERROR ) );

            return false;
        }
        else if ( is_readable( $file ) )
        {
            return popen( 'cat ' . escapeshellarg( realpath( $file ) ), 'r' );
        }

        return false;
    }

    /**
     * Helper to get (distant) file content.
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <string> file location
     * @param <float> http timeout (optional, default to self::TIMEOUT 30s)
     * @param <integer> starting offset (optional, default to null)
     * @param <integer> content length (optional, default to null)
     *
     * @return <string|bool> file content or false if error
     */
    public static function try_to_load_the_file_content( $file, $timeout = self::TIMEOUT, $offset = null, $length = null )
    {
        self::reporting( new Exception( 'Try to load the file content "' . $file . '".', self::DEBUG ) );

        if ( is_file( $file ) || ini_get( 'allow_url_fopen' ) )
        {
            $context = ! is_file( $file ) && $timeout ? stream_context_create( [ 'http' => [ 'timeout' => $timeout ] ] ) : null;

            return ! is_null( $offset ) ? $length ? @ file_get_contents( $file, false, $context, $offset, $length ) : @ file_get_contents( $file, false, $context, $offset ) : @ file_get_contents( $file, false, $context );
        }

        if ( function_exists( 'curl_init' ) == false )
        {
            self::reporting( new Exception( 'Install CURL or enable "allow_url_fopen".', self::ERROR ) );

            return false;
        }

        $handle = curl_init( $file );

        if ( $timeout )
        {
            curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );
        }

        if ( $offset || $length )
        {
            curl_setopt( $handle, CURLOPT_RANGE, $offset . '-' . ( $length ? $offset + $length - 1 : null ) );
        }

        curl_setopt( $handle, CURLOPT_RETURNTRANSFER, 1 );

        $content = curl_exec( $handle );

        $size = curl_getinfo( $handle, CURLINFO_CONTENT_LENGTH_DOWNLOAD );

        curl_close( $handle );

        return ( $offset && $size == -1 ) || ( $length && $length != $size ) ? $length ? substr( $content, $offset, $length ) : substr( $content, $offset ) : $content;
    }

    /**
     * Helper to return filesize (even bigger than 2Gb -linux only- and distant files size).
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <string> file path
     *
     * @return <float|bool> filesize or false if error
     */
    public static function determine_the_file_size( $file )
    {
        if ( is_file( $file ) )
        {
            return (float) sprintf( '%u', @ filesize( $file ) );
        }
        else if ( $content_length = preg_grep( $pattern = '#^Content-Length:\s+(\d+)$#i', (array) @ get_headers( $file ) ) )
        {
            return (integer) preg_replace( $pattern, '$1', reset( $content_length ) );
        }
    }

    /**
     * Helper to format size in bytes to human readable.
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <integer> size in bytes
     * @param <integer> precision after coma
     *
     * @return <string> formated size in appropriate unit
     */
    public static function format( $size, $precision = 2 )
    {
        $label = array( 'Bytes', 'kByte', 'MByte', 'GByte', 'TByte', 'PByte', 'EByte', 'ZByte', 'YByte' );

        return $value ? round( $value / pow( 1024, ( $index = floor( log( $value, 1024 ) ) ) ), $precision ) . ' ' . $label[ $index ] : '0 Bytes';
    }

    /**
     * Helper to scan directories files and sub directories recursively.
     *
     * @since 0.0.9 (2018-06-11)
     * @access public
     * @static
     *
     * @param <string> directory path
     *
     * @return <array> directory content list
     */
    public static function searches_a_folder_for_substructures_and_files( $path )
    {
        self::reporting( new Exception( 'Search for files in this directory "' . $path . '" started.', self::DEBUG ) );

        $list_of_paths = array();

        // foreach ( scandir( $path ) as $item )
        // {
        //     if ( substr( $item, 0, 1 ) === '.' ): continue; endif;

        //     $realpath = realpath( $path . DIRECTORY_SEPARATOR . $item );

        //     if ( is_dir( $realpath ) )
        //     {
        //         $list_of_paths = array_merge( self::searches_a_folder_for_substructures_and_files( $realpath ), $list_of_paths );
        //     }
        //     else
        //     {
        //         $list_of_paths[] = $realpath;
        //     }
        // }

        $iterator = new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS );

        $iterator = new RecursiveIteratorIterator( $iterator );

        if ( $this->skip_hidden_objects )
        {
            $iterator = new ExcludeHiddenObjectsFilterIterator( $iterator );
        }

        foreach ( $iterator as $object )
        {
            $list_of_paths[] = $object->getPathname();
        }

        return $list_of_paths;
    }

    /**
     * Helper to check if string is an url (http).
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <string> url to check
     *
     * @return <boolean> is string an url
     */
    public static function is_url( $url )
    {
        return preg_match( '#^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$#i', $url );
    }

    /**
     * Is the content behind the URL still available?
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <string> url to check
     *
     * @return <boolean> does the url exist or not
     */
    public static function is_url_callable( $url )
    {
        return self::is_url( $url ) ? (bool) self::determine_the_file_size( $url ) : false;
    }

    /**
     * Helper to check if a file is a torrent.
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @see https://github.com/adriengibrat/torrent-rw/issues/32
     * @see https://github.com/adriengibrat/torrent-rw/pull/17
     *
     * @param <string> file location
     * @param <float> http timeout (optional, default to self::TIMEOUT 30s)
     *
     * @return <boolean> is the file a torrent or not
     */
    public static function is_torrent( $file, $timeout = self::TIMEOUT )
    {
        return ( $start = self::try_to_load_the_file_content( $file, $timeout, 0, 11 ) ) && 'd8:announce' === $start || 'd10:created' === $start || 'd13:creatio' === $start || 'd13:announc' === $start || 'd12:_info_l' === $start || 'd7:comment' === substr( $start, 0, 10 ) || 'd4:info' === substr( $start, 0, 7 ) || 'd9:' === substr( $start, 0, 3 );
    }

    /**
     * Flatten announces list.
     *
     * @since 0.0.3
     * @access public
     * @static
     *
     * @param <array> announces list
     *
     * @return <array> flattened announces list
     */
    public static function untier( $announces )
    {
        $list = [];

        foreach ( (array) $announces as $tier )
        {
            is_array( $tier ) ? $list = array_merge( $list, self::untier( $tier ) ) : array_push( $list, $tier );
        }

        return $list;
    }

    /**
     * Contains the path from which folder the application should work.
     *
     * @since 0.0.8 (2018-06-10)
     * @access private
     *
     * @var <string>
     */
    private $workspace = '';

    /**
     * Can be called to specify which directory to work with.
     *
     * @since 0.0.8 (2018-06-10)
     * @access public
     *
     * @param $path <null|string> Path to the root directory.
     *
     * @return $this
     */
    public function workspace( $path = null )
    {
        $this->workspace = is_null( $path ) ? dirname( __FILE__ ) : $path;

        return $this;
    }

    /**
     * Determines whether hidden objects should be processed.
     *
     * @since 0.0.9 (2018-06-11)
     * @access private
     *
     * @var <boolean>
     */
    private $skip_hidden_objects = false;

    /**
     * Can be called to exclude hidden objects.
     *
     * @since 0.0.9 (2018-06-11)
     * @access public
     *
     * @param $void <null>
     *
     * @return $this
     */
    public function hidden( $void = null )
    {
        $this->skip_hidden_objects = true;

        return $this;
    }

    /**
     * Used to specify whether the files or folders are selected for a particular search pattern.
     *
     * @since 0.0.7 (2018-06-03)
     * @access private
     *
     * @var <boolean>
     */
    private $is_a_search_pattern_set = false;

    /**
     * Consists of an array containing the search patterns to be included or excluded.
     *
     * @since 0.0.10 (2018-06-12)
     * @access private
     *
     * @var <array>
     */
    private $include = array();

    /**
     * Can be called to include objects with a specific search pattern.
     *
     * @since 0.0.8 (2018-06-10)
     * @access public
     *
     * @param <string|array> Contains the search pattern.
     *
     * @return $this
     */
    public function include()
    {
        $get_args = func_get_args();

        $num_args = func_num_args();

        if ( $num_args == 0 )
        {
            return false;
        }

        if ( $num_args == 1 && is_string( $get_args[ 0 ] ) )
        {
            if ( strpos( $get_args[ 0 ], ',' ) !== false )
            {
                foreach ( explode( ',', $get_args[ 0 ] ) as $item )
                {
                    array_push( $this->pattern[ 'include' ], $item );
                }
            }
            else
            {
                array_push( $this->pattern[ 'include' ], $get_args[ 0 ] );
            }
        }

        if ( $num_args == 1 && is_array( $get_args[ 0 ] ) )
        {
            foreach ( $get_args[ 0 ] as $item )
            {
                array_push( $this->pattern[ 'include' ], $item );
            }
        }

        if ( $num_args > 1 )
        {
            foreach ( $get_args as $item )
            {
                array_push( $this->pattern[ 'include' ], $item );
            }
        }

        $this->is_a_search_pattern_set = true;

        return $this;
    }

    /**
     * Consists of an array containing the search patterns to be excluded.
     *
     * @since 0.0.10 (2018-06-12)
     * @access private
     *
     * @var <array>
     */
    private $exclude = array();

    /**
     * Can be called to exclude objects with a specific search pattern.
     *
     * @since 0.0.8 (2018-06-10)
     * @access public
     *
     * @param <string|array> Contains the search pattern.
     *
     * @return $this
     */
    public function exclude()
    {
        $get_args = func_get_args();

        $num_args = func_num_args();

        if ( $num_args == 0 )
        {
            return false;
        }

        if ( $num_args == 1 && is_string( $get_args[ 0 ] ) )
        {
            if ( strpos( $get_args[ 0 ], ',' ) !== false )
            {
                foreach ( explode( ',', $get_args[ 0 ] ) as $item )
                {
                    array_push( $this->pattern[ 'exclude' ], $item );
                }
            }
            else
            {
                array_push( $this->pattern[ 'exclude' ], $get_args[ 0 ] );
            }
        }

        if ( $num_args == 1 && is_array( $get_args[ 0 ] ) )
        {
            foreach ( $get_args[ 0 ] as $item )
            {
                array_push( $this->pattern[ 'exclude' ], $item );
            }
        }

        if ( $num_args > 1 )
        {
            foreach ( $get_args as $item )
            {
                array_push( $this->pattern[ 'exclude' ], $item );
            }
        }

        $this->is_a_search_pattern_set = true;

        return $this;
    }

    /**
     * Used to specify whether one or more torrent files should be created from multiple objects.
     *
     * @since 0.0.6 (2018-05-31)
     * @access private
     *
     * @var <boolean>
     */
    private $is_single_processing_active = false;

    /**
     * Determines how to handle each file or folder as a single object to create a torrent file. The names of files or folders are selected only at the first level with a specific search pattern.
     *
     * @since 0.0.5
     * @access public
     *
     * @param <string>
     *
     * @return $this
     */
    public function single( $void = null )
    {
        $this->workspace();

        $this->is_single_processing_active = true;

        return $this;
    }

    /**
     * Contains all information that was generated during processing. There can be multiple torrent files.
     *
     * @since 0.0.8 (2018-06-10)
     * @access private
     *
     * @var <array>
     */
    private $torrent = array();

    /**
     * Search for single files using the search pattern and create torrent files from them.
     *
     * @since 0.0.9 (2018-06-11)
     * @access public
     *
     * @param $void <null>
     *
     * @return $this
     */
    public function file( $void = null )
    {

        $waswas = $this->transform_pattern( '*test*' );

        echo var_dump( $waswas );

        // $this->pattern[ 'include' ] = $this->convert_a_wildcard_to_regex_pattern( $this->pattern[ 'include' ] );

        // $this->pattern[ 'exclude' ] = $this->convert_a_wildcard_to_regex_pattern( $this->pattern[ 'exclude' ] );

        // echo $this->pattern[ 'include' ] . PHP_EOL;

        // echo $this->pattern[ 'exclude' ] . PHP_EOL;



        // $this->exclude  = array_filter( array_map( 'realpath', $exclude ) );


        // foreach ( $this->exclude as $exclude )
        // {
        //     if ( strpos( $path, $exclude ) === 0 )
        //     {
        //         return false;
        //     }
        // }


        exit;

        if ( $this->is_single_processing_active )
        {
            $iterator = new RecursiveDirectoryIterator( $this->workspace, FilesystemIterator::SKIP_DOTS );

            if ( $this->is_a_search_pattern_set )
            {
                $iterator = new RegexIterator( $iterator, $this->pattern[ 'include' ] );
            }

            if ( $this->is_a_search_pattern_set )
            {
                $iterator = new RegexIterator( $iterator, $this->pattern[ 'exclude' ] );
            }

            if ( $this->skip_hidden_objects )
            {
                $iterator = new ExcludeHiddenObjectsFilterIterator( $iterator );
            }

            foreach ( $iterator as $object )
            {
                if ( $object->isFile() )
                {
                    echo $object . PHP_EOL;
                }
            }

            exit;
        }
        else
        {

        }

        return $this;
    }

    /**
     * Search for folders using the search pattern and create torrent files from them.
     *
     * @since 0.0.5
     * @access public
     *
     * @param $void <null>
     *
     * @return $this
     */
    public function folder( $void = null )
    {
        if ( $this->is_single_processing_active )
        {

        }
        else
        {

        }

        return $this;
    }

    /**
     * Convert a wildcard to RegEx pattern.
     *
     * @since 0.0.8 (2018-06-10)
     * @access private
     *
     * @param $value <string> String with wildcards converted to the RegEx standard.
     *
     * @return <string> Returns a string in which the wildcards were converted.
     */
    private function convert_a_wildcard_to_regex_pattern( $value = null )
    {
        $value = is_null( $value ) ? '*' : $value;

        $value = is_string( $value ) ? preg_quote( $value, '/' ) : $value;

        if ( is_string( $value ) && strpos( $value, ',' ) !== false )
        {
            $value = str_replace( ',', '|', $value );
        }

        if ( is_array( $value ) )
        {
            foreach ( $value as $index => $item )
            {
                $value[ $index ] = preg_quote( $item, '/' );
            }

            $value = implode( '|', $value );
        }

        // fnmatch( 'dir/*/file', 'dir/folder1/file' ); // The operation of the function still needs to be checked.

        return '/^' . str_replace( '\*' , '.+?', $value ) . '$/i';
    }

    /**
     * Processes a string and masks certain characters.
     *
     * @since 0.0.10 (2018-06-12)
     * @access private
     *
     * @param $pattern <string>
     *
     * @return <string>
     */
    private static function transform_pattern( $pattern )
    {
        $transformed_pattern = preg_replace_callback( '(([*]{2}|[*]|[?]|[/\\\\]|[^*/\\\\?]+))', function( $match_array )
        {
            $match = current( $match_array );

            switch ( $match )
            {
                case '**':
                {
                    return '(?:.*?)';
                }
                case '*':
                {
                    return '(?:[^/\\\\]*?)';
                }
                case '?':
                {
                    return '(?:[^/\\\\])';
                }
                case '\\':
                case '/':
                {
                    return '(?:[/\\\\])';
                }
            }

            return preg_quote( $match, '()' );

        }, $pattern );

        return sprintf( '((?:%s)$)', $transformed_pattern );
    }

    /**
     * Save torrent file to disk.
     *
     * @since 0.0.3
     * @access public
     *
     * @param $value <null|string> Name of the file (optional).
     *
     * @return <boolean> The return is positive if the torrent file(s) could be created. If not, a silent error is reported.
     */
    public function save( $value = null )
    {
        // If single file or folder is activated, a separate torrent file must be created for each file / folder.

        // return file_put_contents( is_null( $value ) ? $this->info[ 'name' ] . '.torrent' : $value, $this->encode( $this ) );
    }
}

class ExcludeHiddenObjectsFilterIterator extends FilterIterator
{
    public function accept()
    {
        return preg_match( '/\/\./', $this->getInnerIterator()->current() ) ? false : true;
    }
}

?>