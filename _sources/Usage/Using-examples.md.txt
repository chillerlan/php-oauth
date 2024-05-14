# Using the examples

OAuth is not exactly trivial and so is live testing an OAuth flow to make sure the implementation and all its details work as expected.
The examples - specifically [the get-token examples](https://github.com/chillerlan/php-oauth/tree/main/examples/get-token) -
are abstracted and condensed as far as possible to make using them as convenient as it can get.


## Requirements

### Server

Due to the nature of OAuth callbacks, you will need a webserver with public access, or rather, that is accepted as callback URL
by the service you're about to test, so `http://127.0.0.1/oauth/` will *definitely not* work - we'll assume
`https://example.com/oauth/` as the public callback URL throughout the examples.

Further we'll assume that `/var/www/` (with an associated user `webmaster`) is the directory that holds all web related files and dependencies,
and `/var/www/htdocs/` is the root of the public website, which translates to `https://example.com/`, hence `/var/www/htdocs/oauth/` becomes the OAuth callback entrypoint at `https://example.com/oauth/`.
A web framework would probably just have `/var/www/htdocs/index.php` as its entry point and route all requests through it,
but that is not focus of this example.


### Dependencies

To install the required dependencies, create a `composer.json` in `/var/www/` with the following content and run `composer install` (as user `webmaster`):

```json
{
	"require": {
		"php": "^8.1",
		"chillerlan/php-dotenv": "^3.0",
		"chillerlan/php-oauth": "^1.0",
		"guzzlehttp/guzzle": "^7.8",
		"monolog/monolog": "^3.5"
	}
}
```

After that, copy the `examples` directory in the library root to `/var/www/oauth-examples/`.


### Configuration

We'll store the configuration files in the user directory under `/home/webmaster/oauth-config/`.
The `FileStorage` in the  `OAuthExampleProviderFactory` expects a `.filestorage` directory in the configuration directory
with permissions set to at least `0755`, otherwise PHP's `is_writable()` will fail.

The cacert is also expected in the configuration, so we'll fetch a fresh one directly into it:

```shell
curl -o /home/webmaster/oauth-config/cacert.pem https://curl.se/ca/cacert.pem
```

Finally, we need a `.env` file that contains the provider configuration. We'll [create an application on GitHub](https://github.com/settings/developers)
and save the credentials - the naming convention is `<prefix>_<KEY|SECRET|CALLBACK_URL>` where the prefix is `OAuthInterface::IDENTIFIER`.

```
GITHUB_KEY=<Client ID>
GITHUB_SECRET=<Client secret>
GITHUB_CALLBACK_URL=https://example.com/oauth/
```


## Run the authorization flow

The call chain starts with the [`index.php`](https://github.com/chillerlan/php-oauth/blob/main/public/index.php) in `/var/www/htdocs/oauth/`.
We'll need to adjust it to our example configuration from before:

```php
$AUTOLOADER = '/var/www/vendor/autoload.php';
$CFGDIR     = '/home/webmaster/oauth-config/';
$ENVFILE    = '.env';

// additional parameters
$PARAMS     = [];
// scopes to use with the given provider
$SCOPES     = [
	GitHub::SCOPE_USER,
	GitHub::SCOPE_PUBLIC_REPO,
	GitHub::SCOPE_GIST,
];

// uncomment in case something goes wrong
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

require_once '/var/www/oauth-examples/get-token/GitHub.php';
```

You can now navigate to `https://example.com/oauth/` where you will be greeted with a link "Connect with GitHub!".

### Call chain

Let's break down what happens behind the scenes:

1. the `index.php` initializes a set of variables and includes an example from `/var/www/oauth-examples/get-token/`, here `GitHub.php`
2. `<provider>.php` includes [`/var/www/oauth-examples/provider-example-common.php`](https://github.com/chillerlan/php-oauth/blob/main/examples/provider-example-common.php), which:
    - includes the composer autoloader given through `$AUTOLOADER`
    - invokes the PSR-18 HTTP client, which is available as `$http`
    - invokes the [`OAuthExampleProviderFactory`](https://github.com/chillerlan/php-oauth/blob/main/examples/OAuthExampleProviderFactory.php), which is available as `$factory`
3. the OAuth provider is invoked via `$factory->getProvider(<provider>::class)` in `<provider>.php` and becomes available as `$provider`
4. `<provider>.php` includes either `_flow-oauth1.php` or `_flow-oauth2.php` from `/var/www/oauth-examples/get-token/`
5. the [authorization flow](./Authorization.md) is executed:
    - redirect to the URL received from `$provider->getAuthorizationURL($PARAMS, $SCOPES)`
    - the token request is called with the data from the incoming callback via `$provider->getAccessToken(...)`
    - the access token is stored under `/home/webmaster/oauth-config/.filestorage` and is displayed in the output once access is granted

Profit!
