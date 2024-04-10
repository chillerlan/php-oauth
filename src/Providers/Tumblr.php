<?php
/**
 * Class Tumblr
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, OAuth1Provider, UserInfo};
use function sprintf;

/**
 * Tumblr OAuth1
 *
 * @see https://www.tumblr.com/docs/en/api/v2#oauth1-authorization
 */
class Tumblr extends OAuth1Provider implements UserInfo{

	public const IDENTIFIER = 'TUMBLR';

	protected string      $requestTokenURL  = 'https://www.tumblr.com/oauth/request_token';
	protected string      $authorizationURL = 'https://www.tumblr.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://www.tumblr.com/oauth/access_token';
	protected string      $apiURL           = 'https://api.tumblr.com';
	protected string|null $userRevokeURL    = 'https://www.tumblr.com/settings/apps';
	protected string|null $apiDocs          = 'https://www.tumblr.com/docs/en/api/v2';
	protected string|null $applicationURL   = 'https://www.tumblr.com/oauth/apps';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v2/user/info');

		$userdata = [
			'data'   => $json,
			'handle' => $json['response']['user']['name'],
			'url'    => sprintf('https://www.tumblr.com/%s', $json['response']['user']['name']),
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * Exchange the current token for an OAuth2 token - this will invalidate the OAuth1 token.
	 *
	 * @see https://www.tumblr.com/docs/en/api/v2#v2oauth2exchange---oauth1-to-oauth2-token-exchange
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function exchangeForOAuth2Token():AccessToken{
		$response = $this->request(path: '/v2/oauth2/exchange', method: 'POST');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response);

		if($status === 200){
			$token = $this->createAccessToken();

			$token->accessToken  = $json->access_token;
			$token->refreshToken = $json->refresh_token;
			$token->expires      = $json->expires_in;
			$token->extraParams  = ['scope' => $json->scope, 'token_type' => $json->token_type];

			$this->storage->storeAccessToken($token, $this->name);

			return $token;
		}

		if(isset($json->meta, $json->meta->msg)){
			throw new ProviderException($json->meta->msg);
		}

		throw new ProviderException(sprintf('token exchange error HTTP/%s', $status));
	}

}
