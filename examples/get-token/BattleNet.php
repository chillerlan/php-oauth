<?php
/**
 * @link https://develop.battle.net/documentation/guides/using-oauth
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\BattleNet;

$ENVVAR ??= 'BATTLENET';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$provider = $factory->getProvider(BattleNet::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
