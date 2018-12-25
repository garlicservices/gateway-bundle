<?php

namespace Garlic\Gateway\Service\ServiceStatus;

use Doctrine\ORM\EntityManagerInterface;
use Garlic\Gateway\Entity\Service;

/**
 * Class UpdateStatusService
 * @package Garlic\Gateway\Service\ServiceStatus
 */
class UpdateStatusService
{
    /** @var array EntityManagerInterface */
    protected $em;

    /**
     * Service status manager constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * Update Service status
     *
     * @param string $serviceName
     * @param int $status
     * @param $timing
     * @throws \Exception
     */
    public function updateServiceStatus(string $serviceName, int $status, $timing)
    {
        $service = $this->em->getRepository(Service::class)
            ->findOneBy(['name' => $serviceName]);

        if (!$service) {
            $service = new Service();
            $service->setName($serviceName);
        } else {
            $service->setStatus($status);
        }

        $service->setLastTiming(round($timing, 4));

        $service->setEnabled(
            in_array(
                $status,
                [
                    Service::STATUS_NEW,
                    Service::STATUS_OK,
                ]
            )
        );
        $service->setLastHealthCheckAt(new \DateTime('now'));

        $this->em->persist($service);
        $this->em->flush();

        return $service;
    }

}