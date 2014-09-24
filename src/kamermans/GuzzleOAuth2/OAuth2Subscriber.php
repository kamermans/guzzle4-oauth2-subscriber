<?php namespace kamermans\GuzzleOAuth2;

use kamermans\GuzzleOAuth2\GrantType\GrantTypeInterface;
use kamermans\GuzzleOAuth2\Signer\AccessToken\SignerInterface as AccessTokenSigner;
use kamermans\GuzzleOAuth2\Signer\ClientCredentials\SignerInterface as ClientCredentialsSigner;
use kamermans\GuzzleOAuth2\Signer\AccessToken\BasicAuth as AccessTokenBasicAuth;
use kamermans\GuzzleOAuth2\Signer\ClientCredentials\BasicAuth as ClientCredentialsBasicAuth;
use kamermans\GuzzleOAuth2\Exception\AccessTokenRequestException;
use kamermans\GuzzleOAuth2\Exception\RefreshTokenRequestException;

use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Common\EmitterInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Message\RequestInterface;

/**
 * OAuth2 plugin
 * @link http://tools.ietf.org/html/rfc6749
 */
class OAuth2Subscriber implements SubscriberInterface
{

    /** @var GrantTypeInterface The grant type implementation used to acquire access tokens */
    protected $grantType;

    /** @var GrantTypeInterface The grant type implementation used to refresh access tokens */
    protected $refreshTokenGrantType;

    protected $accessTokenSigner;

    protected $clientCredentialsSigner;

    protected $tokenData;

    protected $tokenPersistence;

    /**
     * Create a new Oauth2 plugin
     */
    public function __construct(
                                GrantTypeInterface $grantType = null, 
                                GrantTypeInterface $refreshTokenGrantType = null,
                                ClientCredentialsSigner $clientCredentialsSigner = null,
                                AccessTokenSigner $accessTokenSigner = null
                                )
    {
        $this->grantType = $grantType;
        $this->refreshTokenGrantType = $refreshTokenGrantType;
        $this->clientCredentialsSigner = $clientCredentialsSigner?: new ClientCredentialsBasicAuth();
        $this->accessTokenSigner = $accessTokenSigner?: new AccessTokenBasicAuth();
    }

    public function setAccessTokenSigner(AccessTokenSigner $signer) 
    {
        $this->accessTokenSigner = $signer;
    }

    public function setClientCredentialsSigner(ClientCredentialsSigner $signer) 
    {
        $this->clientCredentialsSigner = $signer;
    }

    public function tokenPersistence(callable $callback)
    {
        $this->tokenPersistence = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            'before' => ['onBefore', RequestEvents::VERIFY_RESPONSE + 100],
            'error'  => ['onError', RequestEvents::EARLY - 100],
        ];
    }

   /**
     * Request before-send event handler.
     *
     * Adds the Authorization header if an access token was found.
     *
     * @param BeforeEvent $event Event received
     */
    public function onBefore(BeforeEvent $event)
    {
        $this->checkTokenData();

        if ($this->tokenData->accessToken) {
            $this->accessTokenSigner->sign($event->getRequest(), $this->tokenData->accessToken);
        }
    }

    /**
      * Request error event handler.
      *
      * Handles unauthorized errors by acquiring a new access token and
      * retrying the request.
      *
      * @param ErrorEvent $event Event received
      */
    public function onError(ErrorEvent $event)
    {
        if (!$event->getResponse() || $event->getResponse()->getStatusCode() == 401) {
            if ($event->getRequest()->getHeader('X-Guzzle-Retry')) {
                // We already retried once, give up.
                return;
            }

            // Acquire a new access token, and retry the request.
            $this->acquireAccessToken();
            if ($this->tokenData->accessToken) {
                $newRequest = clone $event->getRequest();
                $this->accessTokenSigner->sign($newRequest, $this->tokenData->accessToken);
                $newRequest->setHeader('X-Guzzle-Retry', '1');
                $event->intercept(
                    $event->getClient()->send($newRequest)
                );
            }
        }
    }

    public function getTokenData()
    {
        if ($this->tokenData === null) {
            $this->tokenData = new TokenData();
        }
        return $this->tokenData;
    }

    protected function checkTokenData()
    {
        if ($this->tokenData === null) {
            // Try to restore the access token from persistence
            if (!$this->restoreTokenData()) {
                $this->tokenData = new TokenData();
            }
        }

        if ($this->tokenData->isExpired()) {
            // The access token has expired.
            $this->deleteTokenData();
            $this->tokenData = new TokenData();
        }

        if (!$this->tokenData->accessToken) {
            // Try to acquire a new access token from the server.
            $this->acquireAccessToken();
        }
    }

    protected function restoreTokenData()
    {
        if (!is_callable($this->tokenPersistence)) {
            return false;
        }

        $func = $this->tokenPersistence;
        $data = $func('get', null);
        if ($data instanceof TokenData) {
            $this->tokenData = $data;
            return true;
        }

        return false;
    }

    protected function saveTokenData()
    {
        if (!is_callable($this->tokenPersistence)) {
            return false;
        }

        $func = $this->tokenPersistence;
        $func('set', $this->tokenData);
        return true;
    }

    protected function deleteTokenData()
    {
        if (!is_callable($this->tokenPersistence)) {
            return false;
        }

        $func = $this->tokenPersistence;
        $func('delete', $this->tokenData);
        return true;
    }

    /**
     * Acquire a new access token from the server.
     *
     * @throws kamermans\GuzzleOAuth2\Exception\RefreshTokenRequestException
     * @throws kamermans\GuzzleOAuth2\Exception\AccessTokenRequestException
     */
    protected function acquireAccessToken()
    {
        if ($this->refreshTokenGrantType && $this->tokenData->refreshToken) {
            try {
                // Get an access token using the stored refresh token.
                $newTokenData = $this->refreshTokenGrantType->getTokenData($this->clientCredentialsSigner, $this->tokenData->refreshToken);
            } catch (BadResponseException $e) {
                // The refresh token has probably expired.
                $this->tokenData->refreshToken = null;

                // Let this fall through instead of failing since the access_token may still be valid
                //throw new RefreshTokenRequestException("Refresh token was invalid and attempt to reauthorize failed", $e);
            }
        }
        if ($this->grantType && !isset($newTokenData)) {
            try {
                // Get a new access token.
                $newTokenData = $this->grantType->getTokenData($this->clientCredentialsSigner);
            } catch (BadResponseException $e) {
                throw new AccessTokenRequestException("Access token was invalid and attempt to reauthorize failed", $e);
            }
        }

        if (isset($newTokenData)) {
            $this->tokenData = $newTokenData;
            $this->saveTokenData();
        } else {
            $this->tokenData = new TokenData();
        }
    }
}
