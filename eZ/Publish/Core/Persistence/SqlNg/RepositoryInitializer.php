<?php
/**
 * File containing the Handler interface
 *
 * @copyright Copyright (C) 1999-2012 \eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag
 */

namespace eZ\Publish\Core\Persistence\SqlNg;

use eZ\Publish\SPI\Persistence;

/**
 * The repository handler for the legacy storage engine
 */
class RepositoryInitializer
{
    /**
     * Initialize base repository
     *
     * @TODO: This one requires serious refactoring
     *
     * @param Handler $this->handler
     * @return void
     */
    public function initialize( Handler $handler )
    {
        $this->handler = $handler;

        $importUser = $this->createImportUser();
        $language = $this->createLanguage( 'eng-US' );
        $language = $this->createLanguage( 'eng-GB' );

        $standardSection = $this->handler->sectionHandler()->create( 'Standard', 'standard' );
        $usersSection = $this->handler->sectionHandler()->create( 'Users', 'users' );

        // Content Type Groups
        $contentContentTypeGroup = $this->createTypeGroup($importUser, 'Content');
        $usersContentTypeGroup = $this->createTypeGroup($importUser, 'Users');

        // Content Types
        $landingPageType = $this->createLandingPageType( $importUser, $contentContentTypeGroup );
        $userGroupType = $this->createUserGroupType( $importUser, $usersContentTypeGroup );
        $userType = $this->createUserType( $importUser, $usersContentTypeGroup );
        $contentType = $this->createContentType( $importUser, $contentContentTypeGroup );

        // Root location
        $rootLocationCreate = new Persistence\Content\Location\CreateStruct(
            array(
                'remoteId' => '629709ba256fe317c3ddcee35453a96a',
                'mainLocationId' => '1',
                'sortField' => 1,
                'sortOrder' => 1,
            )
        );
        $rootLocation = $this->handler->locationHandler()->create( $rootLocationCreate );

        $role = $this->createRole();

        $userGroup = $this->createRootUserGroup( $importUser, $userGroupType, $usersSection, $rootLocation, $language );
        $userRoot = $userGroup->versionInfo->contentInfo->mainLocationId;
        $home = $this->createHome( $importUser, $landingPageType, $standardSection, $rootLocation, $language );
        $contentRoot = $home->versionInfo->contentInfo->mainLocationId;

        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 1!' ); // 3
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 2!' ); // 4
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 3!' ); // 5
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 4!' ); // 6
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 5!' ); // 7
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 6!' ); // 8
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 7!' ); // 9

        $anonymousUser = $this->createUser( $importUser, $userType, $usersSection, $userRoot, $role, $language, 'anonymous', '4e6f6184135228ccd45f8233d72a0363' );

        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 8!' ); // 11
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 9!' ); // 12
        $this->createContent( $importUser, $contentType, $standardSection, $contentRoot, $language, 'Hello World 10!' ); // 13

        $adminUser = $this->createUser( $importUser, $userType, $usersSection, $userRoot, $role, $language, 'admin', 'c78e3b0f3d9244ed8c6d1c29464bdff9' );
    }

    protected function createImportUser()
    {
        return $this->handler->userHandler()->create(
            new Persistence\User(
                array(
                    'id' => 1,
                    'login' => 'import',
                    'email' => 'nospam@ez.no',
                    'passwordHash' => '*',
                    'hashAlgorithm' => '2',
                )
            )
        );
    }

    protected function createLanguage( $language )
    {
        return $this->handler->contentLanguageHandler()->create(
            new Persistence\Content\Language\CreateStruct(
                array(
                    'languageCode' => $language,
                    'name' => 'English',
                    'isEnabled' => true,
                )
            )
        );
    }

    protected function createTypeGroup($user, $name)
    {
        return $this->handler->contentTypeHandler()->createGroup(
            new Persistence\Content\Type\Group\CreateStruct(
                array(
                    'name' => array(),
                    'description' => array(),
                    'identifier' => $name,
                    'created' => time(),
                    'modified' => time(),
                    'creatorId' => $user->id,
                    'modifierId' => $user->id,
                )
            )
        );
    }

