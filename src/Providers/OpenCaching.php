<?php
/**
 * Class OpenCaching
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth1Provider, UserInfo};
use function implode;

/**
 * Opencaching OAuth1
 *
 * @see https://www.opencaching.de/okapi/
 */
class OpenCaching extends OAuth1Provider implements UserInfo{

	protected const USER_FIELDS = [
		'uuid', 'username', 'profile_url', 'internal_id', 'date_registered',
		'caches_found', 'caches_notfound', 'caches_hidden', 'rcmds_given',
		'rcmds_left', 'rcmd_founds_needed', 'home_location',
	];

	protected string      $requestTokenURL = 'https://www.opencaching.de/okapi/services/oauth/request_token';
	protected string      $authURL         = 'https://www.opencaching.de/okapi/services/oauth/authorize';
	protected string      $accessTokenURL  = 'https://www.opencaching.de/okapi/services/oauth/access_token';
	protected string      $apiURL          = 'https://www.opencaching.de/okapi/services';
	protected string|null $userRevokeURL   = 'https://www.opencaching.de/okapi/apps/';
	protected string|null $apiDocs         = 'https://www.opencaching.de/okapi/';
	protected string|null $applicationURL  = 'https://www.opencaching.de/okapi/signup.html';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/users/user', ['fields' => implode('|', $this::USER_FIELDS)]);

		$userdata = [
			'data'   => $json,
			'handle' => $json['username'],
			'id'     => $json['uuid'],
			'url'    => $json['profile_url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
