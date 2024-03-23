<?php
/**
 * Class Mixcloud
 *
 * @created      28.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{InvalidAccessTokenException, OAuth2Provider};
use Psr\Http\Message\ResponseInterface;
use function sprintf;
use function str_contains;

/**
 * Mixcloud OAuth2
 *
 * note: a missing slash at the end of the path will end up in a HTTP/301
 *
 * @see https://www.mixcloud.com/developers/
 */
class Mixcloud extends OAuth2Provider{

	public const AUTH_METHOD = self::AUTH_METHOD_QUERY;

	protected string      $authURL        = 'https://www.mixcloud.com/oauth/authorize';
	protected string      $accessTokenURL = 'https://www.mixcloud.com/oauth/access_token';
	protected string      $apiURL         = 'https://api.mixcloud.com';
	protected string|null $userRevokeURL  = 'https://www.mixcloud.com/settings/applications/';
	protected string|null $apiDocs        = 'https://www.mixcloud.com/developers/';
	protected string|null $applicationURL = 'https://www.mixcloud.com/developers/create/';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/me/');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error->message)){

			if($status === 400 && str_contains($json->error->message, 'invalid access token')){
				throw new InvalidAccessTokenException($json->error->message);
			}

			throw new ProviderException($json->error->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
