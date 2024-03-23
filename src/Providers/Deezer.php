<?php
/**
 * Class Deezer
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{CSRFToken, InvalidAccessTokenException, OAuth2Provider};
use Psr\Http\Message\ResponseInterface;
use function array_merge, implode, sprintf, trim;

/**
 * Deezer OAuth2
 *
 * @see https://developers.deezer.com/api/oauth
 */
class Deezer extends OAuth2Provider implements CSRFToken{

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

	protected string      $authURL        = 'https://connect.deezer.com/oauth/auth.php';
	protected string      $accessTokenURL = 'https://connect.deezer.com/oauth/access_token.php';
	protected string      $apiURL         = 'https://api.deezer.com';
	protected string|null $userRevokeURL  = 'https://www.deezer.com/account/apps';
	protected string|null $apiDocs        = 'https://developers.deezer.com/api';
	protected string|null $applicationURL = 'http://developers.deezer.com/myapps';

	/**
	 * @inheritDoc
	 *
	 * sure, you *can* use different parameter names than the standard ones... https://xkcd.com/927/
	 */
	protected function getAuthURLRequestParams(array $params, array $scopes):array{
		return array_merge($params, [
			'app_id'       => $this->options->key,
			'redirect_uri' => $this->options->callbackURL,
			'perms'        => implode($this::SCOPE_DELIMITER, $scopes),
		]);
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
	 */
	public function me():ResponseInterface{
		$response = $this->request('/user/me');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response);

		if($status === 200 && !isset($json->error)){
			return $response;
		}

		if(isset($json->error)){

			if($json->error->code === 300){
				throw new InvalidAccessTokenException($json->error->message);
			}

			throw new ProviderException($json->error->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
