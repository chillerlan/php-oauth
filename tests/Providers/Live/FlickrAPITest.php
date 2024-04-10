<?php
/**
 * Class FlickrAPITest
 *
 * @created      15.07.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Live;

use chillerlan\OAuth\Providers\Flickr;
use PHPUnit\Framework\Attributes\Group;

/**
 * @property  \chillerlan\OAuth\Providers\Flickr $provider
 */
#[Group('providerLiveTest')]
final class FlickrAPITest extends OAuth1ProviderLiveTestAbstract{

	protected string $test_name;
	protected string $test_id;

	protected function getProviderFQCN():string{
		return Flickr::class;
	}

}
