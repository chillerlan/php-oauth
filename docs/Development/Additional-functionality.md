# Additional provider functionality

Services may support additional features, such as token refresh or invalidation, among other things. You can add these features
by implementing one or more of the feature interfaces. Some of the methods for these interfaces are already implemented in the
abstract providers, so that you only rarely need to re-implement them - for others, especially newer RFCs, there are traits that can be used.


## `UserInfo`

The `UserInfo` interface implements a method `me()` that returns basic information about the currently authenticated user from a `/me`,
`/tokeninfo` or similar endpoint in a `AuthenticatedUser` instance. To ease implementation, the endpoint request including error handling
has been unified and condensed into a single ugly method that returns an array with the information available.
You only need to assign the available values and hand them over to `AuthenticatedUser`, which takes an array with the following elements:

- `data`: the full response data array
- `handle`: a unique user handle
- `displayName`: the user's display- or full name
- `id`: a unique identifier, e.g. numeric or UUID - not to be confused with the handle
- `email`: the user's e-mail address
- `avatar`: a link to the avatar image
- `url`: a link to the public user profile

All elements except for `data` are nullable.

```php
class MyOAuth2Provider extends OAuth2Provider implements UserInfo{

	/*
	 * ...
	 */

	public function me():AuthenticatedUser{

		// the endpoint can either be an absolute URL or a path relative to $apiURL
		// additional request parameters can be supplied as an array
		$params = ['param' => 'value'];
		$data   = $this->getMeResponseData('/v1/accounts/verify_credentials', $params);

		// assign the fields
		$userdata = [
			'data'        => $data,
			'avatar'      => $data['avatar_url'],
			'displayName' => $data['display_name'],
			'email'       => $data['email'],
			'handle'      => $data['username'],
			'id'          => $data['id'],
			'url'         => $data['profile_url'],
		];

		// the values for AuthenticatedUser can only be assigned via constructor
		return new AuthenticatedUser($userdata);
	}

}
```

Sometimes, the unified request method might not work, for example in case your extended provider class overrides the
`OAuthProvider::request()` method to add functionality that cannot be handled otherwise.
In that case you can override the method `OAuthProvider::sendMeRequest()`:

```php
class MyOAuth2Provider extends OAuth2Provider implements UserInfo{

	/*
	 * ...
	 */

	protected function sendMeRequest(
		string     $endpoint,
		array|null $params = null
	):ResponseInterface{
		return $this->request(path: $endpoint, params: $params);
	}

}
```


## `ClientCredentials` (OAuth2)

