<?php
/**
 * Class Google
 *
 * @created      09.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider};
use function sprintf;

/**
 * Google OAuth2
 *
 * @see https://developers.google.com/identity/protocols/oauth2/web-server
 * @see https://developers.google.com/identity/protocols/oauth2/service-account
 * @see https://developers.google.com/oauthplayground/
 */
class Google extends OAuth2Provider implements CSRFToken{

	public const SCOPE_EMAIL            = 'email';
	public const SCOPE_PROFILE          = 'profile';
	public const SCOPE_USERINFO_EMAIL   = 'https://www.googleapis.com/auth/userinfo.email';
	public const SCOPE_USERINFO_PROFILE = 'https://www.googleapis.com/auth/userinfo.profile';

	public const DEFAULT_SCOPES = [
		self::SCOPE_EMAIL,
		self::SCOPE_PROFILE,
	];

	protected string      $authURL        = 'https://accounts.google.com/o/oauth2/auth';
	protected string      $accessTokenURL = 'https://accounts.google.com/o/oauth2/token';
	protected string      $apiURL         = 'https://www.googleapis.com';
	protected string|null $userRevokeURL  = 'https://myaccount.google.com/connections';
	protected string|null $apiDocs        = 'https://developers.google.com/oauthplayground/';
	protected string|null $applicationURL = 'https://console.developers.google.com/apis/credentials';

	/**
	 * @inheritDoc
	 */
	public function me():AuthenticatedUser{
		$response = $this->request('/userinfo/v2/me');
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		if($status === 200){

			$userdata = [
				'data'        => $json,
				'avatar'      => $json['picture'],
				'displayName' => $json['name'],
				'email'       => $json['email'],
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
