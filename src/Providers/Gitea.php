<?php
/**
 * Class Gitea
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, PKCE, TokenRefresh, UserInfo};
use function sprintf;

/**
 * @see https://docs.gitea.com/development/oauth2-provider
 */
class Gitea extends OAuth2Provider implements CSRFToken, PKCE, TokenRefresh, UserInfo{

	public const SCOPE_ACTIVITYPUB        = 'activitypub';
	public const SCOPE_ACTIVITYPUB_READ   = 'read:activitypub';
	public const SCOPE_ACTIVITYPUB_WRITE  = 'write:activitypub';
	public const SCOPE_ADMIN              = 'admin';
	public const SCOPE_ADMIN_READ         = 'read:admin';
	public const SCOPE_ADMIN_WRITE        = 'write:admin';
	public const SCOPE_ISSUE              = 'issue';
	public const SCOPE_ISSUE_READ         = 'read:issue';
	public const SCOPE_ISSUE_WRITE        = 'write:issue';
#	public const SCOPE_MISC               = 'misc';
#	public const SCOPE_MISC_READ          = 'read:misc';
#	public const SCOPE_MISC_WRITE         = 'write:misc';
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

	protected string $authorizationURL    = 'https://gitea.com/login/oauth/authorize';
	protected string $accessTokenURL      = 'https://gitea.com/login/oauth/access_token';
	protected string $apiURL              = 'https://gitea.com/api';
	protected string|null $apiDocs        = 'https://docs.gitea.com/api/1.20/';
	protected string|null $applicationURL = 'https://gitea.com/user/settings/applications';
	protected string|null $userRevokeURL  = 'https://gitea.com/user/settings/applications';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/user');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar_url'],
			'handle'      => $json['login'],
			'displayName' => $json['full_name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
			'url'         => sprintf('https://gitea.com/%s', $json['login']),
		];

		return new AuthenticatedUser($userdata);
	}

}
