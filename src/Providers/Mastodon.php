<?php
/**
 * Class Mastodon
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};
use chillerlan\OAuth\OAuthException;
use Psr\Http\Message\UriInterface;
use function array_merge;

/**
 * Mastodon OAuth2 (v4.x instances)
 *
 * @link https://docs.joinmastodon.org/client/intro/
 * @link https://docs.joinmastodon.org/methods/apps/oauth/
 */
class Mastodon extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'MASTODON';

	public const SCOPE_READ   = 'read';
	public const SCOPE_WRITE  = 'write';
	public const SCOPE_FOLLOW = 'follow';
	public const SCOPE_PUSH   = 'push';

	public const DEFAULT_SCOPES = [
		self::SCOPE_READ,
		self::SCOPE_FOLLOW,
	];

	protected string      $authorizationURL = 'https://mastodon.social/oauth/authorize';
	protected string      $accessTokenURL   = 'https://mastodon.social/oauth/token';
	protected string      $apiURL           = 'https://mastodon.social/api';
	protected string|null $userRevokeURL    = 'https://mastodon.social/oauth/authorized_applications';
	protected string|null $apiDocs          = 'https://docs.joinmastodon.org/api/';
	protected string|null $applicationURL   = 'https://mastodon.social/settings/applications';
	protected string      $instance         = 'https://mastodon.social';

	/**
	 * set the internal URLs for the given Mastodon instance
	 *
	 * @throws \chillerlan\OAuth\OAuthException
	 */
	public function setInstance(UriInterface|string $instance):static{

		if(!$instance instanceof UriInterface){
			$instance = $this->uriFactory->createUri($instance);
		}

		if($instance->getHost() === ''){
			throw new OAuthException('invalid instance URL');
		}

		// enforce https and remove unnecessary parts
		$instance = $instance->withScheme('https')->withQuery('')->withFragment('');

		// @todo: check if host exists/responds?
		$this->instance         = (string)$instance->withPath('');
		$this->apiURL           = (string)$instance->withPath('/api');
		$this->authorizationURL = (string)$instance->withPath('/oauth/authorize');
		$this->accessTokenURL   = (string)$instance->withPath('/oauth/token');
		$this->userRevokeURL    = (string)$instance->withPath('/oauth/authorized_applications');
		$this->applicationURL   = (string)$instance->withPath('/settings/applications');

		return $this;
	}

	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		$this->checkState($state);  // we're an instance of CSRFToken

		$body     = $this->getAccessTokenRequestBodyParams($code);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $body);
		$token    = $this->parseTokenResponse($response);

		// store the instance the token belongs to
		$token->extraParams = array_merge($token->extraParams, ['instance' => $this->instance]);

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1/accounts/verify_credentials');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['avatar'],
			'handle'      => $json['acct'],
			'displayName' => $json['display_name'],
			'id'          => $json['id'],
			'url'         => $json['url'],
		];

		return new AuthenticatedUser($userdata);
	}

}
