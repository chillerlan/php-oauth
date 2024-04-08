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
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, PKCE, TokenRefresh, UserInfo};
use function sprintf;

/**
 * @see https://codeberg.org/api/swagger
 */
class Codeberg extends OAuth2Provider implements CSRFToken, PKCE, TokenRefresh, UserInfo{

	// I don't know which scopes are supported, but they might be similar to Gitea

	public const SCOPE_USER = 'user';

	public const DEFAULT_SCOPES = [
		self::SCOPE_USER,
	];

	protected string      $authorizationURL = 'https://codeberg.org/login/oauth/authorize';
	protected string      $accessTokenURL   = 'https://codeberg.org/login/oauth/access_token';
	protected string      $apiURL           = 'https://codeberg.org/api';
	protected string|null $apiDocs          = 'https://codeberg.org/api/swagger';
	protected string|null $applicationURL   = 'https://codeberg.org/user/settings/applications';
	protected string|null $userRevokeURL    = 'https://codeberg.org/user/settings/applications';

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
			'url'         => sprintf('https://codeberg.org/%s', $json['login']),
		];

		return new AuthenticatedUser($userdata);
	}

}
