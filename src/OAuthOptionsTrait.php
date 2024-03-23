<?php
/**
 * Trait OAuthOptionsTrait
 *
 * @created      29.01.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth;

/**
 * The settings for the OAuth provider
 */
trait OAuthOptionsTrait{

	/**
	 * The application key (or id) given by your provider
	 */
	protected string $key = '';

	/**
	 * The application secret given by your provider
	 */
	protected string $secret = '';

	/**
	 * The callback URL associated with your application
	 */
	protected string $callbackURL = '';

	/**
	 * Whether to start the session when session storage is used
	 *
	 * Note: this will only start a session if there is no active session present
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected bool $sessionStart = true;

	/**
	 * Whether to end the session when session storage is used
	 *
	 * Note: this is set to `false` by default to not interfere with other session managers
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected bool $sessionStop = false;

	/**
	 * The session array key for token storage
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionTokenVar = 'chillerlan-oauth-token';

	/**
	 * The session array key for <state> storage (OAuth2)
	 *
	 * @see \chillerlan\OAuth\Storage\SessionStorage
	 */
	protected string $sessionStateVar = 'chillerlan-oauth-state';

	/**
	 * Whether to automatically refresh access tokens (OAuth2)
	 */
	protected bool $tokenAutoRefresh = true;

}
