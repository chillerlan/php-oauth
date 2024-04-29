<?php
/**
 * Class CodebergAPITest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Codeberg;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Codeberg $provider
 */
#[Group('providerLiveTest')]
#[Provider(Codeberg::class)]
class CodebergAPITest extends OAuth2ProviderLiveTestAbstract{

}
