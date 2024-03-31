# Overview

[`chillerlan/php-oauth`](https://github.com/chillerlan/php-oauth), a transparent, framework-agnostic, easily extensible PHP
[PSR-18](https://www.php-fig.org/psr/psr-18/) OAuth 1/2 client with a user-friendly API,
fully [PSR-7](https://www.php-fig.org/psr/psr-7/)/[PSR-17](https://www.php-fig.org/psr/psr-17/) compatible.

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
- Provider instances act as [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client, wrapping the given PSR-18 HTTP instance
	- Requests to the provider API will have required OAuth headers and tokens added automatically
- Several built-in provider implementations ([see below](#implemented-providers))
- A unified user data object `AuthenticatedUser` via the `OAuthInterface::me()` method


## Requirements

- PHP 8.1+
	- extensions: `json`, `sodium`
		- from dependencies: `curl`, `fileinfo`, `intl`, `mbstring`, `simplexml`, `zlib`
- a [PSR-18](https://www.php-fig.org/psr/psr-18/) compatible HTTP client library of your choice
- [PSR-17](https://www.php-fig.org/psr/psr-17/) compatible `RequestFactory`, `StreamFactory` and `UriFactory`


## Supported Providers

<!-- TABLE-START -->
<!-- this table is auto-created via /examples/create-description.php -->

| Provider | App keys | revoke access | OAuth | CSRF | CC | TR | TI |
|----------|----------|---------------|-------|------|----|----|----|
| [Amazon]() | [link](https://developer.amazon.com/loginwithamazon/console/site/lwa/overview.html) | [link]() | 2 | ✓ |  | ✓ |  |
| [BattleNet](https://develop.battle.net/documentation) | [link](https://develop.battle.net/access/clients) |  | 2 | ✓ | ✓ |  |  |
| [BigCartel](https://developers.bigcartel.com/api/v1) | [link](https://bigcartel.wufoo.com/forms/big-cartel-api-application/) |  | 2 | ✓ |  |  | ✓ |
| [Bitbucket](https://developer.atlassian.com/bitbucket/api/2/reference/) | [link](https://developer.atlassian.com/apps/) | [link]() | 2 | ✓ | ✓ | ✓ |  |
| [Deezer](https://developers.deezer.com/api) | [link](https://developers.deezer.com/myapps) |  | 2 | ✓ |  |  |  |
| [DeviantArt](https://www.deviantart.com/developers/) | [link](https://www.deviantart.com/developers/apps) |  | 2 | ✓ | ✓ | ✓ | ✓ |
| [Discogs](https://www.discogs.com/developers/) | [link](https://www.discogs.com/settings/developers) |  | 1 |  |  |  |  |
| [Discord](https://discord.com/developers/) | [link](https://discordapp.com/developers/applications/) | [link]() | 2 | ✓ | ✓ | ✓ | ✓ |
| [Flickr](https://www.flickr.com/services/api/) | [link](https://www.flickr.com/services/apps/create/) |  | 1 |  |  |  |  |
| [Foursquare](https://location.foursquare.com/developer/reference/foursquare-apis-overview) | [link](https://foursquare.com/developers/apps) |  | 2 |  |  |  |  |
| [GitHub](https://docs.github.com/rest) | [link](https://github.com/settings/developers) |  | 2 | ✓ |  | ✓ |  |
| [GitLab]() | [link](https://gitlab.com/profile/applications) | [link]() | 2 | ✓ | ✓ | ✓ |  |
| [Google](https://developers.google.com/oauthplayground/) | [link](https://console.developers.google.com/apis/credentials) |  | 2 | ✓ |  |  |  |
| [GuildWars2](https://wiki.guildwars2.com/wiki/API:Main) | [link](https://account.arena.net/applications) |  | 2 |  |  |  |  |
| [Imgur](https://apidocs.imgur.com) | [link](https://api.imgur.com/oauth2/addclient) |  | 2 | ✓ |  | ✓ |  |
| [LastFM](https://www.last.fm/api/) | [link](https://www.last.fm/api/account/create) |  | - |  |  |  |  |
| [MailChimp](https://mailchimp.com/developer/) | [link](https://admin.mailchimp.com/account/oauth2/) | [link]() | 2 | ✓ |  |  |  |
| [Mastodon](https://docs.joinmastodon.org/api/) | [link](https://mastodon.social/settings/applications) |  | 2 | ✓ |  | ✓ |  |
| [MicrosoftGraph](https://learn.microsoft.com/graph/overview) | [link](https://aad.portal.azure.com/#blade/Microsoft_AAD_IAM/ActiveDirectoryMenuBlade/RegisteredApps) |  | 2 | ✓ |  |  |  |
| [Mixcloud](https://www.mixcloud.com/developers/) | [link](https://www.mixcloud.com/developers/create/) |  | 2 |  |  |  |  |
| [MusicBrainz](https://musicbrainz.org/doc/Development) | [link](https://musicbrainz.org/account/applications) |  | 2 | ✓ |  | ✓ | ✓ |
| [NPROne](https://dev.npr.org/api/) | [link](https://dev.npr.org/console) | [link]() | 2 | ✓ |  | ✓ | ✓ |
| [OpenCaching](https://www.opencaching.de/okapi/) | [link](https://www.opencaching.de/okapi/signup.html) |  | 1 |  |  |  |  |
| [OpenStreetmap](https://wiki.openstreetmap.org/wiki/API) | [link](https://www.openstreetmap.org/user/{USERNAME}/oauth_clients) | [link]() | 1 |  |  |  |  |
| [OpenStreetmap2](https://wiki.openstreetmap.org/wiki/API) | [link](https://www.openstreetmap.org/oauth2/applications) | [link]() | 2 | ✓ |  |  |  |
| [Patreon](https://docs.patreon.com/) | [link](https://www.patreon.com/portal/registration/register-clients) | [link]() | 2 | ✓ |  | ✓ |  |
| [PayPal](https://developer.paypal.com/docs/connect-with-paypal/reference/) | [link](https://developer.paypal.com/developer/applications/) | [link]() | 2 | ✓ | ✓ | ✓ |  |
| [PayPalSandbox](https://developer.paypal.com/docs/connect-with-paypal/reference/) | [link](https://developer.paypal.com/developer/applications/) | [link]() | 2 | ✓ | ✓ | ✓ |  |
| [Slack](https://api.slack.com) | [link](https://api.slack.com/apps) |  | 2 | ✓ |  |  |  |
| [SoundCloud](https://developers.soundcloud.com/) | [link](https://soundcloud.com/you/apps) |  | 2 |  | ✓ | ✓ |  |
| [Spotify](https://developer.spotify.com/documentation/web-api/) | [link](https://developer.spotify.com/dashboard) |  | 2 | ✓ | ✓ | ✓ |  |
| [SteamOpenID](https://developer.valvesoftware.com/wiki/Steam_Web_API) | [link](https://steamcommunity.com/dev/apikey) | [link]() | - |  |  |  |  |
| [Stripe](https://stripe.com/docs/api) | [link](https://dashboard.stripe.com/apikeys) |  | 2 | ✓ |  | ✓ | ✓ |
| [Tumblr](https://www.tumblr.com/docs/en/api/v2) | [link](https://www.tumblr.com/oauth/apps) |  | 1 |  |  |  |  |
| [Tumblr2](https://www.tumblr.com/docs/en/api/v2) | [link](https://www.tumblr.com/oauth/apps) |  | 2 | ✓ | ✓ | ✓ |  |
| [Twitch](https://dev.twitch.tv/docs/api/reference/) | [link](https://dev.twitch.tv/console/apps/create) |  | 2 | ✓ | ✓ | ✓ | ✓ |
| [Twitter](https://developer.twitter.com/docs) | [link](https://developer.twitter.com/apps) |  | 1 |  |  |  |  |
| [TwitterCC](https://developer.twitter.com/en/docs/basics/authentication/overview/application-only) | [link](https://developer.twitter.com/apps) |  | 2 |  | ✓ |  |  |
| [Vimeo](https://developer.vimeo.com) | [link](https://developer.vimeo.com/apps) |  | 2 | ✓ | ✓ |  | ✓ |
| [WordPress](https://developer.wordpress.com/docs/api/) | [link](https://developer.wordpress.com/apps/) |  | 2 | ✓ |  |  |  |
| [YouTube](https://developers.google.com/oauthplayground/) | [link](https://console.developers.google.com/apis/credentials) |  | 2 | ✓ |  |  |  |

**Legend:**
- **Provider**: the name of the provider class and link to their API documentation
- **App keys**: links to the provider's OAuth application creation page
- **revoke access**: links to the OAuth application access revocation page in the provider's user profile
- **OAuth**: the OAuth version(s) supported by the provider
- **CSRF**: indicates whether the provider uses [CSRF protection via the `state` parameter](https://datatracker.ietf.org/doc/html/rfc6749#section-10.12) (implements the `CSRFToken` interface)
- **CC**: indicates whether the provider supports the [Client Credentials Grant](https://datatracker.ietf.org/doc/html/rfc6749#section-4.4) (implements the `ClientCredentials` interface)
- **TR**: indicates whether the provider is capable of [refreshing an access token](https://datatracker.ietf.org/doc/html/rfc6749#section-10.4) (implements the `TokenRefresh` interface)
- **TI**: indicates whether the provider is capable of revoking/invalidating an access token (implements the `TokenInvalidate` interface)
<!-- TABLE_END -->

## Shameless advertising

Hi, please check out some of my other projects that are way cooler than qrcodes!

- [php-qrcode](https://github.com/chillerlan/php-qrcode) - a QR code generator and reader with a user-friendly API
- [php-authenticator](https://github.com/chillerlan/php-authenticator) - yet another Google Authenticator implementation
- [php-httpinterface](https://github.com/chillerlan/php-httpinterface) - a PSR-7/17/18 implemetation
- [php-database](https://github.com/chillerlan/php-database) - a database client & querybuilder for MySQL, Postgres, SQLite, MSSQL, Firebird
- [php-tootbot](https://github.com/php-tootbot/tootbot-template) - a Mastodon bot library
