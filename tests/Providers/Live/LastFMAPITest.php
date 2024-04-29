<?php
/**
 * Class LastFMAPITest
 *
 * @created      10.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\LastFM;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
#[Group('providerLiveTest')]
#[Provider(LastFM::class)]
final class LastFMAPITest extends OAuthProviderLiveTestAbstract{

}
