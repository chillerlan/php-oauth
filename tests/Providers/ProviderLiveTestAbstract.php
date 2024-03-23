<?php
/**
 * Class ProviderLiveTestAbstract
 *
 * @created      17.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuthTest\Providers;

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use InvalidArgumentException;
use function constant;
use function defined;
use function ltrim;
use function realpath;
use function sprintf;

/**
 *
 */
abstract class ProviderLiveTestAbstract extends ProviderUnitTestAbstract{

	protected string $HTTP_CLIENT_FACTORY = ProviderLiveTestHttpClientFactory::class;

	protected DotEnv $dotEnv;
	protected string $ENV_PREFIX;
	protected string $CFG_DIR;

	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $TEST_USER = '';

	/**
	 * @throws \InvalidArgumentException
	 */
	protected function setUp():void{

		// are we running on CI? (initializing this here too, so we can back out before initializing the provider)
		$this->ENV_IS_CI = defined('TEST_IS_CI') && constant('TEST_IS_CI') === true;

		if($this->ENV_IS_CI){
			$this->markTestSkipped('not on CI (set TEST_IS_CI in phpunit.xml to "false" if you want to run live API tests)');
		}

		// set the config dir and .env config before initializing the provider
		$this->initEnvConfig();

		// init provider etc.
		parent::setUp();
	}

	/**
	 * returns the prefix in the .env file for the current provider
	 */
	abstract protected function getEnvPrefix():string|null;

	protected function initEnvConfig():void{

		foreach(['TEST_CFGDIR', 'TEST_ENVFILE'] as $constant){
			if(!defined($constant)){
				throw new InvalidArgumentException(sprintf('constant "%s" not set -> see phpunit.xml', $constant));
			}
		}

		$cfgdir        = constant('TEST_CFGDIR');
		$this->CFG_DIR = realpath($this::PROJECT_ROOT.ltrim($cfgdir, '/\\'));

		if($this->CFG_DIR === false){
			throw new InvalidArgumentException(sprintf('invalid config dir "%s" (relative from project root)', $cfgdir));
		}

		$this->dotEnv     = (new DotEnv($this->CFG_DIR, constant('TEST_ENVFILE'), false))->load();
		$this->ENV_PREFIX = $this->getEnvPrefix();
		$this->TEST_USER   = (string)$this->dotEnv->get($this->ENV_PREFIX.'_TESTUSER');

	}

	protected function initOptions():OAuthOptions{
		$options                   = new OAuthOptions;
		$options->tokenAutoRefresh = true;

		if(!empty($this->ENV_PREFIX)){
			$options->key    = ($this->dotEnv->get($this->ENV_PREFIX.'_KEY') ?? '');
			$options->secret = ($this->dotEnv->get($this->ENV_PREFIX.'_SECRET') ?? '');
		}

		return $options;
	}

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new ProviderLiveTestMemoryStorage($options, $this->CFG_DIR);
	}

}
