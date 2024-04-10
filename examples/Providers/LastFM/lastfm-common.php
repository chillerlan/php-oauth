<?php
/**
 * @filesource   lastfm-common.php
 * @created      03.08.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\LastFM;

require_once __DIR__.'/../../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$lfm = $factory->getProvider(LastFM::class, OAuthExampleProviderFactory::STORAGE_FILE);
