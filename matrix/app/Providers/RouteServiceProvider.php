<?php

namespace Matrix\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Matrix\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapClientRoutes();

        $this->mapInteractionRoutes();
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "client" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapClientRoutes()
    {
        Route::prefix('api/v2/client')
             ->middleware('client')
             ->namespace($this->namespace)
             ->group(base_path('routes/client.php'));
    }

    /**
     * Define the "interaction" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapInteractionRoutes()
    {
        Route::prefix('api/v2/interaction')
             ->middleware('interaction')
             ->namespace($this->namespace)
             ->group(base_path('routes/interaction.php'));
    }
}
