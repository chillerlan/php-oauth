<?php
/**
 * mixesdb-scrape.php
 *
 * Not an actual OAuth API example, just a helper script
 *
 * Unfortunately, mixesdb doesn't have the mediawiki API activated, so we gotta scrape the data manually.
 *
 * @see https://www.mixesdb.com/w/Category:Clubnight
 *
 * @created      28.03.2023
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2023 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

use chillerlan\HTTP\Utils\MessageUtil;

/**
 * @var \OAuthProviderFactory $factory
 * @var \chillerlan\OAuth\Providers\Spotify $spotify
 * @var \Psr\Log\LoggerInterface $logger
 * @var string $file
 */
require_once __DIR__.'/spotify-common.php';

$logger         = $factory->getLogger();
$requestFactory = $factory->getRequestFactory();

$file    ??= __DIR__.'/mixesdb-data.json';
$baseURL   = 'https://www.mixesdb.com';
$catPath   = '/db/index.php?title=Category:Clubnight';
$tracklist = [];

// suppress html parse errors
libxml_use_internal_errors(true);

do{
	$logger->info($catPath);

	// fetch the category page
	$catRequest  = $requestFactory->createRequest('GET', $baseURL.$catPath);
	$catResponse = $spotify->sendRequest($catRequest);

	if($catResponse->getStatusCode() !== 200){
		break;
	}

	$catDOM = new DOMDocument('1.0', 'UTF-8');
	$catDOM->loadHTML(MessageUtil::getContents($catResponse));

	// get the pages from the category list
	foreach($catDOM->getElementById('catMixesList')->childNodes as $node){

		if($node->nodeType !== XML_ELEMENT_NODE){
			continue;
		}

		$page = $node->childNodes[0]->attributes->getNamedItem('href')->nodeValue;

		// get the date string
		preg_match('#\d{4}-\d{2}-\d{2}#', $page, $match);

		if(!isset($match[0])){
			continue;
		}

		// fetch the page
		$pageRequest  = $requestFactory->createRequest('GET', $baseURL.$page);
		$pageResponse = $spotify->sendRequest($pageRequest);

		if($pageResponse->getStatusCode() !== 200){
			continue;
		}

		$pageDOM = new DOMDocument('1.0', 'UTF-8');
		$pageDOM->loadHTML(MessageUtil::getContents($pageResponse));

		$name = $pageDOM->getElementById('firstHeading')->nodeValue;

		if(!empty($name)){
			$logger->info($name);

			// get the tracklist
			foreach($pageDOM->getElementsByTagName('ol') as $li){
				foreach($li->childNodes as $e){
					$tracklist[$match[0]][$name][] = trim($e->nodeValue);
				}
			}

		}
		else{
			$logger->warning(sprintf('name empty for page: "%s"', $page));
		}

		// try not to hammer
		usleep(500000);
	}

	// get the next page from the category navigation
	$catPath = null;

	foreach($catDOM->getElementById('catcount')->getElementsByTagName('a') as $node){
		if($node->textContent === 'next 200'){
			$catPath = $node->attributes->getNamedItem('href')->nodeValue;

			break;
		}
	}

}
while(!empty($catPath));

file_put_contents($file, json_encode($tracklist, (JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)));
