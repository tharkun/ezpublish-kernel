<?php
/**
 * File containing the user Provider class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\User;

use eZ\Publish\API\Repository\Exceptions\NotFoundException,
    eZ\Publish\Core\MVC\Symfony\Security\User,
    eZ\Publish\Core\MVC\Symfony\Security\User\APIUserProviderInterface,
    eZ\Publish\API\Repository\Values\User\User as APIUser,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class Provider implements APIUserProviderInterface
{
    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    protected $userService;

    /**
     * @var \Closure
     */
    private $lazyRepository;

    public function __construct( \Closure $lazyRepository )
    {
        $this->lazyRepository = $lazyRepository;
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        $lazyRepository = $this->lazyRepository;
        return $lazyRepository();
    }

    /**
     * @return \eZ\Publish\API\Repository\UserService
     */
    protected function getUserService()
    {
        return $this->getRepository()->getUserService();
    }

    /**
     * Loads the user for the given username.
     * $username actually represents the user ID, not the user login.
     *
     * @param string $userId The user ID. A value of -1 represents an anonymous user.
     * @return \Symfony\Component\Security\Core\User\UserInterface
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername( $userId )
    {
        try
        {
            $apiUser = $userId != -1 ? $this->getUserService()->loadUser( $userId ) : $this->getUserService()->loadAnonymousUser();
            return new User( $apiUser );
        }
        catch ( NotFoundException $e )
        {
            throw new UsernameNotFoundException( $e->getMessage(), null, 0, $e );
        }
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @return UserInterface
     */
    public function refreshUser( UserInterface $user )
    {
        return $user;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass( $class )
    {
        $supportedClass = 'eZ\\Publish\\Core\\MVC\\Symfony\\Security\\User';
        return $class === $supportedClass || is_subclass_of( $class, $supportedClass );
    }

    /**
     * Loads a regular user object, usable by Symfony Security component, from a user object returned by Public API
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     * @return \eZ\Publish\Core\MVC\Symfony\Security\User
     */
    public function loadUserByAPIUser( APIUser $apiUser )
    {
        return new User( $apiUser );
    }
}