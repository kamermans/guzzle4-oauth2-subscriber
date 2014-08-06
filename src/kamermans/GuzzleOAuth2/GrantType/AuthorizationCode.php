<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;

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

    public function __construct(ClientInterface $client, $config)
    {
        $this->client = $client;
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

    /**
     * {@inheritdoc}
     */
    public function getTokenData()
    {
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

        $response = $this->client->post(null, [
            'body' => $postBody,
            'auth' => [$this->config['client_id'], $this->config['client_secret']],
        ]);
        
        return new TokenData($response->json());
    }
}
