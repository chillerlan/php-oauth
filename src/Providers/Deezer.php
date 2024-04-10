<?php
/**
 * Class Deezer
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, InvalidAccessTokenException, OAuth2Provider, UserInfo};
use Psr\Http\Message\ResponseInterface;
use function array_merge, implode, trim;

/**
 * Deezer OAuth2
 *
 * @see https://developers.deezer.com/api/oauth
 */
class Deezer extends OAuth2Provider implements CSRFToken, UserInfo{

	public const IDENTIFIER = 'DEEZER';

	public const SCOPE_BASIC             = 'basic_access';
	public const SCOPE_EMAIL             = 'email';
	public const SCOPE_OFFLINE_ACCESS    = 'offline_access';
	public const SCOPE_MANAGE_LIBRARY    = 'manage_library';
	public const SCOPE_MANAGE_COMMUNITY  = 'manage_community';
	public const SCOPE_DELETE_LIBRARY    = 'delete_library';
	public const SCOPE_LISTENING_HISTORY = 'listening_history';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC,
		self::SCOPE_EMAIL,
		self::SCOPE_OFFLINE_ACCESS,
		self::SCOPE_MANAGE_LIBRARY,
		self::SCOPE_LISTENING_HISTORY,
	];

	public const AUTH_METHOD = self::AUTH_METHOD_QUERY;

	protected string      $authorizationURL = 'https://connect.deezer.com/oauth/auth.php';
	protected string      $accessTokenURL   = 'https://connect.deezer.com/oauth/access_token.php';
	protected string      $apiURL           = 'https://api.deezer.com';
	protected string|null $userRevokeURL    = 'https://www.deezer.com/account/apps';
	protected string|null $apiDocs          = 'https://developers.deezer.com/api';
	protected string|null $applicationURL   = 'https://developers.deezer.com/myapps';

	/**
	 * @inheritDoc
	 *
	 * sure, you *can* use different parameter names than the standard ones... https://xkcd.com/927/
	 */
	protected function getAuthorizationURLRequestParams(array $params, array $scopes):array{

		$params = array_merge($params, [
			'app_id'       => $this->options->key,
			'redirect_uri' => $this->options->callbackURL,
			'perms'        => implode($this::SCOPES_DELIMITER, $scopes),
		]);

		return $this->setState($params); // we are instance of CSRFToken
	}

	/**
	 * @inheritDoc
	 */
	protected function getAccessTokenRequestBodyParams(string $code):array{
		return [
			'app_id' => $this->options->key,
			'secret' => $this->options->secret,
			'code'   => $code,
			'output' => 'json', // for some reason this has no effect
		];
	}

	/**
	 * @inheritDoc
	 *
	 * hey deezer, I suggest re-reading the OAuth2 spec!
	 * also the content-type of "text/html" here is... bad.
	 */
	protected function getTokenResponseData(ResponseInterface $response):array{
		$data = trim(MessageUtil::getContents($response));

		if(empty($data)){
			throw new ProviderException('invalid response');
		}

		return QueryUtil::parse($data);
	}

	/**
	 * deezer keeps testing my sanity - HTTP/200 on invalid token... sure
	 *
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/user/me');

		if(isset($json['error']['code'], $json['error']['message'])){

			if($json['error']['code'] === 300){
				throw new InvalidAccessTokenException($json['error']['message']);
			}

			throw new ProviderException($json['error']['message']);
		}

		$userdata = [
			'data'   => $json,
			'avatar' => $json['picture'],
			'handle' => $json['name'],
			'id'     => $json['id'],
			'url'    => $json['link'],
		];

		return new AuthenticatedUser($userdata);
	}

}
