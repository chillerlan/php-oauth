<?php
/**
 * @link https://www.mixcloud.com/developers/#authorization
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Mixcloud;

$ENVVAR ??= 'MIXCLOUD';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Mixcloud::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2-no-state.php';

exit;
