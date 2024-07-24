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

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, CSRFToken, OAuth2Provider, PAR, PKCE, TokenInvalidate, TokenRefresh};

/**
 * An OAuth2 provider implementation that supports token refresh, csrf tokens and client credentials
 */
final class DummyOAuth2Provider extends OAuth2Provider
	implements ClientCredentials, CSRFToken, PAR, PKCE, TokenRefresh, TokenInvalidate{

	public const IDENTIFIER = 'DUMMYOAUTH2PROVIDER';

	public const AUTH_METHOD  = self::AUTH_METHOD_QUERY;
	public const HEADERS_AUTH = ['foo' => 'bar'];
	public const HEADERS_API  = ['foo' => 'bar'];

	protected string      $authorizationURL    = 'https://example.com/oauth2/authorize';
	protected string      $accessTokenURL      = 'https://example.com/oauth2/token';
	protected string      $revokeURL           = 'https://example.com/oauth2/revoke';
	protected string      $apiURL              = 'https://api.example.com/';
	protected string|null $userRevokeURL       = 'https://account.example.com/apps/';
	protected string      $parAuthorizationURL = 'https://example.com/oauth2/par';

	public function invalidateAccessToken(AccessToken|null $token = null, string|null $type = null):bool{

		if($token === null){
			$tokenToInvalidate = $this->storage->getAccessToken($this->name); // phpcs:ignore
		}

		// ... prepare request with body etc

		$response = $this->request($this->revokeURL);

		if($response->getStatusCode() === 200){

			if($token === null){
				$this->storage->clearAccessToken($this->name);
			}

			return true;
		}

		return false;
	}

}
