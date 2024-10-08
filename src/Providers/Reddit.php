<?php
/**
 * Class Reddit
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AuthenticatedUser, ClientCredentials, ClientCredentialsTrait, CSRFToken, OAuth2Interface,
	OAuth2Provider, TokenInvalidate, TokenInvalidateTrait, TokenRefresh, UserInfo,
};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Reddit OAuth2
 *
 * @link https://github.com/reddit-archive/reddit/wiki/OAuth2
 * @link https://github.com/reddit-archive/reddit/wiki/API
 * @link https://support.reddithelp.com/hc/en-us/articles/16160319875092-Reddit-Data-API-Wiki
 * @link https://github.com/reddit-archive/reddit/wiki/OAuth2#manually-revoking-a-token
 * @link https://www.reddit.com/dev/api
 */
class Reddit extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh, TokenInvalidate, UserInfo{
	use ClientCredentialsTrait, TokenInvalidateTrait;

	public const IDENTIFIER = 'REDDIT';

	public const SCOPE_ACCOUNT          = 'account';
	public const SCOPE_CREDDITS         = 'creddits';
	public const SCOPE_EDIT             = 'edit';
	public const SCOPE_FLAIR            = 'flair';
	public const SCOPE_HISTORY          = 'history';
	public const SCOPE_IDENTITY         = 'identity';
	public const SCOPE_LIVEMANAGE       = 'livemanage';
	public const SCOPE_MODCONFIG        = 'modconfig';
	public const SCOPE_MODCONTRIBUTORS  = 'modcontributors';
	public const SCOPE_MODFLAIR         = 'modflair';
	public const SCOPE_MODLOG           = 'modlog';
	public const SCOPE_MODMAIL          = 'modmail';
	public const SCOPE_MODNOTE          = 'modnote';
	public const SCOPE_MODOTHERS        = 'modothers';
	public const SCOPE_MODPOSTS         = 'modposts';
	public const SCOPE_MODSELF          = 'modself';
	public const SCOPE_MODTRAFFIC       = 'modtraffic';
	public const SCOPE_MODWIKI          = 'modwiki';
	public const SCOPE_MYSUBREDDITS     = 'mysubreddits';
	public const SCOPE_PRIVATEMESSAGES  = 'privatemessages';
	public const SCOPE_READ             = 'read';
	public const SCOPE_REPORT           = 'report';
	public const SCOPE_SAVE             = 'save';
	public const SCOPE_STRUCTUREDSTYLES = 'structuredstyles';
	public const SCOPE_SUBMIT           = 'submit';
	public const SCOPE_SUBSCRIBE        = 'subscribe';
	public const SCOPE_VOTE             = 'vote';
	public const SCOPE_WIKIEDIT         = 'wikiedit';
	public const SCOPE_WIKIREAD         = 'wikiread';

	public const DEFAULT_SCOPES = [
		self::SCOPE_ACCOUNT,
		self::SCOPE_IDENTITY,
		self::SCOPE_READ,
	];

	public const USER_AGENT = OAuth2Interface::USER_AGENT.' (by /u/chillerlan)';

	public const HEADERS_AUTH = [
		'User-Agent' => self::USER_AGENT,
	];

	public const HEADERS_API = [
		'User-Agent' => self::USER_AGENT,
	];

	public const USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST = true;

	protected string      $authorizationURL = 'https://www.reddit.com/api/v1/authorize';
	protected string      $accessTokenURL   = 'https://www.reddit.com/api/v1/access_token';
	protected string      $apiURL           = 'https://oauth.reddit.com/api';
	protected string      $revokeURL        = 'https://www.reddit.com/api/v1/revoke_token';
	protected string|null $apiDocs          = 'https://www.reddit.com/dev/api';
	protected string|null $applicationURL   = 'https://www.reddit.com/prefs/apps/';
	protected string|null $userRevokeURL    = 'https://www.reddit.com/settings/privacy';

	/**
	 * @param array<string, scalar|bool|null> $body
	 */
	protected function sendTokenInvalidateRequest(string $url, array $body):ResponseInterface{ // phpcs:ignore

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		$request  = $this->addBasicAuthHeader($request);
		$request  = $this->setRequestBody($body, $request);

		return $this->http->sendRequest($request);
	}

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['subreddit']['icon_img'],
			'handle'      => $json['name'],
			'displayName' => $json['subreddit']['title'],
			'id'          => $json['id'],
			'url'         => sprintf('https://www.reddit.com%s', $json['subreddit']['url']),
		];

		return new AuthenticatedUser($userdata);
	}

}
