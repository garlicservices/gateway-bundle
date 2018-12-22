<?php

namespace Garlic\Gateway\Service\GraphQL;

use GraphQL\Type\Introspection;
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
     * @param $data
     */
    public function setData($data)
    {
        foreach ($data as $service => $response) {
            if ($response != null  && $service != Introspection::SCHEMA_FIELD_NAME) {
                if (isset($response['data'])) {
                    $this->data[$service] = $response['data'];
                }
                if(isset($response['errors'])) {
                    $this->errors[$service] = $response['errors'];
                }
            } else {
                $this->data[$service] = $response;
            }
        }
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