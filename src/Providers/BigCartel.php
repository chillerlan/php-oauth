<?php
/**
 * Class BigCartel
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenInvalidate, TokenInvalidateTrait, UserInfo,
};
use function sprintf;

/**
 * BigCartel OAuth2
 *
 * @link https://developers.bigcartel.com/api/v1
 * @link https://bigcartel.wufoo.com/confirm/big-cartel-api-application/
 */
class BigCartel extends OAuth2Provider implements CSRFToken, TokenInvalidate, UserInfo{
	use TokenInvalidateTrait;

	public const IDENTIFIER = 'BIGCARTEL';

	public const HEADERS_API = [
		'Accept' => 'application/vnd.api+json',
	];

	protected string      $authorizationURL = 'https://my.bigcartel.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://api.bigcartel.com/oauth/token';
	protected string      $revokeURL        = 'https://api.bigcartel.com/oauth/deauthorize';
	protected string      $apiURL           = 'https://api.bigcartel.com/v1';
	protected string|null $userRevokeURL    = 'https://my.bigcartel.com/account';
	protected string|null $apiDocs          = 'https://developers.bigcartel.com/api/v1';
	protected string|null $applicationURL   = 'https://bigcartel.wufoo.com/forms/big-cartel-api-application/';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/accounts');

		$userdata = [
			'data'   => $json,
			'email'  => $json['data'][0]['attributes']['contact_email'],
			'handle' => $json['data'][0]['attributes']['subdomain'],
			'id'     => $json['data'][0]['id'],
		];

		return new AuthenticatedUser($userdata);
	}

	public function invalidateAccessToken(AccessToken|null $token = null, string|null $type = null):bool{
		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));

		$request = $this->requestFactory
			->createRequest('POST', sprintf('%s/%s', $this->revokeURL, $this->getAccountID($tokenToInvalidate)))
		;

		$request  = $this->addBasicAuthHeader($request);
		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() === 204){

			if($token === null){
				$this->storage->clearAccessToken($this->name);
			}

			return true;
		}

		return false;
	}

	/**
	 * Try to get the user ID from either the token or the `me()` endpoint
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function getAccountID(AccessToken $token):string{

		if(isset($token->extraParams['account_id'])){
			return (string)$token->extraParams['account_id'];
		}

		$me = $this->me();

		if($me->id !== null){
			return (string)$me->id;
		}

		throw new ProviderException('cannot determine account id');
	}

}
