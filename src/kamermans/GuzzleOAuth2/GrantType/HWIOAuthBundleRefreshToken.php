<?php namespace kamermans\GuzzleOAuth2\GrantType;

use kamermans\GuzzleOAuth2\TokenData;

use Symfony\Component\Security\Core\SecurityContextInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;

/**
 * HWIOAuthBundle Aware Refresh token grant type.
 * @link http://tools.ietf.org/html/rfc6749#section-6
 */
class HWIOAuthBundleRefreshToken implements GrantTypeInterface
{
    /** @var SecurityContextInterface Symfony2 security component */
    protected $securityContext;

    /** @var ResourceOwnerMap         HWIOAuthBundle OAuth2 ResourceOwnerMap */
    protected $resourceOwnerMap;

    public function __construct(ResourceOwnerMap $resourceOwnerMap, SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
        $this->resourceOwnerMap = $resourceOwnerMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenData($refreshToken = null)
    {
        $token = $this->securityContext->getToken();
        $resourceName = $token->getResourceOwnerName();
        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($resourceName);

        $data = $resourceOwner->refreshAccessToken($refreshToken);
        $token->setRawToken($data);
        return new TokenData($data);
    }
}
