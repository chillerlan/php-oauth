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

use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, OAuthProvider, UserInfo};
use chillerlan\HTTP\Utils\UriUtil;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function explode, intval, preg_replace;

/**
 * Steam OpenID
 *
 * @see https://steamcommunity.com/dev
 * @see https://partner.steamgames.com/doc/webapi_overview
 * @see https://steamwebapi.azurewebsites.net/
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
	 *
	 */
	public function getAccessToken(array $received):AccessToken{

		$body = [
			'openid.mode' => 'check_authentication',
			'openid.ns'   => 'http://specs.openid.net/auth/2.0',
			'openid.sig'  => $received['openid_sig'],
		];

		foreach(explode(',', $received['openid_signed']) as $item){
			$body['openid.'.$item] = $received['openid_'.$item];
		}

		$request = $this->requestFactory
			->createRequest('POST', $this->accessTokenURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body)));

		$token = $this->parseTokenResponse($this->http->sendRequest($request));
		$id    = preg_replace('/[^\d]/', '', $received['openid_claimed_id']);

		// as this method is intended for one-time authentication only we'll not receive a token.
		// instead we're gonna save the verified steam user id as token as it is required
		// for several "authenticated" endpoints.
		$token->accessToken = $id;
		$token->extraParams = [
			'claimed_id' => $received['openid_claimed_id'],
			'id_int'     => intval($id),
		];

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{
		$data = explode("\x0a", (string)$response->getBody());

		if(!isset($data[1]) || !str_starts_with($data[1], 'is_valid')){
			throw new ProviderException('unable to parse token response');
		}

		if($data[1] !== 'is_valid:true'){
			throw new ProviderException('invalid id');
		}

		// the response is only validation, so we'll just return an empty token and add the id in the next step
		$token = $this->createAccessToken();

		$token->accessToken = 'SteamID';
		$token->expires     = AccessToken::NEVER_EXPIRES;

		return $token;
	}

	/**
	 *
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		$uri = UriUtil::withQueryValue($request->getUri(), 'key', $this->options->secret);

		return $request->withUri($uri);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
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
