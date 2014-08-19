<?php
/*
 * Obtain an access_token by using a username, password and a 
 * client ID/secret pair, then use the access_token in requests
 */

use kamermans\GuzzleOAuth2\GrantType\PasswordCredentials;
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

require_once __DIR__.'/../vendor/autoload.php';

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client(['base_url' => 'http://some_host/access_token_request_url']);
$reauth_config = [
	"client_id" => "your client id",
	"client_secret" => "your client secret",
	"username" => "your username",
	"password" => "your password",
];
$grant_type = new PasswordCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Subscriber($grant_type);

// This is the normal Guzzle client that you use in your application
$client = new GuzzleHttp\Client();
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
