<?php namespace kamermans\GuzzleOAuth2\Signer\AccessToken;

use GuzzleHttp\Message\RequestInterface;

interface SignerInterface
{
    public function sign(RequestInterface $request, $accessToken);
}
