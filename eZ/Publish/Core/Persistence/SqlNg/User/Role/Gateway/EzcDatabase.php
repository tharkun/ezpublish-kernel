<?php
/**
 * File containing the ContentTypeGateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\SqlNg\User\Role\Gateway;

use eZ\Publish\Core\Persistence\SqlNg\User\Role\Gateway;
use eZ\Publish\Core\Persistence\Legacy\EzcDbHandler;
use eZ\Publish\SPI\Persistence\User\Policy;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\Core\Base\Exceptions\NotFoundException as NotFound;

/**
 * Base class for content type gateways.
 */
class EzcDatabase extends Gateway
{
    /**
     * Database dbHandler
     *
     * @var EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Internal type ID for user groups
     */
    const GROUP_TYPE_ID = 3;

    /**
     * Construct from database dbHandler
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     *
     * @return void
     */
    public function __construct( EzcDbHandler $dbHandler )
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Create new role
     *
     * @param \eZ\Publish\SPI\Persistence\User\Role $role
     *
     * @return Role
     */
    public function createRole( Role $role )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query
            ->insertInto( $this->dbHandler->quoteTable( 'ezrole' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'id' ),
                $this->dbHandler->getAutoIncrementValue( 'ezrole', 'id' )
            )->set(
                $this->dbHandler->quoteColumn( 'identifier' ),
                $query->bindValue( $role->identifier )
            )->set(
                $this->dbHandler->quoteColumn( 'name' ),
                $query->bindValue( json_encode( $role->name ) )
            )->set(
                $this->dbHandler->quoteColumn( 'description' ),
                $query->bindValue( json_encode( $role->description ) )
            );
        $query->prepare()->execute();

