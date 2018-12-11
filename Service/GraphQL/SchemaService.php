<?php

namespace Garlic\Gateway\Service\GraphQL;

use Garlic\Gateway\Service\ServiceRegistry;
use GraphQL\Type\Schema;
use Ola\Tools\BuildClientSchema;
use Ola\Tools\MergeSchemas;

/**
 * Class SchemaService
 * @package Garlic\Gateway\Service\GraphQL
 */
class SchemaService
{
    /**
     * @var ServiceRegistry
     */
    private $serviceRegistry;
    
    /**
     * SchemaService constructor.
     * @param ServiceRegistry $serviceRegistry
     */
    public function __construct(ServiceRegistry $serviceRegistry)
    {
        $this->serviceRegistry = $serviceRegistry;
    }
    
    /**
     * Returns whole schema of all registered services
     *
     * @return Schema
     * @throws \Exception
     */
    public function getSchema(): Schema
    {
        return $this->makeSchema(
            $this->serviceRegistry->getRegisteredServices()
        );
    }
    
    /**
     * Generates schema for all registered services
     *
     * @param array $serviceNames
     * @return mixed
     * @throws \Exception
     */
    private function makeSchema(array $serviceNames)
    {
        $serviceSchemas = [];
        foreach ($serviceNames as $name) {
            $serviceSchemas[$name] = $this->getClientSchema($name);
        }
        
        return $this->mergeSchemas($serviceSchemas);
    }
    
    /**
     * Merge multipluservice schemas to big one
     *
     * @param array $schemas
     * @return mixed
     * @throws \Exception
     */
    private function mergeSchemas(array $schemas)
    {
        return MergeSchemas::mergeSchemas(
            $schemas,
            false
        );
    }
    
    /**
     * Get Introspection query and generate schema file based on it
     *
     * @param string $clientName
     * @return Schema
     */
    private function getClientSchema(string $clientName): Schema
    {
        // TODO: Create solution for getting and storing service schemas instead of test files
        
        $source = file_get_contents(dirname(dirname(dirname(__FILE__)))."/Resources/$clientName.json");
        
        return BuildClientSchema::build(json_decode($source)->data);
    }

}