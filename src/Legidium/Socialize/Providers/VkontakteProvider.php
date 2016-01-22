<?php namespace Legidium\Socialize\Providers;

use InvalidArgumentException;
use Legidium\Socialize\User;

/**
 * Class VkontakteProvider
 *
 * @package Legidium\Socialize\Providers
 */
class VkontakteProvider extends AbstractProvider {

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param $state
	 * @return string
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://oauth.vk.com/authorize', $state);
	}

	/**
	 * Get the token URL for the provider.
	 *
	 * @return string
	 */
	protected function getTokenUrl()
	{
		return 'https://oauth.vk.com/access_token';
	}


	/**
	 * Get access info with the received code.
	 *
	 * @param $code
	 * @return \GuzzleHttp\Stream\StreamInterface|null
	 */
	protected function getAccessInfo($code)
	{
		$response = $this->getHttpClient()->get($this->getTokenUrl(), ['query' => $this->getTokenFields($code)]);

		return $response->getBody();
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
	 * Get the user id from the given access information.
	 *
	 * @param $accessInfo
	 * @return string
	 */
	protected function getUserId($accessInfo)
	{
		return json_decode($accessInfo, true)['user_id'];
	}

	/**
	 * Get the user email from the given access information.
	 * Email should be included in the scopes.
	 *
	 * @param $accessInfo
	 * @return string|null
	 */
	protected function getUserEmail($accessInfo)
	{
		$accessInfo = json_decode($accessInfo, true);

		return isset($accessInfo['email']) ? $accessInfo['email'] : null;
	}

	/**
	 * Get the user data with user id.
	 *
	 * @param $accessInfo
	 * @return \GuzzleHttp\Stream\StreamInterface|null
	 */
	protected function getUserById($accessInfo)
	{
		$userId = $this->getUserId($accessInfo);

		$response = $this->getHttpClient()->get('https://api.vk.com/method/users.get', [
			'query' => [
				'user_ids' => $userId,
				'fields' => 'sex, bdate, city, country, photo_50, photo_100, photo_200_orig, photo_200, photo_400_orig, photo_max, photo_max_orig, photo_id, online, online_mobile, domain, has_mobile, contacts, connections, site, education, universities, schools, can_post, can_see_all_posts, can_see_audio, can_write_private_message, status, last_seen, relation, relatives, counters, screen_name, maiden_name, timezone, occupation,activities, interests, music, movies, tv, books, games, about, quotes'
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

			$userData = json_decode($this->getUserById($accessInfo), true)['response'][0];

			return (new User)->getUser([
				'provider' => 'vkontakte',
				'id' => $userData['uid'],
				'name' => $userData['first_name'],
				'surname' => $userData['last_name'],
				'sex' => $this->getSex($userData['sex']),
				'photo' => $userData['photo_max_orig'],
				'email' => $this->getUserEmail($accessInfo),
				'profileUrl' => 'https://vk.com/' . $userData['screen_name'],
				'rawAttributes' => $userData
			]);
		}
	}

	/**
	 * Get the user sex.
	 *
	 * @param string $sex
	 * @return string
	 */
	protected function getSex($sex)
	{
		switch ($sex)
		{
			case(1):
				$sex = 'female';
				break;
			case(2):
				$sex = 'male';
				break;
			default:
				$sex = 'unknown';
		}

		return $sex;
	}

}
