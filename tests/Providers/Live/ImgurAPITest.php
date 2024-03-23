<?php
/**
 * Class ImgurAPITest
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Imgur;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Imgur $provider
 */
#[Group('providerLiveTest')]
class ImgurAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Imgur::class;
	}

	protected function getEnvPrefix():string{
		return 'IMGUR';
	}

	protected function setUp():void{
		parent::setUp();

		$token = $this->storage->getAccessToken($this->provider->serviceName);

		$this->TEST_USER = $token->extraParams['account_id'];
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame((int)$this->TEST_USER, $json->data->id);
	}

}
