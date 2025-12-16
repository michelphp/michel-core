<?php

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\ErrorHandler\ErrorHandler;
use Michel\UniTester\TestCase;

class ErrorHandlerTest extends TestCase
{

    protected function setUp(): void
    {
        // TODO: Implement setUp() method.
    }

    protected function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    protected function execute(): void
    {
       $this->testDeprecationErrors();
    }
    public function testDeprecationErrors()
    {
        $errorHandler = new ErrorHandler();
        $errorHandler->__invoke(E_USER_DEPRECATED, 'This is a deprecated error',__FILE__);

        $deprecations = $errorHandler->getDeprecations();

        $this->assertCount(1, $deprecations);

        $deprecation = $deprecations[0];
        $this->assertArrayHasKey('level', $deprecation);
        $this->assertEquals(E_USER_DEPRECATED, $deprecation['level']);
        $this->assertArrayHasKey('message', $deprecation);
        $this->assertEquals('This is a deprecated error', $deprecation['message']);
    }

}
