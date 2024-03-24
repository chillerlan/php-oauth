<?php
/**
 * Class GitLab
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
 * GitLab OAuth2
 *
 * @see https://docs.gitlab.com/ee/api/oauth2.html
 */
class GitLab extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh{

	protected string      $authURL        = 'https://gitlab.com/oauth/authorize';
	protected string      $accessTokenURL = 'https://gitlab.com/oauth/token';
	protected string      $apiURL         = 'https://gitlab.com/api';
	protected string|null $applicationURL = 'https://gitlab.com/profile/applications';

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/v4/user');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response);

		if($status === 200){

			$userdata = [
				'data'        => (array)$json,
				'avatar'      => $json->avatar_url,
				'displayName' => $json->name,
				'email'       => $json->email,
				'handle'      => $json->username,
				'id'          => $json->id,
				'url'         => $json->web_url,
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json->error, $json->error_description)){
			throw new ProviderException($json->error_description);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
