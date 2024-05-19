<?php
/**
 * Class OAuth2Provider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 *
 * @filesource
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil, UriUtil};
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use Throwable;
use function array_merge, date, explode, hash, hash_equals, implode, in_array, is_array, random_int,
	sodium_bin2base64, sprintf, str_contains, strtolower, trim;
use const PHP_QUERY_RFC1738, PHP_VERSION_ID, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING;

/**
 * Implements an abstract OAuth2 provider with all methods required by the OAuth2Interface.
 * It also implements the ClientCredentials, CSRFToken, TokenRefresh and [...] interfaces in favor over traits.
 *
 * @link https://oauth.net/2/
 * @link https://datatracker.ietf.org/doc/html/rfc6749
 * @link https://datatracker.ietf.org/doc/html/rfc7636
 * @link https://datatracker.ietf.org/doc/html/rfc9126
 */
abstract class OAuth2Provider extends OAuthProvider implements OAuth2Interface{

	/**
	 * An optional refresh token endpoint in case the provider supports TokenRefresh.
	 * If the provider supports token refresh and $refreshTokenURL is null, $accessTokenURL will be used instead.
	 *
	 * @see \chillerlan\OAuth\Core\TokenRefresh::refreshAccessToken()
	 */
	protected string|null $refreshTokenURL = null;

	/**
	 * An optional client credentials token endpoint in case the provider supports ClientCredentials.
	 * If the provider supports client credentials and $clientCredentialsTokenURL is null, $accessTokenURL will be used instead.
	 *
	 * @see \chillerlan\OAuth\Core\ClientCredentials::getClientCredentialsToken()
	 */
	protected string|null $clientCredentialsTokenURL = null;

	/**
	 * An optional PAR (Pushed Authorization Request) endpoint URL
	 *
	 * @see \chillerlan\OAuth\Core\PAR::getParRequestUri()
	 */
	protected string $parAuthorizationURL = '';

