<?php
/**
 * Class GuildWars2
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AccessToken, OAuth2Provider};
use Psr\Http\Message\{ResponseInterface, UriInterface};
use function implode, preg_match, sprintf, str_starts_with, substr;

/**
 * Guild Wars 2
 *
 * Note: GW2 does not support authentication (anymore) but the API still works like a regular OAUth API, so...
 *
 * @see https://api.guildwars2.com/v2
 * @see https://wiki.guildwars2.com/wiki/API:Main
 */
class GuildWars2 extends OAuth2Provider{

	public const SCOPE_ACCOUNT     = 'account';
	public const SCOPE_INVENTORIES = 'inventories';
	public const SCOPE_CHARACTERS  = 'characters';
	public const SCOPE_TRADINGPOST = 'tradingpost';
	public const SCOPE_WALLET      = 'wallet';
	public const SCOPE_UNLOCKS     = 'unlocks';
	public const SCOPE_PVP         = 'pvp';
	public const SCOPE_BUILDS      = 'builds';
	public const SCOPE_PROGRESSION = 'progression';
	public const SCOPE_GUILDS      = 'guilds';

	protected string      $authURL        = 'https://api.guildwars2.com/v2/tokeninfo';
	protected string      $apiURL         = 'https://api.guildwars2.com';
	protected string|null $userRevokeURL  = 'https://account.arena.net/applications';
	protected string|null $apiDocs        = 'https://wiki.guildwars2.com/wiki/API:Main';
	protected string|null $applicationURL = 'https://account.arena.net/applications';

	/**
	 * @param string $access_token
	 *
	 * @return \chillerlan\OAuth\Core\AccessToken
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function storeGW2Token(string $access_token):AccessToken{

		if(!preg_match('/^[a-f\d\-]{72}$/i', $access_token)){
			throw new ProviderException('invalid token');
		}

		// to verify the token we need to send a request without authentication
		$request = $this->requestFactory
			->createRequest('GET', QueryUtil::merge($this->authURL, ['access_token' => $access_token]))
		;

		$tokeninfo = MessageUtil::decodeJSON($this->http->sendRequest($request));

		if(isset($tokeninfo->id) && str_starts_with($access_token, $tokeninfo->id)){
			$token                    = $this->createAccessToken();
			$token->accessToken       = $access_token;
			$token->accessTokenSecret = substr($access_token, 36, 36); // the actual token
			$token->expires           = AccessToken::EOL_NEVER_EXPIRES;
			$token->extraParams       = [
				'token_type' => 'Bearer',
				'id'         => $tokeninfo->id,
				'name'       => $tokeninfo->name,
				'scope'      => implode($this::SCOPE_DELIMITER, $tokeninfo->permissions),
			];

			$this->storage->storeAccessToken($token, $this->serviceName);

			return $token;
		}

		throw new ProviderException('unverified token'); // @codeCoverageIgnore
	}

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface{
		throw new ProviderException('GuildWars2 does not support authentication anymore.');
	}

	/**
	 * @inheritdoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		throw new ProviderException('GuildWars2 does not support authentication anymore.');
	}

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/v2/tokeninfo');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->text)){
			throw new ProviderException($json->text);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
