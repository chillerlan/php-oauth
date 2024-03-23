<?php
/**
 * Class DummyOAuth2Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh};
use chillerlan\OAuth\Providers\ProviderException;

/**
 * An OAuth2 provider implementation that supports token refresh, csrf tokens and client credentials
 */
final class DummyOAuth2Provider extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh, TokenInvalidate{

	public const AUTH_METHOD  = self::AUTH_METHOD_QUERY;
	public const HEADERS_AUTH = ['foo' => 'bar'];
	public const HEADERS_API  = ['foo' => 'bar'];

	protected string      $authURL        = 'https://example.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://example.com/oauth2/token';
	protected string      $revokeURL      = 'https://example.com/oauth2/revoke';
	protected string      $apiURL         = 'https://api.example.com/';
	protected string|null $userRevokeURL  = 'https://account.example.com/apps/';

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken $token = null):bool{

		if($token === null && !$this->storage->hasAccessToken($this->serviceName)){
			throw new ProviderException('no token given');
		}

		$response = $this->request($this->revokeURL);

		if($response->getStatusCode() === 200){
			$this->storage->clearAccessToken($this->serviceName);

			return true;
		}

		return false;
	}

}
