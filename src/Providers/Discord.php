<?php
/**
 * Class Discord
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

use chillerlan\OAuth\Core\{
	AccessToken, AuthenticatedUser, ClientCredentials, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh, UserInfo
};
use function sprintf;

/**
 * Discord OAuth2
 *
 * @see https://discord.com/developers/docs/topics/oauth2
 */
class Discord extends OAuth2Provider implements ClientCredentials, CSRFToken, TokenInvalidate, TokenRefresh, UserInfo{

	public const SCOPE_APPLICATIONS_COMMANDS                    = 'applications.commands';
	public const SCOPE_APPLICATIONS_COMMANDS_UPDATE             = 'applications.commands.update';
	public const SCOPE_APPLICATIONS_COMMANDS_PERMISSIONS_UPDATE = 'applications.commands.permissions.update';
	public const SCOPE_APPLICATIONS_ENTITLEMENTS                = 'applications.entitlements';
	public const SCOPE_BOT                                      = 'bot';
	public const SCOPE_CONNECTIONS                              = 'connections';
	public const SCOPE_EMAIL                                    = 'email';
	public const SCOPE_GDM_JOIN                                 = 'gdm.join';
	public const SCOPE_GUILDS                                   = 'guilds';
	public const SCOPE_GUILDS_JOIN                              = 'guilds.join';
	public const SCOPE_GUILDS_MEMBERS_READ                      = 'guilds.members.read';
	public const SCOPE_IDENTIFY                                 = 'identify';
	public const SCOPE_MESSAGES_READ                            = 'messages.read';
	public const SCOPE_RELATIONSHIPS_READ                       = 'relationships.read';
	public const SCOPE_ROLE_CONNECTIONS_WRITE                   = 'role_connections.write';
	public const SCOPE_RPC                                      = 'rpc';
	public const SCOPE_RPC_ACTIVITIES_WRITE                     = 'rpc.activities.write';
	public const SCOPE_RPC_NOTIFICATIONS_READ                   = 'rpc.notifications.read';
	public const SCOPE_WEBHOOK_INCOMING                         = 'webhook.incoming';

	public const DEFAULT_SCOPES = [
		self::SCOPE_CONNECTIONS,
		self::SCOPE_EMAIL,
		self::SCOPE_IDENTIFY,
		self::SCOPE_GUILDS,
		self::SCOPE_GUILDS_JOIN,
		self::SCOPE_GDM_JOIN,
		self::SCOPE_MESSAGES_READ,
	];

	protected string      $authURL        = 'https://discordapp.com/api/oauth2/authorize';
	protected string      $accessTokenURL = 'https://discordapp.com/api/oauth2/token';
	protected string      $revokeURL      = 'https://discordapp.com/api/oauth2/token/revoke';
	protected string      $apiURL         = 'https://discordapp.com/api';
	protected string|null $apiDocs        = 'https://discord.com/developers/';
	protected string|null $applicationURL = 'https://discordapp.com/developers/applications/';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/users/@me');

		$userdata = [
			'data'        => $json,
			'avatar'      => sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $json['id'], $json['avatar']),
			'displayName' => $json['global_name'],
			'email'       => $json['email'],
			'handle'      => $json['username'],
			'id'          => $json['id'],
			'url'         => sprintf('https://discordapp.com/users/%s', $json['id']), // @me
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken $token = null):bool{
		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));

		$bodyParams = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'token'         => $tokenToInvalidate->accessToken,
		];

		$request = $this->requestFactory
			->createRequest('POST', $this->revokeURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		$request  = $this->setRequestBody($bodyParams, $request);
		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() === 200){

			if($token === null){
				$this->storage->clearAccessToken($this->name);
			}

			return true;
		}

		return false;
	}

}
