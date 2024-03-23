<?php
/**
 * Class WordPress
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, InvalidAccessTokenException, OAuth2Provider};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * WordPress OAuth2
 *
 * @see https://developer.wordpress.com/docs/oauth2/
 */
class WordPress extends OAuth2Provider implements CSRFToken{

	public const SCOPE_AUTH   = 'auth';
	public const SCOPE_GLOBAL = 'global';

	public const DEFAULT_SCOPES = [
		self::SCOPE_GLOBAL,
	];

	protected string      $authURL        = 'https://public-api.wordpress.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://public-api.wordpress.com/oauth2/token';
	protected string      $apiURL         = 'https://public-api.wordpress.com/rest';
	protected string|null $userRevokeURL  = 'https://wordpress.com/me/security/connected-applications';
	protected string|null $apiDocs        = 'https://developer.wordpress.com/docs/api/';
	protected string|null $applicationURL = 'https://developer.wordpress.com/apps/';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/v1/me');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->message)){

			if($status === 400 && $json->error === 'invalid_token'){
				throw new InvalidAccessTokenException($json->message);
			}

			throw new ProviderException($json->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
