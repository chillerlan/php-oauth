<?php
/**
 * Class Stripe
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh, UserInfo};

/**
 * Stripe OAuth2
 *
 * @link https://stripe.com/docs/api
 * @link https://stripe.com/docs/connect/authentication
 * @link https://stripe.com/docs/connect/oauth-reference
 * @link https://stripe.com/docs/connect/standard-accounts
 * @link https://gist.github.com/amfeng/3507366
 */
class Stripe extends OAuth2Provider implements CSRFToken, TokenRefresh, TokenInvalidate, UserInfo{

	public const IDENTIFIER = 'STRIPE';

	public const SCOPE_READ_WRITE = 'read_write';
	public const SCOPE_READ_ONLY  = 'read_only';

	public const DEFAULT_SCOPES = [
		self::SCOPE_READ_ONLY,
	];

	protected string      $authorizationURL = 'https://connect.stripe.com/oauth/authorize';
	protected string      $accessTokenURL   = 'https://connect.stripe.com/oauth/token';
	protected string      $revokeURL        = 'https://connect.stripe.com/oauth/deauthorize';
	protected string      $apiURL           = 'https://api.stripe.com/v1';
	protected string|null $userRevokeURL    = 'https://dashboard.stripe.com/account/applications';
	protected string|null $apiDocs          = 'https://stripe.com/docs/api';
	protected string|null $applicationURL   = 'https://dashboard.stripe.com/apikeys';

	/** @codeCoverageIgnore */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('/accounts');

		$userdata = [
			'data' => $json,
			'id'   => $json['data'][0]['id'],
		];

		return new AuthenticatedUser($userdata);
	}

	protected function getInvalidateAccessTokenBodyParams(AccessToken $token, string $type):array{
		$params = $token->extraParams;

		if(!isset($params['stripe_user_id'])){
			throw new ProviderException('"stripe_user_id" not found in token');
		}

		return [
			'client_id'      => $this->options->key,
			'stripe_user_id' => $params['stripe_user_id'],
		];
	}

}