The `ClientCredentials` interface indicates that the provider supports the OAuth2 *Client Credentials Grant* as described in [RFC-6749, section 4.4](https://datatracker.ietf.org/doc/html/rfc6749#section-4.4).
This allows the creation of access tokens without user context via the method `ClientCredentials::getClientCredentialsToken()` that is already implemented in `OAuth2Provider`.
Similar to the user authorization request, an optional set of scopes can be supplied via the `$scopes` parameter.

```php
class MyOAuth2Provider extends OAuth2Provider implements ClientCredentials{
	use ClientCredentialsTrait;

	/*
	 * ...
	 */

}
```


## `CSRFToken` (OAuth2)

The `CSRFToken` interface indicates that the provider supports CSRF protection during the authorization request via the `state` query parameter,
as defined in [RFC-6749, section 10.12](https://datatracker.ietf.org/doc/html/rfc6749#section-10.12).

The (`final`) methods `CSRFToken::setState()` and `CSRFToken::checkState()` are implemented in `OAuth2Provider` and called in `getAuthorizationURL()` and `getAccessToken()`, respectively (user interaction in between).
If you need to re-implement one of the latter methods, don't forget to add the set/check!

```php
class MyOAuth2Provider extends OAuth2Provider implements CSRFToken{

	/*
	 * ...
	 */

	public function getAccessToken(string $code, string|null $state = null):AccessToken{
		// we're an instance of CSRFToken, no instance check needed
		$this->checkState($state);

		$body     = $this->getAccessTokenRequestBodyParams($code);
		$response = $this->sendAccessTokenRequest($this->accessTokenURL, $body);
		$token    = $this->parseTokenResponse($response);

		// do stuff...
		$token->expires = (time() + 2592000); // set expiry to 30 days

		$this->storage->storeAccessToken($token, $this->name);

		return $token;
	}

}
```


## `TokenRefresh` (OAuth2)

This interface indicates that the provider class is capable of the OAuth2 token refresh, as described in [RFC-6749, section 6](https://datatracker.ietf.org/doc/html/rfc6749#section-6).
It shouldn't be necessary to re-implement the method `OAuth2Provider::refreshAccessToken()` unless the service you're about to implement interprets the RFC in very strange ways...

The method is usually called in `OAuthInterface::getRequestAuthorization()`, which you might need to re-implement only in rare cases:

```php
class MyOAuth2Provider extends OAuth2Provider implements TokenInvalidate{

	/*
	 * ...
	 */

	public function getRequestAuthorization(
		RequestInterface $request,
		AccessToken|null $token = null,
	):RequestInterface{

		// fetch the token from storage if none was given
		$token ??= $this->storage->getAccessToken($this->name);

		// check whether the token is expired
		if($token->isExpired()){

			// throw if the token cannot be refreshed
			if($this->options->tokenAutoRefresh !== true){
				throw new InvalidAccessTokenException;
			}

			// call the token refresh
			$token = $this->refreshAccessToken($token);
		}

		$header = sprintf('%s %s', $this::AUTH_PREFIX_HEADER, $token->accessToken);

		return $request->withHeader('Authorization', $header);
	}

}
```


## `PKCE` (OAuth2)

The `PKCE` interface can be implemented when the service supports *"Proof Key for Code Exchange"* as described in [RFC-7636](https://datatracker.ietf.org/doc/html/rfc7636).
It implements the methods `PKCE::setCodeChallenge()` and `PKCE::setCodeVerifier()` that are called during the *authorization* and *access token* requests, respectively.

If you need to override either of the aforementioned request methods, don't forget to add the PKCE parameters:

```php
class MyOAuth2Provider extends OAuth2Provider implements PKCE{
	use PKCETrait;

	/*
	 * ...
	 */

	// the query parameters for the authorization URL
	protected function getAuthorizationURLRequestParams(array $params, array $scopes):array{

		$params = array_merge($params, [
			'client_id'     => $this->options->key,
			'redirect_uri'  => $this->options->callbackURL,
			'response_type' => 'code',
			'type'          => 'web_server',
			// ...
		]);

		if(!empty($scopes)){
			$params['scope'] = implode($this::SCOPES_DELIMITER, $scopes);
		}

		// set the CSRF token
		$params = $this->setState($params);

		// set the PKCE "code_challenge" and "code_challenge_method" parameters
		$params = $this->setCodeChallenge($params, PKCE::CHALLENGE_METHOD_S256);

		return $params;
	}

	// the body for the access token exchange
	protected function getAccessTokenRequestBodyParams(string $code):array{

		$params = [
			'client_id'     => $this->options->key,
			'client_secret' => $this->options->secret,
			'code'          => $code,
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => $this->options->callbackURL,
			// ...
		];

		// sets the "code_verifier" parameter
		$params = $this->setCodeVerifier($params);

		return $params;
	}

}
```


## `PAR` (OAuth2)

The `PAR` interface indicates support for *"Pushed Authorization Requests"* as described in [RFC-9126](https://datatracker.ietf.org/doc/html/rfc9126).
When this interface is implemented, the method `OAuth2Provider::getAuthorizationURL()` calls `PAR::getParRequestUri()` with the set of parameters from
`OAuth2Provider::getAuthorizationURLRequestParams()` and returns its result. The method `PAR::getParRequestUri()` sends the authorization parameters
to the PAR endpoint of the service and gets a temporary request URI in return, which is then used in the actual authorization URL to redirect the user.

In case the service needs additional parameters in the final authorization URL, you can override the method `OAuth2Provider::getParAuthorizationURLRequestParams()`:

```php
class MyOAuth2Provider extends OAuth2Provider implements PAR{
	use PARTrait;

	/*
	 * ...
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
```


## `TokenInvalidate`

The `TokenInvalidate` adds support for *"Token Revocation""* as described in [RFC-7009](https://datatracker.ietf.org/doc/html/rfc7009).
The method `TokenInvalidate::invalidateAccessToken()` takes an `AccessToken` as optional parameter, in which case this token should be invalidated,
otherwise the token for the current user should be fetched from the storage and be used in the invalidation request.
An optional ["token type hint"](https://datatracker.ietf.org/doc/html/rfc7009#section-2.1) can be given with the `$type` parameter (defaults to `access_token`).

The more common implementation looks as follows: the access token along with type hint (and sometimes other parameters) is sent
with a `POST` request as url-encoded form-data in the body, and the server responds with either an HTTP 200 and (often) an empty
body or an HTTP 204. On a successful response, the token should be deleted from the storage.

The implementation in `OAuth2Provider` is divided in parts that can be overridden separately:

```php
class MyOAuth2Provider extends OAuth2Provider implements TokenInvalidate{
	use TokenInvalidateTrait;

	/*
	 * ...
	 */

	protected function sendTokenInvalidateRequest(
		string $url,
		array  $body,
	):ResponseInterface{

		$request = $this->requestFactory
			->createRequest('POST', $url)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		// an additional basic auth header is set
		$request  = $this->addBasicAuthHeader($request);
		$request  = $this->setRequestBody($body, $request);

		return $this->http->sendRequest($request);
	}

	protected function getInvalidateAccessTokenBodyParams(
		AccessToken $token,
		string      $type,
	):array{
		return [
			// here, client_id and client_secret are set additionally
			'client_id'       => $this->options->key,
			'client_secret'   => $this->options->secret,
			'token'           => $token->accessToken,
			'token_type_hint' => $type,
		];
	}

}
```

Other services may just expect a `POST` or `DELETE` request to the invalidation endpoint with the `Authorization: Bearer <token>` header set.
The problem here is that a token given via parameter can't just be revoked that easily without overwriting the currently stored token (if any).
This can be solved by simply cloning the current provider instance, feed the given token to the clone and call `invalidateAccessToken()` on it:

```php
class MyOAuth2Provider extends OAuth2Provider implements TokenInvalidate{
	use TokenInvalidateTrait;

	/*
	 * ...
	 */

	public function invalidateAccessToken(
		AccessToken|null $token = null,
		string|null      $type = null,
	):bool{

		// a token was given
		if($token !== null){
			// clone the current provider instance
			return (clone $this)
				// replace the storage instance
				->setStorage(new MemoryStorage)
				// store the given token in the clone
				->storeAccessToken($token)
				// call this method on the clone without token parameter
				->invalidateAccessToken()
			;
		}

		// prepare the request
		$request = $this->requestFactory->createRequest('DELETE', $this->revokeURL);
		$request = $this->getRequestAuthorization($request);

		// bypass the host check and request authorization
		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() === 204){
			// delete the token on success
			$this->storage->clearAccessToken($this->name);

			return true;
		}

		return false;
	}

}
```
