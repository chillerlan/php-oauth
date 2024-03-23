<?php
/**
 * @link https://login.amazon.com/
 *
 * @created      10.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Amazon;

$ENVVAR ??= 'AMAZON';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Amazon::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
