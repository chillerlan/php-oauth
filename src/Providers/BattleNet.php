<?php
/**
 * Class BattleNet
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, UserInfo};
use function in_array, ltrim, rtrim, sprintf, strtolower;

/**
 * Battle.net OAuth2
 *
 * @link https://develop.battle.net/documentation/guides/using-oauth
 */
class BattleNet extends OAuth2Provider implements ClientCredentials, CSRFToken, UserInfo{

	public const IDENTIFIER = 'BATTLENET';

	public const SCOPE_OPENID      = 'openid';
	public const SCOPE_PROFILE_D3  = 'd3.profile';
	public const SCOPE_PROFILE_SC2 = 'sc2.profile';
	public const SCOPE_PROFILE_WOW = 'wow.profile';

	public const DEFAULT_SCOPES = [
		self::SCOPE_OPENID,
		self::SCOPE_PROFILE_D3,
		self::SCOPE_PROFILE_SC2,
		self::SCOPE_PROFILE_WOW,
	];

	protected string|null $apiDocs        = 'https://develop.battle.net/documentation';
	protected string|null $applicationURL = 'https://develop.battle.net/access/clients';
	protected string|null $userRevokeURL  = 'https://account.blizzard.com/connections';

	// the URL for the "OAuth" endpoints
	// @link https://develop.battle.net/documentation/battle-net/oauth-apis
	protected string $battleNetOauth      = 'https://oauth.battle.net';
	protected string $region              = 'eu';
	// these URLs will be set dynamically, depending on the chose datacenter
	protected string $apiURL              = 'https://eu.api.blizzard.com';
	protected string $authorizationURL    = 'https://oauth.battle.net/authorize';
	protected string $accessTokenURL      = 'https://oauth.battle.net/token';

	protected const KNOWN_DOMAINS = [
		'oauth.battle.net',
		'eu.api.blizzard.com',
		'kr.api.blizzard.com',
		'tw.api.blizzard.com',
		'us.api.blizzard.com',
		'gateway.battlenet.com.cn',
		'oauth.battlenet.com.cn',
	];

	protected function getRequestTarget(string $uri):string{
		$parsedURL  = $this->uriFactory->createUri($uri);
		$parsedHost = $parsedURL->getHost();
		$api        = $this->uriFactory->createUri($this->apiURL);

		if($parsedHost === ''){
			$parsedPath = $parsedURL->getPath();
			$apiURL     = rtrim((string)$api, '/');

			if($parsedPath === ''){
				return $apiURL;
			}

			return sprintf('%s/%s', $apiURL, ltrim($parsedPath, '/'));
		}

		// for some reason we were given a host name

		// shortcut here for the known domains
		if(in_array($parsedHost, $this::KNOWN_DOMAINS, true)){
			// we explicitly ignore any existing parameters here
			return (string)$parsedURL->withScheme('https')->withQuery('')->withFragment('');
		}

		// back out if it doesn't match
		throw new ProviderException(sprintf('given host (%s) does not match provider (%s)', $parsedHost, $api->getHost()));
	}

	/**
	 * Set the datacenter URLs for the given region
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function setRegion(string $region):static{
		$region = strtolower($region);

		if(!in_array($region, ['cn', 'eu', 'kr', 'tw', 'us'], true)){
			throw new ProviderException('invalid region: '.$region);
		}

		$this->region         = $region;
		$this->apiURL         = sprintf('https://%s.api.blizzard.com', $this->region);
		$this->battleNetOauth = 'https://oauth.battle.net';

		if($region === 'cn'){
			$this->apiURL         = 'https://gateway.battlenet.com.cn';
			$this->battleNetOauth = 'https://oauth.battlenet.com.cn';
		}

		$this->authorizationURL = $this->battleNetOauth.'/authorize';
		$this->accessTokenURL   = $this->battleNetOauth.'/token';

		return $this;
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData($this->battleNetOauth.'/oauth/userinfo');

		$userdata = [
			'data'   => $json,
			'handle' => $json['battletag'],
			'id'     => $json['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
