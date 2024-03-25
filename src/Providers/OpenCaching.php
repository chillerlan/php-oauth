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

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth1Provider};
use function implode, sprintf;

/**
 * Opencaching OAuth1
 *
 * @see https://www.opencaching.de/okapi/
 */
class OpenCaching extends OAuth1Provider{

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
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/users/user', ['fields' => implode('|', $this::USER_FIELDS)]);
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){

			$userdata = [
				'data'   => $json,
				'handle' => $json['username'],
				'id'     => $json['uuid'],
				'url'    => $json['profile_url'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['error'])){
			throw new ProviderException($json['error']['developer_message']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
