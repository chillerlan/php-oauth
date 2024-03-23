<?php
/**
 * Class OpenStreetmap2APITest
 *
 * @created      05.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\OpenStreetmap2;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\OpenStreetmap2 $provider
 */
#[Group('providerLiveTest')]
class OpenStreetmap2APITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return OpenStreetmap2::class;
	}

	protected function getEnvPrefix():string{
		return 'OPENSTREETMAP2';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->user->display_name);
	}

}
