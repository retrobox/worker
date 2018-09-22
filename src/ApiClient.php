<?php

namespace App;

use Httper\Client;
use Httper\Request;
use Httper\Response;
use Httper\Uri;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private $masterApiKey;
    private $endpoint = "http://api.example.com";
    /**
     * @var Client
     */
    private $client;

    public function __construct($endpoint, $masterApiKey)
    {
        $this->masterApiKey = $masterApiKey;
        $this->endpoint = $endpoint;
        $this->client = new Client();
    }

    /**
     * @param $json
     * @return Response
     */
    public function graphQL($json): Response
    {
        return $this->client->request((new Request())
            ->withMethod('POST')
            ->withUri(new Uri($this->endpoint . '/graphql'))
            ->withHeader('Authorization', "Bearer {$this->masterApiKey}")
            ->withJson($json)
        );
    }
}