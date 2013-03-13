<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content;

use eZ\Publish\SPI\Persistence;

/**
 * Mapper for Content Handler.
 *
 * Performs mapping of Content objects.
 */
class Mapper
{
    /**
     * Caching language handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Creates a new mapper.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct( Persistence\Content\Language\Handler $languageHandler )
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Creates a Content from the given $struct and $currentVersionNo
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $currentVersionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    private function createContentInfoFromCreateStruct( Persistence\Content\CreateStruct $struct, $currentVersionNo = 1 )
    {
        $contentInfo = new Persistence\Content\ContentInfo;

        $contentInfo->id = null;
        $contentInfo->contentTypeId = $struct->typeId;
        $contentInfo->sectionId = $struct->sectionId;
        $contentInfo->ownerId = $struct->ownerId;
        $contentInfo->alwaysAvailable = $struct->alwaysAvailable;
        $contentInfo->remoteId = $struct->remoteId;
        $contentInfo->mainLanguageCode = $this->languageHandler->load( $struct->initialLanguageId )->languageCode;
        $contentInfo->name = isset( $struct->name[$contentInfo->mainLanguageCode] )
            ? $struct->name[$contentInfo->mainLanguageCode]
            : "";
        // For drafts published and modified timestamps should be 0
        $contentInfo->publicationDate = 0;
        $contentInfo->modificationDate = 0;
        $contentInfo->currentVersionNo = $currentVersionNo;
        $contentInfo->isPublished = false;

        return $contentInfo;
    }

    /**
     * Creates a new version for the given $struct and $versionNo
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $struct
     * @param mixed $versionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function createVersionInfoFromCreateStruct( Persistence\Content\CreateStruct $struct, $versionNo )
    {
        $versionInfo = new Persistence\Content\VersionInfo();

        $versionInfo->id = null;
        $versionInfo->contentInfo = $this->createContentInfoFromCreateStruct( $struct, $versionNo );
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $struct->ownerId;
        $versionInfo->status = Persistence\Content\VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $this->languageHandler->load( $struct->initialLanguageId )->languageCode;
        $versionInfo->creationDate = $struct->modified;
        $versionInfo->modificationDate = $struct->modified;
        $versionInfo->names = $struct->name;

        $languageIds = array();
        foreach ( $struct->fields as $field )
        {
            if ( !isset( $languageIds[$field->languageCode] ) )
            {
                $languageIds[$field->languageCode] =
                    $this->languageHandler->loadByLanguageCode( $field->languageCode )->id;
            }
        }
        $versionInfo->languageIds = array_values( $languageIds );

        return $versionInfo;
    }

    /**
     * Creates a new version for the given $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     * @param mixed $versionNo
     * @param mixed $userId
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    public function createVersionInfoForContent( Persistence\Content $content, $versionNo, $userId )
    {
        $versionInfo = new Persistence\Content\VersionInfo();

        $versionInfo->contentInfo = $content->versionInfo->contentInfo;
        $versionInfo->versionNo = $versionNo;
        $versionInfo->creatorId = $userId;
        $versionInfo->status = Persistence\Content\VersionInfo::STATUS_DRAFT;
        $versionInfo->initialLanguageCode = $content->versionInfo->initialLanguageCode;
        $versionInfo->creationDate = time();
        $versionInfo->modificationDate = $versionInfo->creationDate;
        $versionInfo->names = is_object( $content->versionInfo ) ? $content->versionInfo->names : array();
        $versionInfo->languageIds = $content->versionInfo->languageIds;

        return $versionInfo;
    }

    /**
     * Converts value of $field to storage value
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return \eZ\Publish\Core\Persistence\SqlNg\Content\StorageFieldValue
     */
    public function convertToStorageValue( Field $field )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Extracts Content objects (and nested) from database result $rows
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content[]
     */
    public function extractContentFromRows( array $rows )
    {
        $contentInfos = array();
        $versionInfos = array();

        $fields = array();

        foreach ( $rows as $row )
        {
            $contentId = (int)$row['ezcontent_id'];
            if ( !isset( $contentInfos[$contentId] ) )
            {
                $contentInfos[$contentId] = $this->extractContentInfoFromRow( $row, 'ezcontent_' );
            }
            if ( !isset( $versionInfos[$contentId] ) )
            {
                $versionInfos[$contentId] = array();
            }

            $versionId = (int)$row['ezcontent_version_id'];
            if ( !isset( $versionInfos[$contentId][$versionId] ) )
            {
                $versionInfos[$contentId][$versionId] = $this->extractVersionInfoFromRow( $row );
            }

            // Filter out fields, which are not defined in content type any
            // more
            $fields[$contentId][$versionId] = $this->readFields(
                json_decode( $row['ezcontent_version_fields'], true )
            );
        }

        $results = array();
        foreach ( $contentInfos as $contentId => $contentInfo )
        {
            foreach ( $versionInfos[$contentId] as $versionId => $versionInfo )
            {
                $content = new Persistence\Content();
                $content->versionInfo = $versionInfo;
                $content->versionInfo->contentInfo = $contentInfo;
                $content->fields = array_values( $fields[$contentId][$versionId] );
                $results[] = $content;
            }
        }
        return $results;
    }

    /**
     * Read fields
     *
     * @param array $fields
     * @return array
     */
    protected function readFields( array $fields )
    {
        return array_map(
            function ( $data )
            {
                $data['value'] = new Persistence\Content\FieldValue( $data['value'] );
                return new Persistence\Content\Field( $data );
            },
            $fields
        );
    }

