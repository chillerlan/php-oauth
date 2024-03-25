<?php
/**
 * Class OpenCachingAPITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\OpenCaching;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property \chillerlan\OAuth\Providers\OpenCaching $provider
 */
#[Group('providerLiveTest')]
final class OpenCachingAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return OpenCaching::class;
	}

	protected function getEnvPrefix():string{
		return 'OKAPI';
	}

}
