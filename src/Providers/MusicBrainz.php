<?php
/**
 * Class MusicBrainz
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{
	AccessToken, AuthenticatedUser, CSRFToken, OAuth2Provider, TokenInvalidate, TokenRefresh, UserInfo
};
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function in_array, strtoupper;

/**
 * MusicBrainz OAuth2
 *
 * @see https://musicbrainz.org/doc/Development
 * @see https://musicbrainz.org/doc/Development/OAuth2
 */
class MusicBrainz extends OAuth2Provider implements CSRFToken, TokenInvalidate, TokenRefresh, UserInfo{

	public const SCOPE_PROFILE        = 'profile';
	public const SCOPE_EMAIL          = 'email';
	public const SCOPE_TAG            = 'tag';
	public const SCOPE_RATING         = 'rating';
	public const SCOPE_COLLECTION     = 'collection';
	public const SCOPE_SUBMIT_ISRC    = 'submit_isrc';
	public const SCOPE_SUBMIT_BARCODE = 'submit_barcode';

	public const DEFAULT_SCOPES = [
		self::SCOPE_PROFILE,
		self::SCOPE_EMAIL,
		self::SCOPE_TAG,
		self::SCOPE_RATING,
		self::SCOPE_COLLECTION,
	];

	protected string      $authorizationURL = 'https://musicbrainz.org/oauth2/authorize';
	protected string      $accessTokenURL   = 'https://musicbrainz.org/oauth2/token';
	protected string      $revokeURL        = 'https://musicbrainz.org/oauth2/revoke ';
	protected string      $apiURL           = 'https://musicbrainz.org/ws/2';
	protected string|null $userRevokeURL    = 'https://musicbrainz.org/account/applications';
	protected string|null $apiDocs          = 'https://musicbrainz.org/doc/Development';
	protected string|null $applicationURL   = 'https://musicbrainz.org/account/applications';

	/**
	 * @inheritdoc
	 */
	protected function getRefreshAccessTokenRequestBodyParams(string $refreshToken):array{
		return [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null,
	):ResponseInterface{
		$params = ($params ?? []);
		$method = strtoupper(($method ?? 'GET'));

		if(!isset($params['fmt'])){
			$params['fmt'] = 'json';
		}

		if(in_array($method, ['POST', 'PUT', 'DELETE']) && !isset($params['client'])){
			$params['client'] = $this::USER_AGENT; // @codeCoverageIgnore
		}

		return parent::request($path, $params, $method, $body, $headers, $protocolVersion);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('https://musicbrainz.org/oauth2/userinfo', ['fmt' => 'json']);

		$userdata = [
			'data'   => $json,
			'handle' => $json['sub'],
			'email'  => $json['email'],
			'id'     => $json['metabrainz_user_id'],
			'url'    => $json['profile'],
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * @inheritDoc
	 */
	public function invalidateAccessToken(AccessToken|null $token = null):bool{
		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));

		$request = $this->requestFactory
			->createRequest('POST', $this->revokeURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		$bodyParams = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'token'         => $tokenToInvalidate->accessToken,
		];

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
