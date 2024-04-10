<?php
/**
 * @link https://codeberg.org/api/swagger
 *
 * @created      08.04.2024
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Codeberg;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(Codeberg::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
