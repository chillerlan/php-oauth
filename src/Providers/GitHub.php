<?php
/**
 * Class GitHub
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};

/**
 * GitHub OAuth2
 *
 * @see https://docs.github.com/en/apps/oauth-apps/building-oauth-apps
 * @see https://docs.github.com/rest
 * @see https://docs.github.com/en/apps/creating-github-apps/authenticating-with-a-github-app/refreshing-user-access-tokens
 */
class GitHub extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'GITHUB';

	public const SCOPE_USER             = 'user';
	public const SCOPE_USER_EMAIL       = 'user:email';
	public const SCOPE_USER_FOLLOW      = 'user:follow';
	public const SCOPE_PUBLIC_REPO      = 'public_repo';
	public const SCOPE_REPO             = 'repo';
	public const SCOPE_REPO_DEPLOYMENT  = 'repo_deployment';
	public const SCOPE_REPO_STATUS      = 'repo:status';
	public const SCOPE_REPO_INVITE      = 'repo:invite';
	public const SCOPE_REPO_DELETE      = 'delete_repo';
	public const SCOPE_NOTIFICATIONS    = 'notifications';
	public const SCOPE_GIST             = 'gist';
	public const SCOPE_REPO_HOOK_READ   = 'read:repo_hook';
	public const SCOPE_REPO_HOOK_WRITE  = 'write:repo_hook';
	public const SCOPE_REPO_HOOK_ADMIN  = 'admin:repo_hook';
	public const SCOPE_ORG_HOOK_ADMIN   = 'admin:org_hook';
	public const SCOPE_ORG_READ         = 'read:org';
	public const SCOPE_ORG_WRITE        = 'write:org';
	public const SCOPE_ORG_ADMIN        = 'admin:org';
	public const SCOPE_PUBLIC_KEY_READ  = 'read:public_key';
	public const SCOPE_PUBLIC_KEY_WRITE = 'write:public_key';
	public const SCOPE_PUBLIC_KEY_ADMIN = 'admin:public_key';
	public const SCOPE_GPG_KEY_READ     = 'read:gpg_key';
	public const SCOPE_GPG_KEY_WRITE    = 'write:gpg_key';
	public const SCOPE_GPG_KEY_ADMIN    = 'admin:gpg_key';

	public const DEFAULT_SCOPES = [
		self::SCOPE_USER,
		self::SCOPE_USER_EMAIL,
		self::SCOPE_PUBLIC_REPO,
		self::SCOPE_GIST,
	];

	public const HEADERS_AUTH = [
		'Accept' => 'application/json',
	];

	public const HEADERS_API = [
		'Accept' => 'application/vnd.github.beta+json',
	];

	protected string      $authorizationURL = 'https://github.com/login/oauth/authorize';
	protected string      $accessTokenURL   = 'https://github.com/login/oauth/access_token';
	protected string      $apiURL           = 'https://api.github.com';
	protected string|null $userRevokeURL    = 'https://github.com/settings/applications';
	protected string|null $apiDocs          = 'https://docs.github.com/rest';
	protected string|null $applicationURL   = 'https://github.com/settings/developers';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/user');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar_url'],
			'handle'      => $json['login'],
			'displayName' => $json['name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
			'url'         => $json['html_url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
