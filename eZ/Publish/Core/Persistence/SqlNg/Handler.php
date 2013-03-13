<?php
/**
 * File containing the Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg;

use eZ\Publish\SPI\Persistence\Handler as HandlerInterface;

use \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;

/**
 * The repository handler for the legacy storage engine
 */
class Handler implements HandlerInterface
{
    /**
     * Content handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Handler
     */
    protected $contentHandler;

    /**
     * Content mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Mapper
     */
    protected $contentMapper;

    /**
     * Storage handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Search handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Search\Handler
     */
    protected $searchHandler;

    /**
     * Content type handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Content Type gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Type\Gateway
     */
    protected $contentTypeGateway;

    /**
     * Content Type update handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Type\Update\Handler
     */
    protected $typeUpdateHandler;

    /**
     * Location handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * Location gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Gateway
     */
    protected $locationGateway;

    /**
     * Location mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Mapper
     */
    protected $locationMapper;

    /**
     * ObjectState handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * ObjectState gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\ObjectState\Gateway
     */
    protected $objectStateGateway;

    /**
     * ObjectState mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\ObjectState\Mapper
     */
    protected $objectStateMapper;

    /**
     * User handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\User\Handler
     */
    protected $userHandler;

    /**
     * Section handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Trash handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Location\Trash\Handler
     */
    protected $trashHandler;

    /**
     * Content gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Gateway
     */
    protected $contentGateway;

    /**
     * Language handler
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Language cache
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Language\Cache
     */
    protected $languageCache;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * UrlAlias handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Handler
     */
    protected $urlAliasHandler;

    /**
     * UrlAlias gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Gateway
     */
    protected $urlAliasGateway;

    /**
     * UrlAlias mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlAlias\Mapper
     */
    protected $urlAliasMapper;

    /**
     * UrlWildcard handler
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlWildcard\Handler
     */
    protected $urlWildcardHandler;

    /**
     * UrlWildcard gateway
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlWildcard\Gateway
     */
    protected $urlWildcardGateway;

    /**
     * UrlWildcard mapper
     *
     * @var \eZ\Publish\Core\Persistence\SqlNg\Content\UrlWildcard\Mapper
     */
    protected $urlWildcardMapper;

