# Quickstart

## The `OAuthOptions` container

`OAuthOptions` is a simple container that implements the `SettingsContainerInterface` of [chillerlan/php-settings-container](https://github.com/chillerlan/php-settings-container#readme),
which allows separating configuration logic from the application, and that can be extended easily.
Read more on that [over here in the php-qrcode documentation](https://php-qrcode.readthedocs.io/en/main/Usage/Advanced-usage.html#configuration-via-qroptions).


```php
$options = new OAuthOptions;

// set the credentials for your OAuth application
// for the example it's: https://github.com/settings/applications/
$options->key          = '[client_id]';
$options->secret       = '[secret]';
$options->callbackURL  = 'https://example.com/callback/';
```

A list with all available `OAuthOptions` can be found under [configuration settings](../Basics/Configuration-settings.md).


## The `OAuthStorageInterface`

The `OAuthStorageInterface` stores access tokens and CSRF states (OAuth2) on a per-user-basis during script runtime. Generally, there are 3 types of storage:

- **non-persistent**: stores an existing token during script runtime and then discards it (i.e. `MemoryStorage`)
- **semi-persistent**: stores a token for as long a user's session is alive, e.g. during the authorization flow (i.e. `SessionStorage`)
- **persistent**: stores a token permanently and can be used for both of the aforementioned scenarios (i.e. a database, or `FileStorage`)

The `$storage` parameter is optional when invoking a provider, by default, a `MemoryStorage` is invoked.
Each storage class has 2 optional parameters: an `OAuthOptions` instance and a [PSR-3 `LoggerInterface`](https://www.php-fig.org/psr/psr-3/) - other parameters may vary depending on the implementation.

Here, we're invoking a `SessionStorage` for the authorization examples:

```php
// starts the session for the session storage
$options->sessionStart = true;

$storage = new SessionStorage($options);
```


### Using existing tokens

Most of the time you'll probably just want to use/import an existing access token to use an API on the user's behalf.
In order to do so, you can simply import an `AccessToken` instance:

```php
$token = new AccessToken;

$token->accessToken       = 'access_token';
$token->expires           = 3600;

// oauth1
$token->accessTokenSecret = 'access_token_secret';

// oauth2
$token->refreshToken      = 'refresh_token';
$token->scopes            = ['scope1', 'scope2'];
```

The token expiry can be either an absolute time (UNIX timestamp), a duration in seconds, a `DateTime` or `DateInterval` instance, or `AccessToken::NEVER_EXPIRES`.

You can hand over the `AccessToken` instance to the storage: the `OAuthStorageInterface::storeAccessToken()` takes 2 parameters -
the token and a provider name this token is associated with, is usually the short name of the provider class.

```php
// store a token
$storage->storeAccessToken($token, $provider->name);

// the provider interface has a shortcut/convenience method,
// it adds the provider name automatically
$provider->storeAccessToken($token);
```


## Provider invocation

To invoke an OAuth provider, you'll need a `OAuthOptions` instance, a [PSR-18](https://www.php-fig.org/psr/psr-18/) `ClientInterface` (such as [`guzzlehttp/guzzle`](https://github.com/guzzle/guzzle) or [`chillerlan/php-httpinterface`](https://github.com/chillerlan/php-httpinterface)),
along with the [PSR-17](https://www.php-fig.org/psr/psr-17/) factories `RequestFactoryInterface`, `StreamFactoryInterface` and `UriFactoryInterface`. An `OAuthStorageInterface` and a PSR-3 logger instance are optional.

First you'll need a PSR-18 compatible HTTP client, it should (at least) support [CURLOPT_SSL_VERIFYPEER](https://curl.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html) and have it enabled,
for which it needs a valid certificate (or [certificate bundle](https://curl.se/docs/caextract.html)).

**chillerlan/HTTP**

```php
$httpOptions = new \chillerlan\HTTP\HTTPOptions([
	'ca_info'    => '/path/to/cacert.pem',
	'user_agent' => OAuthInterface::USER_AGENT,
]);

$http = new \chillerlan\HTTP\CurlClient(new ResponseFactory, $httpOptions);
```

**GuzzleHttp**

```php
$httpOptions = [
	'verify'  => '/path/to/cacert.pem',
	'headers' => [
		'User-Agent' => OAuthInterface::USER_AGENT,
	],
];

$http = new \GuzzleHttp\Client($httpOptions);
```

Now let's invoke a `GitHub` provider:

```php
// now we can invoke the provider
$provider = new GitHub(
	$options,
	$http,
	new RequestFactory,
	new StreamFactory,
	new UriFactory,
	$storage,
);
```


### Provider factory

Invoking a provider can be a bit chunky and perhaps gets messy as soon as you're using more than one OAuth service in your project.
In that case you can use the `OAuthProviderFactory` for convenience:

```php
// invoke the provider factory with the common PSR instances
$providerFactory = new OAuthProviderFactory(
	$http,
	new RequestFactory,
	new StreamFactory,
	new UriFactory,
	$logger, // optional
);

// invoke the provider instance - the $options and $storage params are optional
$provider = $providerFactory->getProvider(GitHub::class, $options, $storage);
```

Additionally, the provider factory offers convenience methods to set a different logger instance and to get each of the PSR-17 factories:

```php
$providerFactory->setLogger(new NullLogger);
$providerFactory->getRequestFactory();
$providerFactory->getStreamFactory();
$providerFactory->getUriFactory();
```
