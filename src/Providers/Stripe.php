<?php
/**
 * Class Stripe
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AccessToken, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

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
	 */
	public function me():ResponseInterface{
		$response = $this->request('/accounts');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error_description)){
			throw new ProviderException($json->error_description);
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
			path   : $this->revokeURL,
			method : 'POST',
			body   : [
				'client_id'      => $this->options->key,
				'stripe_user_id' => ($token->extraParams['stripe_user_id'] ?? ''),
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
