<?php
/**
 * Trait TokenInvalidateTrait
 *
 * @created      19.09.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @phan-file-suppress PhanUndeclaredProperty, PhanUndeclaredMethod
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\ProviderException;
use Psr\Http\Message\ResponseInterface;
use function in_array;
use function sprintf;
use function str_contains;
use function strtolower;
use function trim;

/**
 * Implements token invalidation functionality
 *
 * @see \chillerlan\OAuth\Core\TokenInvalidate
 */
trait TokenInvalidateTrait{

	/**
	 * implements TokenInvalidate::invalidateAccessToken()
	 *
	 * @see \chillerlan\OAuth\Core\TokenInvalidate::invalidateAccessToken()
	 * @throws \chillerlan\OAuth\Providers\ProviderException
	 */
	public function invalidateAccessToken(AccessToken|null $token = null, string|null $type = null):bool{
		$type = strtolower(trim(($type ?? 'access_token')));

		// @link https://datatracker.ietf.org/doc/html/rfc7009#section-2.1
		if(!in_array($type, ['access_token', 'refresh_token'], true)){
			throw new ProviderException(sprintf('invalid token type "%s"', $type)); // @codeCoverageIgnore
		}

		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));
		/** @phan-suppress-next-line PhanTypeMismatchArgumentNullable */
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
			$json = MessageUtil::decodeJSON($response, true);

			if(isset($json['error'])){
				throw new ProviderException($json['error']);
			}
		}

		return false;
	}

	/**
	 * Prepares the body for a token revocation request
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::invalidateAccessToken()
	 *
	 * @return array<string, scalar|bool|null>
	 */
	protected function getInvalidateAccessTokenBodyParams(AccessToken $token, string $type):array{
		return [
			'token'           => $token->accessToken,
			'token_type_hint' => $type,
		];
	}

	/**
	 * Prepares and sends a request to the token invalidation endpoint
	 *
	 * @see \chillerlan\OAuth\Core\OAuth2Provider::invalidateAccessToken()
	 *
	 * @param array<string, scalar|bool|null> $body
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

}
