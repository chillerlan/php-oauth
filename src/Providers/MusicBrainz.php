<?php
/**
 * Class MusicBrainz
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\OAuth\Core\{CSRFToken, OAuth2Provider, TokenRefresh};
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function explode, in_array, strtoupper;

/**
 * MusicBrainz OAuth2
 *
 * @see https://musicbrainz.org/doc/Development
 * @see https://musicbrainz.org/doc/Development/OAuth2
 */
class MusicBrainz extends OAuth2Provider implements CSRFToken, TokenRefresh{

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

	protected string      $authURL        = 'https://musicbrainz.org/oauth2/authorize';
	protected string      $accessTokenURL = 'https://musicbrainz.org/oauth2/token';
	protected string      $apiURL         = 'https://musicbrainz.org/ws/2';
	protected string|null $userRevokeURL  = 'https://musicbrainz.org/account/applications';
	protected string|null $apiDocs        = 'https://musicbrainz.org/doc/Development';
	protected string|null $applicationURL = 'https://musicbrainz.org/account/applications';

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
		$token  = $this->storage->getAccessToken($this->serviceName);

		if($token->isExpired()){
			$this->refreshAccessToken($token);
		}

		if(!isset($params['fmt'])){
			$params['fmt'] = 'json';
		}

		if(in_array($method, ['POST', 'PUT', 'DELETE']) && !isset($params['client'])){
			$params['client'] = $this->options->user_agent; // @codeCoverageIgnore
		}

		return parent::request(explode('?', $path)[0], $params, $method, $body, $headers);
	}

}
