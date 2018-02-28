<?php
/**
 * File containing the Spotify FieldType Type class.
 */

namespace SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as CoreValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\Persistence\Content\FieldValue as PersistenceValue;
use SpotifyFieldTypeBundle\Spotify\SpotifyClientInterface;

class Type extends FieldType
{
    /** @var SpotifyClientInterface */
    protected $spotifyClient;
    /**
     * Type constructor and initialize it with SpotifyClientInterface
     * @param SpotifyClientInterface $spotifyClient
     */
    public function __construct( SpotifyClientInterface $spotifyClient )
    {
        $this->spotifyClient = $spotifyClient;

    }
    /**
     * Returns the field type identifier for this field type.
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezspotify';
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param string|\SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $inputValue
     *
     * @return \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value The potentially converted and structurally plausible value.
     */
    protected function createValueFromInput( $inputValue )
    {

        if ( is_string( $inputValue ) )
        {
            $inputValue = new Value( array( 'url' => $inputValue ) );
        }

        return $inputValue;

    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     */
    protected function checkValueStructure( CoreValue $value )
    {

        if ( !is_string( $value->url ) )
        {
            throw new InvalidArgumentType(
                '$value->url',
                'string',
                $value->url
            );
        }
    }
    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     *
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     *
     * @return string
     */
    public function getName( SPIValue $value )
    {
        if ( !preg_match(

            '#^https?://open.spotify\.com/user/([^/]+/playlist/[0-9_A-Z_a-z]+)$#',
            (string)$value,
            $matches

        ))
            return '';

        return str_replace( '/', '-', $matches[1] );


    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     *
     * @return array
     */
    protected function getSortInfo( CoreValue $value )
    {
        return $this->getName($value);
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value
     */
    public function getEmptyValue()
    {
        return new Value;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration( $validatorConfiguration )
    {
        $validationErrors = array();

        return $validationErrors;
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $fieldValue The field value for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, SPIValue $fieldValue )
    {
        $errors = array();

        if ( $this->isEmptyValue( $fieldValue ) )
        {
            return $errors;
        }

        // Spotify Url validation
        if ( !preg_match( '#^https?://open.spotify\.com/user/([^/]+/playlist/[0-9_A-Z_a-z]+)$#', $fieldValue->url, $matches ) )
            $errors[] = new ValidationError( "Invalid Spotify playlist url: %url%", null, array( '%url%' => $fieldValue->url ) );


        return $errors;
    }

    /**
     * Converts a $value to a persistence value.
     *
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function toPersistenceValue( SPIValue $value )
    {
        if ( $value === null )
        {
            return new PersistenceValue(
                array(
                    "data" => null,
                    "externalData" => null,
                    "sortKey" => null,
                )
            );
        }

        if ( $value->contents === null && !empty($value->url) )
        {
            //spotifyClient call
            $value->contents = $this->spotifyClient->getEmbed( $value->url );
        }

        return new PersistenceValue(
            array(
                "data" => $this->toHash( $value ),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value
     */
    public function fromPersistenceValue( PersistenceValue $fieldValue )
    {
        if ( $fieldValue->data === null )
        {
            return $this->getEmptyValue();
        }

        return new Value( $fieldValue->data );
    }

    /**
     * Converts a $Value to a hash.
     *
     * @param \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     *
     * @return mixed
     */
    public function toHash( SPIValue $value )
    {
        if ( $this->isEmptyValue( $value ) )
        {
            return null;
        }
        return array(
            'url' => $value->url,
            'contents' => $value->contents
        );
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * @param mixed $hash
     *
     * @return \SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify\Value $value
     */
    public function fromHash( $hash )
    {
        if ( $hash === null )
        {
            return $this->getEmptyValue();
        }
        return new Value( $hash );
    }
}