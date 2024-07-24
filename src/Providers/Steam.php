<?php
/**
 * Class Steam
 *
 * @created      15.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil, UriUtil};
use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, OAuthProvider, UserInfo};
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function explode, intval, str_replace;

/**
 * Steam OpenID
 *
 * @link https://steamcommunity.com/dev
 * @link https://partner.steamgames.com/doc/webapi_overview
 * @link https://partner.steamgames.com/doc/features/auth
 * @link https://steamwebapi.azurewebsites.net/
 */
class Steam extends OAuthProvider implements UserInfo{

	public const IDENTIFIER = 'STEAM';

	protected string      $authorizationURL = 'https://steamcommunity.com/openid/login';
	protected string      $accessTokenURL   = 'https://steamcommunity.com/openid/login';
	protected string      $apiURL           = 'https://api.steampowered.com';
	protected string|null $applicationURL   = 'https://steamcommunity.com/dev/apikey';
	protected string|null $apiDocs          = 'https://developer.valvesoftware.com/wiki/Steam_Web_API';

	/**
	 * we ignore user supplied params here
	 *
	 * @inheritDoc
	 *
	 * @param array<string, string>|null $params
	 * @param string[]|null              $scopes
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface{

		$params = [
			'openid.ns'         => 'http://specs.openid.net/auth/2.0',
			'openid.mode'       => 'checkid_setup',
			'openid.return_to'  => $this->options->callbackURL,
			'openid.realm'      => $this->options->key,
			'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
			'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
		];

		return $this->uriFactory->createUri(QueryUtil::merge($this->authorizationURL, $params));
	}

	/**
	 * Obtains an "authentication token" (the steamID64)
	 *
	 * @param array<string, string> $urlQuery
	 */
	public function getAccessToken(array $urlQuery):AccessToken{
		$body     = $this->getAccessTokenRequestBodyParams($urlQuery);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $body);
		$token    = $this->parseTokenResponse($response, $urlQuery['openid_claimed_id']);

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * prepares the request body parameters for the access token request
	 *
	 * @param array<string, string> $received
	 */
	protected function getAccessTokenRequestBodyParams(array $received):array{

		$body = [
			'openid.mode' => 'check_authentication',
			'openid.ns'   => 'http://specs.openid.net/auth/2.0',
			'openid.sig'  => $received['openid_sig'],
		];

		foreach(explode(',', $received['openid_signed']) as $item){
			$body['openid.'.$item] = $received['openid_'.$item];
		}

		return $body;
	}

	/**
	 * sends a request to the access token endpoint $url with the given $params as URL query
	 *
	 * @param array<string, string> $body
	 */
	protected function sendAccessTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body)));

		return $this->http->sendRequest($request);
	}

	/**
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response, string $claimed_id):AccessToken{
		$data = explode("\x0a", MessageUtil::getContents($response));

		if(!isset($data[1]) || !str_starts_with($data[1], 'is_valid')){
			throw new ProviderException('unable to parse token response');
		}

		if($data[1] !== 'is_valid:true'){
			throw new ProviderException('invalid id');
		}

		$token = $this->createAccessToken();
		$id    = str_replace('https://steamcommunity.com/openid/id/', '', $claimed_id);

		// as this method is intended for one-time authentication only we'll not receive a token.
		// instead we're gonna save the verified steam user id as token as it is required
		// for several "authenticated" endpoints.
		$token->accessToken = $id;
		$token->expires     = AccessToken::NEVER_EXPIRES;
		$token->extraParams = [
			'claimed_id' => $claimed_id,
			'id_int'     => intval($id),
		];

		return $token;
	}

	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		$uri = UriUtil::withQueryValue($request->getUri(), 'key', $this->options->secret);

		return $request->withUri($uri);
	}

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$token = $this->storage->getAccessToken($this->name);
		$json  = $this->getMeResponseData('/ISteamUser/GetPlayerSummaries/v0002/', ['steamids' => $token->accessToken]);

		if(!isset($json['response']['players'][0])){
			throw new ProviderException('invalid response');
		}

		$data = $json['response']['players'][0];

		$userdata = [
			'data'        => $data,
			'avatar'      => $data['avatarfull'],
			'displayName' => $data['personaname'],
			'id'          => $data['steamid'],
			'url'         => $data['profileurl'],
		];

		return new AuthenticatedUser($userdata);
	}

}
