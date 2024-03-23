<?php
/**
 * Class GitLabAPITest
 *
 * @created      29.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\GitLab;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\GitLab $provider
 */
#[Group('shortTokenExpiry')]
#[Group('providerLiveTest')]
class GitLabAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return GitLab::class;
	}

	protected function getEnvPrefix():string{
		return 'GITLAB';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		$this::assertSame($this->TEST_USER, $json->username);
	}

}
