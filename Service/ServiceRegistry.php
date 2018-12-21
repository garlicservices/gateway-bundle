<?php

namespace Garlic\Gateway\Service;

use Garlic\Gateway\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;

class ServiceRegistry
{
    /** @var EntityManagerInterface $em */
    protected $em;

    /**
     * ServiceRegistry constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Returns list of registered service names
     *
     * @return array
     * @throws \Exception
     */
    public function getRegisteredServices(): array
    {
        return $this->em->getRepository(Service::class)
            ->getActiveServices();
    }
}