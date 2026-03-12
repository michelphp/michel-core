<?php

namespace Michel\Framework\Core\Auth\Middlewares;

use Michel\Framework\Core\Auth\AuthHandlerInterface;
use Michel\Framework\Core\Auth\AuthIdentity;
use Michel\Framework\Core\Auth\Exception\AuthenticationException;
use Michel\Framework\Core\Helper\IpHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class AuthMiddleware
{
    private AuthHandlerInterface $authHandler;
    private ResponseFactoryInterface $responseFactory;

    private ?LoggerInterface $logger;

    public function __construct(
        AuthHandlerInterface $authHandler,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger = null
    )
    {
        $this->authHandler = $authHandler;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handlerName = get_class($this->authHandler);

        try {
            $authIdentity = $this->authHandler->authenticate($request);
            if ($authIdentity instanceof AuthIdentity) {
                $user = $authIdentity->getUser();
                $request = $request->withAttribute("user", $user);
                if ($authIdentity->isNewLogin()) {
                    $this->log('info', "[{handler}] Authentication successful : {id}.", [
                        'handler' => $handlerName,
                        'id'      => $user->getUserIdentifier()
                    ]);
                }
                return $handler->handle($request);
            }
        }catch (AuthenticationException $exception) {
            $this->log('warning', "[{handler}] Authentication failed: {ip} - {message}", [
                'handler' => $handlerName,
                'message' => $exception->getMessage(),
                'ip'      => IpHelper::getIpFromRequest($request),
            ]);
            return $this->authHandler->onFailure($request, $this->responseFactory, $exception);
        }

        return $this->authHandler->onFailure($request, $this->responseFactory);
    }


    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger === null) {
            return;
        }
        $this->logger->log($level, $message, $context);
    }
}
