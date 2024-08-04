<?php
/**
 * Class Mixcloud
 *
 * @created      28.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth2Provider, UserInfo};

/**
 * Mixcloud OAuth2
 *
 * note: a missing slash at the end of the path will end up in a HTTP/301
 *
 * @link https://www.mixcloud.com/developers/
 */
class Mixcloud extends OAuth2Provider implements UserInfo{

	public const IDENTIFIER = 'MIXCLOUD';

	public const AUTH_METHOD = self::AUTH_METHOD_QUERY;

	protected string      $authorizationURL = 'https://www.mixcloud.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://www.mixcloud.com/oauth/access_token';
	protected string      $apiURL           = 'https://api.mixcloud.com';
	protected string|null $userRevokeURL    = 'https://www.mixcloud.com/settings/applications/';
	protected string|null $apiDocs          = 'https://www.mixcloud.com/developers/';
	protected string|null $applicationURL   = 'https://www.mixcloud.com/developers/create/';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		// mixcloud sends "Content-Type: text/javascript" for JSON content (????)
		$json = $this->getMeResponseData('/me/');

		$userdata = [
			'data'   => $json,
			'avatar' => $json['pictures']['extra_large'],
			'handle' => $json['username'],
			'url'    => $json['url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
