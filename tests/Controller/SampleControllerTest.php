<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Unit test for controller behavior with a sample controller
 */

namespace Test\Michel\Framework\Core\Controller;

use Michel\Framework\Core\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Test\Michel\Framework\Core\Response\ResponseTest;

class SampleControllerTest extends Controller
{
    public function __construct(array $middleware)
    {
        foreach ($middleware as $item) {
            $this->middleware($item);
        }
    }

    public function __invoke() :ResponseInterface
    {
        return new ResponseTest();
    }

    public function testGet(string $id)
    {
        return $this->get($id);
    }

    public function fakeMethod() :ResponseInterface
    {
        return new ResponseTest();
    }
}
