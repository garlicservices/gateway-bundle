<?php

namespace Garlic\Gateway\Repository;

use Garlic\Gateway\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * Update service status
     *
     * @param string $serviceName
     * @param int $status
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function updateServiceStatus(string $serviceName, int $status, $timing)
    {
        $service = $this->_em->getRepository(Service::class)
            ->findOneBy(['name' => $serviceName]);

        if (!$service) {
            $service = new Service();
            $service->setName($serviceName);
        } else {
            $service->setStatus($status);
        }

        $service->setLastTiming(round($timing, 4));

        $service->setEnabled(in_array($status, [
            Service::STATUS_NEW,
            Service::STATUS_OK,
        ]));
        $service->setLastHealthCheckAt(new \DateTime('now'));

        $this->_em->persist($service);
        $this->_em->flush();
    }

    /**
     * Get active services list
     *
     * @return array
     * @throws \Exception
     */
    public function getActiveServices()
    {
        $data = $this->createQueryBuilder('s')
            ->select('s.name')
            ->where('s.lastHealthCheckAt >= :last_heart_beat')
            ->where('s.name != "gateway"') // gateway didn't store any schema itself
            ->setParameter('last_heart_beat', (new \DateTime('-1 minute')))
            ->getQuery()
            ->getResult()
            ;

        return array_map(function ($x) use ($data) {
            return $x['name'];
        }, $data);
    }
}
