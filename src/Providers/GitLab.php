<?php
/**
 * Class GitLab
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh};

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
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v4/user');

		$userdata = [
			'data'        => (array)$json,
			'avatar'      => $json['avatar_url'],
			'displayName' => $json['name'],
			'email'       => $json['email'],
			'handle'      => $json['username'],
			'id'          => $json['id'],
			'url'         => $json['web_url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
