<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Middleware to restrict access based on IP address
 */

namespace Michel\Framework\Core\Middlewares;

use Michel\Framework\Core\Helper\IpHelper;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class IpRestrictionMiddleware implements MiddlewareInterface
{
    private array $allowedIps;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(array $allowedIps, ResponseFactoryInterface $responseFactory)
    {
        $this->allowedIps = $allowedIps;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = IpHelper::getIpFromRequest($request);
        if (!$this->isIpAllowed($ip)) {
            return $this->responseFactory->createResponse(403, 'Forbidden');
        }

        return $handler->handle($request);
    }

    private function isIpAllowed($ip): bool
    {
        if ($this->allowedIps === []) {
            return true;
        }
        foreach ($this->allowedIps as $allowedIp) {
            if (preg_match($allowedIp, $ip)) {
                return true;
            }
        }
        return in_array($ip, $this->allowedIps);
    }
}
