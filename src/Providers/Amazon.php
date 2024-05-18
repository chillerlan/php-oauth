<?php
/**
 * Class Amazon
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AuthenticatedUser, CSRFToken, OAuth2Provider, TokenRefresh, UserInfo};

/**
 * Login with Amazon for Websites (OAuth2)
 *
 * @link https://developer.amazon.com/docs/login-with-amazon/web-docs.html
 * @link https://developer.amazon.com/docs/login-with-amazon/conceptual-overview.html
 */
class Amazon extends OAuth2Provider implements CSRFToken, TokenRefresh, UserInfo{

	public const IDENTIFIER = 'AMAZON';

	public const SCOPE_PROFILE         = 'profile';
	public const SCOPE_PROFILE_USER_ID = 'profile:user_id';
	public const SCOPE_POSTAL_CODE     = 'postal_code';

	public const DEFAULT_SCOPES = [
		self::SCOPE_PROFILE,
		self::SCOPE_PROFILE_USER_ID,
	];

	protected string      $authorizationURL = 'https://www.amazon.com/ap/oa';
	protected string      $accessTokenURL   = 'https://www.amazon.com/ap/oatoken';
	protected string      $apiURL           = 'https://api.amazon.com';
	protected string|null $applicationURL   = 'https://developer.amazon.com/loginwithamazon/console/site/lwa/overview.html';
	protected string|null $apiDocs          = 'https://developer.amazon.com/docs/login-with-amazon/web-docs.html';

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/user/profile');

		$userdata = [
			'data'        => $json,
			'displayName' => $json['name'],
			'email'       => $json['email'],
			'id'          => $json['user_id'],
		];

		return new AuthenticatedUser($userdata);
	}

}
