<?php

namespace Yuga\Backpack\Providers;

use Yuga\Route\Route;
use Yuga\Providers\ServiceProvider;
use Yuga\Admin\Console\AdminCommand;
use Yuga\Admin\Controllers\AuthController;
use Yuga\Interfaces\Application\Application;
use Yuga\Providers\Shared\MakesCommandsTrait;

class BackpackServiceProvider extends ServiceProvider
{
    use MakesCommandsTrait;

    protected array $commands = [
        // 'command.admin' => AdminCommand::class,
    ];
    
    public function load(Application $app)
    {
        // $app['yuga-admin-views'] = $this->app->getVendorDir().'/yuga/admin/src/resources/views';
        $app['yuga-admin-views'] = __DIR__ . '/../../resources/views';

        if ($this->app->runningInConsole()) {

            foreach ($this->commands as $key => $command) {
                $app->singleton($key, fn () => new $command);
            }

            $this->commands(array_keys($this->commands));
        };
    }

    public function boot(Route $router)
    {
        $attributes = [
            'prefix' => 'admin', 
            'namespace' => 'Yuga\Admin\Controllers',
            'middleware' => config('admin.route.middleware'),
        ];

        $router->group($attributes, function (Route $router) {
            $router->resource('auth/users', 'UserController')->name('admin.auth.users');
            $router->resource('auth/roles', 'RoleController')->name('admin.auth.roles');
            $router->resource('auth/permissions', 'PermissionController')->name('admin.auth.permissions');
            $router->resource('auth/menu', 'MenuController')->name('admin.auth.menu');
            $router->resource('auth/logs', 'LogController')->name('admin.auth.logs');

            $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
            $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
            $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
            $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
        });

        $router->group(['prefix' => 'admin'], function (Route $router) {
            // authenticate a user

            $authController = config('admin.auth.controller', AuthController::class);
            $router->get('/', $authController.'@getLogin')->name('admin.login');
            $router->get('auth/login', $authController.'@getLogin')->name('admin.login');
            $router->post('auth/login', $authController.'@postLogin');
            $router->get('auth/logout', $authController.'@getLogout')->name('admin.logout');
            $router->get('auth/setting', $authController.'@getSetting')->name('admin.setting');
            $router->put('auth/setting', $authController.'@putSetting');
        });

        
    }
}