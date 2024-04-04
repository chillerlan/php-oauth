<?php
/**
 * @link https://apidocs.imgur.com/?version=latest#authorization-and-oauth
 *
 * @created      28.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Imgur;

$ENVVAR ??= 'IMGUR';

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthExampleProviderFactory $factory
 * @var array|null                   $PARAMS
 * @var array|null                   $SCOPES
 */

$provider = $factory->getProvider(Imgur::class, $ENVVAR);
$storage  = $provider->getStorage();
$name     = $provider->name;

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL($PARAMS, $SCOPES));
}
// step 3: receive the access token
elseif(isset($_GET['code']) && isset($_GET['state'])){
	$token = $provider->getAccessToken($_GET['code'], $_GET['state']);

	$username = $token->extraParams['account_username'];
	$id       = $token->extraParams['account_id'];

	// imgur sends the token with an expiry of 10 years,
	// so we set the expiry to a sane period to allow auto-refreshing
	$token->expires = (time() + 2592000); // 30 days
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
