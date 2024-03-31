# Configuration settings
<!-- This file is auto generated from the source of OAuthOptionsTrait.php -->
## key

The application key (or client-id) given by your provider


## secret

The application secret given by your provider


## callbackURL

The (main) callback URL associated with your application


## tokenAutoRefresh

Whether to automatically refresh access tokens (OAuth2)


**See also:**

- `\chillerlan\OAuth\Core\TokenRefresh::refreshAccessToken()`


## sessionStart

Whether to start the session when session storage is used

Note: this will only start a session if there is no active session present


**See also:**

- [php.net: `\session_status()`](https://www.php.net/manual/function.session-status)
- `\chillerlan\OAuth\Storage\SessionStorage`


## sessionStop

Whether to end the session when session storage is used

Note: this is set to `false` by default to not interfere with other session managers


**See also:**

- [php.net: `\session_status()`](https://www.php.net/manual/function.session-status)
- `\chillerlan\OAuth\Storage\SessionStorage`


## sessionTokenVar

The session array key for token storage


**See also:**

- `\chillerlan\OAuth\Storage\SessionStorage`


## sessionStateVar

The session array key for <state> storage (OAuth2)


**See also:**

- `\chillerlan\OAuth\Storage\SessionStorage`

