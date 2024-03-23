<?php
/**
 * Class OAuth1Provider
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\{MessageUtil, QueryUtil};
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\{RequestInterface, ResponseInterface, UriInterface};
use function array_merge, base64_encode, hash_hmac, implode, sprintf, strtoupper, time;

/**
 * Implements an abstract OAuth1 (1.0a) provider with all methods required by the OAuth1Interface.
 *
 * @see https://oauth.net/core/1.0a/
 * @see https://datatracker.ietf.org/doc/html/rfc5849
 */
abstract class OAuth1Provider extends OAuthProvider implements OAuth1Interface{

	/**
	 * The request OAuth1 token URL
	 */
	protected string $requestTokenURL;

	/**
	 * @inheritDoc
	 */
	public function getAuthURL(array|null $params = null, array|null $scopes = null):UriInterface{
		$response = $this->sendRequestTokenRequest($this->requestTokenURL);
		$token    = $this->parseTokenResponse($response, true);
		$params   = array_merge(($params ?? []), ['oauth_token' => $token->accessToken]);

		return $this->uriFactory->createUri(QueryUtil::merge($this->authURL, $params));
	}

	/**
	 * prepares the parameters for the request token request header
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
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
	 * Sends a request to the request token endpoint
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
	 * Parses the response from a request to the token endpoint
	 *
	 * Note: "oauth_callback_confirmed" is only sent in request token response
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.1
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-2.3
	 *
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	protected function parseTokenResponse(ResponseInterface $response, bool $confirmCallback = false):AccessToken{
		$data = QueryUtil::parse(MessageUtil::decompress($response));

		if(empty($data)){
			throw new ProviderException('unable to parse token response');
		}

		if(isset($data['error'])){
			throw new ProviderException(sprintf('error retrieving access token: "%s"', $data['error']));
		}

		if(!isset($data['oauth_token']) || !isset($data['oauth_token_secret'])){
			throw new ProviderException('invalid token');
		}

		if($confirmCallback && (!isset($data['oauth_callback_confirmed']) || $data['oauth_callback_confirmed'] !== 'true')){
			throw new ProviderException('oauth callback unconfirmed');
		}

		$token                    = $this->createAccessToken();
		$token->accessToken       = $data['oauth_token'];
		$token->accessTokenSecret = $data['oauth_token_secret'];
		$token->expires           = AccessToken::EOL_NEVER_EXPIRES;

		unset($data['oauth_token'], $data['oauth_token_secret']);

		$token->extraParams       = $data;

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * Generates a request signature
	 *
	 * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.4
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

		unset($signatureParams['oauth_signature']);

		// https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.1
		$data = QueryUtil::recursiveRawurlencode([strtoupper($method), (string)$url, QueryUtil::build($signatureParams)]);

		// https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.2
		$key  = QueryUtil::recursiveRawurlencode([$this->options->secret, ($accessTokenSecret ?? '')]);

		return base64_encode(hash_hmac('sha1', implode('&', $data), implode('&', $key), true));
	}

	/**
	 * @inheritDoc
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getAccessToken(string $requestToken, string $verifier):AccessToken{
		$token = $this->storage->getAccessToken($this->serviceName);

		if($requestToken !== $token->accessToken){
			throw new ProviderException('request token mismatch');
		}

		$response = $this->sendAccessTokenRequest($verifier);

		return $this->parseTokenResponse($response);
	}

	/**
	 * Sends the access token request
	 */
	protected function sendAccessTokenRequest(string $verifier):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', QueryUtil::merge($this->accessTokenURL, ['oauth_verifier' => $verifier]))
			->withHeader('Accept-Encoding', 'identity')
			->withHeader('Content-Length', '0')
		;

		$request = $this->getRequestAuthorization($request);

		return $this->http->sendRequest($request);
	}

	/**
	 * @inheritDoc
	 */
	public function getRequestAuthorization(RequestInterface $request, AccessToken|null $token = null):RequestInterface{

		if($token === null){
			$token = $this->storage->getAccessToken($this->serviceName);
		}

		$uri   = $request->getUri();
		$query = QueryUtil::parse($uri->getQuery());

		$params = [
			'oauth_consumer_key'     => $this->options->key,
			'oauth_nonce'            => $this->nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => time(),
			'oauth_token'            => $token->accessToken,
			'oauth_version'          => '1.0',
		];

		$params['oauth_signature'] = $this->getSignature($uri, $params, $request->getMethod(), $token->accessTokenSecret);

		if(isset($query['oauth_session_handle'])){
			$params['oauth_session_handle'] = $query['oauth_session_handle']; // @codeCoverageIgnore
		}

		return $request->withHeader('Authorization', sprintf('OAuth %s', QueryUtil::build($params, null, ', ', '"')));
	}

}
