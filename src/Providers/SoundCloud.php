<?php
/**
 * Class SoundCloud
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, ClientCredentialsTrait, OAuth2Provider, TokenRefresh, UserInfo};

/**
 * SoundCloud OAuth2
 *
 * @link https://developers.soundcloud.com/
 * @link https://developers.soundcloud.com/docs/api/guide#authentication
 * @link https://developers.soundcloud.com/blog/security-updates-api
 */
class SoundCloud extends OAuth2Provider implements ClientCredentials, TokenRefresh, UserInfo{
	use ClientCredentialsTrait;

	public const IDENTIFIER = 'SOUNDCLOUD';

	public const SCOPE_NONEXPIRING      = 'non-expiring';
#	public const SCOPE_EMAIL            = 'email'; // ???

	public const DEFAULT_SCOPES = [
		self::SCOPE_NONEXPIRING,
	];

	public const AUTH_PREFIX_HEADER = 'OAuth';

	protected string      $authorizationURL = 'https://api.soundcloud.com/connect';
	protected string      $accessTokenURL   = 'https://api.soundcloud.com/oauth2/token';
	protected string      $apiURL           = 'https://api.soundcloud.com';
	protected string|null $userRevokeURL    = 'https://soundcloud.com/settings/connections';
	protected string|null $apiDocs          = 'https://developers.soundcloud.com/';
	protected string|null $applicationURL   = 'https://soundcloud.com/you/apps';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar_url'],
			'handle'      => $json['username'],
			'displayName' => $json['full_name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
			'url'         => $json['permalink_url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
