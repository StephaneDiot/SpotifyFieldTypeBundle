<?php
/**
 * File containing the Spotify FieldType Value class.
 *
 */

namespace SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * The spotify URL
     * @var string
     */
    public $url;

    /**
     * The content that will be stored
     * @var string
     */
    public $contents;

    /**
     * Construct a new Value object
     * @param array $arg
     */
    public function __construct( $arg = array() )
    {
        if ( !is_array( $arg ) )
            $arg = array( 'url' => $arg );

        parent::__construct( $arg );
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->url;
    }
}