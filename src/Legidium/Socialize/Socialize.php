<?php namespace Legidium\Socialize;

use Exception;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Socialize
 *
 * @package Legidium\Socialize
 */
class Socialize {

	/**
	 * The application instance.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a new socialize instance.
	 *
	 * @param Application $app
	 */
	function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Get the provider instance.
	 *
	 * @param $provider
	 * @return mixed
	 * @throws Exception
	 */
	public function with($provider)
	{
		$providerClass = __NAMESPACE__ . '\Providers\\' . ucfirst($provider) . 'Provider';

		$this->checkIfProviderClassExists($providerClass);

		return new $providerClass($this->app['request'], $this->checkProviderConfig($provider));
	}

	/**
	 * Check if the chosen provider file exists.
	 *
	 * @param $providerClass
	 * @throws NotFoundHttpException
	 */
	protected function checkIfProviderClassExists($providerClass)
	{
		if ( ! class_exists($providerClass)) throw new NotFoundHttpException("Provider does not exist");
	}

	/**
	 * Check if the chosen provider configuration is set and is an array.
	 *
	 * @param $provider
	 * @return mixed
	 * @throws Exception
	 */
	protected function checkProviderConfig($provider)
	{
		$config = $this->app['config']['socialize::providers.services.' . $provider];

		if (is_null($config) || ! is_array($config)) throw new Exception("Provider configuration is not set or is not an array.");

		return $config;
	}

}
