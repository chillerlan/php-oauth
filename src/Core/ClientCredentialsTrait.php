<?php
/**
 * Trait ClientCredentialsTrait
 *
 * @created      19.09.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\QueryUtil;
use Psr\Http\Message\ResponseInterface;
use function implode;
use const PHP_QUERY_RFC1738;

/**
 * Implements Client Credentials functionality
 *
 * @see \chillerlan\OAuth\Core\ClientCredentials
 */
trait ClientCredentialsTrait{

	/**
	 * implements ClientCredentials::getClientCredentialsToken()
	 *
	 * @see \chillerlan\OAuth\Core\ClientCredentials::getClientCredentialsToken()
	 *
	 * @param string[]|null $scopes
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function getClientCredentialsToken(array|null $scopes = null):AccessToken{
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
	 * @return array<string, string>
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
	 *
	 * @param array<string, scalar> $body
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

}
