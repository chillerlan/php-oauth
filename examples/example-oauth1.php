<?php
/**
 * A full self-contained OAuth1 example
 *
 * @created      19.05.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Core\OAuthInterface;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Providers\Discogs;
use chillerlan\OAuth\Storage\SessionStorage;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

require_once __DIR__.'/../vendor/autoload.php';

#error_reporting(E_ALL);
#ini_set('display_errors', 1);
ini_set('date.timezone', 'UTC');

// invoke the oauth options instance
$options = new OAuthOptions([
	'key'          => '[client id]',
	'secret'       => '[client secret]',
	'callbackURL'  => '[callback URL]',
	'sessionStart' => true,
]);

// the PSR-18 HTTP client
$http = new Client([
	'verify'  => '/path/to/cacert.pem',
	'headers' => [
		'User-Agent' => OAuthInterface::USER_AGENT,
	],
]);

// the PSR-17 factory/factories
$httpFactory = new HttpFactory;
// the storage instance
$storage     = new SessionStorage($options);
// the provider
$provider    = new Discogs($options, $http, $httpFactory, $httpFactory, $httpFactory, $storage);

// execute the oauth flow
$name = $provider->getName();

// step 2: redirect to the provider's login screen
if(isset($_GET['login']) && $_GET['login'] === $name){
	header('Location: '.$provider->getAuthorizationURL());
}
// step 3: receive the access token
elseif(isset($_GET['oauth_token'], $_GET['oauth_verifier'])){
	$token = $provider->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

	// save the token in a permanent storage
	// [...]

	// access granted, redirect
	header('Location: ?granted='.$name);
}
// step 4: verify the token and use the API
elseif(isset($_GET['granted']) && $_GET['granted'] === $name){
	// use the file storage from now on
	// [...]

	// dump the AuthenticatedUser instance
	printf('<pre>%s</pre>', print_r($provider->me(), true));

	// convert the token to JSON and display it
	$tokenJSON = $provider->getAccessTokenFromStorage()->toJSON();

	printf('<textarea cols="120" rows="5" onclick="this.select();">%s</textarea>', $tokenJSON);
}
// bonus: handle errors
elseif(isset($_GET['error'])){
	throw new RuntimeException($_GET['error']);
}
// step 1 (optional): display a login link
else{
	echo '<a href="?login='.$name.'">Connect with '.$name.'!</a>';
}
