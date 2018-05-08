### PHP Class Torrent Create Read Write

## Features:

- Decode torrent file or data
- Build torrent from source folder/file(s)
- Silent Exception error system

## Usage example

```php
<?php

     header( 'Content-Type:text/plain' );

     require_once 'php-class-torrent-create-read-write.php';

    # GET TORRENT INFOS

    $torrent = new Torrent( './test.torrent' );

    echo 'IS PRIVATE -- ' . ( $torrent->is_private() ? 'yes' : 'no' ) . PHP_EOL;

    echo 'ANNOUNCE -- ' . $torrent->announce() . PHP_EOL;

    echo 'NAME -- ' . $torrent->name() . PHP_EOL;

    echo 'COMMENT -- ' . $torrent->comment() . PHP_EOL;

    echo 'PIECE LENGTH -- ' . $torrent->piece_length() . PHP_EOL;

    echo 'SIZE -- ' . $torrent->size( 2 ) . PHP_EOL;

    echo 'HASH INFO -- ' .  $torrent->hash_info() . PHP_EOL;

    echo 'STATS' . PHP_EOL;

    var_dump( $torrent->scrape() ) . PHP_EOL;

    echo 'CONTENT' . PHP_EOL;

    var_dump( $torrent->content() ) . PHP_EOL;

    echo 'SOURCE -- ' . $torrent;

    # GET MAGNET LINK

    $torrent->magnet(); 
    
    // use $torrent->magnet( false ); to get non html encoded ampersand

    # CREATE TORRENT

    $torrent = new Torrent( array( 'test.mp3', 'test.jpg' ), 'http://torrent.tracker/annonce' );

    $torrent->save( 'test.torrent' ); // save to disk

    # MODIFY TORRENT

    $torrent->announce( 'http://alternate-torrent.tracker/annonce' ); // add a tracker

    $torrent->announce( false ); // reset announce trackers

    $torrent->announce( array( 'http://torrent.tracker/annonce', 'http://alternate-torrent.tracker/annonce' ) ); // set tracker( s ), it also works with a 'one tracker' array...

    $torrent->announce( array( array( 'http://torrent.tracker/annonce', 'http://alternate-torrent.tracker/annonce' ), 'http://another-torrent.tracker/annonce' ) ); // set tiered trackers

    $torrent->comment( 'hello world' );

    $torrent->name( 'test torrent' );

    $torrent->is_private( true );

    $torrent->httpseeds( 'http://file-hosting.domain/path/' ); // BitTornado implementation

    $torrent->url_list( array( 'http://file-hosting.domain/path/','http://another-file-hosting.domain/path/' ) ); // GetRight implementation

    # PRINT ERRORS / SEND

    if ( $errors = $torrent->errors() )
    {
        var_dump( $errors );
    }
    else
    {
        $torrent->send();
    }

?>
```

## Requirements

* PHP >= 5.2

## Contributing

You can help the project by adding features, cleaning the code, adding composer and other.

1. Fork it
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request
