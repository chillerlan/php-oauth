<?php
/**
 * @link https://developer.vimeo.com/api/authentication
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Vimeo;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Vimeo::class);

/*
 * The Vimeo AccessToken instance holds additional values:
 *
 * $app = $token->extraParams['app'];
 */

require_once __DIR__.'/_flow-oauth2.php';

exit;
