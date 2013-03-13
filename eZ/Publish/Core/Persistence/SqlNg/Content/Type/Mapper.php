<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content\Type;

use eZ\Publish\SPI\Persistence;

/**
 * Mapper for Content Type Handler.
 *
 * Performs mapping of Type objects.
 */
class Mapper
{
    /**
     * Creates a Group from its create struct.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Group\CreateStruct $struct
     *
     * @todo $description is not supported by database, yet
     *
     * @return Group
     */
    public function createGroupFromCreateStruct( Persistence\Content\Type\Group\CreateStruct $struct )
    {
        $group = new Persistence\Content\Type\Group();

        $group->name = $struct->name;
        $group->description = $struct->description;
        $group->identifier = $struct->identifier;
        $group->created = $struct->created;
        $group->modified = $struct->modified;
        $group->creatorId = $struct->creatorId;
        $group->modifierId = $struct->modifierId;

        return $group;
    }

    /**
     * Extracts Group objects from the given $rows.
     *
     * @param array $rows
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Group[]
     */
    public function extractGroupsFromRows( array $rows )
    {
        $groups = array();

        foreach ( $rows as $row )
        {
            $group = new Persistence\Content\Type\Group();
            $group->id = (int)$row['id'];
            $group->created = (int)$row['created'];
            $group->creatorId = (int)$row['creator_id'];
            $group->modified = (int)$row['modified'];
            $group->modifierId = (int)$row['modifier_id'];
            $group->identifier = $row['identifier'];
            $group->name = json_decode( $row['name_list'], true );
            $group->description = json_decode( $row['description_list'], true );

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Extracts types and related data from the given $rows.
     *
     * @param array $rows
     *
     * @return array(Type)
     */
    public function extractTypesFromRows( array $rows )
    {
        $types = array();
        $fields = array();

        foreach ( $rows as $row )
        {
            $typeId = (int)$row['ezcontenttype_id'];
            if ( !isset( $types[$typeId] ) )
            {
                $types[$typeId] = $this->extractTypeFromRow( $row );
            }

            $fieldId = (int)$row['ezcontenttype_field_id'];
            if ( !isset( $fields[$fieldId] ) )
            {
                $types[$typeId]->fieldDefinitions[] = $fields[$fieldId] = $this->extractFieldFromRow( $row );
            }

            $groupId = (int)$row['ezcontenttype_group_rel_group_id'];
            if ( !in_array( $groupId, $types[$typeId]->groupIds ) )
            {
                $types[$typeId]->groupIds[] = $groupId;
            }
        }

        // Re-index $types to avoid people relying on ID keys
        return array_values( $types );
    }

    /**
     * Creates a Type from the data in $row.
     *
     * @param array $row
     *
     * @return Type
     */
    protected function extractTypeFromRow( array $row )
    {
        $type = new Persistence\Content\Type();

        $type->id = (int)$row['ezcontenttype_id'];
        $type->status = (int)$row['ezcontenttype_status'];
        $type->name = json_decode( $row['ezcontenttype_name_list'], true );
        $type->description = json_decode( $row['ezcontenttype_description_list'], true );
        $type->identifier = $row['ezcontenttype_identifier'];
        $type->created = (int)$row['ezcontenttype_created'];
        $type->creatorId = (int)$row['ezcontenttype_creator_id'];
        $type->modified = (int)$row['ezcontenttype_modified'];
        $type->modifierId = (int)$row['ezcontenttype_modifier_id'];
        $type->remoteId = $row['ezcontenttype_remote_id'];
        $type->nameSchema = $row['ezcontenttype_contentobject_name'];
        $type->isContainer = ( $row['ezcontenttype_is_container'] == 1 );
        $type->initialLanguageId = (int)$row['ezcontenttype_initial_language_id'];
        $type->defaultAlwaysAvailable = ( $row['ezcontenttype_always_available'] == 1 );
        $type->sortField = (int)$row['ezcontenttype_sort_field'];
        $type->sortOrder = (int)$row['ezcontenttype_sort_order'];

        $type->groupIds = array();
        $type->fieldDefinitions = array();

        return $type;
    }

    /**
     * Creates a FieldDefinition from the data in $row.
     *
     * @param array $row
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function extractFieldFromRow( array $row )
    {
        $field = new Persistence\Content\Type\FieldDefinition();

        $field->id = (int)$row['ezcontenttype_field_id'];
        $field->name = json_decode( $row['ezcontenttype_field_name_list'], true );
        $field->description = json_decode( $row['ezcontenttype_field_description_list'], true );
        $field->identifier = $row['ezcontenttype_field_identifier'];
        $field->fieldGroup = $row['ezcontenttype_field_field_group'];
        $field->fieldType = $row['ezcontenttype_field_type_string'];
        $field->isTranslatable = ( $row['ezcontenttype_field_can_translate'] == 1 );
        $field->isRequired = $row['ezcontenttype_field_is_required'] == 1;
        $field->isInfoCollector = $row['ezcontenttype_field_is_information_collector'] == 1;
        $field->isSearchable = (bool)$row['ezcontenttype_field_is_searchable'];
        $field->position = (int)$row['ezcontenttype_field_placement'];
        $field->defaultValue = new Persistence\Content\FieldValue( json_decode( $row['ezcontenttype_field_default_value'], true ) );
        $field->fieldTypeConstraints = new Persistence\Content\FieldTypeConstraints( json_decode( $row['ezcontenttype_field_constraints'], true ) );

        return $field;
    }

    /**
     * Maps properties from $struct to $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct $createStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    public function createTypeFromCreateStruct( Persistence\Content\Type\CreateStruct $createStruct )
    {
        $type = new Persistence\Content\Type();

        $type->name = $createStruct->name;
        $type->status = $createStruct->status;
        $type->description = $createStruct->description;
        $type->identifier = $createStruct->identifier;
        $type->created = $createStruct->created;
        $type->modified = $createStruct->modified;
        $type->creatorId = $createStruct->creatorId;
        $type->modifierId = $createStruct->modifierId;
        $type->remoteId = $createStruct->remoteId;
        $type->urlAliasSchema = $createStruct->urlAliasSchema;
        $type->nameSchema = $createStruct->nameSchema;
        $type->isContainer = $createStruct->isContainer;
        $type->initialLanguageId = $createStruct->initialLanguageId;
        $type->groupIds = $createStruct->groupIds;
        $type->fieldDefinitions = $createStruct->fieldDefinitions;
        $type->defaultAlwaysAvailable = $createStruct->defaultAlwaysAvailable;
        $type->sortField = $createStruct->sortField;
        $type->sortOrder = $createStruct->sortOrder;

        return $type;
    }

    /**
     * Creates a create struct from an existing $type.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type $type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\CreateStruct
     */
    public function createCreateStructFromType( Persistence\Content\Type $type )
    {
        $createStruct = new Persistence\Content\Type\CreateStruct();

        $createStruct->name = $type->name;
        $createStruct->status = $type->status;
        $createStruct->description = $type->description;
        $createStruct->identifier = $type->identifier;
        $createStruct->created = $type->created;
        $createStruct->modified = $type->modified;
        $createStruct->creatorId = $type->creatorId;
        $createStruct->modifierId = $type->modifierId;
        $createStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        $createStruct->urlAliasSchema = $type->urlAliasSchema;
        $createStruct->nameSchema = $type->nameSchema;
        $createStruct->isContainer = $type->isContainer;
        $createStruct->initialLanguageId = $type->initialLanguageId;
        $createStruct->groupIds = $type->groupIds;
        $createStruct->fieldDefinitions = $type->fieldDefinitions;
        $createStruct->defaultAlwaysAvailable = $type->defaultAlwaysAvailable;
        $createStruct->sortField = $type->sortField;
        $createStruct->sortOrder = $type->sortOrder;

        return $createStruct;
    }
}
