# Gateway bundle

The gateway bundle adds support of merging multiply GraphQL schemas to single structure and handle requests to single microservices. 

Features include:
- Documentation from multiply microservices in single place
- Validation on the gateway layer
- Split and process requests for multiply microservices
- Service discovery

Installation
------------


With [composer](https://getcomposer.org), require:

`composer require garlic/gateway`

Usage
-----

Just install latest version of gateway bundle and add environment variables REDIS_HOST, REDIS_PORT to .env file to
schema caching. 
Gateway service will be available at "/main" route.