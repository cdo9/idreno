<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {

            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);

            $this->app->register(TelescopeServiceProvider::class);

        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('secteurs')) {
            $secteurs = DB::table('secteurs')->select('id', 'nom', 'couleur')->where('activated', 1)->get();

            View::composer('components.header', function ($view) use($secteurs) {
                $user = auth()->user();
                $view->with('secteurs', $secteurs);
                $view->with('user', $user);
            });

            View::composer('components.mobile-menu', function ($view) use($secteurs) {
                $user = auth()->user();
                $view->with('secteurs', $secteurs);
                $view->with('user', $user);
            });

            View::composer('rendez-vous/confirmation-de-rendez-vous', function ($view) {
                $sources = DB::table('sources')->select('id', 'nom')->get();
                $poseurs = DB::table('poseurs')->select('id', 'poseur')->get();

                $view->with([
                    'sources' => $sources,
                    'poseurs' => $poseurs,
                ]);
            });
        }

        Blade::directive('capitalizeLower', function($string){
            return "<?php echo ucfirst(strtolower($string));?>";
        });

        Builder::macro('ddSql', function ($label = null) {
            $sql = Str::replaceArray('?', $this->getBindings(), $this->toSql());
            dd($label ? "[$label] $sql" : $sql);
        });

        EloquentBuilder::macro('ddSql', function ($label = null) {
            $sql = Str::replaceArray('?', $this->getBindings(), $this->toSql());
            $label = $label ? "[$label] " : '';
            dd($label . $sql);
        });
    }
}
