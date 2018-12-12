# Gateway bundle

The gateway bundle adds support of merging multiply GraphQL schemas to single structure and handle requests to single microservices. 

Features include:
- Documentation from multiply microservices in single place
- Validation on the gateway layer
- Split and process requests for single microservice

Installation
------------


With [composer](https://getcomposer.org), require:

`composer require garlic/gateway`

Usage
-----

```php 
class DefaultController extends AbstractController
{
    private $queryProcessor;
    /**
     * DefaultController constructor.
     * @param QueryProcessorService $queryProcessor
     */
    public function __construct(QueryProcessorService $queryProcessor)
    {
        $this->queryProcessor = $queryProcessor;
    }

    public function index()
    {
        try {
            $output = $this->queryProcessor->processQuery();
            $code = JsonResponse::HTTP_OK;
        } catch (\Exception $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
            $code = JsonResponse::HTTP_BAD_REQUEST;
        }

        return new JsonResponse(
            $output,
            $code
        );
    }
}

```
