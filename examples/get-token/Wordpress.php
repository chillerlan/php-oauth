<?php
/**
 * @link https://developer.wordpress.com/docs/oauth2/
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\WordPress;

$ENVVAR ??= 'WORDPRESS';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(WordPress::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
