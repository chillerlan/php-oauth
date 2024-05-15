# Running the test suite

The [PHPUnit tests](https://docs.phpunit.de/en/10.5/) as defined in the [`phpunit.xml`](https://github.com/chillerlan/php-oauth/blob/main/phpunit.xml.dist) offer the following suites:

- `tests/Core` - core functionalities, such as tests for the `OAuthOptions` and `AccessToken` classes etc.
- `tests/Storage` - tests the `OAuthStorageInterface` implementors
- `tests/Providers/Unit` - unit tests for the `OAuthInterface` descendants
- `tests/Providers/Live` - tests the provider classes against the live APIs

Anything in the `tests` directory outside of these directories are abstract classes, helpers, attribute definitions and other files used in the tests.

## Control test behaviour

There are several groups you can use to exclude specific tests: `slow`, `shortTokenExpiry` and `providerLiveTest`, where the latter is excluded by default, as valid access tokens are required.
The group `shortTokenExpiry` is a subgroup of `providerLiveTest` for providers that issue access tokens with a very short expiry (e.g. 1 hour).

The PHPUnit constant `TEST_IS_CI` (defined in the `php` section of `phpunit.xml`) specifies whether the test suite is running in a CI environment such as GitHub Actions etc. -
it overrides the aforementioned group settings and automatically skips live tests.

```xml
<phpunit>
	<!-- ... -->
	<php>
		<const name="TEST_IS_CI" value="true"/>
	</php>
</phpunit>
```

In order to run the live API tests locally, a `.env` file in `<project root>/.config` is required, which you can define in the `TEST_ENVFILE` directive.

```xml
<const name="TEST_ENVFILE" value=".env_example"/>
```

## Using custom PSR-17/PSR-18 factories

The HTTP factories are managed by the helper library [chillerlan/phpunit-http](https://github.com/chillerlan/phpunit-http).
By default, the test suite uses `guzzlehttp/guzzle` and its PSR-7/17 implementation(s) `GuzzleHttp\Psr7\HttpFactory`.
If you want to run the tests against a different PSR-7 implementation, you can easily switch to other factories via the following constants:

```xml
<const name="REQUEST_FACTORY" value="My\Custom\PSR7\RequestFactory"/>
<const name="RESPONSE_FACTORY" value="My\Custom\PSR7\ResponseFactory"/>
<const name="STREAM_FACTORY" value="My\Custom\PSR7\StreamFactory"/>
<const name="URI_FACTORY" value="My\Custom\PSR7\UriFactory"/>
```

To use a different PSR-18 HTTP client, you'll need to wrap it in a `HttpClientFactoryInterface`

```php
final class HttpClientFactory implements HttpClientFactoryInterface{

	public function getClient(
		string                   $cacert,
		ResponseFactoryInterface $responseFactory,
	):ClientInterface{
		return new MyHttpClient(['cacert' => $cacert, /* ... */]);
	}

}
```

You can then specify it in the `phpunit.xml`:

```xml
<const name="HTTP_CLIENT_FACTORY" value="My\Custom\\HttpClientFactory"/>
```
