<?php
/**
 * Class GiteaAPITest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Gitea;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Gitea $provider
 */
#[Group('providerLiveTest')]
#[Provider(Gitea::class)]
class GiteaAPITest extends OAuth2ProviderLiveTestAbstract{

}
