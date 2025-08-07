<?php
/**
 * Class Tidal
 *
 * @created      04.08.2025
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2025 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AuthenticatedUser, ClientCredentials, ClientCredentialsTrait,
	OAuth2Provider, PKCE, PKCETrait, TokenRefresh, UserInfo
};

/**
 * Tidal OAuth2 (OAuth 2.1)
 *
 * @link https://developer.tidal.com/documentation/api-sdk/api-sdk-authorization
 */
class Tidal extends OAuth2Provider implements ClientCredentials, PKCE, TokenRefresh, UserInfo{
	use ClientCredentialsTrait, PKCETrait;

	public const IDENTIFIER = 'TIDAL';

	public const SCOPE_COLLECTION_READ      = 'collection.read';
	public const SCOPE_COLLECTION_WRITE     = 'collection.write';
	public const SCOPE_ENTITLEMENTS_READ    = 'entitlements.read';
	public const SCOPE_PLAYBACK             = 'playback';
	public const SCOPE_PLAYLISTS_READ       = 'playlists.read';
	public const SCOPE_PLAYLISTS_WRITE      = 'playlists.write';
	public const SCOPE_RECOMMENDATIONS_READ = 'recommendations.read';
	public const SCOPE_SEARCH_READ          = 'search.read';
	public const SCOPE_SEARCH_WRITE         = 'search.write';
	public const SCOPE_USER_READ            = 'user.read';

	public const DEFAULT_SCOPES = [
		self::SCOPE_COLLECTION_READ,
		self::SCOPE_COLLECTION_WRITE,
		self::SCOPE_PLAYLISTS_READ,
		self::SCOPE_PLAYLISTS_WRITE,
		self::SCOPE_RECOMMENDATIONS_READ,
		self::SCOPE_USER_READ,
	];

	public const HEADERS_API = [
		'Accept' => 'application/vnd.api+json',
	];

	public const USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST = true;

	protected string      $authorizationURL = 'https://login.tidal.com/authorize';
	protected string      $accessTokenURL   = 'https://auth.tidal.com/v1/oauth2/token';
	protected string      $apiURL           = 'https://openapi.tidal.com';
	protected string|null $userRevokeURL    = 'https://account.tidal.com/third-party-apps';
	protected string|null $apiDocs          = 'https://developer.tidal.com/documentation';
	protected string|null $applicationURL   = 'https://developer.tidal.com/dashboard';


	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v2/users/me');

		$userdata = [
			'data'        => $json,
			'handle'      => $json['data']['attributes']['username'],
			'email'       => $json['data']['attributes']['email'],
			'id'          => $json['data']['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
