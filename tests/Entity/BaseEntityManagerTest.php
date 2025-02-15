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

namespace Sonata\Doctrine\Tests\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Sonata\Doctrine\Entity\BaseEntityManager;

class EntityManager extends BaseEntityManager
{
    public function getRepositoryFromBaseClass(): EntityRepository
    {
        return $this->getRepository();
    }
}

final class BaseEntityManagerTest extends TestCase
{
    public function getManager()
    {
        $registry = $this->createMock(ManagerRegistry::class);

        return new EntityManager('classname', $registry);
    }

    public function test()
    {
        $this->assertSame('classname', $this->getManager()->getClass());
    }

    public function testException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The property exception does not exists');

        $this->getManager()->exception;
    }

    public function testExceptionOnNonMappedEntity()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the mapping information for the class classname. Please check the `auto_mapping` option (http://symfony.com/doc/current/reference/configuration/doctrine.html#configuration-overview) or add the bundle to the `mappings` section in the doctrine configuration');

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())->method('getManagerForClass')->willReturn(null);

        $manager = new EntityManager('classname', $registry);
        $manager->getObjectManager();
    }

    public function testGetEntityManager()
    {
        $objectManager = $this->createMock(ObjectManager::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())->method('getManagerForClass')->willReturn($objectManager);

        $manager = new EntityManager('classname', $registry);

        $manager->em;
    }

    public function testGetRepository(): void
    {
        $entityRepository = $this->createMock(EntityRepository::class);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->once())->method('getRepository')->with('classname')->willReturn($entityRepository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())->method('getManagerForClass')->willReturn($objectManager);

        $manager = new EntityManager('classname', $registry);
        $repository = $manager->getRepositoryFromBaseClass();
        $this->assertInstanceOf(EntityRepository::class, $repository);
    }
}
