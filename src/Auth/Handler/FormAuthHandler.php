<?php

namespace Michel\Framework\Core\Auth\Handler;

use Michel\Framework\Core\Auth\AuthHandlerInterface;
use Michel\Framework\Core\Auth\AuthIdentity;
use Michel\Framework\Core\Auth\Exception\AuthenticationException;
use Michel\Framework\Core\Auth\Exception\InvalidCredentialsException;
use Michel\Framework\Core\Auth\Exception\UserNotFoundException;
use Michel\Framework\Core\Auth\PasswordAuthenticatedUserInterface;
use Michel\Framework\Core\Auth\UserInterface;
use Michel\Framework\Core\Auth\UserProviderInterface;
use Michel\Session\Storage\SessionStorageInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FormAuthHandler implements AuthHandlerInterface
{
    /**
     * @var callable
     */
    private $onFailure;

    private UserProviderInterface $userProvider;
    private SessionStorageInterface $sessionStorage;

    public function __construct(
        UserProviderInterface   $userProvider,
        SessionStorageInterface $sessionStorage,
        callable                $onFailure
    )
    {
        $this->userProvider = $userProvider;
        $this->sessionStorage = $sessionStorage;
        $this->onFailure = $onFailure;
    }

    /**
     * @throws AuthenticationException
     * @throws UserNotFoundException
     * @throws InvalidCredentialsException
     */
    public function authenticate(ServerRequestInterface $request): ?AuthIdentity
    {
        if ($this->sessionStorage->has('user_identifier')) {
            $identifier = $this->sessionStorage->get('user_identifier');
            $user = $this->userProvider->findByIdentifier($identifier);
            if ($user instanceof UserInterface) {
                return new AuthIdentity($user,  false);
            }
        }

        if ($request->getMethod() !== 'POST') {
            return null;
        }

        $data = $request->getParsedBody();
        $login = $data['login'] ?? '';
        $pass = $data['password'] ?? '';
        if (empty($login) || empty($pass)) {
            throw new InvalidCredentialsException("Credentials cannot be empty.");
        }

        /**
         * @var PasswordAuthenticatedUserInterface|UserInterface|null $user
         */
        $user = $this->userProvider->findByIdentifier($login);
        if (!$user instanceof UserInterface) {
            throw new UserNotFoundException("No user found with the provided identifier.");
        }

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AuthenticationException("The resolved user does not support password authentication.");
        }

        if (!$this->userProvider->checkPassword($user, $pass)) {
            throw new InvalidCredentialsException("Invalid username or password.");
        }

        $this->sessionStorage->put('user_identifier', $user->getUserIdentifier());
        return new AuthIdentity($user,  true);
    }

    public function onFailure(ServerRequestInterface $request, ResponseFactoryInterface $responseFactory, ?AuthenticationException $exception = null): ResponseInterface
    {
        return ($this->onFailure)($request, $responseFactory, $exception);
    }

}
