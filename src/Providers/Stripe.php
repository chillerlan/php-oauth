<?php
/**
 * Class Stripe
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

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh};

/**
 * Stripe OAuth2
 *
 * @see https://stripe.com/docs/api
 * @see https://stripe.com/docs/connect/authentication
 * @see https://stripe.com/docs/connect/oauth-reference
 * @see https://stripe.com/docs/connect/standard-accounts
 * @see https://gist.github.com/amfeng/3507366
 */
class Stripe extends OAuth2Provider implements CSRFToken, TokenRefresh, TokenInvalidate{

	public const SCOPE_READ_WRITE = 'read_write';
	public const SCOPE_READ_ONLY  = 'read_only';

	public const DEFAULT_SCOPES = [
		self::SCOPE_READ_ONLY,
	];

	protected string      $authURL        = 'https://connect.stripe.com/oauth/authorize';
	protected string      $accessTokenURL = 'https://connect.stripe.com/oauth/token';
	protected string      $revokeURL      = 'https://connect.stripe.com/oauth/deauthorize';
	protected string      $apiURL         = 'https://api.stripe.com/v1';
	protected string|null $userRevokeURL  = 'https://dashboard.stripe.com/account/applications';
	protected string|null $apiDocs        = 'https://stripe.com/docs/api';
	protected string|null $applicationURL = 'https://dashboard.stripe.com/apikeys';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/accounts');

		$userdata = [
			'data' => $json,
			'id'   => $json['data'][0]['id'],
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool{

		if($token === null && !$this->storage->hasAccessToken($this->name)){
			throw new ProviderException('no token given');
		}

		$token ??= $this->storage->getAccessToken($this->name);

		$request = $this->requestFactory
			->createRequest('POST', $this->revokeURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		$bodyParams = [
			'client_id'      => $this->options->key,
			'stripe_user_id' => ($token->extraParams['stripe_user_id'] ?? ''),
		];

		$request = $this->setRequestBody($bodyParams, $request);

		// bypass the request authoritation
		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() === 200){
			$this->storage->clearAccessToken($this->name);

			return true;
		}

		return false;
	}

}
