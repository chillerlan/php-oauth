<?php
/**
 * Class MailChimpTest
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers\Unit;

use chillerlan\OAuth\Providers\MailChimp;
use chillerlan\OAuthTest\Attributes\Provider;

/**
 * @property \chillerlan\OAuth\Providers\MailChimp $provider
 */
#[Provider(MailChimp::class)]
final class MailChimpTest extends OAuth2ProviderUnitTestAbstract{

}
