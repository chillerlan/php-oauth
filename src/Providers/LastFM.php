<?php
/**
 * Class LastFM
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 *
 * @noinspection PhpUnused
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Core\{AccessToken, AuthenticatedUser, OAuthProvider, UserInfo};
use chillerlan\Settings\SettingsContainerAbstract;
use Psr\Http\Message\{RequestInterface, ResponseInterface, StreamInterface, UriInterface};
use DateTimeInterface, InvalidArgumentException, Throwable;
use function array_chunk, array_filter, array_merge, in_array, is_array, ksort, md5, sprintf, strtoupper, trim;

/**
 * Last.fm
 *
 * @link https://www.last.fm/api/authentication
 */
class LastFM extends OAuthProvider implements UserInfo{

	public const IDENTIFIER = 'LASTFM';

	public const PERIOD_OVERALL = 'overall';
	public const PERIOD_7DAY    = '7day';
	public const PERIOD_1MONTH  = '1month';
	public const PERIOD_3MONTH  = '3month';
	public const PERIOD_6MONTH  = '6month';
	public const PERIOD_12MONTH = '12month';

	public const PERIODS = [
		self::PERIOD_OVERALL,
		self::PERIOD_7DAY,
		self::PERIOD_1MONTH,
		self::PERIOD_3MONTH,
		self::PERIOD_6MONTH,
		self::PERIOD_12MONTH,
	];

	protected string      $authorizationURL = 'https://www.last.fm/api/auth';
	protected string      $accessTokenURL   = 'https://ws.audioscrobbler.com/2.0';
	protected string      $apiURL           = 'https://ws.audioscrobbler.com/2.0';
	protected string|null $userRevokeURL    = 'https://www.last.fm/settings/applications';
	protected string|null $apiDocs          = 'https://www.last.fm/api/';
	protected string|null $applicationURL   = 'https://www.last.fm/api/account/create';

	protected array $scrobbles = [];

	/**
	 * @inheritdoc
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface{

		$params = array_merge(($params ?? []), [
			'api_key' => $this->options->key,
		]);

		return $this->uriFactory->createUri(QueryUtil::merge($this->authorizationURL, $params));
	}

	/**
	 * Obtains an authentication token
	 */
	public function getAccessToken(string $session_token):AccessToken{
		$params   = $this->getAccessTokenRequestBodyParams($session_token);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $params);
		$token    = $this->parseTokenResponse($response);

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * prepares the request body parameters for the access token request
	 */
	protected function getAccessTokenRequestBodyParams(string $session_token):array{

		$params = [
			'method'  => 'auth.getSession',
			'format'  => 'json',
			'api_key' => $this->options->key,
			'token'   => $session_token,
		];

		return $this->addSignature($params);
	}

	/**
	 * sends a request to the access token endpoint $url with the given $params as URL query
	 */
	protected function sendAccessTokenRequest(string $url, array $params):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('GET', QueryUtil::merge($url, $params))
			->withHeader('Accept', 'application/json')
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Length', '0')
		;

