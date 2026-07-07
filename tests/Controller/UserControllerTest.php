<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Unit test for user controller behavior
 */

namespace Test\Michel\Framework\Core\Controller;

use Michel\Attribute\Route;
use Michel\Framework\Core\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Test\Michel\Framework\Core\Response\ResponseTest;

class UserControllerTest extends Controller
{
    public function __construct(array $middleware)
    {
        foreach ($middleware as $item) {
            $this->middleware($item);
        }
    }

    #[Route('/users', name: 'users')]
    public function users() :ResponseInterface
    {
        return new ResponseTest();
    }
}
