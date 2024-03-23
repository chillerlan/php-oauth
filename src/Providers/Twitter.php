<?php
/**
 * Class Twitter
 *
 *
 * @created      08.04.2018
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2018 Smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{OAuth1Provider};
use Psr\Http\Message\ResponseInterface;
use function sprintf;

/**
 * Twitter OAuth1
 *
 * @todo: twitter is dead. fuck elon musk.
 *
 * @see https://developer.twitter.com/en/docs/basics/authentication/overview/oauth
 */
class Twitter extends OAuth1Provider{

	// choose your fighter
	/** @see https://developer.twitter.com/en/docs/basics/authentication/api-reference/authorize */
	protected string $authURL              = 'https://api.twitter.com/oauth/authorize';
	/** @see https://developer.twitter.com/en/docs/basics/authentication/api-reference/authenticate */
#	protected string $authURL              = 'https://api.twitter.com/oauth/authenticate';

	protected string      $requestTokenURL = 'https://api.twitter.com/oauth/request_token';
	protected string      $accessTokenURL  = 'https://api.twitter.com/oauth/access_token';
	protected string      $apiURL          = 'https://api.twitter.com';
	protected string|null $userRevokeURL   = 'https://twitter.com/settings/applications';
	protected string|null $apiDocs         = 'https://developer.twitter.com/docs';
	protected string|null $applicationURL  = 'https://developer.twitter.com/apps';

	/**
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/1.1/account/verify_credentials.json');
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->errors, $json->errors[0]->message)){
			throw new ProviderException($json->errors[0]->message);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
