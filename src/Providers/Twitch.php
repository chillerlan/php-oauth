<?php
/**
 * Class Twitch
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

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Core\{
	AccessToken, AuthenticatedUser, ClientCredentials, ClientCredentialsTrait, CSRFToken, InvalidAccessTokenException,
	OAuth2Provider, TokenInvalidate, TokenInvalidateTrait, TokenRefresh, UserInfo,
};
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use function implode, sprintf;
use const PHP_QUERY_RFC1738;

/**
 * Twitch OAuth2
 *
 * @link https://dev.twitch.tv/docs/api/reference/
 * @link https://dev.twitch.tv/docs/authentication/
 * @link https://dev.twitch.tv/docs/authentication#oauth-client-credentials-flow-app-access-tokens
 */
class Twitch extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenInvalidate, TokenRefresh, UserInfo{
	use ClientCredentialsTrait, TokenInvalidateTrait;

	public const IDENTIFIER = 'TWITCH';

	public const SCOPE_ANALYTICS_READ_EXTENSIONS  = 'analytics:read:extensions';
	public const SCOPE_ANALYTICS_READ_GAMES       = 'analytics:read:games';
	public const SCOPE_BITS_READ                  = 'bits:read';
	public const SCOPE_CHANNEL_EDIT_COMMERCIAL    = 'channel:edit:commercial';
	public const SCOPE_CHANNEL_MANAGE_BROADCAST   = 'channel:manage:broadcast';
	public const SCOPE_CHANNEL_MANAGE_EXTENSIONS  = 'channel:manage:extensions';
	public const SCOPE_CHANNEL_MANAGE_REDEMPTIONS = 'channel:manage:redemptions';
	public const SCOPE_CHANNEL_MANAGE_VIDEOS      = 'channel:manage:videos';
	public const SCOPE_CHANNEL_READ_EDITORS       = 'channel:read:editors';
	public const SCOPE_CHANNEL_READ_HYPE_TRAIN    = 'channel:read:hype_train';
	public const SCOPE_CHANNEL_READ_REDEMPTIONS   = 'channel:read:redemptions';
	public const SCOPE_CHANNEL_READ_STREAM_KEY    = 'channel:read:stream_key';
	public const SCOPE_CHANNEL_READ_SUBSCRIPTIONS = 'channel:read:subscriptions';
	public const SCOPE_CLIPS_EDIT                 = 'clips:edit';
	public const SCOPE_MODERATION_READ            = 'moderation:read';
	public const SCOPE_USER_EDIT                  = 'user:edit';
	public const SCOPE_USER_EDIT_FOLLOWS          = 'user:edit:follows';
	public const SCOPE_USER_READ_BLOCKED_USERS    = 'user:read:blocked_users';
	public const SCOPE_USER_MANAGE_BLOCKED_USERS  = 'user:manage:blocked_users';
	public const SCOPE_USER_READ_BROADCAST        = 'user:read:broadcast';
	public const SCOPE_USER_READ_EMAIL            = 'user:read:email';
	public const SCOPE_USER_READ_SUBSCRIPTIONS    = 'user:read:subscriptions';

	public const DEFAULT_SCOPES = [
		self::SCOPE_USER_READ_EMAIL,
	];

	public const HEADERS_AUTH = [
		'Accept' => 'application/vnd.twitchtv.v5+json',
	];

	public const HEADERS_API  = [
		'Accept' => 'application/vnd.twitchtv.v5+json',
	];

	protected string      $authorizationURL = 'https://id.twitch.tv/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://id.twitch.tv/oauth2/token';
	protected string      $revokeURL        = 'https://id.twitch.tv/oauth2/revoke';
	protected string      $apiURL           = 'https://api.twitch.tv';
	protected string|null $userRevokeURL    = 'https://www.twitch.tv/settings/connections';
	protected string|null $apiDocs          = 'https://dev.twitch.tv/docs/api/reference/';
	protected string|null $applicationURL   = 'https://dev.twitch.tv/console/apps/create';

	/**
	 * @param string[]|null $scopes
	 * @return array<string, string>
	 */
	protected function getClientCredentialsTokenRequestBodyParams(array|null $scopes):array{

		$params = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'client_credentials',
		];

		if($scopes !== null){
			$params['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		return $params;
	}

	/**
	 * @param array<string, string> $body
	 */
	protected function sendClientCredentialsTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withAddedHeader($header, $value);
		}

		return $this->http->sendRequest($request);
	}

	/**
	 * @return array<string, scalar|bool|null>
	 */
	protected function getInvalidateAccessTokenBodyParams(AccessToken $token, string $type):array{
		return [
			'client_id'       => $this->options->key,
			'token'           => $token->accessToken,
			'token_type_hint' => $type,
		];
	}

	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		$token ??= $this->storage->getAccessToken($this->name);

		if($token->isExpired()){

			if($this->options->tokenAutoRefresh !== true){
				throw new InvalidAccessTokenException;
			}

			$token = $this->refreshAccessToken($token);
		}

		return $request
			->withHeader('Authorization', $this::AUTH_PREFIX_HEADER.' '.$token->accessToken)
			->withHeader('Client-ID', $this->options->key);
	}

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/helix/users');
		$user = $json['data'][0];

		$userdata = [
			'data'        => $user,
			'avatar'      => $user['profile_image_url'],
			'handle'      => $user['login'],
			'displayName' => $user['display_name'],
			'email'       => $user['email'],
			'id'          => $user['id'],
			'url'         => sprintf('https://www.twitch.tv/%s', $user['login']),
		];

		return new AuthenticatedUser($userdata);
	}

}
