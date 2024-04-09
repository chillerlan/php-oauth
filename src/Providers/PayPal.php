<?php
/**
 * Class PayPal
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};
use Psr\Http\Message\ResponseInterface;
use const PHP_QUERY_RFC1738;

/**
 * PayPal OAuth2
 *
 * @see https://developer.paypal.com/api/rest/
 */
class PayPal extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh, UserInfo{

	public const SCOPE_BASIC_AUTH     = 'openid';
	public const SCOPE_FULL_NAME      = 'profile';
	public const SCOPE_EMAIL          = 'email';
	public const SCOPE_ADDRESS        = 'address';
	public const SCOPE_ACCOUNT        = 'https://uri.paypal.com/services/paypalattributes';

	public const DEFAULT_SCOPES = [
		self::SCOPE_BASIC_AUTH,
		self::SCOPE_EMAIL,
	];

	protected string      $accessTokenURL   = 'https://api.paypal.com/v1/oauth2/token';
	protected string      $authorizationURL = 'https://www.paypal.com/connect';
	protected string      $apiURL           = 'https://api.paypal.com';
	protected string|null $applicationURL   = 'https://developer.paypal.com/developer/applications/';
	protected string|null $apiDocs          = 'https://developer.paypal.com/docs/connect-with-paypal/reference/';

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
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		$request = $this->addBasicAuthHeader($request);

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/identity/oauth2/userinfo', ['schema' => 'paypalv1.1']);

		$userdata = [
			'data'        => $json,
			'displayName' => $json['name'],
			'id'          => $json['user_id'],
		];

		if(!empty($json['emails'])){
			foreach($json['emails'] as $email){
				if($email['primary']){
					$userdata['email'] = $email['value'];
					break;
				}
			}
		}

		return new AuthenticatedUser($userdata);
	}

}