    /**
     * Extracts a ContentInfo object from $row
     *
     * @param array $row
     * @param string $prefix Prefix for row keys, which are initially mapped by ezcontent fields
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function extractContentInfoFromRow( array $row, $prefix = '' )
    {
        $contentInfo = new Persistence\Content\ContentInfo();
        $contentInfo->id = (int)$row["{$prefix}id"];
        $contentInfo->contentTypeId = (int)$row["{$prefix}contenttype_id"];
        $contentInfo->sectionId = (int)$row["{$prefix}section_id"];
        $contentInfo->currentVersionNo = (int)$row["{$prefix}current_version_no"];
        $contentInfo->isPublished = (bool)( $row["{$prefix}status"] == Persistence\Content\ContentInfo::STATUS_PUBLISHED );
        $contentInfo->ownerId = (int)$row["{$prefix}owner_id"];
        $contentInfo->publicationDate = (int)$row["{$prefix}published"];
        $contentInfo->modificationDate = (int)$row["{$prefix}modified"];
        $contentInfo->mainLanguageCode = $this->languageHandler->load( $row["{$prefix}initial_language_id"] )->languageCode;
        $contentInfo->alwaysAvailable = (bool)$row["{$prefix}always_available"];
        $contentInfo->remoteId = $row["{$prefix}remote_id"];
        $contentInfo->mainLocationId = $row["ezcontent_location_main_id"] ?: $row['ezcontent_location_id'];

        $names = json_decode( $row["{$prefix}name_list"], true );
        $contentInfo->name = $names[$contentInfo->mainLanguageCode];

        return $contentInfo;
    }

    /**
     * Extracts a VersionInfo object from $row.
     *
     * This method will return VersionInfo with incomplete data. It is intended to be used only by
     * {@link self::extractContentFromRows} where missing data will be filled in.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    private function extractVersionInfoFromRow( array $row )
    {
        $versionInfo = new Persistence\Content\VersionInfo();
        $versionInfo->id = (int)$row["ezcontent_version_id"];
        $versionInfo->contentInfo = null;
        $versionInfo->versionNo = (int)$row["ezcontent_version_version_no"];
        $versionInfo->creatorId = (int)$row["ezcontent_version_creator_id"];
        $versionInfo->creationDate = (int)$row["ezcontent_version_created"];
        $versionInfo->modificationDate = (int)$row["ezcontent_version_modified"];
        $versionInfo->initialLanguageCode = $this->languageHandler->load( $row["ezcontent_version_initial_language_id"] )->languageCode;
        $versionInfo->status = (int)$row["ezcontent_version_status"];
        $versionInfo->names = json_decode( $row["ezcontent_name_list"], true );
        $versionInfo->languageIds = array(
            $row["ezcontent_version_initial_language_id"],
        );

        return $versionInfo;
    }

    /**
     * Extracts a VersionInfo object from $row
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\VersionInfo[]
     */
    public function extractVersionInfoListFromRows( array $rows )
    {
        $versionInfoList = array();
        foreach ( $rows as $row )
        {
            $versionInfo = $this->extractVersionInfoFromRow( $row );
            $versionInfo->contentInfo = $this->extractContentInfoFromRow( $row, 'ezcontent_' );
            $versionInfoList[$versionInfo->id] = $versionInfo;
        }

        return array_values( $versionInfoList );
    }

    /**
     * @param int $languageMask
     *
     * @return array
     */
    public function extractLanguageIdsFromMask( $languageMask )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Creates CreateStruct from $content
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    public function createCreateStructFromContent( Persistence\Content $content )
    {
        $struct = new Persistence\Content\CreateStruct();
        $struct->name = $content->versionInfo->names;
        $struct->typeId = $content->versionInfo->contentInfo->contentTypeId;
        $struct->sectionId = $content->versionInfo->contentInfo->sectionId;
        $struct->ownerId = $content->versionInfo->contentInfo->ownerId;
        $struct->locations = array();
        $struct->alwaysAvailable = $content->versionInfo->contentInfo->alwaysAvailable;
        $struct->remoteId = md5( uniqid( get_class( $this ), true ) );
        $struct->initialLanguageId = $this->languageHandler->loadByLanguageCode( $content->versionInfo->initialLanguageCode )->id;
        $struct->modified = time();

        foreach ( $content->fields as $field )
        {
            $newField = clone $field;
            $newField->id = null;
            $struct->fields[] = $newField;
        }

        return $struct;
    }

    /**
     * Extracts relation objects from $rows
     */
    public function extractRelationsFromRows( array $rows )
    {
        $relations = array();

        foreach ( $rows as $row )
        {
            $id = (int)$row['ezcontent_relation_id'];
            if ( !isset( $relations[$id] ) )
            {
                $relations[$id] = $this->extractRelationFromRow( $row );
            }
        }

        return array_values( $relations );
    }

    /**
     * Extracts a Relation object from a $row
     *
     * @param array $row Associative array representing a relation
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    protected function extractRelationFromRow( array $row )
    {
        $relation = new Persistence\Content\Relation();
        $relation->id = (int)$row['ezcontent_relation_id'];
        $relation->sourceContentId = (int)$row['ezcontent_relation_from_content_id'];
        $relation->sourceContentVersionNo = (int)$row['ezcontent_relation_from_content_version_no'];
        $relation->destinationContentId = (int)$row['ezcontent_relation_to_content_id'];
        $relation->type = (int)$row['ezcontent_relation_relation_type'];

        $contentClassAttributeId = (int)$row['ezcontent_relation_contenttype_field_id'];
        if ( $contentClassAttributeId > 0 )
        {
            $relation->sourceFieldDefinitionId = $contentClassAttributeId;
        }

        return $relation;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $struct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function createRelationFromCreateStruct( Persistence\Content\Relation\CreateStruct $struct )
    {
        return new Persistence\Content\Relation( (array) $struct );
    }
}
