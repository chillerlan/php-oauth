<?php
/**
 * Class OpenCachingAPITest
 *
 * @created      04.03.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\OpenCaching;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\OpenCaching $provider
 */
#[Group('providerLiveTest')]
class OpenCachingAPITest extends OAuth1ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return OpenCaching::class;
	}

	protected function getEnvPrefix():string{
		return 'OKAPI';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

}
