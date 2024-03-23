<?php
/**
 * Class MailChimp
 *
 * @created      16.08.2018
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2018 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Providers;

use chillerlan\HTTP\Utils\MessageUtil;
use chillerlan\OAuth\Core\{AccessToken, CSRFToken, OAuth2Provider};
use chillerlan\OAuth\OAuthException;
use Psr\Http\Message\{ResponseInterface, StreamInterface};
use function array_merge, sprintf;

/**
 * MailChimp OAuth2
 *
 * @see https://mailchimp.com/developer/
 * @see https://mailchimp.com/developer/marketing/guides/access-user-data-oauth-2/
 */
class MailChimp extends OAuth2Provider implements CSRFToken{

	protected const API_BASE          = 'https://%s.api.mailchimp.com';
	protected const METADATA_ENDPOINT = 'https://login.mailchimp.com/oauth2/metadata';

	protected string      $authURL        = 'https://login.mailchimp.com/oauth2/authorize';
	protected string      $accessTokenURL = 'https://login.mailchimp.com/oauth2/token';
	protected string|null $apiDocs        = 'https://mailchimp.com/developer/';
	protected string|null $applicationURL = 'https://admin.mailchimp.com/account/oauth2/';
	// set to empty so that we don't run into "uninitialized" errors in mock tests, as the datacenter is in the token
	protected string      $apiURL         = '';

	/**
	 * @throws \chillerlan\OAuth\OAuthException
	 */
	public function getTokenMetadata(AccessToken|null $token = null):AccessToken{

		$token ??= $this->storage->getAccessToken($this->serviceName);

		if(!$token instanceof AccessToken){
			throw new OAuthException('invalid token'); // @codeCoverageIgnore
		}

		$request = $this->requestFactory
			->createRequest('GET', $this::METADATA_ENDPOINT)
			->withHeader('Authorization', 'OAuth '.$token->accessToken)
		;

		$response = $this->http->sendRequest($request);

		if($response->getStatusCode() !== 200){
			throw new OAuthException('metadata response error'); // @codeCoverageIgnore
		}

		$token->extraParams = array_merge($token->extraParams, MessageUtil::decodeJSON($response, true));

		$this->storage->storeAccessToken($token, $this->serviceName);

		return $token;
	}

	/**
	 * prepare the API URL from the token metadata
	 *
	 * @inheritdoc
	 */
	public function request(
		string                            $path,
		array|null                        $params = null,
		string|null                       $method = null,
		StreamInterface|array|string|null $body = null,
		array|null                        $headers = null,
		string|null                       $protocolVersion = null,
	):ResponseInterface{
		$token = $this->storage->getAccessToken($this->serviceName);

		$this->apiURL = sprintf($this::API_BASE, $token->extraParams['dc']);

		return parent::request($path, $params, $method, $body, $headers, $protocolVersion);
	}

	/**
	 * @see https://mailchimp.com/developer/marketing/api/root/list-api-root-resources/
	 * @inheritDoc
	 */
	public function me():ResponseInterface{
		$response = $this->request('/3.0/'); // trailing slash!
		$status   = $response->getStatusCode();

		if($status === 200){
			return $response;
		}

		$json = MessageUtil::decodeJSON($response);

		if(isset($json->detail)){
			throw new ProviderException($json->detail);
		}

		throw new ProviderException(sprintf('user info error error HTTP/%s', $status));
	}

}
