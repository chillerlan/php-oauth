<?php
/**
 * Class ProviderTestLoggerFactory
 *
 * @created      17.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use const JSON_UNESCAPED_SLASHES;

/**
 *
 */
class ProviderTestLoggerFactory{

	public function getLogger(bool $env_is_ci):LoggerInterface{
		$logger = new Logger('oauthProviderTest', [new NullHandler]);

		// logger output only when not on CI
		if(!$env_is_ci){
			$formatter = new LineFormatter(null, 'Y-m-d H:i:s', true, true);
			$formatter->setJsonPrettyPrint(true);
			$formatter->addJsonEncodeOption(JSON_UNESCAPED_SLASHES);

			$logger->pushHandler((new StreamHandler('php://stdout'))->setFormatter($formatter));
		}

		return $logger;
	}

}
