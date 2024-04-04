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

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};
use function sprintf;

/**
 * Imgur OAuth2
 *
 * Note: imgur sends an "expires_in" of 315360000 (10 years!) for access tokens,
 *       but states in the docs that tokens expire after one month.
 *       Either manually saving the expiry with the token to trigger auto refresh
 *       or manually refreshing via the refreshAccessToken() method is required.
 *
 * @see https://apidocs.imgur.com/
 */
class Imgur extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	protected string      $authURL        = 'https://api.imgur.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://api.imgur.com/oauth2/token';
	protected string      $apiURL         = 'https://api.imgur.com';
	protected string|null $userRevokeURL  = 'https://imgur.com/account/settings/apps';
	protected string|null $apiDocs        = 'https://apidocs.imgur.com';
	protected string|null $applicationURL = 'https://api.imgur.com/oauth2/addclient';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
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
