<?php
/**
 * Class LastFMTest
 *
 * @created      05.11.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\LastFM;

/**
 * @property \chillerlan\OAuth\Providers\LastFM $provider
 */
final class LastFMTest extends OAuthProviderUnitTestAbstract{

	protected function getProviderFQCN():string{
		return LastFM::class;
	}

	public function testRequest():void{
		$this::markTestIncomplete();
	}

	public function testGetRequestAuthorizationInvalidTokenException():void{
		$this::markTestSkipped('N/A');
	}

	public function testMeResponseInvalidContentTypeException():void{
		$this::markTestSkipped('N/A');
	}

}
