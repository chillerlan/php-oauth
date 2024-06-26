<?php
/**
 * _flow-oauth2.php
 *
 * @created      04.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Core\{CSRFToken, UserInfo};

/**
 * @var \chillerlan\OAuth\Core\OAuth2Interface $provider
 * @var \OAuthExampleProviderFactory $factory
 * @var array|null $PARAMS
 * @var array|null $SCOPES
 */

$name = $provider->getName();

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL($PARAMS, $SCOPES));
}
// step 3: receive the access token
elseif(isset($_GET['code'])){
	$state = null;

	if($provider instanceof CSRFToken && isset($_GET['state'])){
		$state = $_GET['state'];
	}
	/** @phan-suppress-next-line PhanUndeclaredMethod ($provider is, in fact, also instance of OAuthInterface) */
	$token = $provider->getAccessToken($_GET['code'], $state);

	// save the token [...]
	$factory->getFileStorage()->storeAccessToken($token, $name);

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	// use the file storage from now on
	$provider->setStorage($factory->getFileStorage());

	if($provider instanceof UserInfo){
		printf('<pre>%s</pre>', print_r($provider->me(), true));
	}
	/** @phan-suppress-next-line PhanUndeclaredMethod */
	$tokenJSON = $provider->getAccessTokenFromStorage()->toJSON();

	printf('<textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $tokenJSON);
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">Connect with '.$name.'!</a>';
}
