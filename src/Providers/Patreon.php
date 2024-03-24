<?php
/**
 * Class Patreon
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh};
use function in_array, sprintf;

/**
 * Patreon v2 OAuth2
 *
 * @see https://docs.patreon.com/
 * @see https://docs.patreon.com/#oauth
 * @see https://docs.patreon.com/#apiv2-oauth
 */
class Patreon extends OAuth2Provider implements CSRFToken, TokenRefresh{

	public const SCOPE_V1_USERS                     = 'users';
	public const SCOPE_V1_PLEDGES_TO_ME             = 'pledges-to-me';
	public const SCOPE_V1_MY_CAMPAIGN               = 'my-campaign';

	// wow, consistency...
	public const SCOPE_V2_IDENTITY                  = 'identity';
	public const SCOPE_V2_IDENTITY_EMAIL            = 'identity[email]';
	public const SCOPE_V2_IDENTITY_MEMBERSHIPS      = 'identity.memberships';
	public const SCOPE_V2_CAMPAIGNS                 = 'campaigns';
	public const SCOPE_V2_CAMPAIGNS_WEBHOOK         = 'w:campaigns.webhook';
	public const SCOPE_V2_CAMPAIGNS_MEMBERS         = 'campaigns.members';
	public const SCOPE_V2_CAMPAIGNS_MEMBERS_EMAIL   = 'campaigns.members[email]';
	public const SCOPE_V2_CAMPAIGNS_MEMBERS_ADDRESS = 'campaigns.members.address';

	public const DEFAULT_SCOPES = [
		self::SCOPE_V2_IDENTITY,
		self::SCOPE_V2_IDENTITY_EMAIL,
		self::SCOPE_V2_IDENTITY_MEMBERSHIPS,
		self::SCOPE_V2_CAMPAIGNS,
		self::SCOPE_V2_CAMPAIGNS_MEMBERS,
	];

	protected string      $authURL        = 'https://www.patreon.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://www.patreon.com/api/oauth2/token';
	protected string      $apiURL         = 'https://www.patreon.com/api/oauth2';
	protected string|null $apiDocs        = 'https://docs.patreon.com/';
	protected string|null $applicationURL = 'https://www.patreon.com/portal/registration/register-clients';

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$token = $this->storage->getAccessToken($this->serviceName);

		if(in_array($this::SCOPE_V2_IDENTITY, $token->scopes)){
			$endpoint = '/v2/identity';
			$params   = ['fields[user]' => 'about,created,email,first_name,full_name,image_url,last_name,social_connections,thumb_url,url,vanity'];
		}
		elseif(in_array($this::SCOPE_V1_USERS, $token->scopes)){
			$endpoint = '/api/current_user';
			$params   = [];
		}
		else{
			throw new ProviderException('invalid scopes for the identity endpoint');
		}

		$response = $this->request($endpoint, $params);
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){

			$userdata = [
				'data'        => $json,
				'handle'      => $json['data']['attributes']['vanity'],
				'avatar'      => $json['data']['attributes']['image_url'],
				'displayName' => $json['data']['attributes']['full_name'],
				'email'       => $json['data']['attributes']['email'],
				'id'          => $json['data']['id'],
				'url'         => $json['data']['attributes']['url'],
			];

			return new AuthenticatedUser($userdata);
		}

		if(isset($json['errors'][0]['code_name'])){
			throw new ProviderException($json['errors'][0]['code_name']);
		}

		throw new ProviderException(sprintf('user info error HTTP/%s', $status));
	}

}
