<?php
/**
 * Class FoursquareTest
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Foursquare;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Foursquare $provider
 */
#[Provider(Foursquare::class)]
final class FoursquareTest extends OAuth2ProviderUnitTestAbstract{

}
