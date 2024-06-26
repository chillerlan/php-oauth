<?php
/**
 * Interface OAuthStorageInterface
 *
 * @created      09.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Storage;

use chillerlan\OAuth\Core\AccessToken;
use Psr\Log\LoggerInterface;

/**
 * Specifies the methods required for an OAuth storage adapter
 *
 * The storage is intended to be invoked per-user, for whom it can
 * store tokens, state etc. for any of the implemented providers.
 *
 * The implementer must ensure that the same storage instance is not used for multiple users.
 */
interface OAuthStorageInterface{

	/*
	 * Common
	 */

	/**
	 * Sets a logger. (LoggerAwareInterface is stupid)
	 */
	public function setLogger(LoggerInterface $logger):static;

	/**
	 * Prepares an AccessToken for storage (serialize, encrypt etc.)
	 * and returns a value that is suited for the underlying storage engine
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function toStorage(AccessToken $token):mixed;

	/**
	 * Retrieves token JOSN from the underlying storage engine and returns an AccessToken
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function fromStorage(mixed $data):AccessToken;


	/*
	 * Access token
	 */

	/**
	 * Stores an AccessToken for the given $provider
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeAccessToken(AccessToken $token, string $provider):static;

	/**
	 * Retrieves an AccessToken for the given $provider
	 *
	 * This method *must* throw a ItemNotFoundException if a token is not found
	 *
	 * @throws \chillerlan\OAuth\Storage\ItemNotFoundException
	 */
	public function getAccessToken(string $provider):AccessToken;

	/**
	 * Checks if a token for $provider exists
	 */
	public function hasAccessToken(string $provider):bool;

	/**
	 * Deletes the access token for a given $provider (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAccessToken(string $provider):static;

	/**
	 * Deletes all access tokens (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllAccessTokens():static;


	/*
	 * CSRF state
	 */

	/**
	 * Stores a CSRF <state> value for the given $provider
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function storeCSRFState(string $state, string $provider):static;

	/**
	 * Retrieves a CSRF <state> value for the given $provider
	 *
	 * This method *must* throw a ItemNotFoundException if a state is not found
	 *
	 * @throws \chillerlan\OAuth\Storage\ItemNotFoundException
	 */
	public function getCSRFState(string $provider):string;

	/**
	 * Checks if a CSRF state for the given provider exists
	 */
	public function hasCSRFState(string $provider):bool;

	/**
	 * Deletes a CSRF state for the given $provider (and current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearCSRFState(string $provider):static;

	/**
	 * Deletes all stored CSRF states (for the current user)
	 *
	 * @throws \chillerlan\OAuth\Storage\OAuthStorageException
	 */
	public function clearAllCSRFStates():static;


	/*
	 * PKCE verifier
	 */

	/**
	 * Stores a PKCE verifier
	 */
	public function storeCodeVerifier(string $verifier, string $provider):static;

	/**
	 * Retrieves a PKCE verifier
	 *
	 * This method *must* throw a ItemNotFoundException if a verifier is not found
	 *
	 * @throws \chillerlan\OAuth\Storage\ItemNotFoundException
	 */
	public function getCodeVerifier(string $provider):string;

	/**
	 * Checks whether a PKCE verifier exists
	 */
	public function hasCodeVerifier(string $provider):bool;

	/**
	 * Deletes a PKCE verifier
	 */
	public function clearCodeVerifier(string $provider):static;

	/**
	 * Deletes all PKCE verifiers for this user
	 */
	public function clearAllCodeVerifiers():static;

}
