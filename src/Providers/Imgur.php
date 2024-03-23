<?php
/**
 * Class Imgur
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Imgur OAuth2
 *
 * Note: imgur sends an "expires_in" of 315360000 (10 years!) for access tokens,
 *       but states in the docs that tokens expire after one month.
 *       Either manually saving the expiry with the token to trigger auto refresh
 *       or manually refreshing via the refreshAccessToken() method is required.
 *
 * @see https://apidocs.imgur.com/
 */
class Imgur extends OAuth2Provider implements CSRFToken, TokenRefresh{

	protected string      $authURL        = 'https://api.imgur.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://api.imgur.com/oauth2/token';
	protected string      $apiURL         = 'https://api.imgur.com';
	protected string|null $userRevokeURL  = 'https://imgur.com/account/settings/apps';
	protected string|null $apiDocs        = 'https://apidocs.imgur.com';
	protected string|null $applicationURL = 'https://api.imgur.com/oauth2/addclient';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/3/account/me');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->data, $json->data->error)){
			throw new ProviderException($json->data->error);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
