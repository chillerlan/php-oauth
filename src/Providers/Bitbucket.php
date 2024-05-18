<?php
/**
 * Class Bitbucket
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};

/**
 * Bitbucket OAuth2 (Atlassian)
 *
 * @link https://developer.atlassian.com/cloud/bitbucket/oauth-2/
 */
class Bitbucket extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'BITBUCKET';

	protected string      $authorizationURL = 'https://bitbucket.org/site/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://bitbucket.org/site/oauth2/access_token';
	protected string      $apiURL           = 'https://api.bitbucket.org/2.0';
	protected string|null $apiDocs          = 'https://developer.atlassian.com/bitbucket/api/2/reference/';
	protected string|null $applicationURL   = 'https://developer.atlassian.com/apps/';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/user');

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

}
