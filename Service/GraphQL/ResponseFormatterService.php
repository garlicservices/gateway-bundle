<?php

namespace Garlic\Gateway\Service\GraphQL;

/**
 * Class ResponseFormatterService
 * @package Garlic\Gateway\Service\GraphQL
 */
class ResponseFormatterService
{
    /** @var array */
    private $errors = [];
    /** @var array */
    private $data = [];

    /**
     * @param string $service
     * @param $data
     */
    public function setData(string $service, $data)
    {
        $this->data[$service][] = $data;
    }

    /**
     * @param string $service
     * @param string $message
     * @param int $code
     */
    public function setError(string $service, string $message, $code = 400)
    {
        $this->errors[$service][] = [
            'message' => $message,
            'code' => $code
        ];
    }

    /**
     * @return array
     */
    public function response()
    {
        return [
            'data' => $this->data,
            'errors' => $this->errors
        ];
    }
}