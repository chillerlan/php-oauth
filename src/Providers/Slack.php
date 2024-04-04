<?php
/**
 * Class Slack
 *
 * @created      26.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, InvalidAccessTokenException, OAuth2Provider, UserInfo};
use function sprintf;

/**
 * Slack v2 OAuth2
 *
 * @see https://api.slack.com/authentication/oauth-v2
 * @see https://api.slack.com/authentication/sign-in-with-slack
 * @see https://api.slack.com/authentication/token-types
 */
class Slack extends OAuth2Provider implements CSRFToken, UserInfo{

	// bot token
	public const SCOPE_BOT                 = 'bot';

	// user token
	public const SCOPE_ADMIN               = 'admin';
	public const SCOPE_CHAT_WRITE_BOT      = 'chat:write:bot';
	public const SCOPE_CLIENT              = 'client';
	public const SCOPE_DND_READ            = 'dnd:read';
	public const SCOPE_DND_WRITE           = 'dnd:write';
	public const SCOPE_FILES_READ          = 'files:read';
	public const SCOPE_FILES_WRITE_USER    = 'files:write:user';
	public const SCOPE_IDENTIFY            = 'identify';
	public const SCOPE_IDENTITY_AVATAR     = 'identity.avatar';
	public const SCOPE_IDENTITY_BASIC      = 'identity.basic';
	public const SCOPE_IDENTITY_EMAIL      = 'identity.email';
	public const SCOPE_IDENTITY_TEAM       = 'identity.team';
	public const SCOPE_INCOMING_WEBHOOK    = 'incoming-webhook';
	public const SCOPE_POST                = 'post';
	public const SCOPE_READ                = 'read';
	public const SCOPE_REMINDERS_READ      = 'reminders:read';
	public const SCOPE_REMINDERS_WRITE     = 'reminders:write';
	public const SCOPE_SEARCH_READ         = 'search:read';
	public const SCOPE_STARS_READ          = 'stars:read';
	public const SCOPE_STARS_WRITE         = 'stars:write';

	// user & workspace tokens
	public const SCOPE_CHANNELS_HISTORY    = 'channels:history';
	public const SCOPE_CHANNELS_READ       = 'channels:read';
	public const SCOPE_CHANNELS_WRITE      = 'channels:write';
	public const SCOPE_CHAT_WRITE_USER     = 'chat:write:user';
	public const SCOPE_COMMANDS            = 'commands';
	public const SCOPE_EMOJI_READ          = 'emoji:read';
	public const SCOPE_GROUPS_HISTORY      = 'groups:history';
	public const SCOPE_GROUPS_READ         = 'groups:read';
	public const SCOPE_GROUPS_WRITE        = 'groups:write';
	public const SCOPE_IM_HISTORY          = 'im:history';
	public const SCOPE_IM_READ             = 'im:read';
	public const SCOPE_IM_WRITE            = 'im:write';
	public const SCOPE_LINKS_READ          = 'links:read';
	public const SCOPE_LINKS_WRITE         = 'links:write';
	public const SCOPE_MPIM_HISTORY        = 'mpim:history';
	public const SCOPE_MPIM_READ           = 'mpim:read';
	public const SCOPE_MPIM_WRITE          = 'mpim:write';
	public const SCOPE_PINS_READ           = 'pins:read';
	public const SCOPE_PINS_WRITE          = 'pins:write';
	public const SCOPE_REACTIONS_READ      = 'reactions:read';
	public const SCOPE_REACTIONS_WRITE     = 'reactions:write';
	public const SCOPE_TEAM_READ           = 'team:read';
	public const SCOPE_USERGROUPS_READ     = 'usergroups:read';
	public const SCOPE_USERGROUPS_WRITE    = 'usergroups:write';
	public const SCOPE_USERS_PROFILE_READ  = 'users.profile:read';
	public const SCOPE_USERS_PROFILE_WRITE = 'users.profile:write';
	public const SCOPE_USERS_READ          = 'users:read';
	public const SCOPE_USERS_READ_EMAIL    = 'users:read.email';
	public const SCOPE_USERS_WRITE         = 'users:write';

	public const DEFAULT_SCOPES = [
		self::SCOPE_IDENTITY_AVATAR,
		self::SCOPE_IDENTITY_BASIC,
		self::SCOPE_IDENTITY_EMAIL,
		self::SCOPE_IDENTITY_TEAM,
	];

	protected string      $authURL        = 'https://slack.com/oauth/v2/authorize';
	protected string      $accessTokenURL = 'https://slack.com/api/oauth.v2.access';
	protected string      $apiURL         = 'https://slack.com/api';
	protected string|null $userRevokeURL  = 'https://slack.com/apps/manage';
	protected string|null $apiDocs        = 'https://api.slack.com';
	protected string|null $applicationURL = 'https://api.slack.com/apps';

	/**
	 * HTTP/200 OK on errors? you're fired.
	 *
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/users.identity');

		if(!empty($json['ok'])){

			$userdata = [
				'data'        => $json,
				'avatar'      => $json['user']['image_512'],
				'displayName' => $json['user']['name'],
				'email'       => $json['user']['email'],
				'id'          => $json['user']['id'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['error'])){

			if($json['error'] === 'invalid_auth'){
				throw new InvalidAccessTokenException;
			}

			throw new ProviderException($json['error']);
		}

		throw new ProviderException(sprintf('user info error'));
	}

}
