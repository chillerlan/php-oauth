# chillerlan/php-oauth

A transparent, framework-agnostic, easily extensible PHP [PSR-18](https://www.php-fig.org/psr/psr-18/) OAuth1/2 client with a user-friendly API, fully [PSR-7](https://www.php-fig.org/psr/psr-7/)/[PSR-17](https://www.php-fig.org/psr/psr-17/) compatible.


[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]
[![License][license-badge]][license]
[![Continuous Integration][gh-action-badge]][gh-action]
[![CodeCov][coverage-badge]][coverage]
[![Codacy][codacy-badge]][codacy]
[![Packagist downloads][downloads-badge]][downloads]

[php-badge]: https://img.shields.io/packagist/php-v/chillerlan/php-oauth?logo=php&color=8892BF&logoColor=fff
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/chillerlan/php-oauth.svg?logo=packagist&logoColor=fff
[packagist]: https://packagist.org/packages/chillerlan/php-oauth
[license-badge]: https://img.shields.io/github/license/chillerlan/php-oauth.svg
[license]: https://github.com/chillerlan/php-oauth/blob/main/LICENSE
[coverage-badge]: https://img.shields.io/codecov/c/github/chillerlan/php-oauth.svg?logo=codecov&logoColor=fff
[coverage]: https://codecov.io/github/chillerlan/php-oauth
[codacy-badge]: https://img.shields.io/codacy/grade/2e83b9167e5a41dba8af4b928ffa13ac?logo=codacy&logoColor=fff
[codacy]: https://app.codacy.com/gh/chillerlan/php-oauth/dashboard
[downloads-badge]: https://img.shields.io/packagist/dt/chillerlan/php-oauth.svg?logo=packagist&logoColor=fff
[downloads]: https://packagist.org/packages/chillerlan/php-oauth/stats
[gh-action-badge]: https://img.shields.io/github/actions/workflow/status/chillerlan/php-oauth/ci.yml?branch=main&logo=github&logoColor=fff
[gh-action]: https://github.com/chillerlan/php-oauth/actions/workflows/ci.yml?query=branch%3Amain


# Overview

## Features

- OAuth client capabilities
  - [OAuth 1.0a](https://oauth.net/core/1.0a/)
  - [OAuth 2.0](https://oauth.net/2/)
    - [Authorization Code Grant](https://datatracker.ietf.org/doc/html/rfc6749#section-4.1)
    - [Client Credentials Grant](https://datatracker.ietf.org/doc/html/rfc6749#section-4.4)
    - [Token refresh](https://datatracker.ietf.org/doc/html/rfc6749#section-1.5)
    - [CSRF Token](https://datatracker.ietf.org/doc/html/rfc6749#section-10.12) ("state" parameter)
  - Proprietary, OAuth-like authorization flows (e.g. [Last.fm](https://www.last.fm/api/authentication))
  - Invalidation of access tokens (if supported by the provider)
  - Client acts as [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client, wraps the given PSR-18 HTTP instance
    - Requests to the provider API will have required OAuth headers and tokens added automatically
- Several built-in provider implementations ([see below](#implemented-providers))


## Requirements

- PHP 8.1+
	- extensions: `json`, `sodium`
      - from dependencies: `curl`, `fileinfo`, `intl`, `mbstring`, `simplexml`, `zlib`
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice
- [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible `RequestFactory`, `StreamFactory` and `UriFactory`


# Documentation

- The user manual is at https://php-oauth.readthedocs.io/ ([sources](https://github.com/chillerlan/php-oauth/tree/main/docs))
- An API documentation created with [phpDocumentor](https://www.phpdoc.org/) can be found at https://chillerlan.github.io/php-oauth/
- The documentation for the `AccessToken`, `AuthenticatedUser` and `OAuthOptions` containers can be found here: [chillerlan/php-settings-container](https://github.com/chillerlan/php-settings-container#readme)


## Installation with [composer](https://getcomposer.org)

See [the installation guide](https://php-oauth.readthedocs.io/en/main/Usage/Installation.html) for more info!


### Terminal

```
composer require chillerlan/php-oauth
```


### composer.json

```json
{
	"require": {
		"php": "^8.1",
		"chillerlan/php-oauth": "dev-main#<commit_hash>"
	}
}
```

Note: replace `dev-main` with a [version constraint](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints), e.g. `^1.0` - see [releases](https://github.com/chillerlan/php-oauth/releases) for valid versions.


## "Quick" start

### Provider invocation

To invoke an OAuth provider, you'll need a `OAuthOptions` instance, a PSR-18 `ClientInterface` (such as [`guzzlehttp/guzzle`](https://github.com/guzzle/guzzle) or [`chillerlan/php-httpinterface`](https://github.com/chillerlan/php-httpinterface)),
along with the [PSR-17](https://www.php-fig.org/psr/psr-17/) factories `RequestFactoryInterface`, `StreamFactoryInterface` and `UriFactoryInterface`. An `OAuthStorageInterface` and a [PSR-3](https://www.php-fig.org/psr/psr-3/) logger instance are optional.


Let's invoke a `GitHub` provider:

```php
$options = new OAuthOptions;

// set the credentials for your OAuth application
// https://github.com/settings/applications/
$options->key          = '[client_id]';
$options->secret       = '[secret]';
$options->callbackURL  = 'https://example.com/callback/';
// starts the session for the session storage
$options->sessionStart = true;

// a session (or other permanet) storage is required for the authentication flow
$storage = new SessionStorage($options);

$provider = new GitHub($options, $http, new RequestFactory, new StreamFactory, new UriFactory, $storage);
```

### Redirect a user

The first step is to redirect the user to the provider's log-in page. Ideally you'd do this with a separate route in your web application,
as provider instances may call the service internally (OAuth1 request tokens). This step is identical with all providers.

The method `OAuthInterface::getAuthURL()` takes two (optional) parameters:

- `$params`: the params array contains additional query parameters that will be added to the URL query (provider dependent)
- `$scopes`: the scopes array contains all scopes that will be used for this authentication (unused in OAuth1)

First you present a log-in link to the user - instead of the provider's authentication URL with possibly sensitive parameters,
you'd rather redirect the user via `header()`:

```php
// display a link to the log-in redirect
echo '<a href="?route=oauth-login">connect with GitHub!</a>';
```

When the user clicks the link, just redirect them:

```php
if($route === 'oauth-login'){
	header('Location: '.$provider->getAuthURL($params, $scopes));

	// -> https://github.com/login/oauth/authorize?client_id=<client_id>
	//       &redirect_uri=https%3A%2F%2Fexample.com%2Fcallback%2F&response_type=code
	//       &scope=<scopes>&state=<state>&type=web_server
}
```


### Receive the incoming callback

After a user has successfully logged in with the provider and confirmed the OAuth request, they are redirected to the given callback URL,
along with the URL query parameters `oauth_token` and `oauth_verifier` (OAuth1) or `code` and (optional) `state` (OAuth2)

The method `OAuth2Interface::getAccessToken()` takes two parameters `$code` and optional `$state`,
while the similar `OAuth1Interface::getAccessToken()` has 2 mandatory parameters `$requestToken` and `$verifier`.

In our GitHub OAuth2 example we're now receiving the callback to `https://example.com/callback/?code=<code>&state=<state>`.
The `getAccessToken()` method initiates a backend request to the provider's server to exchange the temporary credentials for an access token:

```php
if($route === 'oauth2-callback' && isset($_GET['code'])){
	$state = null;

	// not all providers support "state"
	if($provider instanceof CSRFToken && isset($_GET['state'])){
		$state = $_GET['state'];
	}

	$token = $provider->getAccessToken($_GET['code'], $state);

	// some providers may send additional data with the callback query parameters,
	// that you may want to save here along with the token

	// when everything is done here, you should redirect the user to wherever they were headed,
	// but also to clear the URL query parameters
	header('Location: ...');
}
```

For OAuth1 providers it looks very similar, only the URL query parameters are different:

```php
if($route === 'oauth1-callback' && isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
	$token = $provider->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

	// ...
}
```


### Use the access token

After exchanging and saving the access token, you're now ready to access the provider's API on behalf of the authenticated user.
Most providers offer a token verification, user or just "me" endpoint, that you can access conveniently via the `OAuthInterface::me()` method:

```php
$user = $provider->me(); // -> AuthenticatedUser instance
```

Every provider instance also acts as PSR-18 client: calls to the provider's API will have the `Authorization` headers added automatically,
calls to other hosts will be rerouted directly to the http client.

```php
$request  = $requestFactory->createRequest('GET', 'https://api.github.com/user');
// authenticated request
$response = $provider->sendRequest($request); // -> ResponseInterface instance
```


# Implemented Providers

- **Provider**: the name of the provider and link to their API documentation
- **App keys**: links to the provider's OAuth application creation page
- **revoke access**: links to the OAuth application access revocation page in the provider's user profile
- **OAuth**: the OAuth version(s) supported by the provider
- **ClientCredentials**: indicates whether the provider supports the Client Credentials Grant (indicated by the `ClientCredentials` interface)

<!-- TABLE-START -->
| Provider | App keys | revoke access | OAuth | `ClientCredentials` |
|----------|----------|---------------|-------|---------------------|
| [Amazon]() | [link](https://developer.amazon.com/loginwithamazon/console/site/lwa/overview.html) |  | 2 |  |
| [BattleNet](https://develop.battle.net/documentation) | [link](https://develop.battle.net/access/clients) | [link](https://account.blizzard.com/connections) | 2 | ✓ |
| [BigCartel](https://developers.bigcartel.com/api/v1) | [link](https://bigcartel.wufoo.com/forms/big-cartel-api-application/) | [link](https://my.bigcartel.com/account) | 2 |  |
| [Bitbucket](https://developer.atlassian.com/bitbucket/api/2/reference/) | [link](https://developer.atlassian.com/apps/) |  | 2 | ✓ |
| [Deezer](https://developers.deezer.com/api) | [link](http://developers.deezer.com/myapps) | [link](https://www.deezer.com/account/apps) | 2 |  |
| [DeviantArt](https://www.deviantart.com/developers/) | [link](https://www.deviantart.com/developers/apps) | [link](https://www.deviantart.com/settings/applications) | 2 | ✓ |
| [Discogs](https://www.discogs.com/developers/) | [link](https://www.discogs.com/settings/developers) | [link](https://www.discogs.com/settings/applications) | 1 |  |
| [Discord](https://discord.com/developers/) | [link](https://discordapp.com/developers/applications/) |  | 2 | ✓ |
| [Flickr](https://www.flickr.com/services/api/) | [link](https://www.flickr.com/services/apps/create/) | [link](https://www.flickr.com/services/auth/list.gne) | 1 |  |
| [Foursquare](https://location.foursquare.com/developer/reference/foursquare-apis-overview) | [link](https://foursquare.com/developers/apps) | [link](https://foursquare.com/settings/connections) | 2 |  |
| [GitHub](https://docs.github.com/rest) | [link](https://github.com/settings/developers) | [link](https://github.com/settings/applications) | 2 |  |
| [GitLab]() | [link](https://gitlab.com/profile/applications) |  | 2 | ✓ |
| [Google](https://developers.google.com/oauthplayground/) | [link](https://console.developers.google.com/apis/credentials) | [link](https://myaccount.google.com/permissions) | 2 |  |
| [GuildWars2](https://wiki.guildwars2.com/wiki/API:Main) | [link](https://account.arena.net/applications) | [link](https://account.arena.net/applications) | 2 |  |
| [Imgur](https://apidocs.imgur.com) | [link](https://api.imgur.com/oauth2/addclient) | [link](https://imgur.com/account/settings/apps) | 2 |  |
| [LastFM](https://www.last.fm/api/) | [link](https://www.last.fm/api/account/create) | [link](https://www.last.fm/settings/applications) | - |  |
| [MailChimp](https://mailchimp.com/developer/) | [link](https://admin.mailchimp.com/account/oauth2/) |  | 2 |  |
| [Mastodon](https://docs.joinmastodon.org/api/) | [link](https://mastodon.social/settings/applications) | [link](https://mastodon.social/oauth/authorized_applications) | 2 |  |
| [MicrosoftGraph](https://learn.microsoft.com/graph/overview) | [link](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps) | [link](https://account.live.com/consent/Manage) | 2 |  |
| [Mixcloud](https://www.mixcloud.com/developers/) | [link](https://www.mixcloud.com/developers/create/) | [link](https://www.mixcloud.com/settings/applications/) | 2 |  |
| [MusicBrainz](https://musicbrainz.org/doc/Development) | [link](https://musicbrainz.org/account/applications) | [link](https://musicbrainz.org/account/applications) | 2 |  |
| [NPROne](https://dev.npr.org/api/) | [link](https://dev.npr.org/console) |  | 2 |  |
| [OpenCaching](https://www.opencaching.de/okapi/) | [link](https://www.opencaching.de/okapi/signup.html) | [link](https://www.opencaching.de/okapi/apps/) | 1 |  |
| [OpenStreetmap](https://wiki.openstreetmap.org/wiki/API) | [link](https://www.openstreetmap.org/user/{USERNAME}/oauth_clients) |  | 1 |  |
| [OpenStreetmap2](https://wiki.openstreetmap.org/wiki/API) | [link](https://www.openstreetmap.org/oauth2/applications) |  | 2 |  |
| [Patreon](https://docs.patreon.com/) | [link](https://www.patreon.com/portal/registration/register-clients) |  | 2 |  |
| [PayPal](https://developer.paypal.com/docs/connect-with-paypal/reference/) | [link](https://developer.paypal.com/developer/applications/) |  | 2 | ✓ |
| [PayPalSandbox](https://developer.paypal.com/docs/connect-with-paypal/reference/) | [link](https://developer.paypal.com/developer/applications/) |  | 2 | ✓ |
| [Slack](https://api.slack.com) | [link](https://api.slack.com/apps) | [link](https://slack.com/apps/manage) | 2 |  |
| [SoundCloud](https://developers.soundcloud.com/) | [link](https://soundcloud.com/you/apps) | [link](https://soundcloud.com/settings/connections) | 2 | ✓ |
| [Spotify](https://developer.spotify.com/documentation/web-api/) | [link](https://developer.spotify.com/dashboard) | [link](https://www.spotify.com/account/apps/) | 2 | ✓ |
| [SteamOpenID](https://developer.valvesoftware.com/wiki/Steam_Web_API) | [link](https://steamcommunity.com/dev/apikey) |  | - |  |
| [Stripe](https://stripe.com/docs/api) | [link](https://dashboard.stripe.com/apikeys) | [link](https://dashboard.stripe.com/account/applications) | 2 |  |
| [Tumblr](https://www.tumblr.com/docs/en/api/v2) | [link](https://www.tumblr.com/oauth/apps) | [link](https://www.tumblr.com/settings/apps) | 1 |  |
| [Tumblr2](https://www.tumblr.com/docs/en/api/v2) | [link](https://www.tumblr.com/oauth/apps) | [link](https://www.tumblr.com/settings/apps) | 2 |  |
| [Twitch](https://dev.twitch.tv/docs/api/reference/) | [link](https://dev.twitch.tv/console/apps/create) | [link](https://www.twitch.tv/settings/connections) | 2 | ✓ |
| [Twitter](https://developer.twitter.com/docs) | [link](https://developer.twitter.com/apps) | [link](https://twitter.com/settings/applications) | 1 |  |
| [TwitterCC](https://developer.twitter.com/en/docs/basics/authentication/overview/application-only) | [link](https://developer.twitter.com/apps) | [link](https://twitter.com/settings/applications) | 2 | ✓ |
| [Vimeo](https://developer.vimeo.com) | [link](https://developer.vimeo.com/apps) | [link](https://vimeo.com/settings/apps) | 2 | ✓ |
| [WordPress](https://developer.wordpress.com/docs/api/) | [link](https://developer.wordpress.com/apps/) | [link](https://wordpress.com/me/security/connected-applications) | 2 |  |
| [YouTube](https://developers.google.com/oauthplayground/) | [link](https://console.developers.google.com/apis/credentials) | [link](https://myaccount.google.com/permissions) | 2 |  |
<!-- TABLE_END -->


# Disclaimer
OAuth tokens are secrets and should be treated as such. Store them in a safe place,
[consider encryption](http://php.net/manual/book.sodium.php). <br/>
I don't take responsibility for stolen auth tokens. Use at your own risk.
