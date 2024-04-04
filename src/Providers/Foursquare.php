<?php
/**
 * Class Foursquare
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth2Provider, UserInfo};
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function array_merge, sprintf;

/**
 * Foursquare OAuth2
 *
 * @see https://location.foursquare.com/developer/reference/personalization-apis-authentication
 */
class Foursquare extends OAuth2Provider implements UserInfo{

	public const AUTH_METHOD       = self::AUTH_METHOD_QUERY;
	public const AUTH_PREFIX_QUERY = 'oauth_token';

	protected const API_VERSIONDATE = '20190225';
	protected const QUERY_PARAMS    = ['m' => 'foursquare', 'v' => self::API_VERSIONDATE];

	protected string      $authURL         = 'https://foursquare.com/oauth2/authenticate';
	protected string      $accessTokenURL  = 'https://foursquare.com/oauth2/access_token';
	protected string      $apiURL          = 'https://api.foursquare.com';
	protected string|null $userRevokeURL   = 'https://foursquare.com/settings/connections';
	protected string|null $apiDocs         = 'https://location.foursquare.com/developer/reference/foursquare-apis-overview';
	protected string|null $applicationURL  = 'https://foursquare.com/developers/apps';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null
	):ResponseInterface{
		$params = array_merge(($params ?? []), $this::QUERY_PARAMS);

		return parent::request($path, $params, $method, $body, $headers, $protocolVersion);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v2/users/self', $this::QUERY_PARAMS);
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

}
