<?php
/**
 * @link https://develop.battle.net/documentation/guides/using-oauth
 *
 * @created      02.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\BattleNet;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(BattleNet::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
