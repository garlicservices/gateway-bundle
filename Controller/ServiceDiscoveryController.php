<?php
    
namespace Garlic\Gateway\Controller;

use Garlic\Gateway\Entity\Service;
use Garlic\Gateway\Service\GraphQL\SchemaService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Garlic\Gateway\Service\ServiceStatus\UpdateStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class ServiceDiscoveryController extends AbstractController
{
    /**
     * @var SchemaService
     */
    private $schemaService;
    /**
     * @var UpdateStatusService
     */
    private $service;
    /**
     * @var ClientInterface
     */
    private $redis;
    
    /**
     * DefaultController constructor.
     * @param SchemaService $schemaService
     * @param UpdateStatusService $service
     * @param ClientInterface $redis
     */
    public function __construct(
        SchemaService $schemaService,
        UpdateStatusService $service,
        ClientInterface $redis
    ) {
        $this->schemaService = $schemaService;
        $this->service = $service;
        $this->redis = $redis;
    }

    /**
     * Default method that requests default route from Template service (for example)
     *
     * @Route("/service/rebuild/schema", name="servicediscovery.rebuildschema", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $name = $request->request->get('name');

        $this->service->updateServiceStatus($name, Service::STATUS_OK, $request->request->get('timing'));

        $this->redis->set($name, $request->request->get('data'));

        $this->schemaService->rebuildSchema();

        return JsonResponse::create([]);
    }
}
