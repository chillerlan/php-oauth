<?php
/**
 * Class MastodonAPITest
 *
 * @created      19.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Mastodon;
use chillerlan\OAuthTest\Attributes\Provider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\Mastodon $provider
 */
#[Group('providerLiveTest')]
#[Provider(Mastodon::class)]
final class MastodonAPITest extends OAuth2ProviderLiveTestAbstract{

	protected string $testInstance;

	protected function setUp():void{
		parent::setUp();

		$this->testInstance = ($this->dotEnv->get($this->ENV_PREFIX.'_INSTANCE') ?? '');

		$this->provider->setInstance($this->testInstance);
	}

}
