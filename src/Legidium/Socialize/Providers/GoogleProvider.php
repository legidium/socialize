<?php namespace Legidium\Socialize\Providers;

use InvalidArgumentException;
use Legidium\Socialize\User;

/**
 * Class GoogleProvider
 *
 * @package Legidium\Socialize\Providers
 */
class GoogleProvider extends AbstractProvider {

	/**
	 * The scope delimiter.
	 *
	 * @var string
	 */
	protected $scopeDelimiter = ' ';

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param $state
	 * @return string
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
	}

	/**
	 * Get the token URL for the provider.
	 *
	 * @return string
	 */
	protected function getTokenUrl()
	{
		return 'https://accounts.google.com/o/oauth2/token';
	}

	/**
	 * Get access info with the received code.
	 *
	 * @param $code
	 * @return mixed
	 */
	protected function getAccessInfo($code)
	{
		$response = $this->getHttpClient()->post($this->getTokenUrl(), [
			'body' => $this->getTokenFields($code)
		]);

		return $response->getBody();
	}

	/**
	 * Get the POST fields for the token request.
	 *
	 * @param  string $code
	 * @return array
	 */
	protected function getTokenFields($code)
	{
		return array_add(
			parent::getTokenFields($code), 'grant_type', 'authorization_code'
		);
	}

	/**
	 * Get the access token from the given access information.
	 *
	 * @param $accessInfo
	 * @return string
	 */
	protected function getAccessToken($accessInfo)
	{
		return json_decode($accessInfo, true)['access_token'];
	}

	/**
	 * Get the user data with user token.
	 *
	 * @param $accessInfo
	 * @return \GuzzleHttp\Stream\StreamInterface|null
	 */
	protected function getUserByToken($accessInfo)
	{
		$userToken = $this->getAccessToken($accessInfo);

		$response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $userToken, [
			'headers' => [
				'Accept' => 'application/json',
			]
		]);

		return $response->getBody();
	}

	/**
	 * Get the User instance for the authenticated user.
	 *
	 * @return \Legidium\Socialize\User
	 */
	public function user()
	{
		// Check for the state code.
		if ($this->hasInvalidState())
		{
			throw new InvalidArgumentException;
		}
		else
		{
			$accessInfo = $this->getAccessInfo($this->getCode());

			$userData = json_decode($this->getUserByToken($accessInfo), true);

			return (new User)->getUser([
				'provider' => 'google',
				'id' => $userData['id'],
				'name' => $userData['given_name'],
				'surname' => $userData['family_name'],
				'sex' => $userData['gender'],
				'photo' => $userData['picture'],
				'email' => $userData['email'],
				'profileUrl' => $userData['link'],
				'rawAttributes' => $userData
			]);
		}
	}

}
