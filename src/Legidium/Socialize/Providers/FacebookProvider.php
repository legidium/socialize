<?php namespace Legidium\Socialize\Providers;

use Exception;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Legidium\Socialize\User;

/**
 * Class FacebookProvider
 *
 * @package Legidium\Socialize\Providers
 */
class FacebookProvider extends AbstractProvider {

	/**
	 * Facebook Api version
	 *
	 * @var
	 */
	protected $apiVersion;

	/**
	 * Create a new provider instance.
	 * Set Api version.
	 *
	 * @param Request $request
	 * @param array   $options
	 * @throws Exception
	 */
	function __construct(Request $request, array $options)
	{
		parent::__construct($request, $options);

		$this->apiVersion = $this->checkIfApiVersionIsSet($options);
	}

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param $state
	 * @return string
	 */
	protected function getAuthUrl($state)
	{
		return $this->buildAuthUrlFromBase('https://www.facebook.com/dialog/oauth', $state);
	}

	/**
	 * Get the token URL for the provider.
	 *
	 * @return string
	 */
	protected function getTokenUrl()
	{
		return 'https://graph.facebook.com/oauth/access_token';
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
		parse_str($accessInfo);

		return $access_token;
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

		$response = $this->getHttpClient()->get('https://graph.facebook.com/v' . $this->apiVersion . '/me?access_token=' . $userToken);

		return $response->getBody();
	}

	/**
	 * Get the User instance for the authenticated user.
	 *
	 * @return \Shcherbin\Socialize\User
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
				'provider' => 'facebook',
				'id' => $userData['id'],
				'name' => $userData['first_name'],
				'surname' => $userData['last_name'],
				'sex' => $userData['gender'],
				'photo' => 'https://graph.facebook.com/v' . $this->apiVersion . '/' . $userData['id'] . '/picture?type=normal',
				'email' => $userData['email'],
				'profileUrl' => 'https://facebook.com/' . $userData['id'],
				'rawAttributes' => $userData
			]);
		}
	}

	/**
	 * Check if the provider api version is set.
	 *
	 * @param $options
	 * @return mixed
	 * @throws Exception
	 */
	protected function checkIfApiVersionIsSet($options)
	{
		if ( ! isset($options['version']) || empty($options['version'])) throw new Exception('Provider API version is not specified');

		return $options['version'];
	}
}
