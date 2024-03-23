<?php
/**
 * @link https://www.tumblr.com/docs/en/api/v2#oauth
 *
 * @created      24.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\Tumblr;

$ENVVAR ??= 'TUMBLR';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(Tumblr::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth1.php';

exit;
