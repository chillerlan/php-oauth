<?php
/**
 * Class GitHubAPITest
 *
 * @created      18.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\GitHub;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property  \chillerlan\OAuth\Providers\GitHub $provider
 */
#[Group('providerLiveTest')]
class GitHubAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return GitHub::class;
	}

	protected function getEnvPrefix():string{
		return 'GITHUB';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->login);
	}

}
