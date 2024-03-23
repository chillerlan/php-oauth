<?php
/**
 * Class WordpressAPITest
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\WordPress;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\WordPress $provider
 */
#[Group('providerLiveTest')]
class WordpressAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return WordPress::class;
	}

	protected function getEnvPrefix():string{
		return 'WORDPRESS';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

}
