<?php

namespace App\Providers\Filament;

use App\Filament\AppPanel\Pages\MyDocuments;
use App\Filament\AppPanel\Pages\MyProfile;
use App\Filament\AppPanel\Pages\SubmitReport;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\User; // Import du modèle User

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/AppPanel/Resources'), for: 'App\\Filament\\AppPanel\\Resources')
            ->discoverPages(in: app_path('Filament/AppPanel/Pages'), for: 'App\\Filament\\AppPanel\\Pages')
            ->pages([
                Pages\Dashboard::class,
                MyProfile::class,
                MyDocuments::class,
                SubmitReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/AppPanel/Widgets'), for: 'App\\Filament\\AppPanel\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\AppPanel\Widgets\StudentReportStatusWidget::class,
                \App\Filament\AppPanel\Widgets\CommissionVoteOverview::class,
            ])
            ->middleware([
                StartSession::class,
                AuthenticateSession::class,
                AddQueuedCookiesToResponse::class,
                EncryptCookies::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigation(function (\Filament\Navigation\NavigationBuilder $navigation): \Filament\Navigation\NavigationBuilder {
                $user = auth()->user();

                // Navigation commune
                $navigation->items([
                    \Filament\Navigation\NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        ->url(fn (): string => \App\Filament\AppPanel\Pages\Dashboard::getUrl())
                        ->activeIcon('heroicon-s-home')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.app.pages.dashboard')),
                    \Filament\Navigation\NavigationItem::make('Mon Profil')
                        ->icon('heroicon-o-user')
                        ->url(fn (): string => MyProfile::getUrl())
                        ->activeIcon('heroicon-s-user')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.app.pages.my-profile')),
                    \Filament\Navigation\NavigationItem::make('Mes Documents')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (): string => MyDocuments::getUrl())
                        ->activeIcon('heroicon-s-document-text')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.app.pages.my-documents')),
                ]);

                // Navigation spécifique par rôle
                if ($user && $user->hasRole('Etudiant')) {
                    $navigation->items([
                        \Filament\Navigation\NavigationItem::make('Soumettre Rapport')
                            ->icon('heroicon-o-arrow-up-tray')
                            ->url(fn (): string => SubmitReport::getUrl())
                            ->activeIcon('heroicon-s-arrow-up-tray')
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.app.pages.submit-report')),
                    ]);
                }

                if ($user && $user->hasRole('Responsable Scolarite')) {
                    $navigation->items([
                        \App\Filament\AppPanel\Resources\StudentResource::getNavigationGroup() ?
                            \Filament\Navigation\NavigationGroup::make(\App\Filament\AppPanel\Resources\StudentResource::getNavigationGroup())
                                ->items([
                                    \App\Filament\AppPanel\Resources\StudentResource::getNavigationItem(),
                                    \App\Filament\AppPanel\Resources\InternshipResource::getNavigationItem(),
                                ]) :
                            \App\Filament\AppPanel\Resources\StudentResource::getNavigationItem(),
                    ]);
                }

                if ($user && $user->hasAnyRole(['Membre Commission', 'President Commission'])) {
                    $navigation->items([
                        \App\Filament\AppPanel\Resources\CommissionSessionResource::getNavigationGroup() ?
                            \Filament\Navigation\NavigationGroup::make(\App\Filament\AppPanel\Resources\CommissionSessionResource::getNavigationGroup())
                                ->items([
                                    \App\Filament\AppPanel\Resources\CommissionSessionResource::getNavigationItem(),
                                    // Ajouter d'autres ressources spécifiques à la commission ici (ex: PvResource)
                                ]) :
                            \App\Filament\AppPanel\Resources\CommissionSessionResource::getNavigationItem(),
                    ]);
                }

                return $navigation;
            });
    }
}
