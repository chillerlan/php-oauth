<?php
/**
 * Class OAuth2Provider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil, UriUtil};
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use Throwable;
use function array_merge, date, explode, hash_equals, implode, in_array, is_array, sodium_bin2base64, sprintf;
use const PHP_QUERY_RFC1738, SODIUM_BASE64_VARIANT_ORIGINAL;

/**
 * Implements an abstract OAuth2 provider with all methods required by the OAuth2Interface.
 * It also implements the ClientCredentials, CSRFToken and TokenRefresh interfaces in favor over traits.

 * @see https://oauth.net/2/
 * @see https://datatracker.ietf.org/doc/html/rfc6749
 */
abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

	/**
	 * An optional refresh token endpoint in case the provider supports TokenRefresh.
	 * If the provider supports token refresh and $refreshTokenURL is null, $accessTokenURL will be used instead.
	 *
	 * @see \chillerlan\OAuth\Core\TokenRefresh
	 */
	protected string $refreshTokenURL;

	/**
	 * An optional client credentials token endpoint in case the provider supports ClientCredentials.
	 * If the provider supports client credentials and $clientCredentialsTokenURL is null, $accessTokenURL will be used instead.
	 */
	protected string|null $clientCredentialsTokenURL = null;

	/**
	 * @inheritDoc
	 *
	 * @param string[]|null $scopes
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$params ??= [];

		// this should NEVER be set in the given params
		unset($params['client_secret']);

		$queryParams = $this->getAuthorizationURLRequestParams($params, ($scopes ?? $this::DEFAULT_SCOPES));

		if($this instanceof CSRFToken){
			$queryParams = $this->setState($queryParams);
		}

		return $this->uriFactory->createUri(QueryUtil::merge($this->authorizationURL, $queryParams));
	}

	/**
	 * prepares the query parameters for the auth URL
	 */
	protected function getAuthorizationURLRequestParams(array $params, array $scopes):array{

		$params = array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'type'          => 'web_server',
		]);

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		return $params;
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.4
	 * @see https://datatracker.ietf.org/doc/html/rfc6749#section-5.1
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response):AccessToken{

		try{
			$data = $this->getTokenResponseData($response);
		}
		catch(Throwable $e){
			throw new ProviderException(sprintf('unable to parse token response: %s', $e->getMessage()));
		}

		// deezer: "error_reason", paypal: "message" (along with "links", "name")
		foreach(['error', 'error_description', 'error_reason', 'message'] as $field){
			if(isset($data[$field])){

				if(in_array($response->getStatusCode(), [400, 401, 403], true)){
					throw new UnauthorizedAccessException($data[$field]);
				}

				throw new ProviderException(sprintf('error retrieving access token: "%s"', $data[$field]));
			}
		}

		if(!isset($data['access_token'])){
			throw new ProviderException('access token missing');
		}

		$scopes = ($data['scope'] ?? $data['scopes'] ?? []);

		if(!is_array($scopes)){
			$scopes = explode($this::SCOPES_DELIMITER, $scopes);
		}

		$token               = $this->createAccessToken();
		$token->accessToken  = $data['access_token'];
		$token->expires      = (int)($data['expires'] ?? $data['expires_in'] ?? AccessToken::NEVER_EXPIRES);
		$token->refreshToken = ($data['refresh_token'] ?? null);
		$token->scopes       = $scopes;

		foreach(['access_token', 'refresh_token', 'expires', 'expires_in', 'scope', 'scopes'] as $var){
			unset($data[$var]);
		}

		$token->extraParams  = $data;

		return $token;
	}

	/**
	 * extracts the data from the access token response and returns an array with the key->value pairs contained
	 *
	 * we don't bother checking the content type here as it's sometimes vendor specific, not set or plain wrong:
	 * the spec mandates a JSON body which is what almost all providers send - weird exceptions:
	 *
	 *   - mixcloud sends JSON with a "text/javascript" header
	 *   - deezer sends form-data with a "text/html" header (???)
	 *   - silly amazon sends gzip compressed data... (handled by decodeJSON)
	 *
	 * @throws \JsonException
	 */
	protected function getTokenResponseData(ResponseInterface $response):array{
		$data = MessageUtil::decodeJSON($response, true);

		if(!is_array($data)){
			// nearly impossible to run into this as json_decode() would throw first
			throw new ProviderException('decoded json is not an array');
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken(string $code, string|null $state = null):AccessToken{

		if($this instanceof CSRFToken){
			$this->checkState($state);
		}

		$body     = $this->getAccessTokenRequestBodyParams($code);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $body);
		$token    = $this->parseTokenResponse($response);

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * prepares the request body parameters for the access token request
	 */
	protected function getAccessTokenRequestBodyParams(string $code):array{
		return [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->options->callbackURL,
		];
	}

	/**
	 * sends a request to the access/refresh token endpoint $url with the given $body as form data
	 */
	protected function sendAccessTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		$token ??= $this->storage->getAccessToken($this->name);

		if($token->isExpired()){

			if(!$this instanceof TokenRefresh || $this->options->tokenAutoRefresh !== true){
				throw new InvalidAccessTokenException;
			}

			$token = $this->refreshAccessToken($token);
		}

		if($this::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_HEADER){
			return $request->withHeader('Authorization', $this::AUTH_PREFIX_HEADER.' '.$token->accessToken);
		}

		if($this::AUTH_METHOD === OAuth2Interface::AUTH_METHOD_QUERY){
			$uri = UriUtil::withQueryValue($request->getUri(), $this::AUTH_PREFIX_QUERY, $token->accessToken);

			return $request->withUri($uri);
		}

		// it's near impossible to run into this in any other scenario than development...
		throw new ProviderException('invalid auth AUTH_METHOD'); // @codeCoverageIgnore
	}

	/**
	 * @param string[]|null $scopes
	 * @implements \chillerlan\OAuth\Core\ClientCredentials
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getClientCredentialsToken(array|null $scopes = null):AccessToken{

		if(!$this instanceof ClientCredentials){
			throw new ProviderException('client credentials token not supported');
		}

		$body     = $this->getClientCredentialsTokenRequestBodyParams($scopes);
		$response = $this->sendClientCredentialsTokenRequest(($this->clientCredentialsTokenURL ?? $this->accessTokenURL), $body);
		$token    = $this->parseTokenResponse($response);

		// provider didn't send a set of scopes with the token response, so add the given ones manually
		if(empty($token->scopes)){
			$token->scopes = ($scopes ?? []);
		}

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * prepares the request body parameters for the client credentials token request
	 *
	 * @param string[]|null $scopes
	 */
	protected function getClientCredentialsTokenRequestBodyParams(array|null $scopes):array{
		$body = ['grant_type' => 'client_credentials'];

		if(!empty($scopes)){
			$body['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		return $body;
	}

	/**
	 * sends a request to the client credentials endpoint, using basic authentication
	 */
	protected function sendClientCredentialsTokenRequest(string $url, array $body):ResponseInterface{
		$auth = sodium_bin2base64(sprintf('%s:%s', $this->options->key, $this->options->secret), SODIUM_BASE64_VARIANT_ORIGINAL);

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Authorization', sprintf('Basic %s', $auth))
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		return $this->http->sendRequest($request);
	}

	/**
	 * @implements \chillerlan\OAuth\Core\TokenRefresh
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function refreshAccessToken(AccessToken|null $token = null):AccessToken{

		if(!$this instanceof TokenRefresh){
			throw new ProviderException('token refresh not supported');
		}

		$token        ??= $this->storage->getAccessToken($this->name);
		$refreshToken   = $token->refreshToken;

		if(empty($refreshToken)){
			$msg = 'no refresh token available, token expired [%s]';

			throw new ProviderException(sprintf($msg, date('Y-m-d h:i:s A', $token->expires)));
		}

		$body     = $this->getRefreshAccessTokenRequestBodyParams($refreshToken);
		$response = $this->sendAccessTokenRequest(($this->refreshTokenURL ?? $this->accessTokenURL), $body);
		$newToken = $this->parseTokenResponse($response);

		if(empty($newToken->refreshToken)){
			$newToken->refreshToken = $refreshToken;
		}

		$this->storage->storeAccessToken($newToken, $this->name);

		return $newToken;
	}

	/**
	 * prepares the request body parameters for the token refresh
	 */
	protected function getRefreshAccessTokenRequestBodyParams(string $refreshToken):array{
		return [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'grant_type'    => 'refresh_token',
			'refresh_token' => $refreshToken,
			'type'          => 'web_server',
		];
	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::checkState()
	 * @throws \chillerlan\OAuth\Providers\ProviderException|\chillerlan\OAuth\Core\CSRFStateMismatchException
	 * @internal
	 */
	public function checkState(string|null $state = null):void{

		if(!$this instanceof CSRFToken){
			throw new ProviderException('CSRF protection not supported');
		}

		if(empty($state)){
			throw new ProviderException('invalid CSRF state');
		}

		$knownState = $this->storage->getCSRFState($this->name);
		// delete the used token
		$this->storage->clearCSRFState($this->name);

		if(!hash_equals($knownState, $state)){
			throw new CSRFStateMismatchException(sprintf('CSRF state mismatch for provider "%s": %s', $this->name, $state));
		}

	}

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::setState()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 * @internal
	 */
	public function setState(array $params):array{

		if(!$this instanceof CSRFToken){
			throw new ProviderException('CSRF protection not supported');
		}

		// don't touch the parameter if it has been deliberately set
		if(!isset($params['state'])){
			$params['state'] = $this->nonce();
		}

		$this->storage->storeCSRFState($params['state'], $this->name);

		return $params;
	}

}
