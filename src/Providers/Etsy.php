<?php
/**
 * Class Etsy
 *
 * @created      06.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, PKCE, TokenRefresh};

/**
 * @see https://developers.etsy.com/documentation/essentials/authentication
 */
class Etsy extends OAuth2Provider implements CSRFToken, PKCE, TokenRefresh{

	public const IDENTIFIER = 'ETSY';

	public const SCOPE_ADDRESS_R      = 'address_r';
	public const SCOPE_ADDRESS_W      = 'address_w';
	public const SCOPE_BILLING_R      = 'billing_r';
	public const SCOPE_CART_R         = 'cart_r';
	public const SCOPE_CART_W         = 'cart_w';
	public const SCOPE_EMAIL_R        = 'email_r';
	public const SCOPE_FAVORITES_R    = 'favorites_r';
	public const SCOPE_FAVORITES_W    = 'favorites_w';
	public const SCOPE_FEEDBACK_R     = 'feedback_r';
	public const SCOPE_LISTINGS_D     = 'listings_d';
	public const SCOPE_LISTINGS_R     = 'listings_r';
	public const SCOPE_LISTINGS_W     = 'listings_w';
	public const SCOPE_PROFILE_R      = 'profile_r';
	public const SCOPE_PROFILE_W      = 'profile_w';
	public const SCOPE_RECOMMEND_R    = 'recommend_r';
	public const SCOPE_RECOMMEND_W    = 'recommend_w';
	public const SCOPE_SHOPS_R        = 'shops_r';
	public const SCOPE_SHOPS_W        = 'shops_w';
	public const SCOPE_TRANSACTIONS_R = 'transactions_r';
	public const SCOPE_TRANSACTIONS_W = 'transactions_w';

	public const DEFAULT_SCOPES = [
		self::SCOPE_EMAIL_R,
	];

	protected string      $authorizationURL = 'https://www.etsy.com/oauth/connect';
	protected string      $accessTokenURL   = 'https://api.etsy.com/v3/public/oauth/token';
	protected string      $apiURL           = 'https://api.etsy.com';
	protected string|null $apiDocs          = 'https://developers.etsy.com/documentation/reference/';
	protected string|null $applicationURL   = 'https://www.etsy.com/developers/your-apps';
	protected string|null $userRevokeURL    = 'hhttps://www.etsy.com/your/apps';

	/**
	 * @inheritDoc
	 */
	protected function getAccessTokenRequestBodyParams(string $code):array{

		$params = [
			'code'         => $code,
			'grant_type'   => 'authorization_code',
			'redirect_uri' => $this->options->callbackURL,
			'client_id'    => $this->options->key,
		];

		return $this->setCodeVerifier($params);
	}

	/**
	 * @inheritDoc
	 */
	protected function getRefreshAccessTokenRequestBodyParams(string $refreshToken):array{
		return [
			'client_id'     => $this->options->key,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
		];
	}


}