    /**
     * @var \eZ\Publish\Core\Persistence\SqlNg\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Storage registry
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Creates a new repository handler.
     *
     * @param \eZ\Publish\Core\Persistence\SqlNg\EzcDbHandler $dbHandler The database handler
     */
    public function __construct(
        EzcDbHandler $dbHandler,
        Content\StorageRegistry $storageRegistry )
    {
        $this->dbHandler = $dbHandler;
        $this->storageRegistry = $storageRegistry;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    public function contentLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = new Content\Language\Handler(
                new Content\Language\Gateway\ExceptionConversion(
                    new Content\Language\Gateway\EzcDatabase( $this->dbHandler )
                ),
                new Content\Language\Mapper()
            );
        }
        return $this->languageHandler;
    }

    /**
     * @internal LocationHandler is injected into property to avoid circular dependency
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Content\Handler(
                $this->getContentGateway(),
                $this->getLocationGateway(),
                $this->getContentMapper(),
                $this->getFieldIdGenerator()
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns a content gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getContentGateway()
    {
        if ( !isset( $this->contentGateway ) )
        {
            $this->contentGateway = new Content\Gateway\ExceptionConversion(
                new Content\Gateway\EzcDatabase(
                    $this->dbHandler,
                    new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler ),
                    $this->contentLanguageHandler()
                )
            );
        }
        return $this->contentGateway;
    }

    /**
     * Returns a content mapper
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getContentMapper()
    {
        if ( !isset( $this->contentMapper ) )
        {
            $this->contentMapper = new Content\Mapper(
                $this->contentLanguageHandler()
            );
        }
        return $this->contentMapper;
    }

    /**
     * Returns a field ID generator
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldIdGenerator
     */
    protected function getFieldIdGenerator()
    {
        if ( !isset( $this->fieldIdGenerator ) )
        {
            $this->fieldIdGenerator = new Content\FieldIdGenerator\Random();
        }
        return $this->fieldIdGenerator;
    }

    /**
     * Returns a storage handler
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected function getStorageHandler()
    {
        if ( !isset( $this->storageHandler ) )
        {
            $this->storageHandler = new Content\StorageHandler(
                $this->storageRegistry,
                $this->getContext()
            );
        }
        return $this->storageHandler;
    }

    /**
     * Get context definition for external storage layers
     *
     * @return array
     */
    protected function getContext()
    {
        return array(
            'identifier' => 'SqlNgStorage',
            'connection' => $this->dbHandler,
        );
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new Content\Type\Handler(
                $this->getContentTypeGateway(),
                new Content\Type\Mapper()
            );
        }
        return $this->contentTypeHandler;
    }

    /**
     * Returns the content type gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getContentTypeGateway()
    {
        if ( !isset( $this->contentTypeGateway ) )
        {
            $this->contentTypeGateway = new Content\Type\Gateway\ExceptionConversion(
                new Content\Type\Gateway\EzcDatabase(
                    $this->dbHandler
                )
            );
        }
        return $this->contentTypeGateway;
    }

    /**
     * Returns the storage registry
     *
     * @return Content\StorageRegistry
     */
    public function getStorageRegistry()
    {
        return $this->storageRegistry;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Search\Handler
     */
    public function searchHandler()
    {
        if ( !isset( $this->searchHandler ) )
        {
            $this->searchHandler = new Content\Search\Handler(
                new Content\Search\Gateway\ExceptionConversion(
                    new Content\Search\Gateway\EzcDatabase(
                        $this->dbHandler,
                        new Content\Search\Gateway\CriteriaConverter(
                            array(
                                new Content\Search\Gateway\CriterionHandler\ContentId( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\Subtree( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\LocationId( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\RemoteId( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\Status( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\LogicalNot( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\LogicalAnd( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\LogicalOr( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\ContentTypeId( $this->dbHandler ),
                                new Content\Search\Gateway\CriterionHandler\ParentLocationId( $this->dbHandler ),
                                // new Content\Search\Gateway\CriterionHandler\ContentTypeIdentifier( $this->dbHandler ),
                                // new Content\Search\Gateway\CriterionHandler\ContentTypeGroupId( $this->dbHandler ),
                                // new Content\Search\Gateway\CriterionHandler\LocationRemoteId( $this->dbHandler ),
                                // new Content\Search\Gateway\CriterionHandler\SectionId( $this->dbHandler ),
                            )
                        ),
                        new Content\Search\Gateway\SortClauseConverter(
                        ),
                        new Content\Gateway\EzcDatabase\QueryBuilder( $this->dbHandler ),
                        $this->contentLanguageHandler()
                    )
                ),
                $this->getContentMapper()
            );
        }
        return $this->searchHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new Content\Location\Handler(
                $this->getLocationGateway(),
                new Content\Location\Mapper(),
                $this->contentHandler(),
                $this->getContentMapper()
            );
        }
        return $this->locationHandler;
    }

    /**
     * Returns a location gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase
     */
    protected function getLocationGateway()
    {
        if ( !isset( $this->locationGateway ) )
        {
            $this->locationGateway = new Content\Location\Gateway\ExceptionConversion(
                new Content\Location\Gateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->locationGateway;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Handler
     */
    public function objectStateHandler()
    {
        if ( !isset( $this->objectStateHandler ) )
        {
            $this->objectStateHandler = new Content\ObjectState\Handler(
            );
        }
        return $this->objectStateHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\User\Handler
     */
    public function userHandler()
    {
        if ( !isset( $this->userHandler ) )
        {
            $this->userHandler = new User\Handler(
                new User\Gateway\ExceptionConversion(
                    new User\Gateway\EzcDatabase( $this->dbHandler )
                ),
                new User\Role\Gateway\EzcDatabase( $this->dbHandler ),
                new User\Mapper()
            );
        }
        return $this->userHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        if ( !isset( $this->sectionHandler ) )
        {
            $this->sectionHandler = new Content\Section\Handler(
                new Content\Section\Gateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->sectionHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler
     */
    public function trashHandler()
    {
        if ( !isset( $this->trashHandler ) )
        {
            $this->trashHandler = new Content\Location\Trash\Handler(
                $this->locationHandler(),
                $this->getLocationGateway(),
                new Content\Location\Mapper(),
                $this->contentHandler()
            );
        }

        return $this->trashHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    public function urlAliasHandler()
    {
        if ( !isset( $this->urlAliasHandler ) )
        {
            $this->urlAliasHandler = new Content\UrlAlias\Handler(
            );
        }

        return $this->urlAliasHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
     */
    public function urlWildcardHandler()
    {
        if ( !isset( $this->urlWildcardHandler ) )
        {
            $this->urlWildcardHandler = new Content\UrlWildcard\Handler(
            );
        }

        return $this->urlWildcardHandler;
    }

    /**
     * Begin transaction
     *
     * Begins an transaction, make sure you'll call commit or rollback when done,
     * otherwise work will be lost.
     */
    public function beginTransaction()
    {
        $this->dbHandler->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * Commit transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function commit()
    {
        try
        {
            $this->dbHandler->commit();
        }
        catch ( \ezcDbTransactionException $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }

    /**
     * Rollback transaction
     *
     * Rollback transaction, or throw exceptions if no transactions has been started.
     *
     * @throws \RuntimeException If no transaction has been started
     */
    public function rollback()
    {
        try
        {
            $this->dbHandler->rollback();
        }
        catch ( \ezcDbTransactionException $e )
        {
            throw new RuntimeException( $e->getMessage() );
        }
    }

    /**
     * Initialize repository
     *
     * @return void
     */
    public function initializeRepository()
    {
        $initializer = new RepositoryInitializer();
        $initializer->initializeSchema($this->dbHandler);
        $initializer->initialize($this);
    }
}
