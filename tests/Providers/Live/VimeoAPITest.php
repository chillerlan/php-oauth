<?php
/**
 * Class VimeoAPITest
 *
 * @created      09.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\Vimeo;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\Vimeo $provider
 */
#[Group('providerLiveTest')]
class VimeoAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return Vimeo::class;
	}

	protected function getEnvPrefix():string{
		return 'VIMEO';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		// vimeo sends "Content-Type: application/vnd.vimeo.user+json"
		$json = MessageUtil::decodeJSON($response);

		$this::assertSame('https://vimeo.com/'.$this->TEST_USER, $json->link);
	}

}
