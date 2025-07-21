<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route pour la page d'accueil par défaut (accessible aux non-connectés)
Route::get('/', function () {
    return view('welcome');
})->name('welcome'); // Donnez-lui un nom pour référence facile

// Redirection après connexion ou accès au tableau de bord
// Cette route est protégée par les middlewares d'authentification de Jetstream
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        // Redirige l'utilisateur vers le panneau Filament approprié en fonction de son rôle
        if (auth()->user()->hasRole('Admin')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }
        // Assurez-vous que les autres rôles ont aussi une redirection spécifique si nécessaire
        // Sinon, ils iront par défaut au tableau de bord AppPanel
        return redirect()->route('filament.app.pages.dashboard');
    })->name('dashboard'); // Nom de la route pour le tableau de bord
});

// Les routes de Filament sont automatiquement enregistrées par les PanelProviders
// Les routes de Fortify (login, register, etc.) sont aussi automatiquement enregistrées
