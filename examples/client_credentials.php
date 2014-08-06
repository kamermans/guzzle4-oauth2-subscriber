<?php
/*
 * OAuth2 client_credentials are used when a client (an application in this case),
 * needs to authenticate on its own behalf.  Since there is no third-party involved
 * the client can simply send the client_id and client_secret to get an access_token.
 */

use kamermans\GuzzleOAuth2\GrantType\ClientCredentials;
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

require_once __DIR__.'/../vendor/autoload.php';

// Setup OAuth
$oauth = new OAuth2Subscriber(new ClientCredentials());

// Manually specify access_token.  When it expires, you will get an exception
$oauth->getTokenData()->accessToken = 'somelongtoken';

$client = new GuzzleHttp\Client();
// Attach OAuth subscriber to the Guzzle client and all URLs will be authenticated
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
