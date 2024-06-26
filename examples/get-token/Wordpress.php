<?php
/**
 * @link https://developer.wordpress.com/docs/oauth2/
 *
 * @created      21.04.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\WordPress;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(WordPress::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
