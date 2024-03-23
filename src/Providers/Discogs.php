<?php
/**
 * Class Discogs
 *
 * @created      08.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\OAuth1Provider;
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Discogs OAuth1
 *
 * @see https://www.discogs.com/developers/
 * @see https://www.discogs.com/developers/#page:authentication,header:authentication-oauth-flow
 */
class Discogs extends OAuth1Provider{

	public const HEADERS_API = [
		'Accept' => 'application/vnd.discogs.v2.discogs+json',
	];

	protected string      $requestTokenURL = 'https://api.discogs.com/oauth/request_token';
	protected string      $authURL         = 'https://www.discogs.com/oauth/authorize';
	protected string      $accessTokenURL  = 'https://api.discogs.com/oauth/access_token';
	protected string      $apiURL          = 'https://api.discogs.com';
	protected string|null $userRevokeURL   = 'https://www.discogs.com/settings/applications';
	protected string|null $apiDocs         = 'https://www.discogs.com/developers/';
	protected string|null $applicationURL = 'https://www.discogs.com/settings/developers';
	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/oauth/identity');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->message)){
			throw new ProviderException($json->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
