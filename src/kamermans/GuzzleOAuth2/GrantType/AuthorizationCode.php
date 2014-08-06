<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;
use kamermans\GuzzleOAuth2\Signer\ClientCredentials\SignerInterface;
use kamermans\GuzzleOAuth2\Exception\ReauthorizationException;

use GuzzleHttp\Collection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Authorization code grant type.
 * @link http://tools.ietf.org/html/rfc6749#section-4.1
 */
class AuthorizationCode implements GrantTypeInterface
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
                    'scope' => '',
                    'redirect_uri' => '',
                ], 
                [
                    'client_id', 
                    'code',
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenData(SignerInterface $clientCredentialsSigner)
    {
        if (!$this->client || !$this->config) {
            throw new ReauthorizationException('No OAuth reauthorization method was set');
        }

        $postBody = [
            'grant_type' => 'authorization_code',
            'code' => $this->config['code'],
        ];

        if ($this->config['scope']) {
            $postBody['scope'] = $this->config['scope'];
        }

        if ($this->config['redirect_uri']) {
            $postBody['redirect_uri'] = $this->config['redirect_uri'];
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
