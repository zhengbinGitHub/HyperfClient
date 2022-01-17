<?php

namespace RpcService;

use Illuminate\Support\ServiceProvider;

class RpcServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
		$this->app->bind('rpcservice', function($app){
			return new RpcServiceManage($app['config']['rpcservice']);
		});
    }

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/../config/rpcservice.php' => config_path('rpcservice.php'),
		], 'config');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['rpcservice'];
	}
}
