<?php
/**
 * Class CSRFStateMismatchException
 *
 * @created      19.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\OAuthException;

/**
 * Thrown on mismatching CSRF ("state") token
 *
 * @see \chillerlan\OAuth\Core\CSRFToken
 */
class CSRFStateMismatchException extends OAuthException{

}
