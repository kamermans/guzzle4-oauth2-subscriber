guzzle-oauth2-subscriber
====================

Provides an OAuth2 subscriber for [Guzzle](http://guzzlephp.org/) 4.x and 5.x.

# Attribution #
-----------
This plugin is based on the Guzzle 3.x OAuth2 plugin by Bojan Zivanovic and Damien Tournoud from the [CommerceGuys guzzle-oauth2-plugin repository](https://github.com/commerceguys/guzzle-oauth2-plugin).

I originally forked that project, but moved to a new repo since most of the code has changed and I needed to reset the versions to < 1.0.

# Features #
--------

- Acquires access tokens via one of the supported grant types (code, client credentials,
  user credentials, refresh token). Or you can set an access token yourself.
- Supports refresh tokens (stores them and uses them to get new access tokens).
- Handles token expiration (acquires new tokens and retries failed requests).
- Allows storage and lookup of access tokens via callbacks

# Usage #
-----

This plugin extends Guzzle, transparently adding authentication to outgoing requests and optionally attempting reauthorization if the access token is no longer valid.

There are several grant types available like `PasswordCredentials`, `ClientCredentials` and `AuthorizationCode`.

## Access Token Method ##
If you already have an access token, you can use that to authenticate to a service, but beware that access tokens are meant to expire, and the process of obtaining a new access token is included in this library as well (for example, by using the `PasswordCredentials` method).

Here's how to use an existing access token for the request, thus no re-authorization client is needed:

```php
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

// Setup OAuth
$oauth = new OAuth2Subscriber();

// Manually specify access_token.  When it expires, you will get an exception
$oauth->getTokenData()->accessToken = 'somelongtoken';

$client = new GuzzleHttp\Client();
// Attach OAuth subscriber to the Guzzle client and all URLs will be authenticated
$client->getEmitter()->attach($oauth);
$response = $client->get('http://somehost/some_secure_url');

echo "Status: ".$response->getStatusCode()."\n";
```

## Client Credentials Method ##
Client credentials are normally used in server-to-server authentication.  With this grant type, a client is requesting authorization in its own behalf, so there are only two parties involved.  At a minimum, a `client_id` and `client_secret` are required, although many services require a `scope` and other parameters.

Here's an example of the client credentials method:

```php
use kamermans\GuzzleOAuth2\GrantType\ClientCredentials;
use kamermans\GuzzleOAuth2\OAuth2Subscriber;

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client([
    // URL for access_token request
    'base_url' => 'http://some_host/access_token_request_url',
]);
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
```