        $role->id = $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezrole', 'id' )
        );
    }

    /**
     * Loads a specified role by id
     *
     * @param mixed $roleId
     *
     * @return array
     */
    public function loadRole( $roleId )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'description', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'content_id', 'ezrole_content_rel' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->dbHandler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $this->dbHandler->quoteTable( 'ezrole' )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezrole_content_rel' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezrole_content_rel' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezrole' ),
                $query->bindValue( $roleId, null, \PDO::PARAM_INT )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads a specified role by $identifier
     *
     * @param string $identifier
     *
     * @return array
     */
    public function loadRoleByIdentifier( $identifier )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Loads all roles
     *
     * @return array
     */
    public function loadRoles()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'description', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'content_id', 'ezrole_content_rel' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->dbHandler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $this->dbHandler->quoteTable( 'ezrole' )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezrole_content_rel' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezrole_content_rel' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads all roles associated with the given content objects
     *
     * @param array $contentIds
     *
     * @return array
     */
    public function loadRolesForContentObjects( $contentIds )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->aliasedColumn( $query, 'content_id', 'ezrole_content_rel' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'name', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'description', 'ezrole' ),
            $this->dbHandler->aliasedColumn( $query, 'id', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'function_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'module_name', 'ezpolicy' ),
            $this->dbHandler->aliasedColumn( $query, 'identifier', 'ezpolicy_limitation' ),
            $this->dbHandler->aliasedColumn( $query, 'value', 'ezpolicy_limitation_value' )
        )->from(
            $query->alias(
                $this->dbHandler->quoteTable( 'ezrole_content_rel' ),
                $this->dbHandler->quoteIdentifier( 'ezrole_content_rel_search' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezrole' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'id', 'ezrole' ),
                $this->dbHandler->quoteColumn( 'role_id', 'ezrole_content_rel_search' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezrole_content_rel' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezrole_content_rel' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'role_id', 'ezpolicy' ),
                $this->dbHandler->quoteColumn( 'id', 'ezrole' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'policy_id', 'ezpolicy_limitation' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy' )
            )
        )->leftJoin(
            $this->dbHandler->quoteTable( 'ezpolicy_limitation_value' ),
            $query->expr->eq(
                $this->dbHandler->quoteColumn( 'limitation_id', 'ezpolicy_limitation_value' ),
                $this->dbHandler->quoteColumn( 'id', 'ezpolicy_limitation' )
            )
        )->where(
            $query->expr->in(
                $this->dbHandler->quoteColumn( 'content_id', 'ezrole_content_rel_search' ),
                $contentIds
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Loads role assignments for specified content ID
     *
     * @param mixed $groupId
     * @param boolean $inherited
     *
     * @return array
     */
    public function loadRoleAssignmentsByGroupId( $groupId, $inherited = false )
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn( 'content_id' ),
            $this->dbHandler->quoteColumn( 'limit_identifier' ),
            $this->dbHandler->quoteColumn( 'limit_value' ),
            $this->dbHandler->quoteColumn( 'role_id' )
        )->from(
            $this->dbHandler->quoteTable( 'ezrole_content_rel' )
        );

        // @TODO: Re-enable and test
        if ( false && $inherited )
        {
            $groupIds = $this->fetchUserGroups( $groupId );
            $groupIds[] = $groupId;
            $query->where(
                $query->expr->in(
                    $this->dbHandler->quoteColumn( 'content_id' ),
                    $groupIds
                )
            );
        }
        else
        {
            $query->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'content_id' ),
                    $query->bindValue( $groupId, null, \PDO::PARAM_INT )
                )
            );
        }

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll( \PDO::FETCH_ASSOC );
    }

    /**
     * Returns the user policies associated with the user
     *
     * @param mixed $userId
     *
     * @return UserPolicy[]
     */
    public function loadPoliciesByUserId( $userId )
    {
        throw new \RuntimeException( "@TODO: Implement" );
    }

    /**
     * Update role
     *
     * @param \eZ\Publish\SPI\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role )
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query
            ->update( $this->dbHandler->quoteTable( 'ezrole' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'identifier' ),
                $query->bindValue( $role->identifier )
            )->set(
                $this->dbHandler->quoteColumn( 'name' ),
                $query->bindValue( json_encode( $role->name ) )
            )->set(
                $this->dbHandler->quoteColumn( 'description' ),
                $query->bindValue( json_encode( $role->description ) )
            )->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $role->id, null, \PDO::PARAM_INT )
                )
            );
        $query->prepare()->execute();
    }

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezrole' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $roleId, null, \PDO::PARAM_INT )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $statement->rowCount() < 1 )
        {
            throw new NotFound( 'role', $roleId );
        }
    }

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \eZ\Publish\SPI\Persistence\User\Policy $policy
     *
     * @return void
     */
    public function addPolicy( $roleId, Policy $policy )
    {
        $query = $this->dbHandler->createInsertQuery();
        $query
            ->insertInto( $this->dbHandler->quoteTable( 'ezpolicy' ) )
            ->set(
                $this->dbHandler->quoteColumn( 'id' ),
                $this->dbHandler->getAutoIncrementValue( 'ezpolicy', 'id' )
            )->set(
                $this->dbHandler->quoteColumn( 'function_name' ),
                $query->bindValue( $policy->function )
            )->set(
                $this->dbHandler->quoteColumn( 'module_name' ),
                $query->bindValue( $policy->module )
            )->set(
                $this->dbHandler->quoteColumn( 'role_id' ),
                $query->bindValue( $roleId, null, \PDO::PARAM_INT )
            );
        $query->prepare()->execute();

        $policy->roleId = $roleId;
        $policy->id = $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName( 'ezpolicy', 'id' )
        );

        // Handle the only valid non-array value "*" by not inserting
        // anything. Still has not been documented by eZ Systems. So we
        // assume this is the right way to handle it.
        if ( is_array( $policy->limitations ) )
        {
            $this->addPolicyLimitations( $policy->id, $policy->limitations );
        }

        return $policy;
    }

    /**
     * Adds limitations to an existing policy
     *
     * @param int $policyId
     * @param array $limitations
     *
     * @return void
     */
    public function addPolicyLimitations( $policyId, array $limitations )
    {
        foreach ( $limitations as $identifier => $values )
        {
            $query = $this->dbHandler->createInsertQuery();
            $query
                ->insertInto( $this->dbHandler->quoteTable( 'ezpolicy_limitation' ) )
                ->set(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $this->dbHandler->getAutoIncrementValue( 'ezpolicy_limitation', 'id' )
                )->set(
                    $this->dbHandler->quoteColumn( 'identifier' ),
                    $query->bindValue( $identifier )
                )->set(
                    $this->dbHandler->quoteColumn( 'policy_id' ),
                    $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                );
            $query->prepare()->execute();

            $limitationId = $this->dbHandler->lastInsertId(
                $this->dbHandler->getSequenceName( 'ezpolicy_limitation', 'id' )
            );

            foreach ( $values as $value )
            {
                $query = $this->dbHandler->createInsertQuery();
                $query
                    ->insertInto( $this->dbHandler->quoteTable( 'ezpolicy_limitation_value' ) )
                    ->set(
                        $this->dbHandler->quoteColumn( 'id' ),
                        $this->dbHandler->getAutoIncrementValue( 'ezpolicy_limitation_value', 'id' )
                    )->set(
                        $this->dbHandler->quoteColumn( 'value' ),
                        $query->bindValue( $value )
                    )->set(
                        $this->dbHandler->quoteColumn( 'limitation_id' ),
                        $query->bindValue( $limitationId, null, \PDO::PARAM_INT )
                    );
                $query->prepare()->execute();
            }
        }
    }

    /**
     * Removes a policy from a role
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function removePolicy( $policyId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezpolicy' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'id' ),
                    $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $statement->rowCount() < 1 )
        {
            throw new NotFound( 'policy', $policyId );
        }
    }

    /**
     * Remove all limitations for a policy
     *
     * @param mixed $policyId
     *
     * @return void
     */
    public function removePolicyLimitations( $policyId )
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom( $this->dbHandler->quoteTable( 'ezpolicy_limitation' ) )
            ->where(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn( 'policy_id' ),
                    $query->bindValue( $policyId, null, \PDO::PARAM_INT )
                )
            );
        $statement = $query->prepare();
        $statement->execute();

        if ( $statement->rowCount() < 1 )
        {
            throw new NotFound( 'limitations', $policyId );
        }
    }
}
