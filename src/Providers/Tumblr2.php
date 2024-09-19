<?php
/**
 * Class Tumblr2
 *
 * @created      30.07.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AuthenticatedUser, ClientCredentials, ClientCredentialsTrait, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo,
};
use function sprintf;

/**
 * Tumblr OAuth2
 *
 * @link https://www.tumblr.com/docs/en/api/v2#oauth2-authorization
 */
class Tumblr2 extends OAuth2Provider implements CSRFToken, TokenRefresh, ClientCredentials, UserInfo{
	use ClientCredentialsTrait;

	public const IDENTIFIER = 'TUMBLR2';

	public const SCOPE_BASIC          = 'basic';
	public const SCOPE_WRITE          = 'write';
	public const SCOPE_OFFLINE_ACCESS = 'offline_access';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC,
		self::SCOPE_WRITE,
		self::SCOPE_OFFLINE_ACCESS,
	];

	protected string      $authorizationURL = 'https://www.tumblr.com/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://api.tumblr.com/v2/oauth2/token';
	protected string      $apiURL           = 'https://api.tumblr.com';
	protected string|null $userRevokeURL    = 'https://www.tumblr.com/settings/apps';
	protected string|null $apiDocs          = 'https://www.tumblr.com/docs/en/api/v2';
	protected string|null $applicationURL   = 'https://www.tumblr.com/oauth/apps';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v2/user/info');

		$userdata = [
			'data'   => $json,
			'handle' => $json['response']['user']['name'],
			'url'    => sprintf('https://www.tumblr.com/%s', $json['response']['user']['name']),
		];

		return new AuthenticatedUser($userdata);
	}

}
