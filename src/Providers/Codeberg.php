<?php
/**
 * Class Codeberg
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, PKCE, PKCETrait, TokenRefresh, UserInfo};
use function sprintf;

/**
 * Codeberg OAuth2
 *
 * @link https://forgejo.org/docs/latest/user/oauth2-provider/
 * @link https://forgejo.org/docs/latest/user/token-scope/
 * @link https://codeberg.org/api/swagger
 */
class Codeberg extends OAuth2Provider implements CSRFToken, PKCE, TokenRefresh, UserInfo{
	use PKCETrait;

	public const IDENTIFIER = 'CODEBERG';

	public const SCOPE_ACTIVITYPUB        = 'activitypub';
	public const SCOPE_ACTIVITYPUB_READ   = 'read:activitypub';
	public const SCOPE_ACTIVITYPUB_WRITE  = 'write:activitypub';
	public const SCOPE_ADMIN              = 'admin';
	public const SCOPE_ADMIN_READ         = 'read:admin';
	public const SCOPE_ADMIN_WRITE        = 'write:admin';
	public const SCOPE_ISSUE              = 'issue';
	public const SCOPE_ISSUE_READ         = 'read:issue';
	public const SCOPE_ISSUE_WRITE        = 'write:issue';
	public const SCOPE_MISC               = 'misc';
	public const SCOPE_MISC_READ          = 'read:misc';
	public const SCOPE_MISC_WRITE         = 'write:misc';
	public const SCOPE_NOTIFICATION       = 'notification';
	public const SCOPE_NOTIFICATION_READ  = 'read:notification';
	public const SCOPE_NOTIFICATION_WRITE = 'write:notification';
	public const SCOPE_ORGANIZATION       = 'organization';
	public const SCOPE_ORGANIZATION_READ  = 'read:organization';
	public const SCOPE_ORGANIZATION_WRITE = 'write:organization';
	public const SCOPE_PACKAGE            = 'package';
	public const SCOPE_PACKAGE_READ       = 'read:package';
	public const SCOPE_PACKAGE_WRITE      = 'write:package';
	public const SCOPE_REPOSITORY         = 'repository';
	public const SCOPE_REPOSITORY_READ    = 'read:repository';
	public const SCOPE_REPOSITORY_WRITE   = 'write:repository';
	public const SCOPE_USER               = 'user';
	public const SCOPE_USER_READ          = 'read:user';
	public const SCOPE_USER_WRITE         = 'write:user';

	public const DEFAULT_SCOPES = [
		self::SCOPE_REPOSITORY_READ,
		self::SCOPE_USER_READ,
	];

	protected string      $authorizationURL = 'https://codeberg.org/login/oauth/authorize';
	protected string      $accessTokenURL   = 'https://codeberg.org/login/oauth/access_token';
	protected string      $apiURL           = 'https://codeberg.org/api';
	protected string|null $apiDocs          = 'https://codeberg.org/api/swagger';
	protected string|null $applicationURL   = 'https://codeberg.org/user/settings/applications';
	protected string|null $userRevokeURL    = 'https://codeberg.org/user/settings/applications';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/user');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar_url'],
			'handle'      => $json['login'],
			'displayName' => $json['full_name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
			'url'         => sprintf('https://codeberg.org/%s', $json['login']),
		];

		return new AuthenticatedUser($userdata);
	}

}