	/**
	 * @inheritDoc
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$queryParams = $this->getAuthorizationURLRequestParams(($params ?? []), ($scopes ?? $this::DEFAULT_SCOPES));

		if($this instanceof PAR){
			return $this->getParRequestUri($queryParams);
		}

		return $this->uriFactory->createUri(QueryUtil::merge($this->authorizationURL, $queryParams));
	}

	/**
	 * prepares the query parameters for the auth URL
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURL()
	 */
	protected function getAuthorizationURLRequestParams(array $params, array $scopes):array{

		// this should NEVER be set in the given params
		unset($params['client_secret']);

		$params = array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'type'          => 'web_server',
		]);

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		if($this instanceof CSRFToken){
			$params = $this->setState($params);
		}

		if($this instanceof PKCE){
			$params = $this->setCodeChallenge($params, PKCE::CHALLENGE_METHOD_S256);
		}

		return $params;
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc6749#section-4.1.4
	 * @link https://datatracker.ietf.org/doc/html/rfc6749#section-5.1
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessToken()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::refreshAccessToken()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getClientCredentialsToken()
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
		// reddit sends "message" and "error" as int, which will throw a TypeError when handed into the exception
		// detection order changed accordingly
		foreach(['message', 'error', 'error_description', 'error_reason'] as $field){
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
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::parseTokenResponse()
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
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessToken()
	 */
	protected function getAccessTokenRequestBodyParams(string $code):array{

		$params = [
			'code'         => $code,
			'grant_type'   => 'authorization_code',
			'redirect_uri' => $this->options->callbackURL,
		];

		if(!$this::USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST){
			$params['client_id']     = $this->options->key;
			$params['client_secret'] = $this->options->secret;
		}

		if($this instanceof PKCE){
			$params = $this->setCodeVerifier($params);
		}

		return $params;
	}

	/**
	 * sends a request to the access/refresh token endpoint $url with the given $body as form data
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessToken()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::refreshAccessToken()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getParRequestUri()
	 */
	protected function sendAccessTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept', 'application/json')
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		if($this::USES_BASIC_AUTH_IN_ACCESS_TOKEN_REQUEST){
			$request = $this->addBasicAuthHeader($request);
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


	/*
	 * ClientCredentials
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\ClientCredentials::getClientCredentialsToken()
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
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getClientCredentialsToken()
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
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getClientCredentialsToken()
	 */
	protected function sendClientCredentialsTokenRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Accept', 'application/json')
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
			->withBody($this->streamFactory->createStream(QueryUtil::build($body, PHP_QUERY_RFC1738)))
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		$request = $this->addBasicAuthHeader($request);

		return $this->http->sendRequest($request);
	}


	/*
	 * TokenRefresh
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\TokenRefresh::refreshAccessToken()
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
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::refreshAccessToken()
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


	/*
	 * TokenInvalidate
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\TokenInvalidate::invalidateAccessToken()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function invalidateAccessToken(AccessToken $token = null, string|null $type = null):bool{
		$type = strtolower(trim($type ?? 'access_token'));

		// @link https://datatracker.ietf.org/doc/html/rfc7009#section-2.1
		if(!in_array($type, ['access_token', 'refresh_token'])){
			throw new ProviderException(sprintf('invalid token type "%s"', $type));
		}

		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));
		$body              = $this->getInvalidateAccessTokenBodyParams($tokenToInvalidate, $type);
		$response          = $this->sendTokenInvalidateRequest($this->revokeURL, $body);

		// some endpoints may return 204, others 200 with empty body
		if(in_array($response->getStatusCode(), [200, 204], true)){

			// if the token was given via parameter it cannot be deleted from storage
			if($token === null){
				$this->storage->clearAccessToken($this->name);
			}

			return true;
		}

		// ok, let's see if we got a response body
		// @link https://datatracker.ietf.org/doc/html/rfc7009#section-2.2.1
		if(str_contains($response->getHeaderLine('content-type'), 'json')){
			$json = MessageUtil::decodeJSON($response);

			if(isset($json['error'])){
				throw new ProviderException($json['error']);
			}
		}

		return false;
	}

	/**
	 * Prepares and sends a request to the token invalidation endpoint
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::invalidateAccessToken()
	 */
	protected function sendTokenInvalidateRequest(string $url, array $body):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		// some enpoints may require a basic auth header here
		$request  = $this->setRequestBody($body, $request);

		return $this->http->sendRequest($request);
	}

	/**
	 * Prepares the body for a token revocation request
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::invalidateAccessToken()
	 */
	protected function getInvalidateAccessTokenBodyParams(AccessToken $token, string $type):array{
		return [
			'token'           => $token->accessToken,
			'token_type_hint' => $type,
		];
	}


	/*
	 * CSRFToken
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::setState()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURLRequestParams()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	final public function setState(array $params):array{

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

	/**
	 * @implements \chillerlan\OAuth\Core\CSRFToken::checkState()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessToken()
	 * @throws \chillerlan\OAuth\Providers\ProviderException|\chillerlan\OAuth\Core\CSRFStateMismatchException
	 */
	final public function checkState(string|null $state = null):void{

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


	/*
	 * PKCE
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\PKCE::setCodeChallenge()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURLRequestParams()
	 */
	final public function setCodeChallenge(array $params, string $challengeMethod):array{

		if(!$this instanceof PKCE){
			throw new ProviderException('PKCE challenge not supported');
		}

		if(!isset($params['response_type']) || $params['response_type'] !== 'code'){
			throw new ProviderException('invalid authorization request params');
		}

		$verifier = $this->generateVerifier($this->options->pkceVerifierLength);

		$params['code_challenge']        = $this->generateChallenge($verifier, $challengeMethod);
		$params['code_challenge_method'] = $challengeMethod;

		$this->storage->storeCodeVerifier($verifier, $this->name);

		return $params;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\PKCE::setCodeVerifier()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAccessTokenRequestBodyParams()
	 */
	final public function setCodeVerifier(array $params):array{

		if(!$this instanceof PKCE){
			throw new ProviderException('PKCE challenge not supported');
		}

		if(!isset($params['grant_type'], $params['code']) || $params['grant_type'] !== 'authorization_code'){
			throw new ProviderException('invalid authorization request body');
		}

		$params['code_verifier'] = $this->storage->getCodeVerifier($this->name);

		// delete verifier after use
		$this->storage->clearCodeVerifier($this->name);

		return $params;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\PKCE::generateVerifier()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::setCodeChallenge()
	 * @phan-suppress PhanUndeclaredClassMethod, PhanUndeclaredMethod
	 */
	final public function generateVerifier(int $length):string{

		// use the Randomizer if available
		if(PHP_VERSION_ID >= 80300){
			$randomizer = new \Random\Randomizer(new \Random\Engine\Secure);

			return $randomizer->getBytesFromString(PKCE::VERIFIER_CHARSET, $length);
		}

		$str = '';

		for($i = 0; $i < $length; $i++){
			$str .= PKCE::VERIFIER_CHARSET[random_int(0, 65)];
		}

		return $str;
	}

	/**
	 * @implements \chillerlan\OAuth\Core\PKCE::generateChallenge()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::setCodeChallenge()
	 */
	final public function generateChallenge(string $verifier, string $challengeMethod):string{

		if($challengeMethod === PKCE::CHALLENGE_METHOD_PLAIN){
			return $verifier;
		}

		$verifier = match($challengeMethod){
			PKCE::CHALLENGE_METHOD_S256 => hash('sha256', $verifier, true),
			// no other hash methods yet
		};

		return sodium_bin2base64($verifier, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
	}


	/*
	 * PAR
	 */

	/**
	 * @implements \chillerlan\OAuth\Core\PAR::getParRequestUri()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURL()
	 */
	public function getParRequestUri(array $body):UriInterface{

		if(!$this instanceof PAR){
			throw new ProviderException('PKCE challenge not supported');
		}

		// send the request with the same method and parameters as the token requests
		// @link https://datatracker.ietf.org/doc/html/rfc9126#name-request
		$response = $this->sendAccessTokenRequest($this->parAuthorizationURL, $body);
		$status   = $response->getStatusCode();
		$json     = MessageUtil::decodeJSON($response, true);

		// something went horribly wrong
		if($status !== 200){

			// @link https://datatracker.ietf.org/doc/html/rfc9126#section-2.3
			if(isset($json['error'], $json['error_description'])){
				throw new ProviderException(sprintf('PAR error: "%s" (%s)', $json['error'], $json['error_description']));
			}

			throw new ProviderException(sprintf('PAR request error: (HTTP/%s)', $status)); // @codeCoverageIgnore
		}

		$url = QueryUtil::merge($this->authorizationURL, $this->getParAuthorizationURLRequestParams($json));

		return $this->uriFactory->createUri($url);
	}

	/**
	 * Parses the response from the PAR request and returns the query parameters for the authorization URL
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getParRequestUri()
	 * @codeCoverageIgnore
	 */
	protected function getParAuthorizationURLRequestParams(array $response):array{

		if(!isset($response['request_uri'])){
			throw new ProviderException('PAR response error: "request_uri" missing');
		}

		return [
			'client_id'   => $this->options->key,
			'request_uri' => $response['request_uri'],
		];
	}

}
