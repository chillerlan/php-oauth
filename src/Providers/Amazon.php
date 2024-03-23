<?php
/**
 * Class Amazon
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, InvalidAccessTokenException, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Login with Amazon for Websites (OAuth2)
 *
 * @see https://developer.amazon.com/docs/login-with-amazon/web-docs.html
 * @see https://developer.amazon.com/docs/login-with-amazon/conceptual-overview.html
 */
class Amazon extends OAuth2Provider implements CSRFToken, TokenRefresh{

	public const SCOPE_PROFILE         = 'profile';
	public const SCOPE_PROFILE_USER_ID = 'profile:user_id';
	public const SCOPE_POSTAL_CODE     = 'postal_code';

	public const DEFAULT_SCOPES = [
		self::SCOPE_PROFILE,
		self::SCOPE_PROFILE_USER_ID,
	];

	protected string      $authURL        = 'https://www.amazon.com/ap/oa';
	protected string      $accessTokenURL = 'https://www.amazon.com/ap/oatoken';
	protected string      $apiURL         = 'https://api.amazon.com';
	protected string|null $applicationURL = 'https://developer.amazon.com/loginwithamazon/console/site/lwa/overview.html';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/user/profile');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error_description)){

			if($json->error === 'invalid_token'){
				throw new InvalidAccessTokenException($json->error_description);
			}

			throw new ProviderException($json->error_description);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
