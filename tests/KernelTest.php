<?php

namespace Test\Michel\Framework\Core;

use Michel\Framework\Core\BaseKernel;
use Michel\UniTester\TestCase;
use Psr\Container\ContainerInterface;
use Test\Michel\Framework\Core\Kernel\SampleKernelTest;
use Test\Michel\Framework\Core\Package\MyPackageTest;

class KernelTest extends TestCase
{

    protected function setUp(): void
    {
        $this->initEnv();
    }

    protected function execute(): void
    {
        $this->testLoadKernel();
        $this->initEnv();

        $this->testLoadConfigurationIfExists();
        $this->initEnv();

        $this->testDefaultValue();
        $this->initEnv();

        $this->testKernelContainer();
    }

    protected function tearDown(): void
    {
        $this->initEnv();
    }
    private function initEnv(): void
    {
        unset($_ENV['APP_ENV']);
        unset($_SERVER['APP_ENV']);
        unset($_ENV['APP_TIMEZONE']);
        unset($_SERVER['APP_TIMEZONE']);
        unset($_ENV['APP_LOCALE']);
        unset($_SERVER['APP_LOCALE']);
        unset($_ENV['APP_URL']);
        unset($_SERVER['APP_URL']);

        putenv('APP_ENV');
        putenv('APP_TIMEZONE');
        putenv('APP_LOCALE');
        putenv('APP_URL');
        putenv('APP_DEBUG');


        date_default_timezone_set('UTC');
    }
    public function testLoadKernel()
    {
        $baseKernel = new SampleKernelTest('.env');

        $this->assertEquals('dev', $baseKernel->getEnv());
        $this->assertEquals(true, $baseKernel->isDebug());
        $this->assertEquals('dev', getenv('APP_ENV'));
        $this->assertEquals(0, getenv('APP_DEBUG'));
        $this->assertEquals('Europe/Paris', getenv('APP_TIMEZONE'));
        $this->assertEquals('fr', getenv('APP_LOCALE'));
        $this->assertEquals('http://localhost', getenv('APP_URL'));
        $this->assertEquals('Europe/Paris', date_default_timezone_get());
    }

    public function testLoadConfigurationIfExists()
    {
        $baseKernel = new SampleKernelTest('.env');
        $this->assertEquals([], $baseKernel->loadConfigurationIfExists('test.php'));
    }

    public function testDefaultValue()
    {
        $baseKernel = new SampleKernelTest('.env.test');
        $this->assertEquals('prod', $baseKernel->getEnv());
        $this->assertEquals('prod', getenv('APP_ENV'));
        $this->assertEquals('UTC', getenv('APP_TIMEZONE'));
        $this->assertEquals('en', getenv('APP_LOCALE'));
        $this->assertFalse(getenv('APP_URL'));
        $this->assertEquals('UTC', date_default_timezone_get());
    }

    public function testKernelContainer()
    {
        $baseKernel = new SampleKernelTest('.env');
        $container = $baseKernel->getContainer();
        $this->assertInstanceOf(ContainerInterface::class, $baseKernel->getContainer());

        $packages = $container->get('michel.packages');
        $this->assertTrue(is_array($packages));
        $this->assertInstanceOf(MyPackageTest::class, $packages[0]);

        $this->assertTrue(is_array($container->get('michel.middleware')));
        $this->assertTrue(is_array($container->get('michel.commands')));
        $this->assertTrue(is_array($container->get('michel.listeners')));
        $this->assertTrue(is_array($container->get('michel.routes')));
        if (PHP_VERSION_ID >= 80000) {
            $this->assertCount(3, $container->get('michel.routes'));
        }else {
            $this->assertCount(2, $container->get('michel.routes'));
        }
        $this->assertTrue(is_array($container->get('michel.controllers')));
        $this->assertCount(2, $container->get('michel.controllers'));
        $this->assertTrue(is_array($container->get('michel.services_ids')));
        $this->assertEquals($baseKernel->getEnv(), $container->get('michel.environment'));
        $this->assertEquals($baseKernel->getEnv() === 'dev', $container->get('michel.debug'));
        $this->assertEquals($baseKernel->getProjectDir(), $container->get('michel.project_dir'));
        $this->assertEquals($baseKernel->getCacheDir(), $container->get('michel.cache_dir'));
        $this->assertEquals($baseKernel->getLogDir(), $container->get('michel.logs_dir'));
        $this->assertEquals($baseKernel->getConfigDir(), $container->get('michel.config_dir'));
        $this->assertEquals($baseKernel->getPublicDir(), $container->get('michel.public_dir'));
        $this->assertInstanceOf(BaseKernel::class, $container->get(BaseKernel::class));
    }

}
