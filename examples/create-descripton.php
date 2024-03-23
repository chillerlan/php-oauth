<?php
/**
 *
 * @created      27.07.2019
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2019 smiley
 * @license      MIT
 */

use chillerlan\OAuth\Core\{ClientCredentials, OAuth1Interface, OAuth2Interface, OAuthInterface};

/**
 * @var \Psr\Http\Client\ClientInterface                $http
 * @var \chillerlan\Settings\SettingsContainerInterface $options
 * @var \Psr\Log\LoggerInterface                        $logger
 */

require_once __DIR__.'/provider-example-common.php';

const REPLACE_START = '<!-- TABLE-START -->';
const REPLACE_END   = '<!-- TABLE_END -->';

$table = [
	'| Provider | API keys | revoke access | OAuth | `ClientCredentials` |',
	'|----------|----------|---------------|-------|---------------------|',
];

foreach(getProviders(__DIR__.'/../src/Providers') as $p){
	/** @var \OAuthProviderFactory $factory */
	$provider = $factory->getProvider($p['fqcn'], '', false);

	$oauth = match(true){
		$provider instanceof OAuth2Interface => '2',
		$provider instanceof OAuth1Interface => '1',
		default                              => '-',
	};

	$table[] = '| ['.$p['name'].']('.$provider->apiDocs.')'.
		' | [link]('.$provider->applicationURL.')'.
		' | '.((!$provider->userRevokeURL) ? '' : '[link]('.$provider->userRevokeURL.')').
		' | '.$oauth.
		' | '.(($provider instanceof ClientCredentials) ? '✓' : '').
	    ' |' ;

	printf("%s\n", $p['fqcn']);
}

$file   = __DIR__.'/../README.md';
$readme = file_get_contents($file);
$start  = strpos($readme, REPLACE_START);
$end    = (strpos($readme, REPLACE_END) + strlen(REPLACE_END));

$readme = str_replace(
	substr($readme, $start, ($end - $start)),
	REPLACE_START."\n".implode("\n", $table)."\n".REPLACE_END,
	$readme,
);

file_put_contents($file, $readme);

exit;

// @todo
function getProviders(string $providerDir):array{
	$providerDir = realpath($providerDir);
	$providers = [];

	/** @var \SplFileInfo $e */
	foreach(new IteratorIterator(new DirectoryIterator($providerDir)) as $e){

		if($e->getExtension() !== 'php'){
			continue;
		}

		$class = 'chillerlan\\OAuth\\Providers\\'.substr($e->getFilename(), 0, -4);

		try{
			$r = new ReflectionClass($class);

			if(!$r->implementsInterface(OAuthInterface::class) || $r->isAbstract()){
				continue;
			}

			$providers[hash('crc32b', $r->getShortName())] = ['name' => $r->getShortName(), 'fqcn' => $class];
		}
		catch(Throwable){
			continue;
		}

	}

	return $providers;
}
