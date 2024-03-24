<?php
/**
 * Class Flickr
 *
 * @created      20.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AuthenticatedUser, InvalidAccessTokenException, OAuth1Provider};
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function array_merge, sprintf;

/**
 * Flickr OAuth1
 *
 * @see https://www.flickr.com/services/api/auth.oauth.html
 * @see https://www.flickr.com/services/api/
 */
class Flickr extends OAuth1Provider{

	public const PERM_READ   = 'read';
	public const PERM_WRITE  = 'write';
	public const PERM_DELETE = 'delete';

	protected string      $requestTokenURL = 'https://www.flickr.com/services/oauth/request_token';
	protected string      $authURL         = 'https://www.flickr.com/services/oauth/authorize';
	protected string      $accessTokenURL  = 'https://www.flickr.com/services/oauth/access_token';
	protected string      $apiURL          = 'https://api.flickr.com/services/rest';
	protected string|null $userRevokeURL   = 'https://www.flickr.com/services/auth/list.gne';
	protected string|null $apiDocs         = 'https://www.flickr.com/services/api/';
	protected string|null $applicationURL  = 'https://www.flickr.com/services/apps/create/';

	/**
	 * @inheritDoc
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null
	):ResponseInterface{

		$params = array_merge(($params ?? []), [
			'method'         => $path,
			'format'         => 'json',
			'nojsoncallback' => true,
		]);

		$request = $this->getRequestAuthorization(
			/** @phan-suppress-next-line PhanTypeMismatchArgumentNullable */
			$this->requestFactory->createRequest(($method ?? 'POST'), QueryUtil::merge($this->apiURL, $params)),
		);

		return $this->http->sendRequest($request);
	}

	/**
	 * hi flickr, can i have a 401 on invalid token???
	 *
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('flickr.test.login');
		$json     = MessageUtil::decodeJSON($response, true);

		if(isset($json['stat']) && $json['stat'] === 'ok'){

			$userdata = [
				'data'   => $json['user'],
				'handle' => $json['user']['username']['_content'],
				'id'     => $json['user']['id'],
				'url'    => sprintf('https://www.flickr.com/people/%s/', $json['user']['path_alias']),
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['message'])){

			if($json['message'] === 'Invalid auth token'){
				throw new InvalidAccessTokenException($json['message']);
			}

			throw new ProviderException($json['message']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $response->getStatusCode()));
	}

}
