<?php
/**
 * Class Twitter
 *
 * @created      08.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, OAuth1Provider, UserInfo};
use function sprintf, str_replace;

/**
 * Twitter OAuth1
 *
 * @todo: twitter is dead. fuck elon musk.
 *
 * @link https://developer.twitter.com/en/docs/basics/authentication/overview/oauth
 */
class Twitter extends OAuth1Provider implements UserInfo{

	public const IDENTIFIER = 'TWITTER';

	// choose your fighter
	/** @link https://developer.twitter.com/en/docs/basics/authentication/api-reference/authorize */
	protected string $authorizationURL     = 'https://api.twitter.com/oauth/authorize';
	/** @link https://developer.twitter.com/en/docs/basics/authentication/api-reference/authenticate */
#	protected string $authorizationURL     = 'https://api.twitter.com/oauth/authenticate';

	protected string      $requestTokenURL = 'https://api.twitter.com/oauth/request_token';
	protected string      $accessTokenURL  = 'https://api.twitter.com/oauth/access_token';
	protected string      $apiURL          = 'https://api.twitter.com';
	protected string|null $userRevokeURL   = 'https://twitter.com/settings/applications';
	protected string|null $apiDocs         = 'https://developer.twitter.com/docs';
	protected string|null $applicationURL  = 'https://developer.twitter.com/apps';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/1.1/account/verify_credentials.json');

		$userdata = [
			'data'        => $json,
			'avatar'      => str_replace('_normal', '_400x400', $json['profile_image_url_https']),
			'handle'      => $json['screen_name'],
			'displayName' => $json['name'],
			'id'          => $json['id'],
			'url'         => sprintf('https://twitter.com/%s', $json['screen_name']),
		];

		return new AuthenticatedUser($userdata);
	}

}
