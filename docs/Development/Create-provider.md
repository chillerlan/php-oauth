# Create your own Provider

Thanks to clever abstraction, creating a new provider class is almost trivial; often it's only a few lines of code.
You start by extending one of the abstract classes, depending on which OAuth version the service supports.
It's even possible to implement a provider class for services that use proprietary OAuth-like protocols,
e.g. [last.fm](https://www.last.fm/api/authentication), however, that requires implementing most of the methods by yourself.


## Minimal implementation

Absolutely necessary are the several endpoints for *authorization* and *access token* (as well as *request token* in case of OAuth1), the *API base URL* -
all of which you can find in the documentation of the service you're about to implement. With that, the bare minimum for a provider class looks as follows:

**OAuth1**

```php
class MyOAuth1Provider extends OAuth1Provider{

	protected string $requestTokenURL  = 'https://example.com/oauth/request_token';
	protected string $authorizationURL = 'https://example.com/oauth/authorize';
	protected string $accessTokenURL   = 'https://example.com/oauth/access_token';
	protected string $apiURL           = 'https://example.com/api';

}
```

**OAuth2**

```php
class MyOAuth2Provider extends OAuth2Provider{

	protected string $authorizationURL = 'https://example.com/oauth2/authorize';
	protected string $accessTokenURL   = 'https://example.com/oauth2/token';
	protected string $apiURL           = 'https://example.com/api';

}
```

That's it! You have now a fully working OAuth provider class for *$Service*. Profit!

Ok, that might not be all in some cases, and also you might want to add some informational URLs or scopes for convenience etc.,
so let's continue expanding the class.


### Dynamic URLs

Before you ask *"why aren't these constants?"* or *"why don't you implement abstract getter methods"*: some providers require dynamic URLs.
Take `Mastodon` for example: the URLs and API endpoints change with the host of the Mastodon instance - this would quickly become a mess with getter methods.
The `Mastodon` provider class implements a method `setInstance()` that takes the hostname of the instance and changes the internal URLs.

```php
class Mastodon extends OAuth2Provider{

	// the URLs are set to the main instance "mastodon.social" by default
	protected string $authorizationURL = 'https://mastodon.social/oauth/authorize';
	protected string $accessTokenURL   = 'https://mastodon.social/oauth/token';
	protected string $apiURL           = 'https://mastodon.social/api';

	public function setInstance(UriInterface|string $instance):static{

		if(!$instance instanceof UriInterface){
			$instance = $this->uriFactory->createUri($instance);
		}

		// throw if the host is empty
		if($instance->getHost() === ''){
			throw new OAuthException('invalid instance URL');
		}

		// enforce https and remove unnecessary parts
		$instance = $instance->withScheme('https')->withQuery('')->withFragment('');

		// set the provider URLs
		$this->authorizationURL = (string)$instance->withPath('/oauth/authorize');
		$this->accessTokenURL   = (string)$instance->withPath('/oauth/token');
		$this->apiURL           = (string)$instance->withPath('/api');

		return $this;
	}

}
```


## Informational values

In order to automatically create the fancy table under [supported providers](../Basics/Overview.md#supported-providers),
a provider class can implement the following informational properties (that can be accessed via magic `__get()`):

- `$apiDocs` - a link to the API documentation for this service
- `$applicationURL` - links to the OAuth application page, where a developer can obtain (or apply for) credentials
- `$userRevokeURL` - a link to the settings page where a user can manually revoke OAuth access

Further, the `OAuthInterface` provides a `USER_AGENT` constant that can be implemented for ease of access.

This is what our provider class looks now:

```php
class MyOAuth2Provider extends OAuth2Provider{

	/*
	 * ...
	 */
	public const USER_AGENT = 'myCoolOAuthClient/1.0.0 +https://github.com/my/oauth';

	protected string|null $apiDocs        = 'https://example.com/docs/api/reference';
	protected string|null $applicationURL = 'https://example.com/developer/console/apps';
	protected string|null $userRevokeURL  = 'https://example.com/user/settings/connections';

}
```


## Additional headers

Some APIs may require extra headers sent, such as `Accept` or API versioning, in which case you can implement the (array) constants
`HEADERS_AUTH` and `HEADERS_API` that are sent during authorization or API requests, respectively.

```php
class MyOAuth2Provider extends OAuth2Provider{

	/*
	 * ...
	 */

	// must not contain: Accept-Encoding, Authorization, Content-Length, Content-Type
	public const HEADERS_AUTH = [
		'Accept' => 'application/vnd.api+json',
	];

	// must not contain: Authorization
	public const HEADERS_API = [
		'Accept'        => 'application/vnd.api+json',
		'X-API-Version' => '3',
	];

}
```


## Request authorization method (OAuth2)

Unlike OAuth1 with the header `Authorization: OAuth <signed params>`, OAuth2 allows for several different authorization methods for requests,
most commonly `Authorization: Bearer <token>`, sometimes an URL query parameter `?access_token=<token>` or something entirely different.

The `OAuth2Interface` offers several constants that allow you to alter the behaviour of the request authorization:

- `AUTH_METHOD`: specifies whether the token is passed via header (`AUTH_METHOD_HEADER`, default) or in the URL query (`AUTH_METHOD_QUERY`)
- `AUTH_PREFIX_HEADER`: the prefix for the value of the `Authorization` header, e.g. `Bearer` (default), `OAuth` or whatever the service accepts
- `AUTH_PREFIX_QUERY`: the name of the query parameter, e.g. `access_token`

The following example would send the token via the URL query parameters, which would result in an URL similar to
`https://example.com/api/endpoint?param=value&here_is_the_access_token=<token>`


```php
class MyOAuth2Provider extends OAuth2Provider{

	/*
	 * ...
	 */

	public const AUTH_METHOD       = self::AUTH_METHOD_QUERY;
	public const AUTH_PREFIX_QUERY = 'here_is_the_access_token';

}
```


## Scopes (OAuth2)

The scopes as described in [RFC-6749, section 3.3](https://datatracker.ietf.org/doc/html/rfc6749#section-3.3) can be added to an authorization request,
here as an array via the optional `$scopes` parameter of `OAuthInterface::getAuthorizationURL()`.
The `OAuth2Interface` offers a constant `DEFAULT_SCOPES` that can be defined for cases when no scopes are given otherwise.
Further, a `SCOPES_DELIMITER` can be defined in case it deviates from the `space` specified in the RFC.

You can and should add any scopes that the service supports as public constants in the form `SCOPE_<scope name>` so that they can be accessed easily:

```php
class MyOAuth2Provider extends OAuth2Provider{

	/*
	 * ...
	 */

	public const SCOPE_THIS_IS_A_SCOPE = 'this-is-a-scope';
	public const SCOPE_ANOTHER_SCOPE   = 'another-scope';
	public const SCOPE_MORE_SCOPES     = 'wowee';

	public const DEFAULT_SCOPES = [
		self::SCOPE_THIS_IS_A_SCOPE,
		self::SCOPE_MORE_SCOPES,
	];

	public const SCOPES_DELIMITER = ',';

}
```


## Overriding methods

The abstract providers are implemented close to the RFCs, however, some services deviate from the proposals (e.g. different parameter names or content encodings),
so you may need to adjust your class. The respective methods are chopped up into small bits that make it easy to re-implement them.

### OAuth1

- `OAuth1Provider::getRequestTokenRequestParams()`
- `OAuth1Provider::sendRequestTokenRequest()`
- `OAuth1Provider::sendAccessTokenRequest()`

### OAuth2

- `OAuth2Provider::getAuthorizationURLRequestParams()`
- `OAuth2Provider::getAccessTokenRequestBodyParams()`
- `OAuth2Provider::sendAccessTokenRequest()`
- `OAuth2Provider::getTokenResponseData()`
- `OAuth2Provider::getClientCredentialsTokenRequestBodyParams()`
- `OAuth2Provider::sendClientCredentialsTokenRequest()`
- `OAuth2Provider::getRefreshAccessTokenRequestBodyParams()`


## Additional functionality

Services may support additional features, such as token refresh or invalidation, among other things. You can add these features
by implementing one or more of the feature interfaces. Some of the methods for these interfaces are already implemented in the
abstract providers, so that you only rarely need to re-implement them.


### `UserInfo`

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


### `ClientCredentials` (OAuth2)

The `ClientCredentials` interface indicates that the provider supports the OAuth2 *Client Credentials Grant* as described in [RFC-6749, section 4.4](https://datatracker.ietf.org/doc/html/rfc6749#section-4.4).
This allows the creation of access tokens without user context via the method `ClientCredentials::getClientCredentialsToken()` that is already implemented in `OAuth2Provider`.
Similar to the user authorization request, an optional set of scopes can be supplied via the `$scopes` parameter.


```php
class MyOAuth2Provider extends OAuth2Provider implements ClientCredentials{

	/*
	 * ...
	 */

}
```


### `CSRFToken` (OAuth2)

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


### `TokenRefresh` (OAuth2)

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


### `PKCE` (OAuth2)

The `PKCE` interface can be implemented when the service supports *"Proof Key for Code Exchange"* as described in [RFC-7636](https://datatracker.ietf.org/doc/html/rfc7636).
It implements the methods `PKCE::setCodeChallenge()` and `PKCE::setCodeVerifier()` that are called during the *authorization* and *access token* requests, respectively.

If you need to override either of the aforementioned request methods, don't forget to add the PKCE parameters:

```php
class MyOAuth2Provider extends OAuth2Provider implements PKCE{

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


### `TokenInvalidate`

This is interface is *not* implemented in the abstract providers, as it may differ drastically between services or is not supported at all.
The method `TokenInvalidate::invalidateAccessToken()` takes an `AccessToken` as optional parameter, in which case this token should be invalidated,
otherwise the token for the current user should be fetched from the storage and be used in the invalidation request.

The more common implementation looks as follows: the access token along with client-id is sent with a `POST` request as url-encoded
form-data in the body, and the server responds with either a HTTP 200 and (often) an empty body or a HTTP 204.
On a successful response, the token should be deleted from the storage.

```php
class MyOAuth2Provider extends OAuth2Provider implements TokenInvalidate{

	/*
	 * ...
	 */

	public function invalidateAccessToken(AccessToken|null $token = null):bool{
		$tokenToInvalidate = ($token ?? $this->storage->getAccessToken($this->name));

		// the body may vary between services
		$bodyParams = [
			'client_id' => $this->options->key,
			'token'     => $tokenToInvalidate->accessToken,
		];

		// prepare the request
		$request = $this->requestFactory
			->createRequest('POST', $this->revokeURL)
			->withHeader('Content-Type', 'application/x-www-form-urlencoded')
		;

		// encode the body according to the content-type given in the request header
		$request = $this->setRequestBody($bodyParams, $request);

		// bypass the host check and request authorization
		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() === 200){
			// delete the token on success (only if it wasn't given via param)
			if($token === null){
				$this->storage->clearAccessToken($this->name);
			}

			return true;
		}

		return false;
	}

}
```

Other services may just expect a `POST` or `DELETE` request to the invalidation endpoint with the `Authorization: Bearer <token>` header set.
The problem here is that a token given via parameter can't just be revoked that easily without overwriting the currently stored token (if any).
This can be solved by simply cloning the current provider instance, feed the given token to the clone and call `invalidateAccessToken()` on it:

```php
class MyOAuth2Provider extends OAuth2Provider implements TokenInvalidate{

	/*
	 * ...
	 */

	public function invalidateAccessToken(AccessToken|null $token = null):bool{

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
