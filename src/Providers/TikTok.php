<?php
/**
 * Class TikTok
 *
 * @created      11.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, PKCE, PKCETrait, TokenRefresh, UserInfo};
use function array_merge, implode;

/**
 * @see https://developers.tiktok.com/doc/login-kit-web/
 * @see https://developers.tiktok.com/doc/oauth-user-access-token-management/
 */
class TikTok extends OAuth2Provider implements CSRFToken, PKCE, TokenRefresh, UserInfo{
	use PKCETrait;

	public const IDENTIFIER = 'TIKTOK';

	public const SCOPES_DELIMITER = ',';

	public const SCOPE_VIDEO_UPLOAD                       = 'video.upload';
	public const SCOPE_VIDEO_LIST                         = 'video.list';
	public const SCOPE_VIDEO_PUBLISH                      = 'video.publish';
	public const SCOPE_USER_INFO_BASIC                    = 'user.info.basic';
	public const SCOPE_USER_INFO_PROFILE                  = 'user.info.profile';
	public const SCOPE_USER_INFO_STATS                    = 'user.info.stats';
	public const SCOPE_PORTABILITY_PPOSTPROFILE_ONGOING   = 'portability.postsandprofile.ongoing';
	public const SCOPE_PORTABILITY_PPOSTPROFILE_SINGLE    = 'portability.postsandprofile.single';
	public const SCOPE_PORTABILITY_ALL_ONGOING            = 'portability.all.ongoing';
	public const SCOPE_PORTABILITY_ALL_SINGLE             = 'portability.all.single';
	public const SCOPE_PORTABILITY_DIRECTMESSAGES_ONGOING = 'portability.directmessages.ongoing';
	public const SCOPE_PORTABILITY_DIRECTMESSAGES_SINGLE  = 'portability.directmessages.single';
	public const SCOPE_PORTABILITY_ACTIVITY_ONGOING       = 'portability.activity.ongoing';
	public const SCOPE_PORTABILITY_ACTIVITY_SINGLE        = 'portability.activity.single';

	public const DEFAULT_SCOPES = [
		self::SCOPE_USER_INFO_BASIC,
		self::SCOPE_USER_INFO_PROFILE,
		self::SCOPE_USER_INFO_STATS,
		self::SCOPE_VIDEO_LIST,
	];

	protected string      $authorizationURL = 'https://www.tiktok.com/v2/auth/authorize/';
	protected string      $accessTokenURL   = 'https://open.tiktokapis.com/v2/oauth/token/';
	protected string      $revokeURL        = 'https://open.tiktokapis.com/v2/oauth/revoke/';
	protected string      $apiURL           = 'https://open.tiktokapis.com';
	protected string|null $apiDocs          = 'https://developers.tiktok.com/doc/overview/';
	protected string|null $applicationURL   = 'https://developers.tiktok.com/apps/';
	protected string|null $userRevokeURL    = 'https://example.com/user/settings/connections';

	protected function getAuthorizationURLRequestParams(array $params, array $scopes):array{

		unset($params['client_secret']);

		$params = array_merge($params, [
			'client_key'    => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
		]);

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		$params = $this->setCodeChallenge($params, PKCE::CHALLENGE_METHOD_S256);

		return $this->setState($params);
	}

	protected function getAccessTokenRequestBodyParams(string $code):array{

		$params = [
			'client_key'    => $this->options->key,
			'client_secret' => $this->options->secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->options->callbackURL,
		];

		return $this->setCodeVerifier($params);
	}

	protected function getRefreshAccessTokenRequestBodyParams(string $refreshToken):array{
		return [
			'client_key'    => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
		];
	}

	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v2/user/info/', ['fields' => 'open_id,avatar_url,display_name,profile_deep_link,username,is_verified']);

		$userdata = [
			'avatar'      => $json['data']['user']['avatar_url'],
			'data'        => $json,
			'displayName' => $json['data']['user']['display_name'],
			'handle'      => $json['data']['user']['username'],
			'id'          => $json['data']['user']['open_id'],
			'url'         => $json['data']['user']['profile_deep_link'],
		];

		return new AuthenticatedUser($userdata);
	}

}
