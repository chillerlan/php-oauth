<?php
/**
 * Class Spotify
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AuthenticatedUser, ClientCredentials, ClientCredentialsTrait, CSRFToken,
	OAuth2Provider, PKCE, PKCETrait, TokenRefresh, UserInfo,
};

/**
 * Spotify OAuth2
 *
 * @link https://developer.spotify.com/documentation/web-api
 * @link https://developer.spotify.com/documentation/web-api/tutorials/code-flow
 * @link https://developer.spotify.com/documentation/web-api/tutorials/client-credentials-flow
 * @link https://developer.spotify.com/documentation/web-api/tutorials/code-pkce-flow
 */
class Spotify extends OAuth2Provider implements ClientCredentials, CSRFToken, PKCE, TokenRefresh, UserInfo{
	use ClientCredentialsTrait, PKCETrait;

	public const IDENTIFIER = 'SPOTIFY';

	/**
	 * @link https://developer.spotify.com/documentation/web-api/concepts/scopes
	 */
	// images
	public const SCOPE_UGC_IMAGE_UPLOAD            = 'ugc-image-upload';
	// spotify connect
	public const SCOPE_USER_READ_PLAYBACK_STATE    = 'user-read-playback-state';
	public const SCOPE_USER_MODIFY_PLAYBACK_STATE  = 'user-modify-playback-state';
	public const SCOPE_USER_READ_CURRENTLY_PLAYING = 'user-read-currently-playing';
	// playback
#	public const SCOPE_APP_REMOTE_CONTROL          = 'app-remote-control'; // currently only on ios and android
	public const SCOPE_STREAMING                   = 'streaming'; // web playback SDK
	// playlists
	public const SCOPE_PLAYLIST_READ_PRIVATE       = 'playlist-read-private';
	public const SCOPE_PLAYLIST_READ_COLLABORATIVE = 'playlist-read-collaborative';
	public const SCOPE_PLAYLIST_MODIFY_PRIVATE     = 'playlist-modify-private';
	public const SCOPE_PLAYLIST_MODIFY_PUBLIC      = 'playlist-modify-public';
	// follow
	public const SCOPE_USER_FOLLOW_MODIFY          = 'user-follow-modify';
	public const SCOPE_USER_FOLLOW_READ            = 'user-follow-read';
	// listening history
	public const SCOPE_USER_READ_PLAYBACK_POSITION = 'user-read-playback-position';
	public const SCOPE_USER_TOP_READ               = 'user-top-read';
	public const SCOPE_USER_READ_RECENTLY_PLAYED   = 'user-read-recently-played';
	// library
	public const SCOPE_USER_LIBRARY_MODIFY         = 'user-library-modify';
	public const SCOPE_USER_LIBRARY_READ           = 'user-library-read';
	// users
	public const SCOPE_USER_READ_EMAIL             = 'user-read-email';
	public const SCOPE_USER_READ_PRIVATE           = 'user-read-private';
	// open access
	public const SCOPE_USER_SOA_LINK               = 'user-soa-link';
	public const SCOPE_USER_SOA_UNLINK             = 'user-soa-unlink';
	public const SCOPE_USER_MANAGE_ENTITLEMENTS    = 'user-manage-entitlements';
	public const SCOPE_USER_MANAGE_PARTNER         = 'user-manage-partner';
	public const SCOPE_USER_CREATE_PARTNER         = 'user-create-partner';

	public const DEFAULT_SCOPES = [
		self::SCOPE_PLAYLIST_READ_COLLABORATIVE,
		self::SCOPE_PLAYLIST_MODIFY_PUBLIC,
		self::SCOPE_USER_FOLLOW_MODIFY,
		self::SCOPE_USER_FOLLOW_READ,
		self::SCOPE_USER_LIBRARY_READ,
		self::SCOPE_USER_LIBRARY_MODIFY,
		self::SCOPE_USER_TOP_READ,
		self::SCOPE_USER_READ_EMAIL,
		self::SCOPE_STREAMING,
		self::SCOPE_USER_READ_PLAYBACK_STATE,
		self::SCOPE_USER_MODIFY_PLAYBACK_STATE,
		self::SCOPE_USER_READ_CURRENTLY_PLAYING,
		self::SCOPE_USER_READ_RECENTLY_PLAYED,
	];

	protected string      $authorizationURL = 'https://accounts.spotify.com/authorize';
	protected string      $accessTokenURL   = 'https://accounts.spotify.com/api/token';
	protected string      $apiURL           = 'https://api.spotify.com';
	protected string|null $userRevokeURL    = 'https://www.spotify.com/account/apps/';
	protected string|null $apiDocs          = 'https://developer.spotify.com/documentation/web-api/';
	protected string|null $applicationURL   = 'https://developer.spotify.com/dashboard';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => ($json['images'][1]['url'] ?? $json['images'][0]['url'] ?? null),
			'handle'      => $json['uri'],
			'displayName' => $json['display_name'],
			'email'       => $json['email'],
			'id'          => $json['id'],
			'url'         => $json['external_urls']['spotify'],
		];

		return new AuthenticatedUser($userdata);
	}

}
