<?php
/**
 * @link https://github.com/reddit-archive/reddit/wiki/OAuth2
 *
 * @created      09.04.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Reddit;

$PARAMS ??= ['duration' => 'permanent'];

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Reddit::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
