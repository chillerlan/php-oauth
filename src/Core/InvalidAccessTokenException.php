<?php
/**
 * Class InvalidAccessTokenException
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Thrown when an access token is expired and cannot be refreshed
 *
 * @see \chillerlan\OAuth\Core\TokenRefresh
 * @see \chillerlan\OAuth\Core\OAuth1Provider::getRequestAuthorization()
 * @see \chillerlan\OAuth\Core\OAuth2Provider::getRequestAuthorization()
 */
class InvalidAccessTokenException extends UnauthorizedAccessException{

}
