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
     * Set service response data
     *
     * @param $service
     * @param $data
     */
    public function setData($service, $data)
    {
        if ($data != null && $service != Introspection::SCHEMA_FIELD_NAME) {
            if (isset($data['data'])) {
                $this->data[$service] = $data['data'];
            }
            if (isset($data['errors'])) {
                foreach ($data['errors'] as $error) {
                    $message = isset($error['message']) ? $error['message'] : '';
                    $code = isset($error['statusCode']) ? $error['statusCode'] : 400;
                    $this->setError($service, $message, $code);
                }
            }
        } else {
            $this->data[$service] = $data;
        }
    }

    /**
     * Set service error
     *
     * @param string $service
     * @param string $message
     * @param int $code
     */
    public function setError(string $service, string $message, $code = 400)
    {
        $this->errors[] = [
            'message' => $message,
            'statusCode' => $code,
            'path' => [$service],
        ];
    }

    /**
     * Get query response
     *
     * @return array
     */
    public function response()
    {
        return [
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}