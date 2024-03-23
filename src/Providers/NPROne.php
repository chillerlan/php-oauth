<?php
/**
 * Class NPROne
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AccessToken, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh};
use Psr\Http\Message\{ResponseInterface};
use function in_array, sprintf, strtolower;

/**
 * NPR API services (OAuth2)
 *
 * @see https://dev.npr.org
 * @see https://github.com/npr/npr-one-backend-proxy-php
 */
class NPROne extends OAuth2Provider implements CSRFToken, TokenRefresh, TokenInvalidate{

	public const SCOPE_IDENTITY_READONLY  = 'identity.readonly';
	public const SCOPE_IDENTITY_WRITE     = 'identity.write';
	public const SCOPE_LISTENING_READONLY = 'listening.readonly';
	public const SCOPE_LISTENING_WRITE    = 'listening.write';
	public const SCOPE_LOCALACTIVATION    = 'localactivation';

	public const DEFAULT_SCOPES = [
		self::SCOPE_IDENTITY_READONLY,
		self::SCOPE_LISTENING_READONLY,
	];

	protected string      $apiURL         = 'https://listening.api.npr.org';
	protected string      $authURL        = 'https://authorization.api.npr.org/v2/authorize';
	protected string      $accessTokenURL = 'https://authorization.api.npr.org/v2/token';
	protected string      $revokeURL      = 'https://authorization.api.npr.org/v2/token/revoke';
	protected string|null $apiDocs        = 'https://dev.npr.org/api/';
	protected string|null $applicationURL = 'https://dev.npr.org/console';

	/**
	 * Sets the API to work with ("listening" is set as default)
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setAPI(string $api):static{
		$api = strtolower($api);

		if(!in_array($api, ['identity', 'listening', 'station'])){
			throw new ProviderException(sprintf('invalid API: "%s"', $api));
		}

		$this->apiURL = sprintf('https://%s.api.npr.org', $api);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('https://identity.api.npr.org/v2/user');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->errors)){
			throw new ProviderException($json->errors[0]->text);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool{

		if($token === null && !$this->storage->hasAccessToken($this->serviceName)){
			throw new ProviderException('no token given');
		}

		$token ??= $this->storage->getAccessToken($this->serviceName);

		$response = $this->request(
			path: $this->revokeURL,
			method: 'POST',
			body: [
				'token'           => $token->accessToken,
				'token_type_hint' => 'access_token',
			],
			headers: ['Content-Type' => 'application/x-www-form-urlencoded']
		);

		if($response->getStatusCode() === 200){
			$this->storage->clearAccessToken($this->serviceName);

			return true;
		}

		return false;
	}

}
