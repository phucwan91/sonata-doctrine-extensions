<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Doctrine\Tests\Bridge\Symfony\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\Doctrine\Bridge\Symfony\DependencyInjection\Compiler\AdapterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class AdapterCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AdapterCompilerPass());
    }

    public function testDefinitionsAdded()
    {
        $adapterChain = new Definition();
        $this->setDefinition('sonata.doctrine.model.adapter.chain', $adapterChain);

        $this->registerService('doctrine', 'foo');
        $this->registerService('doctrine_phpcr', 'foo');
        $this->registerService('sonata.doctrine.adapter.doctrine_orm', 'foo');

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.doctrine.model.adapter.chain',
            'addAdapter',
            [new Reference('sonata.doctrine.adapter.doctrine_orm')]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.doctrine.model.adapter.chain',
            'addAdapter',
            [new Reference('sonata.doctrine.adapter.doctrine_phpcr')]
        );
    }

    public function testDefinitionsAddedWithoutOrm()
    {
        $adapterChain = new Definition();
        $this->setDefinition('sonata.doctrine.model.adapter.chain', $adapterChain);

        $this->registerService('doctrine', 'foo');
        $this->registerService('doctrine_phpcr', 'foo');

        $this->compile();

        $this->assertContainerBuilderNotHasService('sonata.doctrine.adapter.doctrine_orm');

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'sonata.doctrine.model.adapter.chain',
            'addAdapter',
            [new Reference('sonata.doctrine.adapter.doctrine_phpcr')]
        );
    }

    public function testDefinitionsRemoved()
    {
        $adapterChain = new Definition();
        $this->setDefinition('sonata.doctrine.model.adapter.chain', $adapterChain);

        $this->registerService('sonata.doctrine.adapter.doctrine_orm', 'foo');
        $this->registerService('sonata.doctrine.adapter.doctrine_phpcr', 'foo');

        $this->compile();

        $this->assertContainerBuilderNotHasService('sonata.doctrine.adapter.doctrine_orm');
        $this->assertContainerBuilderNotHasService('sonata.doctrine.adapter.doctrine_phpcr');
    }
}
