<?php
namespace Garlic\Gateway\Tests\Service\ServiceStatus;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Garlic\Gateway\Service\ServiceStatus\UpdateStatusService;
use PHPUnit\Framework\TestCase;

class UpdateStatusServiceTest extends TestCase
{
    public function testUpdateServiceStatus_givenRegularRequest_expectsDataStoredToDB()
    {
        $service = new \Garlic\Gateway\Entity\Service();

        $employeeRepository = $this->createMock(ObjectRepository::class);
        $employeeRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($service);

        $em = $this->createPartialMock(EntityManager::class, [
            'getRepository', 'persist', 'flush'
        ]);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($employeeRepository);
        $em->expects($this->once())
            ->method('persist')
            ->willReturn(true);
        $em->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        /** @var \Garlic\Gateway\Entity\Service $entity */
        $entity = (new UpdateStatusService($em))->updateServiceStatus('name', 1, 123);

        $this->assertEquals(1, $entity->getEnabled());
        $this->assertEquals(123, $entity->getLastTiming());
        $this->assertEquals(1, $entity->getStatus());
    }

    public function testUpdateServiceStatus_givenNewEntity_expectsDataStoredToDB()
    {
        $employeeRepository = $this->createMock(ObjectRepository::class);
        $employeeRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $em = $this->createPartialMock(EntityManager::class, [
            'getRepository', 'persist', 'flush'
        ]);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($employeeRepository);
        $em->expects($this->once())
            ->method('persist')
            ->willReturn(true);
        $em->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        /** @var \Garlic\Gateway\Entity\Service $entity */
        $entity = (new UpdateStatusService($em))->updateServiceStatus('new_name', 2, 123);

        $this->assertEquals(0, $entity->getEnabled());
        $this->assertEquals(null, $entity->getStatus());
        $this->assertEquals('new_name', $entity->getName());
    }
}