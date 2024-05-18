<?php
/**
 * Class UnauthorizedAccessException
 *
 * @created      18.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\OAuth\OAuthException;

/**
 * Thrown on generic "Unauthorized" HTTP errors: 400, 401, 403
 */
class UnauthorizedAccessException extends OAuthException{

}
