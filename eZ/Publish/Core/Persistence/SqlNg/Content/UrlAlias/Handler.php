<?php
/**
 * File containing the UrlAlias Handler
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias;

use eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler as UrlAliasHandlerInterface;
use eZ\Publish\Core\Persistence\SqlNg\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\SqlNg\Content\Search\TransformationProcessor;
use eZ\Publish\Core\Persistence\SqlNg\Content\Location\Gateway as LocationGateway;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\ForbiddenException;
use RuntimeException;

/**
 * The UrlAlias Handler provides nice urls management.
 *
 * Its methods operate on a representation of the url alias data structure held
 * inside a storage engine.
 */
class Handler implements UrlAliasHandlerInterface
{
    protected $configuration = array();

    /**
     * UrlAlias Gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Gateway
     */
    protected $gateway;

    /**
     * Gateway for handling location data
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * UrlAlias Mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Mapper
     */
    protected $mapper;

    /**
     * Caching language handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Language\CachingHandler
     */
    protected $languageHandler;

    /**
     * Transformation processor to normalize URL strings
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Search\TransformationProcessor
     */
    protected $transformationProcessor;

    /**
     * Creates a new UrlAlias Handler
     *
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Gateway $gateway
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Mapper $mapper
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Gateway $locationGateway
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\Language\Handler $languageHandler
     * @param \eZ\Publish\Core\Persistence\SqlNg\Content\Search\TransformationProcessor $transformationProcessor
     * @param array $configuration
     */
    public function __construct(
    /*
        Gateway $gateway,
        Mapper $mapper,
        LocationGateway $locationGateway,
        LanguageHandler $languageHandler,
        TransformationProcessor $transformationProcessor,
        array $configuration = array() */
    )
    {
        // throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * This method creates or updates an urlalias from a new or changed content name in a language
     * (if published). It also can be used to create an alias for a new location of content.
     * On update the old alias is linked to the new one (i.e. a history alias is generated).
     *
     * $alwaysAvailable controls whether the url alias is accessible in all
     * languages.
     *
     * @param mixed $locationId
     * @param mixed $parentLocationId
     * @param string $name the new name computed by the name schema or url alias schema
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     * @param boolean $isLanguageMain used only for legacy storage for updating ezcontentobject_tree.path_identification_string
     *
     * @return void
     */
    public function publishUrlAliasForLocation(
        $locationId,
        $parentLocationId,
        $name,
        $languageCode,
        $alwaysAvailable = false,
        $isLanguageMain = false
    )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Create a user chosen $alias pointing to $locationId in $languageCode.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @param mixed $locationId
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createCustomUrlAlias( $locationId, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Create a user chosen $alias pointing to a resource in $languageCode.
     * This method does not handle location resources - if a user enters a location target
     * the createCustomUrlAlias method has to be used.
     *
     * If $languageCode is null the $alias is created in the system's default
     * language. $alwaysAvailable makes the alias available in all languages.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException if the path already exists for the given language
     *
     * @param string $resource
     * @param string $path
     * @param boolean $forwarding
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function createGlobalUrlAlias( $resource, $path, $forwarding = false, $languageCode = null, $alwaysAvailable = false )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * List of user generated or autogenerated url entries, pointing to $locationId.
     *
     * @param mixed $locationId
     * @param boolean $custom if true the user generated aliases are listed otherwise the autogenerated
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listURLAliasesForLocation( $locationId, $custom = false )
    {
        return array();
    }

    /**
     * List global aliases.
     *
     * @param string|null $languageCode
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias[]
     */
    public function listGlobalURLAliases( $languageCode = null, $offset = 0, $limit = -1 )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Removes url aliases.
     *
     * Autogenerated aliases are not removed by this method.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $urlAliases
     *
     * @return boolean
     */
    public function removeURLAliases( array $urlAliases )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Looks up a url alias for the given url
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \RuntimeException
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @param string $url
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function lookup( $url )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads URL alias by given $id
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     *
     * @param string $id
     *
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias
     */
    public function loadUrlAlias( $id )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the change of the autogenerated aliases.
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return void
     */
    public function locationMoved( $locationId, $oldParentId, $newParentId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Notifies the underlying engine that a location has moved.
     *
     * This method triggers the creation of the autogenerated aliases for the copied locations
     *
     * @param mixed $locationId
     * @param mixed $oldParentId
     * @param mixed $newParentId
     *
     * @return void
     */
    public function locationCopied( $locationId, $newLocationId, $newParentId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Notifies the underlying engine that a location was deleted or moved to trash
     *
     * @param mixed $locationId
     */
    public function locationDeleted( $locationId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }
}
