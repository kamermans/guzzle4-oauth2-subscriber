<?php
/*
 * OAuth2 client_credentials are used when a client (an application in this case),
 * needs to authenticate on its own behalf.  Since there is no third-party involved
 * the client can simply send the client_id and client_secret to get an access_token.
 */

use kamermans\GuzzleOAuth2\GrantType\ClientCredentials;
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

require_once __DIR__.'/../vendor/autoload.php';

$reauth_client = new GuzzleHttp\Client(['base_url' => 'http://some_host/access_token_request_url']);
$reauth_config = [
	"client_id" => "your client id",
	"client_secret" => "your client secret",
	"scope" => "your scope(s)", // optional
	"state" => time(), // optional
];
$grant_type = new ClientCredentials($reauth_client, $reauth_config);
$oauth = new OAuth2Subscriber($grant_type);

// This callback will be used to save, retreive and delete access keys
$oauth->tokenPersistence(function($action, $data) {
	$persist_file = __DIR__.'/oauth_token.key';
	switch ($action) {
		case 'get':
			return file_exists($persist_file)? unserialize(file_get_contents($persist_file)): false;
			break;
		case 'set':
			file_put_contents($persist_file, serialize($data));
			break;
		case 'delete':
			unlink($persist_file);
			break;
	}
	return true;
});

$client = new GuzzleHttp\Client();
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
