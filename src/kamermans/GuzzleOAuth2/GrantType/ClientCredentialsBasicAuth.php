<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;
use kamermans\GuzzleOAuth2\Exception\ReauthorizationRequestException;

use GuzzleHttp\Collection;
use GuzzleHttp\ClientInterface;
use GuzzleHttpException\RequestException;

/**
 * Client credentials grant type.
 * @link http://tools.ietf.org/html/rfc6749#section-4.4
 */
class ClientCredentialsBasicAuth implements GrantTypeInterface
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
    public function getTokenData()
    {
        if (!$this->client || !$this->config) {
            throw new ReauthorizationRequestException('No OAuth reauthorization method was set');
        }

        $postBody = [
            'grant_type' => 'client_credentials',
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
