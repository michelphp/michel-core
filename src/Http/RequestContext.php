<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Context holder for the current HTTP request
 */

namespace Michel\Framework\Core\Http;

use Michel\Framework\Core\Auth\UserInterface;
use Michel\Route;
use Michel\RouterMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class RequestContext
{
    private ?ServerRequestInterface $request = null;

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function getCurrentRoute(): ?string
    {
        if ($this->request ===  null) {
            return null;
        }
        $route = $this->request->getAttribute(RouterMiddleware::ATTRIBUTE_KEY);
        if (!$route instanceof Route) {
            return null;
        }
        return $route->getName();
    }

    public function getUser(): ?UserInterface
    {
        return $this->request?->getAttribute('user');
    }
}
