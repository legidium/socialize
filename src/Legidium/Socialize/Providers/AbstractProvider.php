<?php namespace Legidium\Socialize\Providers;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AbstractProvider
 *
 * @package Legidium\Socialize\Providers
 */
abstract class AbstractProvider {

	/**
	 * The client Id.
	 *
	 * @var string
	 */
	protected $clientId;

	/**
	 * The client secret key.
	 *
	 * @var string
	 */
	protected $clientSecret;

	/**
	 * The scopes being requested.
	 *
	 * @var array
	 */
	protected $scopes = [];

	/**
	 * The scope delimiter.
	 *
	 * @var string
	 */
	protected $scopeDelimiter = ',';

	/**
	 * The HTTP request instance.
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * The redirect url.
	 *
	 * @var string
	 */
	protected $redirectUrl;

	/**
	 * Additional query params.
	 *
	 * @var array
	 */
	protected $additionalParams = [];

	/**
	 * Create a new provider instance.
	 *
	 * @param Request $request
	 * @param array   $options
	 * @throws Exception
	 */
	public function __construct(Request $request, array $options)
	{
		$this->checkIfProviderIsEnabled($options);
		$this->checkIfProviderClientIdIsSet($options);
		$this->checkIfProviderSecretKeyIsEnabled($options);
		$this->request = $request;
		$this->clientId = $options['id'];
		$this->clientSecret = $options['secret'];
		$this->redirectUrl = $this->formatRedirectUrl($request->getPathInfo());
		if (isset($options['scopes']))
		{
			$this->scopes = $options['scopes'];
		}
		if (isset($options['additional']))
		{
			$this->additionalParams = $options['additional'];
		}
	}

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param $state
	 * @return string
	 */
	abstract protected function getAuthUrl($state);

	/**
	 * Get the token URL for the provider.
	 *
	 * @return string
	 */
	abstract protected function getTokenUrl();

	/**
	 * Get access info with the received code.
	 *
	 * @param $code
	 * @return mixed
	 */
	abstract protected function getAccessInfo($code);

	/**
	 * Get the User instance for the authenticated user.
	 *
	 * @return \Legidium\Socialize\User
	 */
	abstract protected function user();

	/**
	 * Get the authentication URL for the provider.
	 *
	 * @param $url
	 * @param $state
	 * @return string
	 */
	protected function buildAuthUrlFromBase($url, $state)
	{
		$params = [
			'client_id' => $this->clientId,
			'redirect_uri' => $this->redirectUrl,
			'scope' => $this->formatScopes($this->scopes),
			'state' => $state,
			'response_type' => 'code'
		];

		// Check for additional params in the query.
		if ( ! empty($this->additionalParams))
		{
			foreach ($this->additionalParams as $param => $value)
			{
				$params[$param] = $value;
			}
		}

		return $url . '?' . http_build_query($params);
	}

	/**
	 * Get the fields for the token request.
	 *
	 * @param string $code
	 * @return array
	 */
	protected function getTokenFields($code)
	{
		return [
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'code' => $code,
			'redirect_uri' => $this->redirectUrl
		];
	}

	/**
	 * Get the redirect url.
	 *
	 * @return string
	 */
	protected function formatRedirectUrl()
	{
		return url($this->request->getPathInfo());
	}

	/**
	 * Redirect the user of the application to the provider's authentication screen.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function redirect()
	{
		// Set unique state for user in session.
		$this->request->getSession()->set(
			'state', $state = sha1(time() . $this->request->getSession()->get('_token'))
		);

		return new RedirectResponse($this->getAuthUrl($state));
	}

	/**
	 * Determine if the current request and session has a mismatching "state".
	 *
	 * @return bool
	 */
	protected function hasInvalidState()
	{
		$session = $this->request->getSession();

		return ! ($this->request->input('state') === $session->get('state'));
	}

	/**
	 * Format the scopes using delimiter.
	 *
	 * @param array $scopes
	 * @return string
	 */
	protected function formatScopes(array $scopes)
	{
		return implode($this->scopeDelimiter, $scopes);
	}

	/**
	 * Get the code from the request.
	 *
	 * @return string
	 */
	protected function getCode()
	{
		return $this->request->input('code');
	}

	/**
	 * Get a fresh instance of the Guzzle HTTP client.
	 *
	 * @return Client
	 */
	protected function getHttpClient()
	{
		return new Client;
	}

	/**
	 * Check if the provider is enabled.
	 *
	 * @param array $options
	 * @throws NotFoundHttpException
	 */
	private function checkIfProviderIsEnabled(array $options)
	{
		if ( ! $options['enabled']) throw new NotFoundHttpException('Provider is disabled');
	}

	/**
	 * Check if the provider id is set.
	 *
	 * @param array $options
	 * @throws Exception
	 */
	private function checkIfProviderClientIdIsSet(array $options)
	{
		if (empty($options['id'])) throw new Exception('Provider client Id is empty');
	}

	/**
	 * Check if the provider secret key is set.
	 *
	 * @param array $options
	 * @throws Exception
	 */
	private function checkIfProviderSecretKeyIsEnabled(array $options)
	{
		if (empty($options['secret'])) throw new Exception('Provider secret key is empty');
	}

}
