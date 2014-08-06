<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;

use GuzzleHttp\Collection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Resource owner password credentials grant type.
 * @link http://tools.ietf.org/html/rfc6749#section-4.3
 */
class PasswordCredentials implements GrantTypeInterface
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
            ],
            [
                'client_id',
                'username',
                'password',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenData()
    {
        $postBody = [
            'grant_type' => 'password',
            'username' => $this->config['username'],
            'password' => $this->config['password'],
        ];
        
        if ($this->config['scope']) {
            $postBody['scope'] = $this->config['scope'];
        }

        $response = $this->client->post(null, [
            'body' => $postBody,
            'auth' => [$this->config['client_id'], $this->config['client_secret']],
        ]);
        
        return new TokenData($response->json());
    }
}