    protected function createUserGroupType( $importUser, $usersContentTypeGroup )
    {
        $userGroupTypeCreate = new Persistence\Content\Type\CreateStruct(
            array(
                'name' => array(
                    'eng-GB' => 'User group',
                ),
                'status' => 0,
                'description' => array(),
                'identifier' => 'user_group',

                'created' => time(),
                'modified' => time(),
                'creatorId' => $importUser->id,
                'modifierId' => $importUser->id,

                'remoteId' => '25b4268cdcd01921b808a0d854b877ef',

                'urlAliasSchema' => '',
                'nameSchema' => '<name>',
                'isContainer' => true,
                'initialLanguageId' => 2,

                'sortField' => 1,
                'sortOrder' => 1,

                'groupIds' => array( $usersContentTypeGroup->id ),

                'fieldDefinitions' => array(
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'Name',
                            ),
                            'description' => array(),
                            'identifier' => 'name',
                            'fieldGroup' => '',
                            'position' => 1,
                            'fieldType' => 'ezstring',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(
                                        'StringLengthValidator' => array(
                                            'maxStringLength' => 255,
                                            'minStringLength' => 0,
                                        ),
                                    ),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'Description',
                            ),
                            'description' => array(),
                            'identifier' => 'description',
                            'fieldGroup' => '',
                            'position' => 2,
                            'fieldType' => 'ezstring',
                            'isTranslatable' => true,
                            'isRequired' => false,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(
                                        'StringLengthValidator' => array(
                                            'maxStringLength' => 255,
                                            'minStringLength' => 0,
                                        ),
                                    ),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                ),
                'defaultAlwaysAvailable' => true,
            )
        );

        return $this->handler->contentTypeHandler()->create( $userGroupTypeCreate );
    }

    protected function createUserType( $importUser, $usersContentTypeGroup )
    {
        $userGroupTypeCreate = new Persistence\Content\Type\CreateStruct(
            array(
                'name' => array(
                    'eng-GB' => 'User',
                ),
                'status' => 0,
                'description' => array(),
                'identifier' => 'user',

                'created' => time(),
                'modified' => time(),
                'creatorId' => $importUser->id,
                'modifierId' => $importUser->id,

                'remoteId' => 'user-8432795823475923',

                'urlAliasSchema' => '',
                'nameSchema' => '<name>',
                'isContainer' => true,
                'initialLanguageId' => 2,

                'sortField' => 1,
                'sortOrder' => 1,

                'groupIds' => array( $usersContentTypeGroup->id ),

                'fieldDefinitions' => array(
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'Name',
                            ),
                            'description' => array(),
                            'identifier' => 'name',
                            'fieldGroup' => '',
                            'position' => 1,
                            'fieldType' => 'ezstring',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(
                                        'StringLengthValidator' => array(
                                            'maxStringLength' => 255,
                                            'minStringLength' => 0,
                                        ),
                                    ),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                ),
                'defaultAlwaysAvailable' => true,
            )
        );

        return $this->handler->contentTypeHandler()->create( $userGroupTypeCreate );
    }

    protected function createContentType( $importUser, $contentTypeGroup )
    {
        $contentTypeCreate = new Persistence\Content\Type\CreateStruct(
            array(
                'name' => array(
                    'eng-GB' => 'Folder',
                ),
                'status' => 0,
                'description' => array(),
                'identifier' => 'folder',

                'created' => time(),
                'modified' => time(),
                'creatorId' => $importUser->id,
                'modifierId' => $importUser->id,

                'remoteId' => 'folder-8432795823475923',

                'urlAliasSchema' => '',
                'nameSchema' => '<title>',
                'isContainer' => true,
                'initialLanguageId' => 2,

                'sortField' => 1,
                'sortOrder' => 1,

                'groupIds' => array( $contentTypeGroup->id ),

                'fieldDefinitions' => array(
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'title',
                            ),
                            'description' => array(),
                            'identifier' => 'title',
                            'fieldGroup' => '',
                            'position' => 1,
                            'fieldType' => 'ezstring',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(
                                        'StringLengthValidator' => array(
                                            'maxStringLength' => 255,
                                            'minStringLength' => 0,
                                        ),
                                    ),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'text',
                            ),
                            'description' => array(),
                            'identifier' => 'text',
                            'fieldGroup' => '',
                            'position' => 2,
                            'fieldType' => 'ezxmltext',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                ),
                'defaultAlwaysAvailable' => true,
            )
        );

        return $this->handler->contentTypeHandler()->create( $contentTypeCreate );
    }

    protected function createLandingPageType( $importUser, $contentContentTypeGroup )
    {
        $landingPageTypeCreate = new Persistence\Content\Type\CreateStruct(
            array(
                'name' => array(
                    'eng-GB' => 'Landing Page',
                ),
                'status' => 0,
                'description' => array(),
                'identifier' => 'landing_page',

                'created' => time(),
                'modified' => time(),
                'creatorId' => $importUser->id,
                'modifierId' => $importUser->id,

                'remoteId' => 'e36c458e3e4a81298a0945f53a2c81f4',

                'urlAliasSchema' => '',
                'nameSchema' => '<name>',
                'isContainer' => true,
                'initialLanguageId' => 2,

                'sortField' => 1,
                'sortOrder' => 1,

                'groupIds' => array( $contentContentTypeGroup->id ),

                'fieldDefinitions' => array(
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'Name',
                            ),
                            'description' => array(
                                'eng-GB' => '',
                            ),
                            'identifier' => 'name',
                            'fieldGroup' => '',
                            'position' => 1,
                            'fieldType' => 'ezstring',
                            'isTranslatable' => true,
                            'isRequired' => true,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => array(
                                        'StringLengthValidator' => array(
                                            'maxStringLength' => 0,
                                            'minStringLength' => 0,
                                        ),
                                    ),
                                    'fieldSettings' => NULL,
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => true,
                        )
                    ),
                    new Persistence\Content\Type\FieldDefinition(
                        array(
                            'name' => array(
                                'eng-GB' => 'Layout',
                            ),
                            'description' => array(
                                'eng-GB' => '',
                            ),
                            'identifier' => 'page',
                            'fieldGroup' => '',
                            'position' => 2,
                            'fieldType' => 'ezpage',
                            'isTranslatable' => true,
                            'isRequired' => false,
                            'isInfoCollector' => false,
                            'fieldTypeConstraints' => new Persistence\Content\FieldTypeConstraints(
                                array(
                                    'validators' => NULL,
                                    'fieldSettings' => new \eZ\Publish\Core\FieldType\FieldSettings(
                                        array(
                                            'defaultLayout' => '',
                                        )
                                    )
                                )
                            ),
                            'defaultValue' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => NULL,
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'isSearchable' => false,
                        )
                    )
                ),
                'defaultAlwaysAvailable' => false,
            )
        );

        return $this->handler->contentTypeHandler()->create( $landingPageTypeCreate );
    }

    protected function createRootUserGroup( $importUser, $userGroupType, $usersSection, $rootLocation, $language )
    {
        $usersContentCreate = new Persistence\Content\CreateStruct(
            array(
                'name' => 'Users',
                'typeId' => $userGroupType->id,
                'sectionId' => $usersSection->id,
                'ownerId' => $importUser->id,
                'modified' => time(),

                'locations' => array(
                    $userRootLocation = new Persistence\Content\Location\CreateStruct(
                        array(
                            'priority' => 0,
                            'remoteId' => '3f6d92f8044aed134f32153517850f5a',
                            'parentId' => $rootLocation->id,
                            'pathIdentificationString' => 'users',
                            'sortField' => 1,
                            'sortOrder' => 1,
                        )
                    )
                ),

                'fields' => array(
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $userGroupType, 1 )->id,
                            'type' => 'ezstring',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => 'Main group',
                                    'externalData' => NULL,
                                    'sortKey' => '',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $userGroupType, 2 )->id,
                            'type' => 'ezstring',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => 'Users',
                                    'externalData' => NULL,
                                    'sortKey' => '',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                ),

                'alwaysAvailable' => true,
                'remoteId' => 'f5c88a2209584891056f987fd965b0ba',

                'initialLanguageId' => $language->id,

                'name' => array(
                    'eng-GB' => 'Users',
                ),
            )
        );

        $userContent = $this->handler->contentHandler()->create( $usersContentCreate );
        return $this->handler->contentHandler()->publish(
            $userContent->versionInfo->id,
            $userContent->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct()
        );
    }

    protected function createHome( $importUser, $landingPageType, $standardSection, $rootLocation, $language )
    {
        $homeContentCreate = new Persistence\Content\CreateStruct(
            array(
                'name' => 'Home',
                'typeId' => $landingPageType->id,
                'sectionId' => $standardSection->id,
                'ownerId' => $importUser->id,
                'modified' => time(),

                'locations' => array(
                    $homeLocation = new Persistence\Content\Location\CreateStruct(
                        array(
                            'priority' => '0',
                            'remoteId' => 'f3e90596361e31d496d4026eb624c983',
                            'parentId' => $rootLocation->id,
                            'pathIdentificationString' => '',
                            'sortField' => 8,
                            'sortOrder' => 1,
                        )
                    )
                ),

                'alwaysAvailable' => 1,
                'remoteId' => '8a9c9c761004866fb458d89910f52bee',

                'initialLanguageId' => $language->id,
                'name' => array(
                    'eng-GB' => 'Home',
                ),
                'fields' => array(
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $landingPageType, 1 )->id,
                            'type' => 'ezstring',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => 'Home',
                                    'externalData' => NULL,
                                    'sortKey' => 'home',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $landingPageType, 2 )->id,
                            'type' => 'ezpage',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => new \eZ\Publish\Core\FieldType\Page\Parts\Page(
                                        new \eZ\Publish\Core\FieldType\Page\Service()
                                    ),
                                    'externalData' => NULL,
                                    'sortKey' => NULL,
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                ),
            )
        );

        $homeContent = $this->handler->contentHandler()->create( $homeContentCreate );
        return $this->handler->contentHandler()->publish(
            $homeContent->versionInfo->id,
            $homeContent->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct()
        );
    }

    protected function createUser( $importUser, $userGroupType, $usersSection, $rootLocation, $role, $language, $name, $passwordHash )
    {
        $userContentCreate = new Persistence\Content\CreateStruct(
            array(
                'name' => 'Users',
                'typeId' => $userGroupType->id,
                'sectionId' => $usersSection->id,
                'ownerId' => $importUser->id,
                'modified' => time(),

                'locations' => array(
                    $userRootLocation = new Persistence\Content\Location\CreateStruct(
                        array(
                            'priority' => 0,
                            'remoteId' => $name,
                            'parentId' => $rootLocation,
                            'sortField' => 1,
                            'sortOrder' => 1,
                        )
                    )
                ),

                'fields' => array(
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $userGroupType, 1 )->id,
                            'type' => 'ezstring',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => $name,
                                    'externalData' => NULL,
                                    'sortKey' => '',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                ),

                'alwaysAvailable' => true,
                'remoteId' => $name,

                'initialLanguageId' => $language->id,

                'name' => array(
                    'eng-GB' => $name,
                ),
            )
        );

        $userContent = $this->handler->contentHandler()->create( $userContentCreate );
        $userContent = $this->handler->contentHandler()->publish(
            $userContent->versionInfo->id,
            $userContent->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct()
        );

        $user = $this->handler->userHandler()->create(
            new Persistence\User(
                array(
                    'id' => $userContent->versionInfo->contentInfo->id,
                    'login' => $name,
                    'email' => 'nospam@ez.no',
                    'passwordHash' => $passwordHash,
                    'hashAlgorithm' => '2',
                )
            )
        );

        $this->handler->userHandler()->assignRole(
            $user->id,
            $role->id
        );
    }

    protected function createRole()
    {
        $role = new Persistence\User\Role();
        $role->identifier = 'Allow everything';
        $role->policies[] = $policy = new Persistence\User\Policy();

        $policy->module = '*';
        $policy->function = '*';
        $policy->limitations = '*';

        return $this->handler->userHandler()->createRole( $role );
    }

    protected function createContent( $importUser, $contentType, $section, $rootLocation, $language, $content )
    {
        $contentCreate = new Persistence\Content\CreateStruct(
            array(
                'name' => 'Users',
                'typeId' => $contentType->id,
                'sectionId' => $section->id,
                'ownerId' => $importUser->id,
                'modified' => time(),

                'locations' => array(
                    $userRootLocation = new Persistence\Content\Location\CreateStruct(
                        array(
                            'priority' => 0,
                            'remoteId' => md5( microtime() ),
                            'parentId' => $rootLocation,
                        )
                    )
                ),

                'fields' => array(
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $contentType, 1 )->id,
                            'type' => 'ezstring',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => $content,
                                    'externalData' => NULL,
                                    'sortKey' => '',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                    new Persistence\Content\Field(
                        array(
                            'fieldDefinitionId' => $this->getFieldDefinition( $contentType, 2 )->id,
                            'type' => 'ezxmltext',
                            'value' => new Persistence\Content\FieldValue(
                                array(
                                    'data' => '<?xml version="1.0"?>' . "\n" .
                                    '<section><paragraph>' . $content . '</paragraph></section>',
                                    'externalData' => NULL,
                                    'sortKey' => '',
                                )
                            ),
                            'languageCode' => 'eng-GB',
                        )
                    ),
                ),

                'alwaysAvailable' => true,
                'remoteId' => md5( microtime() ),

                'initialLanguageId' => $language->id,

                'name' => array(
                    'eng-GB' => $content,
                ),
            )
        );

        $content = $this->handler->contentHandler()->create( $contentCreate );
        return $this->handler->contentHandler()->publish(
            $content->versionInfo->id,
            $content->versionInfo->versionNo,
            new Persistence\Content\MetadataUpdateStruct()
        );
    }

    /**
     * Get field definition at position
     *
     * @param mixed $type
     * @param mixed $position
     * @return void
     */
    protected function getFieldDefinition( $type, $position )
    {
        foreach( $type->fieldDefinitions as $fieldDefinition )
        {
            if ( $fieldDefinition->position == $position )
            {
                return $fieldDefinition;
            }
        }
        throw new \RuntimeException( "Field definition with position $position not found." );
    }

    /**
     * Initilialize database schema
     *
     * @param EzcDbHandler $dbHandler
     * @return void
     */
    public function initializeSchema( $dbHandler )
    {
        foreach ( $this->getSchemaStatements() as $statement )
        {
            $dbHandler->exec( $statement );
        }
    }

    /**
     * Returns the database schema as an array of SQL statements
     *
     * @return string[]
     */
    protected function getSchemaStatements()
    {

        return array_filter(
            preg_split(
                '(;\\s*$)m',
                file_get_contents(
                    __DIR__ . '/schema/schema.mysql.sql'
                )
            )
        );
    }
}
