<?php
/**
 * @link http://developer.mailchimp.com/documentation/mailchimp/guides/how-to-use-oauth2/
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\MailChimp;

require_once __DIR__.'/../provider-example-common.php';

/** @var \OAuthExampleProviderFactory $factory */
$provider = $factory->getProvider(MailChimp::class);

require_once __DIR__.'/_flow-oauth2.php';

exit;
