<?php
/**
 * @link https://docs.patreon.com/#oauth
 *
 * @created      09.08.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Patreon;

$ENVVAR ??= 'PATREON';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Patreon::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
