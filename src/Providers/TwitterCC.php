<?php
/**
 * Class TwitterCC
 *
 * @created      08.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AccessToken, ClientCredentials, OAuth2Provider};
use Psr\Http\Message\UriInterface;

/**
 * Twitter OAuth2 (client credentials)
 *
 * @todo: twitter is dead. fuck elon musk.
 *
 * @see https://dev.twitter.com/overview/api
 * @see https://developer.twitter.com/en/docs/basics/authentication/overview/application-only
 *
 * @todo: https://developer.twitter.com/en/docs/basics/authentication/api-reference/invalidate_token
 */
class TwitterCC extends OAuth2Provider implements ClientCredentials{

	protected const AUTH_ERRMSG = 'TwitterCC only supports Client Credentials Grant, use the Twitter OAuth1 class for authentication instead.';

	protected string      $apiURL                    = 'https://api.twitter.com';
	protected string|null $clientCredentialsTokenURL = 'https://api.twitter.com/oauth2/token';
	protected string|null $userRevokeURL             = 'https://twitter.com/settings/applications';
	protected string|null $apiDocs                   = 'https://developer.twitter.com/en/docs/basics/authentication/overview/application-only';
	protected string|null $applicationURL            = 'https://developer.twitter.com/apps';

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface{
		throw new ProviderException($this::AUTH_ERRMSG);
	}

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		throw new ProviderException($this::AUTH_ERRMSG);
	}

}
