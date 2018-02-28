<?php
/**
 * Created by PhpStorm.
 * User: ez
 * Date: 14/05/17
 * Time: 4:44 PM
 */

namespace SpotifyFieldTypeBundle\eZ\Publish\FieldType\Spotify;


use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class LegacyConverter implements Converter
{

    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->data = json_decode( $value->dataText, true );
        $fieldValue->sortKey = $value->sortKeyString;
    }
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = json_encode( $value->data );
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        // TODO: Implement toStorageFieldDefinition() method.
    }


    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        // TODO: Implement toFieldDefinition() method.
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * If the indexing is not supported, this method must return false.
     *
     * @return string|\eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\false
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }
}