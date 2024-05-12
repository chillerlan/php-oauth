# Create your own Provider class

Thanks to clever abstraction, creating a new provider class is almost trivial; often it's only a few lines of code.
You start by extending one of the abstract classes, depending on which OAuth version the service supports.
It's even possible to implement a provider class for services that use proprietary OAuth-like protocols,
e.g. [last.fm](https://www.last.fm/api/authentication), however, that requires implementing most of the methods by yourself.


## Minimal implementation

Absolutely necessary are the several endpoints for *authorization* and *access token* (as well as *request token* in case of OAuth1),
the *API base URL* - all of which you can find in the documentation of the service you're about to implement.
Further, an `IDENTIFIER` constant for use in tests, fetching environment variables etc., is required.

With that, the bare minimum for a provider class looks as follows:

**OAuth1**

```php
class MyOAuth1Provider extends OAuth1Provider{

	public const IDENTIFIER = 'MYOAUTH1PROVIDER';

	protected string $requestTokenURL  = 'https://example.com/oauth/request_token';
	protected string $authorizationURL = 'https://example.com/oauth/authorize';
	protected string $accessTokenURL   = 'https://example.com/oauth/access_token';
	protected string $apiURL           = 'https://example.com/api';

}
```

**OAuth2**

```php
class MyOAuth2Provider extends OAuth2Provider{

	public const IDENTIFIER = 'MYOAUTH2PROVIDER';

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
Take `Mastodon` for example: the URLs and API endpoints change with the host of the Mastodon instance - this would quickly become a mess with setter/getter methods.
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


## Scopes

The scopes as described in [RFC-6749, section 3.3](https://datatracker.ietf.org/doc/html/rfc6749#section-3.3) can be added to an authorization request,
here as an array via the optional `$scopes` parameter of `OAuthInterface::getAuthorizationURL()`.
This interface also offers a constant `DEFAULT_SCOPES` that can be defined for cases when no scopes are given otherwise.
Further, a `SCOPES_DELIMITER` can be defined in case it deviates from the `space` specified in the RFC.

Since some OAuth1-based services offer a similar granular permission system, the scopes implementation is not limited to OAuth2 provider classes.
All basic functionality is implemented with the `OAuthInterface` and can be used with any service.

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
