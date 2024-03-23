<?php
/**
 * @created      09.01.2022
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2022 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Providers\GitHub;

$ENVVAR = 'GITHUB';

require_once __DIR__.'/../../provider-example-common.php';

/** @var \OAuthProviderFactory $factory */
$github = $factory->getProvider(GitHub::class, $ENVVAR);
