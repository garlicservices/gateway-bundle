<?php

namespace Garlic\Gateway\Service\GraphQL;

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

/**
 * Class QueryProcessorService
 * @package Garlic\Gateway\Service\GraphQL
 */
class QueryProcessorService
{
    /** @var array $queryPayload */
    private $queryPayload = [];
    /** @var array $querySplit */
    private $querySplit = [];
    /** @var array $queryFragments */
    private $queryFragments = [];
    /** @var string $queryType */
    private $queryType;
    /** @var CommunicatorService */
    private $communicatorService;
    /** @var SchemaService */
    private $schemaService;
    /** @var ResponseFormatterService */
    private $responseService;
    /** @var ServiceRegistry */
    private $registryService;
    /** @var \Symfony\Component\HttpFoundation\Request|null */
    private $request;

    /**
     * QueryProcessorService constructor.
     *
     * @param RequestStack $requestStack
     * @param CommunicatorService $communicatorService
     * @param SchemaService $schemaService
     * @param ServiceRegistry $registryService
     * @param ResponseFormatterService $responseService
     */
    public function __construct(
        RequestStack $requestStack,
        CommunicatorService $communicatorService,
        SchemaService $schemaService,
        ServiceRegistry $registryService,
        ResponseFormatterService $responseService
    ) {
        $this->communicatorService = $communicatorService;
        $this->schemaService = $schemaService;
        $this->registryService = $registryService;
        $this->responseService = $responseService;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Parse request and extract query payload
     */
    public function getQueryPayload()
    {
        $query = $this->request->get('query', null);
        $variables = $this->request->get('variables', []);
        $variables = is_string($variables) ? json_decode($variables, true) ?: [] : [];
        $content = $this->request->getContent();
        if (!empty($content)) {
            if ($this->request->headers->has('Content-Type') && 'application/graphql' == $this->request->headers->get(
                    'Content-Type'
                )) {
                $this->queryPayload = [
                    'query' => $content,
                    'variables' => [],
                ];
            } else {
                $params = json_decode($content, true);
                if ($params) {
                    $query = isset($params['query']) ? $params['query'] : $query;
                    if (isset($params['variables'])) {
                        if (is_string($params['variables'])) {
                            $variables = json_decode($params['variables'], true) ?: $variables;
                        } else {
                            $variables = $params['variables'];
                        }
                        $variables = is_array($variables) ? $variables : [];
                    }
                    $this->queryPayload = [
                        'query' => $query,
                        'variables' => $variables,
                    ];
                }
            }
        } else {
            $this->queryPayload = [
                'query' => $query,
                'variables' => $variables,
            ];
        }
    }

    /**
     * Process all queries and generate combined response
     *
     * @return mixed
     * @throws \Exception
     */
    public function processQuery()
    {
        try {
            $this->getQueryPayload();
            $documentNode = Parser::parse(new Source($this->queryPayload['query'] ?: '', 'GraphQL'));
            $validate = DocumentValidator::validate($this->schemaService->getSchema(), $documentNode);

            if (!empty($validate)) {
                foreach ($validate as $error) {
                    $this->responseService->setError('graphql', $error->getMessage(), $error->getCode());
                }

                return $this->responseService->response();
            }

            if ($documentNode instanceof DocumentNode) {

                foreach ($documentNode->definitions as $definition) {
                    /** @var DefinitionNode $definition */
                    switch ($definition->kind) {
                        case NodeKind::OPERATION_DEFINITION:
                            $this->queryType = $definition->operation;
                            /** @var SelectionSetNode $selectionSet */
                            $selectionSet = $definition->selectionSet;
                            foreach ($selectionSet->selections as $selection) {
                                /** @var FieldNode $selection */
                                if (in_array(
                                    $selection->name->value,
                                    $this->registryService->getRegisteredServices()
                                )) {
                                    $this->querySplit[$selection->name->value] = Printer::doPrint(
                                        $selection->selectionSet
                                    );
                                }
                                $this->querySplit[$selection->name->value] = Printer::doPrint($selection->selectionSet);
                            }
                            break;
                        case NodeKind::FRAGMENT_DEFINITION:
                            $this->queryFragments[] = Printer::doPrint($definition);
                            break;
                    }
                }
            }
            $this->sendRequest();
        } catch (\Exception $error) {
            $this->responseService->setError('graphql', $error->getMessage(), $error->getCode());
        }

        return $this->responseService->response();
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function sendRequest()
    {
        foreach ($this->querySplit as $serviceName => $query) {
            try {
                if ($serviceName == Introspection::SCHEMA_FIELD_NAME) {
                    $result = GraphQL::executeQuery($this->schemaService->getSchema(), $this->queryPayload['query']);
                    $schema = $result->toArray();
                    $this->responseService->setData([Introspection::SCHEMA_FIELD_NAME => $schema['data'][Introspection::SCHEMA_FIELD_NAME]]);
                } else {
                    /** @var Response $response */
                    $this->communicatorService->pool(
                        $serviceName,
                        'graphql',
                        [],
                        ['query' => $this->prepareQuery($query), 'variables' => $this->queryPayload['variables']]
                    );
                }
            } catch (\Exception $e) {
                $this->responseService->setError($serviceName, $e->getMessage(), $e->getCode());
            }
        }
        return $this->responseService->setData($this->communicatorService->fetch());
    }

    /**
     * @param $query
     * @return string
     */
    private function prepareQuery($query)
    {
        return $this->queryType.$query.implode(' ', $this->queryFragments);
    }
}