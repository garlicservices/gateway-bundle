<?php

namespace Garlic\Gateway\Controller;

use Garlic\Gateway\Service\GraphQL\QueryProcessorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /** @var QueryProcessorService */
    private $queryProcessor;

    /**
     * DefaultController constructor.
     * @param QueryProcessorService $queryProcessor
     */
    public function __construct(QueryProcessorService $queryProcessor)
    {
        $this->queryProcessor = $queryProcessor;
    }

    /**
     * Gateway entry point method
     *
     * @Route("/main", name="default")
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function index()
    {

        $output = $this->queryProcessor->processQuery();

        return new JsonResponse(
            $output
        );
    }
}
