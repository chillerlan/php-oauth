<?php
/**
 * @link https://discordapp.com/developers/docs/topics/oauth2
 *
 * @created      20.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Discord;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Discord::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
