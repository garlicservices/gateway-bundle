<?php

namespace Garlic\Gateway\Service;

/**
 * Class ServiceRegistry
 * @package Garlic\Gateway\Service
 */
class ServiceRegistry
{
    /**
     * Return list of registered services names
     *
     * @return array
     */
    public function getRegisteredServices(): array
    {
        return [
            'template',
        ];
    }
}