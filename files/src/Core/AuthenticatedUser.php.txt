<?php
/**
 * Class AuthenticatedUser
 *
 * @created      23.03.2024
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2024 smiley
 * @license      MIT
 *
 * @filesource
 */
declare(strict_types=1);

namespace chillerlan\OAuth\Core;

use chillerlan\Settings\SettingsContainerAbstract;
use function intval, is_int, is_numeric, trim;

/**
 * A simple read-only container for user data responses
 *
 * @see \chillerlan\OAuth\Core\UserInfo::me()
 *
 * @property string|null          $handle
 * @property string|null          $displayName
 * @property string|null          $email
 * @property string|int|null      $id
 * @property string|null          $avatar
 * @property string|null          $url
 * @property array<string, mixed> $data
 */
final class AuthenticatedUser extends SettingsContainerAbstract{

	/**
	 * (magic) The user handle, account or tag name
	 */
	protected string|null $handle = null;

	/**
	 * (magic) The user's display name
	 */
	protected string|null $displayName = null;

	/**
	 * (magic) The (main) email address
	 */
	protected string|null $email = null;

	/**
	 * (magic) A user ID, may be string or integer
	 */
	protected string|int|null $id = null;

	/**
	 * (magic) An avatar URL
	 */
	protected string|null $avatar = null;

	/**
	 * (magic) URL to the user profile
	 */
	protected string|null $url = null;

	/**
	 * (magic) The full user endpoint response
	 *
	 * @var array<string, mixed>
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
	public function __set(string $property, mixed $value):void{
		// noop
	}

	/** @codeCoverageIgnore */
	public function fromIterable(iterable $properties):static{ // phpcs:ignore
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

		if($displayName !== ''){
			$this->displayName = $displayName;
		}

	}

}
