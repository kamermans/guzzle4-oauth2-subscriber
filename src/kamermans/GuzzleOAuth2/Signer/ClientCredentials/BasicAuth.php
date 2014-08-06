<?php namespace kamermans\GuzzleOAuth2\Signer\ClientCredentials;

use GuzzleHttp\Message\RequestInterface;

class BasicAuth implements SignerInterface
{

    public function sign(RequestInterface $request, $client_id, $client_secret) 
    {
        $request->getConfig()->set('auth', 'basic');
        $request->setHeader('Authorization', 'Basic '.base64_encode("$client_id:$client_secret"));
    }
}
