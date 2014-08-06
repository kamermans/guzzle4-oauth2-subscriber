<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;
use kamermans\GuzzleOAuth2\Signer\ClientCredentials\SignerInterface;
use kamermans\GuzzleOAuth2\Exception\ReauthorizationException;

use GuzzleHttp\Collection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Refresh token grant type.
 * @link http://tools.ietf.org/html/rfc6749#section-6
 */
class RefreshToken implements GrantTypeInterface
{
    /** @var ClientInterface The token endpoint client */
    protected $client;

    /** @var Collection Configuration settings */
    protected $config;

    public function __construct(ClientInterface $client = null, $config = null)
    {
        $this->client = $client;
        if ($config) {
            $this->config = Collection::fromConfig($config,
                [
                    'client_secret' => '',
                    'refresh_token' => '',
                    'scope' => '',
                ],
                [
                    'client_id',
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        if (!$this->client || !$this->config) {
            throw new ReauthorizationException('No OAuth reauthorization method was set');
        }

        $postBody = [
            'grant_type' => 'refresh_token',
            // If no refresh token was provided to the method, use the one
            // provided to the constructor.
            'refresh_token' => $refreshToken ?: $this->config['refresh_token'],
        ];
        
        if ($this->config['scope']) {
            $postBody['scope'] = $this->config['scope'];
        }
        
        $request = $this->client->createRequest('POST', null);
        $request->setBody(Utils::arrayToPostBody($this->config));
        $clientCredentialsSigner->sign(
            $request, 
            $this->config['client_id'], 
            $this->config['client_secret']
        );
        $response = $this->client->send($request);

        return new TokenData($response->json());
    }
}
