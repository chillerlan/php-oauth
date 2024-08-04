<?php
/**
 * Class Google
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, UserInfo};

/**
 * Google OAuth2
 *
 * @link https://developers.google.com/identity/protocols/oauth2/web-server
 * @link https://developers.google.com/identity/protocols/oauth2/service-account
 * @link https://developers.google.com/oauthplayground/
 */
class Google extends OAuth2Provider implements CSRFToken, UserInfo{

	public const IDENTIFIER = 'GOOGLE';

	public const SCOPE_EMAIL            = 'email';
	public const SCOPE_PROFILE          = 'profile';
	public const SCOPE_USERINFO_EMAIL   = 'https://www.googleapis.com/auth/userinfo.email';
	public const SCOPE_USERINFO_PROFILE = 'https://www.googleapis.com/auth/userinfo.profile';

	public const DEFAULT_SCOPES = [
		self::SCOPE_EMAIL,
		self::SCOPE_PROFILE,
	];

	protected string      $authorizationURL = 'https://accounts.google.com/o/oauth2/auth';
	protected string      $accessTokenURL   = 'https://accounts.google.com/o/oauth2/token';
	protected string      $apiURL           = 'https://www.googleapis.com';
	protected string|null $userRevokeURL    = 'https://myaccount.google.com/connections';
	protected string|null $apiDocs          = 'https://developers.google.com/oauthplayground/';
	protected string|null $applicationURL   = 'https://console.developers.google.com/apis/credentials';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/userinfo/v2/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['picture'],
			'displayName' => $json['name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
