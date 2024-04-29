<?php
/**
 * Class MicrosoftGraphTest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\MicrosoftGraph;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\MicrosoftGraph $provider
 */
#[Provider(MicrosoftGraph::class)]
final class MicrosoftGraphTest extends OAuth2ProviderUnitTestAbstract{

}
