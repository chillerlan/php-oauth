# Configuration settings
<!-- This file is auto generated from the source of OAuthOptionsTrait.php -->
## key

The application key (or client-id) given by your provider


## secret

The application secret given by your provider


## callbackURL

The (main) callback URL associated with your application


## useStorageEncryption

Whether to use encryption for the file storage


**See also:**

- `\chillerlan\OAuth\Storage\FileStorage`


## storageEncryptionKey

The encryption key (hexadecimal) to use


**See also:**

- [php.net: `\sodium_crypto_secretbox_keygen()`](https://www.php.net/manual/function.sodium-crypto-secretbox-keygen)
- `\chillerlan\OAuth\Storage\FileStorage`


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


## sessionStorageVar

The session key for the storage array


**See also:**

- `\chillerlan\OAuth\Storage\SessionStorage`


## fileStoragePath

The file storage root path (requires permissions 0777)


**See also:**

- [php.net: `\is_writable()`](https://www.php.net/manual/function.is-writable)
- `\chillerlan\OAuth\Storage\FileStorage`


## pkceVerifierLength

The length of the PKCE challenge verifier (43-128 characters)


**Links:**

- [datatracker.ietf.org/doc/html/rfc7636#section-4.1](https://datatracker.ietf.org/doc/html/rfc7636#section-4.1)

