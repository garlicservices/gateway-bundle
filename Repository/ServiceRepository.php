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
     * Get active services list
     *
     * @return array
     * @throws \Exception
     */
    public function getActiveServices()
    {
        $data = $this->createQueryBuilder('s')
            ->select('s.name')
            ->where('s.lastHealthCheckAt >= :last_heart_beat and s.name != :gateway')
            ->setParameter('last_heart_beat', (new \DateTime('-2 minute')))
            ->setParameter('gateway', "gateway")
            ->getQuery()
            ->getResult();

        return array_map(
            function ($x) use ($data) {
                return $x['name'];
            },
            $data
        );
    }
}
