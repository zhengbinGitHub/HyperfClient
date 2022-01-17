<?php

namespace LaravelHyperfClientRpcService;

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
		$this->app->bind('hyperf-rpc-client', function($app){
			return new RpcServiceManage($app['config']['hyperf-rpc-client']);
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
			__DIR__.'/../config/hyperf-rpc-client.php' => config_path('hyperf-rpc-client.php'),
		], 'config');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['hyperf-rpc-client'];
	}
}
