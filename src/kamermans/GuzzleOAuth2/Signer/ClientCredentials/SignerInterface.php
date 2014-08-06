<?php namespace kamermans\GuzzleOAuth2\Signer\ClientCredentials;

use GuzzleHttp\Message\RequestInterface;

interface SignerInterface
{
    public function sign(RequestInterface $request, $client_id, $client_secret);
}
