<?php
/**
 * Class OAuth1Provider
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

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Providers\ProviderException;
use chillerlan\Utilities\Str;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function array_merge, hash_hmac, implode, in_array, sprintf, strtoupper, time;

/**
 * Implements an abstract OAuth1 (1.0a) provider with all methods required by the OAuth1Interface.
 *
 * @link https://oauth.net/core/1.0a/
 * @link https://datatracker.ietf.org/doc/html/rfc5849
 */
abstract class OAuth1Provider extends OAuthProvider implements OAuth1Interface{

	/**
	 * The request token URL
	 */
	protected string $requestTokenURL = '';

	/**
	 * @param array<string, scalar>|null $params
	 */
	public function getAuthorizationURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$response = $this->sendRequestTokenRequest($this->requestTokenURL);
		$token    = $this->parseTokenResponse($response, true);
		$params   = array_merge(($params ?? []), ['oauth_token' => $token->accessToken]);

		return $this->uriFactory->createUri(QueryUtil::merge($this->authorizationURL, $params));
	}

	/**
	 * Sends a request to the request token endpoint
	 *
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getAuthorizationURL()
	 */
	protected function sendRequestTokenRequest(string $url):ResponseInterface{
		$params  = $this->getRequestTokenRequestParams();

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Authorization', 'OAuth '.QueryUtil::build($params, null, ', ', '"'))
			// try to avoid compression
			->withHeader('Accept-Encoding', 'identity')
			// tumblr requires a content-length header set
			->withHeader('Content-Length', '0')
		;

		foreach($this::HEADERS_AUTH as $header => $value){
			$request = $request->withHeader($header, $value);
		}

		return $this->http->sendRequest($request);
	}

	/**
	 * prepares the parameters for the request token request header
	 *
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::sendRequestTokenRequest()
	 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
	 *
	 * @return array<string, scalar>
	 */
	protected function getRequestTokenRequestParams():array{

		$params = [
			'oauth_callback'         => $this->options->callbackURL,
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature($this->requestTokenURL, $params, 'POST');

		return $params;
	}

	/**
	 * Parses the response from a request to the token endpoint
	 *
	 * Note: "oauth_callback_confirmed" is only sent in the request token response
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
	 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getAuthorizationURL()
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getAccessToken()
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response, bool|null $checkCallbackConfirmed = null):AccessToken{
		/** @var array<string, string> $data */
		$data = QueryUtil::parse(MessageUtil::decompress($response));

		if($data === []){
			throw new ProviderException('unable to parse token response');
		}

		if(isset($data['error'])){

			if(in_array($response->getStatusCode(), [400, 401, 403], true)){
				throw new UnauthorizedAccessException($data['error']);
			}

			throw new ProviderException(sprintf('error retrieving access token: "%s"', $data['error']));
		}

		if(!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])){
			throw new ProviderException('invalid token');
		}

		// MUST be present and set to "true". The parameter is used to differentiate from previous versions of the protocol
		if(
			$checkCallbackConfirmed === true
			&& (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true')
		){
			throw new ProviderException('invalid OAuth 1.0a response');
		}

		$token                    = $this->createAccessToken();
		$token->accessToken       = $data['oauth_token'];
		$token->accessTokenSecret = $data['oauth_token_secret'];
		$token->expires           = AccessToken::NEVER_EXPIRES;

		unset($data['oauth_token'], $data['oauth_token_secret']);

		$token->extraParams       = $data;

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

	/**
	 * Generates a request signature
	 *
	 * @link https://datatracker.ietf.org/doc/html/rfc5849#section-3.4
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getRequestTokenRequestParams()
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getRequestAuthorization()
	 *
	 * @param array<string, scalar> $params
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function getSignature(
		UriInterface|string $url,
		array               $params,
		string              $method,
		string|null         $accessTokenSecret = null,
	):string{

		if(!$url instanceof UriInterface){
			$url = $this->uriFactory->createUri($url);
		}

		if($url->getHost() === '' || $url->getScheme() !== 'https'){
			throw new ProviderException(sprintf('getSignature: invalid url: "%s"', $url));
		}

		$signatureParams = array_merge(QueryUtil::parse($url->getQuery()), $params);
		$url             = $url->withQuery('')->withFragment('');

		// make sure we have no unwanted params in the array
		unset($signatureParams['oauth_signature']);

		// @link https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.1
		$data = QueryUtil::recursiveRawurlencode([strtoupper($method), (string)$url, QueryUtil::build($signatureParams)]);

		// @link https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.2
		$key  = QueryUtil::recursiveRawurlencode([$this->options->secret, ($accessTokenSecret ?? '')]);

		$hash = hash_hmac('sha1', implode('&', $data), implode('&', $key), true);

		return Str::base64encode($hash);
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAccessToken(string $requestToken, string $verifier):AccessToken{
		$token = $this->storage->getAccessToken($this->name);

		if($requestToken !== $token->accessToken){
			throw new ProviderException('request token mismatch');
		}

		$params   = $this->getAccessTokenRequestHeaderParams($token, $verifier);
		$response = $this->sendAccessTokenRequest($params);

		return $this->parseTokenResponse($response);
	}

	/**
	 * Prepares the header params for the access token request
	 *
	 * @return array<string, scalar>
	 */
	protected function getAccessTokenRequestHeaderParams(AccessToken $requestToken, string $verifier):array{
		/** @var array<string, scalar> $params */
		$params = [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $requestToken->accessToken,
			'oauth_version'          => '1.0',
			'oauth_verifier'         => $verifier,
		];

		$params['oauth_signature'] = $this->getSignature(
			$this->accessTokenURL,
			$params,
			'POST',
			$requestToken->accessTokenSecret,
		);

		return $params;
	}

	/**
	 * Adds the "Authorization" header to the given `RequestInterface` using the given array or parameters
	 *
	 * @param array<string, scalar> $params
	 */
	protected function setAuthorizationHeader(RequestInterface $request, array $params):RequestInterface{
		return $request->withHeader('Authorization', sprintf('OAuth %s', QueryUtil::build($params, null, ', ', '"')));
	}

	/**
	 * Sends the access token request
	 *
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::getAccessToken()
	 *
	 * @param array<string, scalar> $headerParams
	 */
	protected function sendAccessTokenRequest(array $headerParams):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $this->accessTokenURL)
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Length', '0')
		;

		$request = $this->setAuthorizationHeader($request, $headerParams);

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 * @see \chillerlan\OAuth\Core\OAuth1Provider::sendAccessTokenRequest()
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{
		$token ??= $this->storage->getAccessToken($this->name);

		if($token->isExpired()){
			throw new InvalidAccessTokenException;
		}

		/** @var array<string, scalar> $params */
		$params = [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $token->accessToken,
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature(
			$request->getUri(),
			$params,
			$request->getMethod(),
			$token->accessTokenSecret,
		);

		return $this->setAuthorizationHeader($request, $params);
	}

}
