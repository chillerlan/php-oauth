<?php
/**
 * @link https://developer.foursquare.com/overview/auth
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Foursquare;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Foursquare::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
