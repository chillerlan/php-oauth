<?php
/**
 * Class GiteaTest
 *
 * @created      08.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\Gitea;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\Gitea $provider
 */
#[Provider(Gitea::class)]
class GiteaTest extends OAuth2ProviderUnitTestAbstract{

}
