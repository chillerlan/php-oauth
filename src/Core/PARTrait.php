<?php
/**
 * Trait PARTrait
 *
 * @created      19.09.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\HTTP\Utils\QueryUtil;
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\UriInterface;
use function sprintf;

/**
 * Implements PAR (Pushed Authorization Requests) functionality
 *
 * @see \chillerlan\OAuth\Core\PAR
 */
trait PARTrait{

	/**
	 * implements PAR::getParRequestUri()
	 *
	 * @see \chillerlan\OAuth\Core\PAR::getParRequestUri()
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::getAuthorizationURL()
	 *
	 * @param array<string, string> $body
	 */
	public function getParRequestUri(array $body):UriInterface{
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
	 *
	 * @param array<string, string> $response
	 * @return array<string, string>
	 *
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
