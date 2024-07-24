<?php
/**
 * Class Imgur
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};
use function sprintf, time;

/**
 * Imgur OAuth2
 *
 * Note: imgur sends an "expires_in" of 315360000 (10 years!) for access tokens,
 *       but states in the docs that tokens expire after one month.
 *
 * @link https://apidocs.imgur.com/
 */
class Imgur extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'IMGUR';

	protected string      $authorizationURL = 'https://api.imgur.com/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://api.imgur.com/oauth2/token';
	protected string      $apiURL           = 'https://api.imgur.com';
	protected string|null $userRevokeURL    = 'https://imgur.com/account/settings/apps';
	protected string|null $apiDocs          = 'https://apidocs.imgur.com';
	protected string|null $applicationURL   = 'https://api.imgur.com/oauth2/addclient';

	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		$this->checkState($state);

		$body     = $this->getAccessTokenRequestBodyParams($code);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $body);
		$token    = $this->parseTokenResponse($response);

		// set the expiry to a sane period to allow auto-refreshing
		$token->expires = (time() + 2592000); // 30 days

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/3/account/me');

		$userdata = [
			'data'   => $json,
			'avatar' => $json['data']['avatar'],
			'handle' => $json['data']['url'],
			'id'     => $json['data']['id'],
			'url'    => sprintf('https://imgur.com/user/%s', $json['data']['url']),
		];

		return new AuthenticatedUser($userdata);
	}

}
