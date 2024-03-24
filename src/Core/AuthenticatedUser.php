<?php
/**
 * Class AuthenticatedUser
 *
 * @created      23.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 */

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;
use function intval, is_int, is_numeric, trim;

/**
 * A simple read-only container for user data responses from `OAuthInterface::me()`
 *
 * @property string|null     $handle
 * @property string|null     $displayName
 * @property string|null     $email
 * @property string|int|null $id
 * @property string|null     $avatar
 * @property string|null     $url
 * @property array           $data
 */
final class AuthenticatedUser extends SettingsContainerAbstract{

	/**
	 * The user handle, account or tag name
	 */
	protected string|null $handle = null;

	/**
	 * The user's display name
	 */
	protected string|null $displayName = null;

	/**
	 * The (main) email address
	 */
	protected string|null $email = null;

	/**
	 * A user ID, may be string or integer
	 */
	protected string|int|null $id = null;

	/**
	 * An avatar URL
	 */
	protected string|null $avatar = null;

	/**
	 * URL to the user profile
	 */
	protected string|null $url = null;

	/**
	 * The full user endpoint response
	 */
	protected array $data = [];

	/**
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(iterable|null $properties = null){

		if(!empty($properties)){
			// call the parent's setter here
			foreach($properties as $property => $value){
				parent::__set($property, $value);
			}

		}

	}

	/*
	 * make this class readonly
	 */

	/** @codeCoverageIgnore */
	public function __set(string $property, $value):void{
		// noop
	}

	/** @codeCoverageIgnore */
	public function fromIterable(iterable $properties):static{
		// noop
		return $this;
	}

	/** @codeCoverageIgnore */
	public function fromJSON(string $json):static{
		// noop
		return $this;
	}

	/*
	 * setters
	 */

	/**
	 * set the user id, convert to int if possible
	 */
	protected function set_id(string|int|null $id):void{

		if($id === null){
			return;
		}

		$this->id = $id;

		if(!is_int($id) && is_numeric($id)){
			$intID = intval($id);

			if((string)$intID === $id){
				$this->id = $intID;
			}
		}

	}

	/**
	 * trim and set the display name
	 */
	protected function set_displayName(string|null $displayName):void{

		if($displayName === null){
			return;
		}

		$displayName = trim($displayName);

		if(!empty($displayName)){
			$this->displayName = $displayName;
		}

	}

}
