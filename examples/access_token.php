<?php
/*
 * You can authenticate directly to an OAuth-secured URL by
 * providing an access_token, assuming you already have one
 */

use kamermans\GuzzleOAuth2\OAuth2Subscriber;

require_once __DIR__.'/../vendor/autoload.php';

// Setup OAuth
$oauth = new OAuth2Subscriber();

// Manually specify access_token.  When it expires, you will get an exception
$oauth->getTokenData()->accessToken = 'somelongtoken';

$client = new GuzzleHttp\Client();
// Attach OAuth subscriber to the Guzzle client and all URLs will be authenticated
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
