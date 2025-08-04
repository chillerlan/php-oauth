<?php
/**
 * Class TikTokAPITest
 *
 * @created      06.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\TikTok;
use chillerlan\OAuthTest\Attributes\Provider;
use chillerlan\OAuthTest\Providers\Live\OAuth2ProviderLiveTestAbstract;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\TikTok $provider
 */
#[Group('providerLiveTest')]
#[Provider(TikTok::class)]
final class TikTokAPITest extends OAuth2ProviderLiveTestAbstract{

}
