<?php
/**
 * @created      27.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */
declare(strict_types=1);

use chillerlan\OAuth\Core\{
	ClientCredentials, CSRFToken, OAuth1Interface, OAuth2Interface,
	PKCE, TokenInvalidate, TokenRefresh, UserInfo, Utilities
};

/**
 * @var \Psr\Http\Client\ClientInterface                $http
 * @var \chillerlan\Settings\SettingsContainerInterface $options
 * @var \Psr\Log\LoggerInterface                        $logger
 */

require_once __DIR__.'/provider-example-common.php';

const FILES         = [__DIR__.'/../README.md', __DIR__.'/../docs/Basics/Overview.md'];
const REPLACE_START = '<!-- TABLE-START -->';
const REPLACE_END   = '<!-- TABLE-END -->';

$table = [
	'<!-- this table is auto-created via /examples/create-description.php -->',
	'',
	'| Provider | keys | revoke | ver | User | CSRF | PKCE | CC | TR | TI |',
	'|----------|------|--------|-----|------|------|------|----|----|----|',
];

foreach(Utilities::getProviders() as $p){
	/** @var \OAuthExampleProviderFactory $factory */
	$provider = $factory->getProvider($p['fqcn'], OAuthExampleProviderFactory::STORAGE_MEMORY);

	$oauth = match(true){
		$provider instanceof OAuth2Interface => '2',
		$provider instanceof OAuth1Interface => '1',
		default                              => '-',
	};

	$table[] = '| ['.$p['name'].']('.$provider->apiDocs.')'.
		' | [link]('.$provider->applicationURL.')'.
		' | '.($provider->userRevokeURL !== null ? '[link]('.$provider->userRevokeURL.')' : '').
		' | '.$oauth.
		' | '.(($provider instanceof UserInfo) ? '✓' : '').
		' | '.(($provider instanceof CSRFToken) ? '✓' : '').
		' | '.(($provider instanceof PKCE) ? '✓' : '').
		' | '.(($provider instanceof ClientCredentials) ? '✓' : '').
		' | '.(($provider instanceof TokenRefresh) ? '✓' : '').
		' | '.(($provider instanceof TokenInvalidate) ? '✓' : '').
	    ' |' ;

	printf("%s\n", $p['fqcn']);
}

$table[] = '';
$table[] = '**Legend:**';
$table[] = '- **Provider**: the name of the provider class and link to their API documentation';
$table[] = '- **keys**: links to the provider\'s OAuth application creation page';
$table[] = '- **revoke**: links to the OAuth application access revocation page in the provider\'s user profile';
$table[] = '- **ver**: the OAuth version(s) supported by the provider';
$table[] = '- **User**: indicates that the provider offers information about the currently authenticated user via the `me()` method (implements the `UserInfo` interface)';
$table[] = '- **CSRF**: indicates that the provider uses [CSRF protection via the `state` parameter](https://datatracker.ietf.org/doc/html/rfc6749#section-10.12) (implements the `CSRFToken` interface)';
$table[] = '- **PKCE**: indicates that the provider supports [Proof Key for Code Exchange](https://datatracker.ietf.org/doc/html/rfc7636) (implements the `PKCE` interface)';
$table[] = '- **CC**: indicates that the provider supports the [Client Credentials Grant](https://datatracker.ietf.org/doc/html/rfc6749#section-4.4) (implements the `ClientCredentials` interface)';
$table[] = '- **TR**: indicates that the provider is capable of [refreshing an access token](https://datatracker.ietf.org/doc/html/rfc6749#section-10.4) (implements the `TokenRefresh` interface)';
$table[] = '- **TI**: indicates that the provider is capable of revoking/invalidating an access token (implements the `TokenInvalidate` interface)';


foreach(FILES as $file){
	$content = file_get_contents($file);
	$start   = strpos($content, REPLACE_START);
	$end     = (strpos($content, REPLACE_END) + strlen(REPLACE_END));

	$content = str_replace(
		substr($content, $start, ($end - $start)),
		REPLACE_START."\n".implode("\n", $table)."\n".REPLACE_END,
		$content,
	);

	file_put_contents($file, $content);
}

exit;
