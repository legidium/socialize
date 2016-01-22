<?php namespace Legidium\Socialize;

class User {

	/**
	 * The user provider.
	 *
	 * @var
	 */
	public $provider;

	/**
	 * The user id.
	 *
	 * @var
	 */
	public $id;

	/**
	 * The user name.
	 *
	 * @var
	 */
	public $name;

	/**
	 * The user surname.
	 *
	 * @var
	 */
	public $surname;

	/**
	 * The user sex.
	 *
	 * @var
	 */
	public $sex;

	/**
	 * The user photo image path.
	 *
	 * @var
	 */
	public $photo;

	/**
	 * The user email.
	 *
	 * @var
	 */
	public $email;

	/**
	 * The user profile url.
	 *
	 * @var
	 */
	public $profileUrl;

	/**
	 * All user attributes.
	 *
	 * @var
	 */
	public $rawAttributes;

	/**
	 * Get user instance with mapped properties.
	 *
	 * @param array $attributes
	 * @return $this
	 */
	public function getUser(array $attributes)
	{
		$this->mapAttributes($attributes);

		return $this;
	}

	/**
	 * Get raw user w/o mapped properties.
	 *
	 * @param $attributes
	 * @return $this
	 */
	public function getRawUser($attributes)
	{
		$this->rawAttributes = $attributes;

		return $this;
	}

	/**
	 * Map the user properties.
	 *
	 * @param $attributes
	 */
	protected function mapAttributes($attributes)
	{
		foreach ($attributes as $attribute => $value)
		{
			$this->{$attribute} = $value;
		}
	}

}