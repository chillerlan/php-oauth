<?php
/**
 * spotify-common.php
 *
 * @created      03.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthExamples\Providers\Spotify;

use chillerlan\OAuth\Providers\Spotify;

$ENVVAR = 'SPOTIFY';

require_once __DIR__.'/../../provider-example-common.php';
require_once __DIR__.'/SpotifyClient.php';

/** @var \OAuthExampleProviderFactory $factory */
$spotify = $factory->getProvider(Spotify::class, $ENVVAR);
