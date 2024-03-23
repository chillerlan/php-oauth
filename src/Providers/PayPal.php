<?php
/**
 * Class PayPal
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function base64_encode, sprintf;
use const PHP_QUERY_RFC1738;

/**
 * PayPal OAuth2
 *
 * @see https://developer.paypal.com/api/rest/
 */
class PayPal extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	public const SCOPE_BASIC_AUTH     = 'openid';
	public const SCOPE_FULL_NAME      = 'profile';
	public const SCOPE_EMAIL          = 'email';
	public const SCOPE_ADDRESS        = 'address';
	public const SCOPE_ACCOUNT        = 'https://uri.paypal.com/services/paypalattributes';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC_AUTH,
		self::SCOPE_EMAIL,
	];

	protected string      $accessTokenURL = 'https://api.paypal.com/v1/oauth2/token';
	protected string      $authURL        = 'https://www.paypal.com/connect';
	protected string      $apiURL         = 'https://api.paypal.com';
	protected string|null $applicationURL = 'https://developer.paypal.com/developer/applications/';
	protected string|null $apiDocs        = 'https://developer.paypal.com/docs/connect-with-paypal/reference/';

	/**
	 * @inheritDoc
	 */
	protected function getAccessTokenRequestBodyParams(string $code):array{
		return [
			'code'         => $code,
			'grant_type'   => 'authorization_code',
			'redirect_uri' => $this->options->callbackURL,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function sendAccessTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Authorization', 'Basic '.base64_encode($this->options->key.':'.$this->options->secret))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/v1/identity/oauth2/userinfo', ['schema' => 'paypalv1.1']);
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

}
