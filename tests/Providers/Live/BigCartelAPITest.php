<?php
/**
 * Class BigCartelAPITest
 *
 * @created      10.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\BigCartel;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\BigCartel $provider
 */
#[Group('providerLiveTest')]
class BigCartelAPITest extends OAuth2ProviderLiveTestAbstract{

	protected int $account_id;

	protected function getProviderFQCN():string{
		return BigCartel::class;
	}

	protected function getEnvPrefix():string{
		return 'BIGCARTEL';
	}

	protected function setUp():void{
		parent::setUp();

		$this->account_id = (int)$this->storage->getAccessToken($this->provider->serviceName)->extraParams['account_id'];
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->account_id, (int)$json->data[0]->id);
	}

}
