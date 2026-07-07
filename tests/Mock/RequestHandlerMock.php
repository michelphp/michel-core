<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Request handler mock for testing purposes
 */

namespace Test\Michel\Framework\Core\Mock;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerMock implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement handle() method.
    }
}
