<?php
/**
 * Class DummyOAuth1Provider
 *
 * @created      16.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\OAuth\Core\{AccessToken, OAuth1Provider, TokenInvalidate};

/**
 * An OAuth1 provider implementation
 */
final class DummyOAuth1Provider extends OAuth1Provider implements TokenInvalidate{

	public const IDENTIFIER = 'DUMMYOAUTH1PROVIDER';

	public const HEADERS_AUTH = ['foo' => 'bar'];
	public const HEADERS_API  = ['foo' => 'bar'];

	protected string      $authorizationURL = 'https://example.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://example.com/oauth/access_token';
	protected string      $requestTokenURL  = 'https://example.com/oauth/request_token';
	protected string      $revokeURL        = 'https://example.com/oauth/revoke';
	protected string      $apiURL           = 'https://api.sub.example.com';
	protected string|null $userRevokeURL    = 'https://account.example.com/apps/';

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
