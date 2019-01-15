<?php

namespace Garlic\Gateway\Service\GraphQL;

use Garlic\Gateway\Service\ServiceRegistry;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Ola\GraphQL\Tools\BuildClientSchema;
use Ola\Tools\MergeInfo;
use Ola\Tools\MergeSchemas;
use Symfony\Component\HttpKernel\Config\FileLocator;
use SymfonyBundles\RedisBundle\Redis\ClientInterface;

class SchemaService
{
    protected $schemaKey = '_garlic_schema';

    /**
     * @var ServiceRegistry
     */
    private $serviceRegistry;
    /**
     * @var ClientInterface
     */
    private $redis;
    private $fileLocator;

    /**
     * SchemaService constructor.
     * @param ServiceRegistry $serviceRegistry
     * @param ClientInterface $redis
     * @param FileLocator $fileLocator
     */
    public function __construct(
        ServiceRegistry $serviceRegistry,
        ClientInterface $redis,
        FileLocator $fileLocator
    ) {
        $this->serviceRegistry = $serviceRegistry;
        $this->redis = $redis;
        $this->fileLocator = $fileLocator;
    }

    /**
     * Returns whole schema of all registered services
     *
     * @return Schema
     * @throws \Exception
     */
    public function getSchema(): Schema
    {
        return BuildClientSchema::build(\json_decode($this->redis->get($this->getSchemaKey()))->data);
    }

    /**
     * Generates big schema for all registered services
     *
     * @return mixed
     * @throws \Exception
     */
    public function rebuildSchema()
    {
        $serviceNames = $this->serviceRegistry->getRegisteredServices();
        $serviceSchemas = [];
        foreach ($serviceNames as $name) {
            $serviceSchemas[$name] = $this->getClientSchema($name);
        }

        return $this->mergeSchemas($serviceSchemas);
    }

    /**
     * Merge service schemas to big one
     *
     * @param array $schemas
     * @return mixed
     * @throws \Exception
     */
    private function mergeSchemas(array $schemas)
    {
        $finalSchema = MergeSchemas::mergeSchemas(
            $schemas,
            false
        );

        $result = GraphQL::executeQuery(
            $finalSchema,
            file_get_contents(
                $this->fileLocator->locate('@HealthCheckBundle/Resources/query/introspection.graphql')
            )
        );

        $json = \json_encode($result->toArray());

        $this->redis->set($this->getSchemaKey(), $json);

        return $result;
    }

    /**
     * Get Introspection query and generate schema file based on it
     *
     * @param string $clientName
     * @return Schema
     */
    private function getClientSchema(string $clientName): Schema
    {
        $payload = $this->redis->get($clientName);

        return BuildClientSchema::build(json_decode($payload)->data);
    }

    /**
     * Schema key to be stored in Redis
     *
     * @return string
     */
    public function getSchemaKey()
    {
        return $this->schemaKey;
    }
}