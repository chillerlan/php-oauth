<?php
/**
 * Class MicrosoftGraphAPITest
 *
 * @created      30.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\MicrosoftGraph;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\MicrosoftGraph $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
#[Provider(MicrosoftGraph::class)]
final class MicrosoftGraphAPITest extends OAuth2ProviderLiveTestAbstract{

}
