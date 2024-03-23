<?php
/**
 * Class MixcloudAPITest
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Mixcloud;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Mixcloud $provider
 */
#[Group('providerLiveTest')]
class MixcloudAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Mixcloud::class;
	}

	protected function getEnvPrefix():string{
		return 'MIXCLOUD';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		// mixcloud sends "Content-Type: text/javascript" for JSON content (????)
		$json = MessageUtil::decodeJSON($response);

		$this::assertSame($this->TEST_USER,$json->username);
	}

}
