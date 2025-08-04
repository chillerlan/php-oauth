<?php
/**
 * Interface OAuth21Interface
 *
 * @created      04.08.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

/**
 * Specifies the basic methods for an OAuth2.1 provider. (WIP)
 */
interface OAuth21Interface extends OAuth2Interface, CSRFToken, PKCE{

}
