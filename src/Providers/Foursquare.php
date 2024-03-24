<?php
/**
 * Class Foursquare
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth2Provider};
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function array_merge, explode, sprintf;

/**
 * Foursquare OAuth2
 *
 * @see https://location.foursquare.com/developer/reference/personalization-apis-authentication
 */
class Foursquare extends OAuth2Provider{

	public const AUTH_METHOD       = self::AUTH_METHOD_QUERY;
	public const AUTH_PREFIX_QUERY = 'oauth_token';

	protected const API_VERSIONDATE = '20190225';

	protected string      $authURL         = 'https://foursquare.com/oauth2/authenticate';
	protected string      $accessTokenURL  = 'https://foursquare.com/oauth2/access_token';
	protected string      $apiURL          = 'https://api.foursquare.com';
	protected string|null $userRevokeURL   = 'https://foursquare.com/settings/connections';
	protected string|null $apiDocs         = 'https://location.foursquare.com/developer/reference/foursquare-apis-overview';
	protected string|null $applicationURL  = 'https://foursquare.com/developers/apps';

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
		$queryparams      = QueryUtil::parse($this->uriFactory->createUri($this->apiURL.$path)->getPath());
		$queryparams['v'] = $this::API_VERSIONDATE;
		$queryparams['m'] = 'foursquare';

		return parent::request(explode('?', $path)[0], array_merge(($params ?? []), $queryparams), $method, $body, $headers);
	}

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/v2/users/self');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){
			$user = $json['response']['user'];

			$userdata = [
				'data'        => $json,
				'avatar'      => sprintf('%s%s%s', $user['photo']['prefix'], $user['id'], $user['photo']['suffix']),
				'displayName' => $user['firstName'],
				'email'       => $user['contact']['email'],
				'id'          => $user['id'],
				'handle'      => $user['handle'],
				'url'         => $user['canonicalUrl'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['meta'], $json['meta']['errorDetail'])){
			throw new ProviderException($json['meta']['errorDetail']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
