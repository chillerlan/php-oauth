<?php
/**
 * Class OpenStreetmap
 *
 * @created      12.05.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{OAuth1Provider};
use Psr\Http\Message\ResponseInterface;
use function sprintf, strip_tags;

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
	 */
	public function me():ResponseInterface{
		$response = $this->request('/api/0.6/user/details.json');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$body = MessageUtil::getContents($response);

		if(!empty($body)){
			throw new ProviderException(strip_tags($body));
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
