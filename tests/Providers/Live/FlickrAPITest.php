<?php
/**
 * Class FlickrAPITest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Flickr;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property  \chillerlan\OAuth\Providers\Flickr $provider
 */
#[Group('providerLiveTest')]
class FlickrAPITest extends OAuth1ProviderLiveTestAbstract{

	protected string $test_name;
	protected string $test_id;

	protected function getProviderFQCN():string{
		return Flickr::class;
	}

	protected function getEnvPrefix():string{
		return 'FLICKR';
	}

	protected function setUp():void{
		parent::setUp();

		$tokenParams     = $this->storage->getAccessToken($this->provider->serviceName)->extraParams;

		$this->test_name = $tokenParams['username'];
		$this->test_id   = $tokenParams['user_nsid'];
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->test_name, $json->user->username->_content);
		$this::assertSame($this->test_id, $json->user->id);
	}

}
