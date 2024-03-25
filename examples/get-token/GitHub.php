<?php
/**
 * @link https://developer.github.com/apps/building-integrations/setting-up-and-registering-oauth-apps/
 *
 * @created      22.10.2017
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2017 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\GitHub;

$ENVVAR ??= 'GITHUB';

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(GitHub::class, $ENVVAR);

require_once __DIR__.'/_flow-oauth2.php';

exit;
