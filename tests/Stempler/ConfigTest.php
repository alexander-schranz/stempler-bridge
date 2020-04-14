<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Tests;

use Spiral\Core\Container\Autowire;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Stempler\Builder;
use Spiral\Stempler\Config\StemplerConfig;
use Spiral\Stempler\Directive\ConditionalDirective;
use Spiral\Stempler\Directive\ContainerDirective;
use Spiral\Stempler\Directive\JsonDirective;
use Spiral\Stempler\Directive\LoopDirective;
use Spiral\Stempler\Directive\PHPDirective;
use Spiral\Stempler\Directive\RouteDirective;
use Spiral\Views\Processor\ContextProcessor;

class ConfigTest extends BaseTest
{
    public function testWireConfigString(): void
    {
        $config = new StemplerConfig([
            'processors' => [ContextProcessor::class]
        ]);

        $this->assertInstanceOf(
            ContextProcessor::class,
            $config->getProcessors()[0]->resolve($this->container)
        );
    }

    public function testWireDirective(): void
    {
        $config = new StemplerConfig([
            'directives' => [ContainerDirective::class]
        ]);

        $this->assertInstanceOf(
            ContainerDirective::class,
            $config->getDirectives()[0]->resolve($this->container)
        );
    }

    public function testWireConfig(): void
    {
        $config = new StemplerConfig([
            'processors' => [
                new Autowire(ContextProcessor::class)
            ]
        ]);

        $this->assertInstanceOf(
            ContextProcessor::class,
            $config->getProcessors()[0]->resolve($this->container)
        );
    }

    public function testDebugConfig(): void
    {
        $loader = $this->container->get(StemplerBootloader::class);
        $loader->addDirective(self::class);

        $config = $this->container->get(StemplerConfig::class);

        $this->assertEquals([
            new Autowire(PHPDirective::class),
            new Autowire(RouteDirective::class),
            new Autowire(LoopDirective::class),
            new Autowire(JsonDirective::class),
            new Autowire(ConditionalDirective::class),
            new Autowire(ContainerDirective::class),
            new Autowire(self::class)
        ], $config->getDirectives());
    }

    public function testBootloaderDirective(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addDirective('testBinding');

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(7, $cfg->getDirectives());
        $this->assertSame('test result', $cfg->getDirectives()[6]->resolve($this->container));
    }

    public function testBootloaderProcessors(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addProcessor('testBinding');

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(2, $cfg->getProcessors());
        $this->assertSame('test result', $cfg->getProcessors()[1]->resolve($this->container));
    }

    public function testBootloaderVisitors(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addVisitor('testBinding', Builder::STAGE_FINALIZE);

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(3, $cfg->getVisitors(Builder::STAGE_FINALIZE));
        $this->assertSame('test result', $cfg->getVisitors(Builder::STAGE_FINALIZE)[2]->resolve($this->container));
    }

    public function testBootloaderVisitors0(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addVisitor('testBinding', Builder::STAGE_COMPILE);

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(3, $cfg->getVisitors(Builder::STAGE_COMPILE));
        $this->assertSame('test result', $cfg->getVisitors(Builder::STAGE_COMPILE)[2]->resolve($this->container));
    }

    public function testBootloaderVisitors2(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addVisitor('testBinding', Builder::STAGE_TRANSFORM);

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(1, $cfg->getVisitors(Builder::STAGE_TRANSFORM));
        $this->assertSame('test result', $cfg->getVisitors(Builder::STAGE_TRANSFORM)[0]->resolve($this->container));
    }

    public function testBootloaderVisitors3(): void
    {
        $this->container->bind('testBinding', function () {
            return 'test result';
        });

        /** @var StemplerBootloader $bootloader */
        $bootloader = $this->container->get(StemplerBootloader::class);

        $bootloader->addVisitor('testBinding', Builder::STAGE_PREPARE);

        /** @var StemplerConfig $cfg */
        $cfg = $this->container->get(StemplerConfig::class);

        $this->assertCount(4, $cfg->getVisitors(Builder::STAGE_PREPARE));
        $this->assertSame('test result', $cfg->getVisitors(Builder::STAGE_PREPARE)[3]->resolve($this->container));
    }
}
