<?php namespace kamermans\GuzzleOAuth2\Signer\ClientCredentials;

use GuzzleHttp\Message\RequestInterface;

class PostFormData implements SignerInterface
{
    protected $client_id_field;
    protected $client_secret_field;

    public function __construct($client_id_field='client_id', $client_secret_field='client_secret')
    {
        $this->client_id_field = $client_id_field;
        $this->client_secret_field = $client_secret_field;
    }

    public function sign(RequestInterface $request, $client_id, $client_secret) 
    {
        $body = $request->getBody();
        $body->setField($this->client_id_field, $client_id);
        $body->setField($this->client_secret_field, $client_secret);
    }
}
