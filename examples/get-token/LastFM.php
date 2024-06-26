<?php
/**
 * @link https://www.last.fm/api/authentication
 *
 * @created      10.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Providers\LastFM;

require_once __DIR__.'/../provider-example-common.php';

/**
 * @var \OAuthExampleProviderFactory $factory
 * @var array|null                   $PARAMS
 */

$provider = $factory->getProvider(LastFM::class);
$name     = $provider->getName();

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL($PARAMS));
}
// step 3: receive the access token
elseif(isset($_GET['token'])){
	$token = $provider->getAccessToken($_GET['token']);

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
