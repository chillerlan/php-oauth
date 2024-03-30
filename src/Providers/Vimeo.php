<?php
/**
 * Class Vimeo
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenInvalidate};
use function str_replace;

/**
 * Vimeo OAuth2
 *
 * @see https://developer.vimeo.com/
 * @see https://developer.vimeo.com/api/authentication
 */
class Vimeo extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenInvalidate{

	/**
	 * @see https://developer.vimeo.com/api/authentication#understanding-the-auth-process
	 */
	public const SCOPE_PUBLIC      = 'public';
	public const SCOPE_PRIVATE     = 'private';
	public const SCOPE_PURCHASED   = 'purchased';
	public const SCOPE_CREATE      = 'create';
	public const SCOPE_EDIT        = 'edit';
	public const SCOPE_DELETE      = 'delete';
	public const SCOPE_INTERACT    = 'interact';
	public const SCOPE_STATS       = 'stats';
	public const SCOPE_UPLOAD      = 'upload';
	public const SCOPE_PROMO_CODES = 'promo_codes';
	public const SCOPE_VIDEO_FILES = 'video_files';

	public const DEFAULT_SCOPES = [
		self::SCOPE_PUBLIC,
		self::SCOPE_PRIVATE,
		self::SCOPE_INTERACT,
		self::SCOPE_STATS,
	];

	// @see https://developer.vimeo.com/api/changelog
	protected const API_VERSION    = '3.4';

	public const HEADERS_AUTH = [
		'Accept' => 'application/vnd.vimeo.*+json;version='.self::API_VERSION,
	];

	public const HEADERS_API  = [
		'Accept' => 'application/vnd.vimeo.*+json;version='.self::API_VERSION,
	];

	protected string      $authURL                   = 'https://api.vimeo.com/oauth/authorize';
	protected string      $accessTokenURL            = 'https://api.vimeo.com/oauth/access_token';
	protected string      $revokeURL                 = 'https://api.vimeo.com/tokens';
	protected string      $apiURL                    = 'https://api.vimeo.com';
	protected string|null $userRevokeURL             = 'https://vimeo.com/settings/apps';
	protected string|null $clientCredentialsTokenURL = 'https://api.vimeo.com/oauth/authorize/client';
	protected string|null $apiDocs                   = 'https://developer.vimeo.com';
	protected string|null $applicationURL            = 'https://developer.vimeo.com/apps';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/me');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['pictures']['base_link'],
			'handle'      => str_replace('https://vimeo.com/', '', $json['link']),
			'displayName' => $json['name'],
			'id'          => str_replace('/users/', '', $json['uri']),
			'url'         => $json['link'],
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool{

		if($token !== null){
			// to revoke a token different from the one of the currently authenticated user,
			// we're going to clone the provider and feed the other token for the invalidate request
			$provider = clone $this;
			$provider->storeAccessToken($token);
			$response = $provider->request(path: $this->revokeURL, method: 'DELETE');
		}
		else{

			if(!$this->storage->hasAccessToken($this->name)){
				throw new ProviderException('no token given');
			}

			$response = $this->request(path: $this->revokeURL, method: 'DELETE');
		}

		if($response->getStatusCode() === 204){
			$this->storage->clearAccessToken($this->name);

			return true;
		}

		return false;
	}

}
