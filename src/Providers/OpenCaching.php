<?php
/**
 * Class OpenCaching
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{OAuth1Provider};
use Psr\Http\Message\ResponseInterface;
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
	public function me():ResponseInterface{
		$response = $this->request('/users/user', ['fields' => implode('|', $this::USER_FIELDS)]);
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->error, $json->error_description)){
			throw new ProviderException($json->error_description);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
