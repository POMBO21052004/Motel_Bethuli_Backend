<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Fix pour MySQL : la longueur maximale des index sur utf8mb4 est 767 bytes.
     * utf8mb4 utilise 4 bytes/char → 767/4 ≈ 191 chars.
     * Sans cette ligne, les colonnes string (VARCHAR 255) génèrent une erreur
     * "La clé est trop longue" lors de la création d'index uniques.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
