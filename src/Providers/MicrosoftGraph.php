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

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\AuthenticatedUser;
use function sprintf;

/**
 * Microsoft Graph OAuth2
 *
 * @see https://learn.microsoft.com/en-us/graph/permissions-reference
 */
class MicrosoftGraph extends AzureActiveDirectory{

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

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/v1.0/me');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){

			$userdata = [
				'data'        => $json,
				'handle'      => $json['userPrincipalName'],
				'displayName' => $json['displayName'],
				'email'       => $json['mail'],
				'id'          => $json['id'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['error'], $json['error']['message'])){
			throw new ProviderException($json['error']['message']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
