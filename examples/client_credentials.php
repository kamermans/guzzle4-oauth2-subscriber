<?php
/*
 * OAuth2 client_credentials are used when a client (an application in this case),
 * needs to authenticate on its own behalf.  Since there is no third-party involved
 * the client can simply send the client_id and client_secret to get an access_token.
 *
 * Note that in this example, every time this script is called, a new access_token
 * is requested and used.  It is better to store the access token somewhere so you
 * can use it in future requests.  See client_credentials_persistence.php for example.
 */

use kamermans\GuzzleOAuth2\GrantType\ClientCredentials;
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

require_once __DIR__.'/../vendor/autoload.php';

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client(['base_url' => 'http://some_host/access_token_request_url']);
$reauth_config = [
	"client_id" => "your client id",
	"client_secret" => "your client secret",
	"scope" => "your scope(s)", // optional
	"state" => time(), // optional
];
$grant_type = new ClientCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Subscriber($grant_type);

// This is the normal Guzzle client that you use in your application
$client = new GuzzleHttp\Client();
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