		return $this->http->sendRequest($request);
	}

	/**
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{

		try{
			$data = MessageUtil::decodeJSON($response, true);

			if(!is_array($data)){
				throw new ProviderException;
			}
		}
		catch(Throwable){
			throw new ProviderException('unable to parse token response');
		}

		if(isset($data['error'])){
			throw new ProviderException(sprintf('error retrieving access token: "%s"', $data['message']));
		}

		if(!isset($data['session']['key'])){
			throw new ProviderException('token missing');
		}

		$token = $this->createAccessToken();

		$token->accessToken  = $data['session']['key'];
		$token->expires      = AccessToken::NEVER_EXPIRES;

		unset($data['session']['key']);

		$token->extraParams = $data;

		return $token;
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
		string|null                       $protocolVersion = null
	):ResponseInterface{
		$method    = strtoupper($method ?? 'GET');
		$headers ??= [];

		if($body !== null && !is_array($body)){
			throw new InvalidArgumentException('$body must be an array');
		}

		// all parameters go either in the query or in the body - there is no in-between
		$params = array_merge(($params ?? []), ($body ?? []), ['method' => $path]);

		if(!isset($params['format'])){
			$params['format']  = 'json';
			$headers['Accept'] = 'application/json';
		}

		// request authorization is always part of the parameter array
		$params = $this->getAuthorization($params);

		if($method === 'POST'){
			$body   = $params;
			$params = [];

			$headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}

		return parent::request('', $params, $method, $body, $headers, $protocolVersion);
	}

	/**
	 * adds the authorization parameters to the request parameters
	 */
	protected function getAuthorization(array $params, AccessToken|null $token = null):array{
		$token ??= $this->storage->getAccessToken($this->name);

		$params = array_merge($params, [
			'api_key' => $this->options->key,
			'sk'      => $token->accessToken,
		]);

		return $this->addSignature($params);
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		// noop - just return the request
		return $request;
	}

	/**
	 * returns the signature for the set of parameters
	 */
	protected function addSignature(array $params):array{

		if(!isset($params['api_key'])){
			throw new ProviderException('"api_key" missing'); // @codeCoverageIgnore
		}

		ksort($params);

		$signature = '';

		foreach($params as $k => $v){

			if(in_array($k, ['format', 'callback'])){
				continue;
			}

			$signature .= $k.$v;
		}

		$params['api_sig'] = md5($signature.$this->options->secret);

		return $params;
	}

	/**
	 * @inheritDoc
	 */
	protected function sendMeRequest(string $endpoint, array|null $params = null):ResponseInterface{
		return $this->request($endpoint, $params);
	}

	/**
	 * @inheritDoc
	 * @codeCoverageIgnore
	 */
	public function me():AuthenticatedUser{
		$json = $this->getMeResponseData('user.getInfo');

		$userdata = [
			'data'        => $json,
			'avatar'      => $json['user']['image'][3]['#text'],
			'handle'      => $json['user']['name'],
			'displayName' => $json['user']['realname'],
			'url'         => $json['user']['url'],
		];

		return new AuthenticatedUser($userdata);
	}

	/**
	 * Scrobbles an array of one or more tracks
	 *
	 * There is no limit for adding tracks, they will be sent to the API in chunks of 50 automatically.
	 * The return value of this method is an array that contains a response array for each 50 tracks sent,
	 * if an error happened, the element will be null.
	 *
	 * Each track array may consist of the following values
	 *
	 *   - artist      : [required] The artist name.
	 *   - track       : [required] The track name.
	 *   - timestamp   : [required] The time the track started playing, in UNIX timestamp format (UTC time zone).
	 *   - album       : [optional] The album name.
	 *   - context     : [optional] Sub-client version (not public, only enabled for certain API keys)
	 *   - streamId    : [optional] The stream id for this track received from the radio.getPlaylist service,
	 *                             if scrobbling Last.fm radio (unavailable)
	 *   - chosenByUser: [optional] Set to 1 if the user chose this song, or 0 if the song was chosen by someone else
	 *                             (such as a radio station or recommendation service). Assumes 1 if not specified
	 *   - trackNumber : [optional] The track number of the track on the album.
	 *   - mbid        : [optional] The MusicBrainz Track ID.
	 *   - albumArtist : [optional] The album artist - if this differs from the track artist.
	 *   - duration    : [optional] The length of the track in seconds.
	 *
	 * @link https://www.last.fm/api/show/track.scrobble
	 */
	public function scrobble(array $tracks):array{

		// a single track was given
		if(isset($tracks['artist'], $tracks['track'], $tracks['timestamp'])){
			$tracks = [$tracks];
		}

		foreach($tracks as $track){
			$this->addScrobble($track);
		}

		if(empty($this->scrobbles)){
			throw new InvalidArgumentException('no tracks to scrobble'); // @codeCoverageIgnore
		}

		// we're going to collect the responses in an array
		$return = [];

		// 50 tracks max per request
		foreach(array_chunk($this->scrobbles, 50) as $chunk){
			$body = [];

			foreach($chunk as $i => $track){
				foreach($track as $key => $value){
					$body[sprintf('%s[%s]', $key, $i)] = $value;
				}
			}

			$return[] = $this->sendScrobbles($body);
		}

		return $return;
	}

	/**
	 * Adds a track to scrobble
	 */
	public function addScrobble(array $track):static{

		if(!isset($track['artist'], $track['track'], $track['timestamp'])){
			throw new InvalidArgumentException('"artist", "track" and "timestamp" are required'); // @codeCoverageIgnore
		}

		$this->scrobbles[] = $this->parseTrack($track);

		return $this;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function clearScrobbles():static{
		$this->scrobbles = [];

		return $this;
	}

	/**
	 * @codeCoverageIgnore
	 */
	protected function parseTrack(array $track):array{
		// we're using the settings container and its setters to enforce variables and types etc.
		return (new class($track) extends SettingsContainerAbstract{

			protected string      $artist;
			protected string      $track;
			protected int         $timestamp;
			protected string|null $album        = null;
			protected string|null $context      = null;
			protected string|null $streamId     = null;
			protected int         $chosenByUser = 1;
			protected int|null    $trackNumber  = null;
			protected string|null $mbid         = null;
			protected string|null $albumArtist  = null;
			protected int|null    $duration     = null;

			protected function construct():void{
				foreach(['artist', 'track', 'album', 'context', 'streamId', 'mbid', 'albumArtist'] as $var){

					if($this->{$var} === null){
						continue;
					}

					$this->{$var} = trim($this->{$var});

					if(empty($this->{$var})){
						throw new InvalidArgumentException(sprintf('variable "%s" must not be empty', $var));
					}
				}
			}

			public function toArray():array{
				// filter out the null values
				return array_filter(parent::toArray(), fn(mixed $val):bool => $val !== null);
			}

			protected function set_timestamp(DateTimeInterface|int $timestamp):void{

				if($timestamp instanceof DateTimeInterface){
					$timestamp = $timestamp->getTimestamp();
				}

				$this->timestamp = $timestamp;
			}

			protected function set_chosenByUser(bool $chosenByUser):void{
				$this->chosenByUser = (int)$chosenByUser;
			}

			protected function set_trackNumber(int $trackNumber):void{

				if($trackNumber < 1){
					throw new InvalidArgumentException('invalid track number');
				}

				$this->trackNumber = $trackNumber;
			}

			protected function set_duration(int $duration):void{

				if($duration < 0){
					throw new InvalidArgumentException('invalid track duration');
				}

				$this->duration = $duration;
			}

		})->toArray();
	}

	/**
	 * @codeCoverageIgnore
	 */
	protected function sendScrobbles(array $body):array|null{

		$response = $this->request(
			path  : 'track.scrobble',
			method: 'POST',
			body  : $body,
		);

		if($response->getStatusCode() === 200){
			$json = MessageUtil::decodeJSON($response, true);

			if(!isset($json['scrobbles'])){
				return null;
			}

			return $json['scrobbles'];
		}

		return null;
	}

}
