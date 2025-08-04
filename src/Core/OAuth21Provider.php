<?php
/**
 * Class OAuth21Provider
 *
 * @created      04.08.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Implements an abstract OAuth 2.1 provider (WIP)
 *
 * @link https://datatracker.ietf.org/doc/html/draft-ietf-oauth-v2-1-13
 */
class OAuth21Provider extends OAuth2Provider implements OAuth21Interface{
	use PKCETrait;

}
