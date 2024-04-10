<?php
/**
 * Class Discogs
 *
 * @created      08.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth1Provider, UserInfo};
use function sprintf;

/**
 * Discogs OAuth1
 *
 * @see https://www.discogs.com/developers/
 * @see https://www.discogs.com/developers/#page:authentication,header:authentication-oauth-flow
 */
class Discogs extends OAuth1Provider implements UserInfo{

	public const IDENTIFIER = 'DISCOGS';

	public const HEADERS_API = [
		'Accept' => 'application/vnd.discogs.v2.discogs+json',
	];

	protected string      $requestTokenURL  = 'https://api.discogs.com/oauth/request_token';
	protected string      $authorizationURL = 'https://www.discogs.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://api.discogs.com/oauth/access_token';
	protected string      $apiURL           = 'https://api.discogs.com';
	protected string|null $userRevokeURL    = 'https://www.discogs.com/settings/applications';
	protected string|null $apiDocs          = 'https://www.discogs.com/developers/';
	protected string|null $applicationURL   = 'https://www.discogs.com/settings/developers';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/oauth/identity');

		// we could do a second request to [resource_url] for the avatar and more info, but that's not really worth it.
		$userdata = [
			'data'   => $json,
			'handle' => $json['username'],
			'id'     => $json['id'],
			'url'    => sprintf('https://www.discogs.com/user/%s', $json['username']),
		];

		return new AuthenticatedUser($userdata);
	}

}
