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

$ENVVAR ??= 'MAILCHIMP';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthExampleProviderFactory $factory
 * @var array|null                   $PARAMS
 * @var array|null                   $SCOPES
 */

$provider = $factory->getProvider(MailChimp::class, $ENVVAR);
$name     = $provider->name;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL($PARAMS, $SCOPES));
}
// step 3: receive the access token
elseif(isset($_GET['code']) && isset($_GET['state'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state']);

	// MailChimp needs another call to the auth metadata endpoint
	// to receive the datacenter prefix/API URL, which will then
	// be stored in AccessToken::$extraParams
	$token = $provider->getTokenMetadata($token);

	// save the token [...]
	$factory->getFileStorage()->storeAccessToken($token, $name);

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	// use the file storage from now on
	$provider->setStorage($factory->getFileStorage());

	$me        = print_r($provider->me(), true);
	$tokenJSON = $provider->getAccessTokenFromStorage()->toJSON();

	printf('<pre>%s</pre><textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $me, $tokenJSON);
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}

exit;
