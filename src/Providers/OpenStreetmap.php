<?php
/**
 * Class OpenStreetmap
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth1Provider};

/**
 * OpenStreetmap OAuth1 (deprecated)
 *
 * @see https://wiki.openstreetmap.org/wiki/API
 * @see https://wiki.openstreetmap.org/wiki/OAuth
 *
 * @deprecated https://github.com/openstreetmap/operations/issues/867
 */
class OpenStreetmap extends OAuth1Provider{

	protected string      $requestTokenURL = 'https://www.openstreetmap.org/oauth/request_token';
	protected string      $authURL         = 'https://www.openstreetmap.org/oauth/authorize';
	protected string      $accessTokenURL  = 'https://www.openstreetmap.org/oauth/access_token';
	protected string      $apiURL          = 'https://api.openstreetmap.org';
	protected string|null $apiDocs         = 'https://wiki.openstreetmap.org/wiki/API';
	protected string|null $applicationURL  = 'https://www.openstreetmap.org/user/{USERNAME}/oauth_clients';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/api/0.6/user/details.json');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['user']['img']['href'],
			'displayName' => $json['user']['display_name'],
			'id'          => $json['user']['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
