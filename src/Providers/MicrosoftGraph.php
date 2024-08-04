<?php
/**
 * Class MicrosoftGraph
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, UserInfo};

/**
 * Microsoft Graph OAuth2
 *
 * @link https://learn.microsoft.com/en-us/graph/permissions-reference
 */
class MicrosoftGraph extends AzureActiveDirectory implements UserInfo{

	public const IDENTIFIER = 'MICROSOFTGRAPH';

	public const SCOPE_USER_READ          = 'User.Read';
	public const SCOPE_USER_READBASIC_ALL = 'User.ReadBasic.All';

	public const DEFAULT_SCOPES = [
		self::SCOPE_OPENID,
		self::SCOPE_OPENID_EMAIL,
		self::SCOPE_OPENID_PROFILE,
		self::SCOPE_OFFLINE_ACCESS,
		self::SCOPE_USER_READ,
		self::SCOPE_USER_READBASIC_ALL,
	];

	protected string      $apiURL  = 'https://graph.microsoft.com';
	protected string|null $apiDocs = 'https://learn.microsoft.com/graph/overview';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/v1.0/me');

		$userdata = [
			'data'        => $json,
			'handle'      => $json['userPrincipalName'],
			'displayName' => $json['displayName'],
			'email'       => $json['mail'],
			'id'          => $json['id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
