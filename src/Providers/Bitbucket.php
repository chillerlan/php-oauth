<?php
/**
 * Class Bitbucket
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh};
use function sprintf;

/**
 * Bitbucket OAuth2 (Atlassian)
 *
 * @see https://developer.atlassian.com/cloud/bitbucket/oauth-2/
 */
class Bitbucket extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	protected string      $authURL        = 'https://bitbucket.org/site/oauth2/authorize';
	protected string      $accessTokenURL = 'https://bitbucket.org/site/oauth2/access_token';
	protected string      $apiURL         = 'https://api.bitbucket.org/2.0';
	protected string|null $apiDocs        = 'https://developer.atlassian.com/bitbucket/api/2/reference/';
	protected string|null $applicationURL = 'https://developer.atlassian.com/apps/';

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/user');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){

			$userdata = [
				'data'        => $json,
				'avatar'      => $json['links']['avatar']['href'],
				'displayName' => $json['display_name'],
				'handle'      => $json['username'],
				'id'          => $json['account_id'],
				'url'         => $json['links']['self']['href'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['error'], $json['error']['message'])){
			throw new ProviderException($json['error']['message']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
