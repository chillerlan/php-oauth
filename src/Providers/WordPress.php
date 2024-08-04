<?php
/**
 * Class WordPress
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, UserInfo};

/**
 * WordPress OAuth2
 *
 * @link https://developer.wordpress.com/docs/oauth2/
 */
class WordPress extends OAuth2Provider implements CSRFToken, UserInfo{

	public const IDENTIFIER = 'WORDPRESS';

	public const SCOPE_AUTH   = 'auth';
	public const SCOPE_GLOBAL = 'global';

	public const DEFAULT_SCOPES = [
		self::SCOPE_GLOBAL,
	];

	protected string      $authorizationURL = 'https://public-api.wordpress.com/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://public-api.wordpress.com/oauth2/token';
	protected string      $apiURL           = 'https://public-api.wordpress.com/rest';
	protected string|null $userRevokeURL    = 'https://wordpress.com/me/security/connected-applications';
	protected string|null $apiDocs          = 'https://developer.wordpress.com/docs/api/';
	protected string|null $applicationURL   = 'https://developer.wordpress.com/apps/';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar_URL'],
			'handle'      => $json['username'],
			'displayName' => $json['display_name'],
			'email'       => $json['email'],
			'id'          => $json['ID'],
			'url'         => $json['profile_URL'],
		];

		return new AuthenticatedUser($userdata);
	}

}
