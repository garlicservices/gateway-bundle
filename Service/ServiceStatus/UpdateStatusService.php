<?php

namespace Garlic\Gateway\Service\ServiceStatus;

use Doctrine\ORM\EntityManagerInterface;
use Garlic\Bus\Entity\Response;
use Garlic\Bus\Service\CommunicatorService;
use Garlic\Gateway\Service\ServiceRegistry;
use GraphQL\GraphQL;
use GraphQL\Language\AST\DefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Printer;
use GraphQL\Language\Source;
use GraphQL\Type\Introspection;
use GraphQL\Validator\DocumentValidator;
use Symfony\Component\HttpFoundation\RequestStack;
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
    }

}