<?php
/**
 * @link https://steamcommunity.com/dev
 *
 * @created      20.03.2021
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2021 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\Steam;

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthExampleProviderFactory      $factory
 * @var \chillerlan\OAuth\Providers\Steam $provider
 */
$provider = $factory->getProvider(Steam::class);
$name     = $provider->getName();

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL());
}
// step 3: receive the access token
elseif(isset($_GET['openid_sig'], $_GET['openid_signed'], $_GET['openid_claimed_id'])){
	// the Steam provider takes the whole $_GET array as it uses multiple of the query parameters
	$token = $provider->getAccessToken($_GET);

	// save the token [...]
	$factory->getFileStorage()->storeAccessToken($token, $name);

	// access granted, redirect
	header('Location: ?granted='.$name);
}
//step 3.1: oh noes!
elseif(isset($_GET['openid_error'])){ // openid.error -> https://stackoverflow.com/questions/68651/
	exit('oh noes: '.$_GET['openid_error']);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	// use the file storage from now on
	$provider->setStorage($factory->getFileStorage());

	$token = $provider->getAccessTokenFromStorage(); // the user's steamid is stored as access token

	printf('<pre>%s</pre>', print_r($provider->me(), true));
	printf('<textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $token->toJSON());
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">connect with '.$name.'!</a>';
}
