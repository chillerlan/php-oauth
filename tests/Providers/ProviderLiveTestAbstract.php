<?php
/**
 * Class ProviderLiveTestAbstract
 *
 * @created      17.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */
declare(strict_types=1);

namespace chillerlan\OAuthTest\Providers;

use chillerlan\DotEnv\DotEnv;
use chillerlan\OAuth\OAuthOptions;
use chillerlan\OAuth\Storage\FileStorage;
use chillerlan\OAuth\Storage\OAuthStorageInterface;
use function constant;
use function defined;

/**
 *
 */
abstract class ProviderLiveTestAbstract extends ProviderUnitTestAbstract{

	protected string $HTTP_CLIENT_FACTORY = ProviderLiveTestHttpClientFactory::class;

	protected DotEnv $dotEnv;
	protected string $ENV_PREFIX;

	/** a test username for live API tests, defined in .env as {ENV-PREFIX}_TESTUSER*/
	protected string $TEST_USER = '';

	/**
	 * @throws \InvalidArgumentException
	 */
	protected function setUp():void{

		// are we running on CI? (checking this here, so we can back out before initializing the provider)
		if(defined('TEST_IS_CI') && constant('TEST_IS_CI') === true){
			$this->markTestSkipped('not on CI (set TEST_IS_CI in phpunit.xml to "false" if you want to run live API tests)');
		}

		// init provider etc.
		parent::setUp();
	}

	protected function initConfig():void{
		parent::initConfig();

		/** @var \chillerlan\OAuth\Core\OAuthInterface $providerFQCN */
		$providerFQCN = $this->getProviderFQCN();

		$this->dotEnv     = (new DotEnv($this->CFG_DIR, constant('TEST_ENVFILE'), false))->load();
		$this->ENV_PREFIX = $providerFQCN::IDENTIFIER;
		$this->TEST_USER  = (string)$this->dotEnv->get($this->ENV_PREFIX.'_TESTUSER');
	}

	protected function initOptions():OAuthOptions{
		$options                   = new OAuthOptions;
		$options->tokenAutoRefresh = true;
		$options->fileStoragePath  = $this->CFG_DIR.'/.filestorage';

		if(!empty($this->ENV_PREFIX)){
			$options->key    = ($this->dotEnv->get($this->ENV_PREFIX.'_KEY') ?? '');
			$options->secret = ($this->dotEnv->get($this->ENV_PREFIX.'_SECRET') ?? '');
		}

		return $options;
	}

	protected function initStorage(OAuthOptions $options):OAuthStorageInterface{
		return new FileStorage('oauth-example', $options);
	}

}
