<?php
/**
 * Class Pinterest
 *
 * @created      07.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};
use function sprintf;

/**
 * Pinterest OAuth2
 *
 * @link https://developers.pinterest.com/docs/getting-started/authentication/
 */
class Pinterest extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'PINTEREST';

	public const SCOPE_ADS_READ            = 'ads:read';
	public const SCOPE_ADS_WRITE           = 'ads:write';
	public const SCOPE_BOARDS_READ         = 'boards:read';
	public const SCOPE_BOARDS_READ_SECRET  = 'boards:read_secret';
	public const SCOPE_BOARDS_WRITE        = 'boards:write';
	public const SCOPE_BOARDS_WRITE_SECRET = 'boards:write_secret';
	public const SCOPE_CATALOGS_READ       = 'catalogs:read';
	public const SCOPE_CATALOGS_WRITE      = 'catalogs:write';
	public const SCOPE_PINS_READ           = 'pins:read';
	public const SCOPE_PINS_READ_SECRET    = 'pins:read_secret';
	public const SCOPE_PINS_WRITE          = 'pins:write';
	public const SCOPE_PINS_WRITE_SECRET   = 'pins:write_secret';
	public const SCOPE_USER_ACCOUNTS_READ  = 'user_accounts:read';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BOARDS_READ,
		self::SCOPE_PINS_READ,
		self::SCOPE_USER_ACCOUNTS_READ,
	];

	public const USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST = true;

	protected string      $authorizationURL = 'https://www.pinterest.com/oauth/';
	protected string      $accessTokenURL   = 'https://api.pinterest.com/v5/oauth/token';
	protected string      $apiURL           = 'https://api.pinterest.com';
	protected string|null $apiDocs          = 'https://developers.pinterest.com/docs/';
	protected string|null $applicationURL   = 'https://developers.pinterest.com/apps/';
	protected string|null $userRevokeURL    = 'https://www.pinterest.com/settings/security';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v5/user_account');

		$userdata = [
			'data'   => $json,
			'avatar' => $json['profile_image'],
			'handle' => $json['username'],
			'id'     => $json['id'],
			'url'    => sprintf('https://www.pinterest.com/%s/', $json['username']),
		];

		return new AuthenticatedUser($userdata);
	}

}
