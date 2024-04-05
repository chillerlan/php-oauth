<?php
/**
 * @created      09.01.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\GitHub;

$ENVVAR = 'GITHUB';

require_once __DIR__.'/../../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$github = $factory->getProvider(GitHub::class, $ENVVAR, OAuthExampleProviderFactory::STORAGE_FILE);
