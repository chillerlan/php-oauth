<?php
/**
 * Class MusicBrainzTest
 *
 * @created      31.07.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Providers\MusicBrainz;
use chillerlan\OAuth\Providers\ProviderException;
use PHPUnit\Framework\Attributes\Group;
use Psr\Http\Message\ResponseInterface;

/**
 * @property \chillerlan\OAuth\Providers\MusicBrainz $provider
 */
#[Group('providerLiveTest')]
class MusicBrainzAPITest extends OAuth2ProviderLiveTestAbstract{

	protected function getProviderFQCN():string{
		return MusicBrainz::class;
	}

	protected function getEnvPrefix():string{
		return 'MUSICBRAINZ';
	}

	protected function assertMeResponse(ResponseInterface $response, object|null $json):void{
		// noop
	}

	public function testMe():void{
		$this::markTestSkipped('user endpoint N/A');
	}

	public function testMeErrorException():void{
		$this::markTestSkipped('not implemented');
	}

	public function testArtistId():void{
		try{
			$response = $this->provider->request(
				'/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d',
				['inc' => 'url-rels work-rels'],
			);

			$json = MessageUtil::decodeJSON($response);

			$this::assertSame('Helium', $json->name);
			$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', $json->id);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

	public function testArtistIdXML():void{
		try{
			$response = $this->provider->request(
				'/artist/573510d6-bb5d-4d07-b0aa-ea6afe39e28d',
				['inc' => 'url-rels work-rels', 'fmt' => 'xml'],
			);

			$xml = MessageUtil::decodeXML($response);

			$this::assertSame('Helium', (string)$xml->artist[0]->name);
			$this::assertSame('573510d6-bb5d-4d07-b0aa-ea6afe39e28d', (string)$xml->artist[0]->attributes()['id']);
		}
		catch(ProviderException){
			$this::markTestSkipped('token is missing or expired');
		}
	}

}
