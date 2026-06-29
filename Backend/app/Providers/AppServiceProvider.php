<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Interfaces
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\MunicipalityRepositoryInterface;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\IncidentPredictionRepositoryInterface;
use App\Repositories\Contracts\ZoneRepositoryInterface;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\CityRepositoryInterface;

// Implémentations
use App\Repositories\UserRepository;
use App\Repositories\AuthRepository;
use App\Repositories\MunicipalityRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\IncidentPredictionRepository;
use App\Repositories\ZoneRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;

use App\Repositories\CommentRepository;
use App\Repositories\MessageRepository;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\ConversationRepository;
use App\Repositories\CityRepository;
use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\CountryRepository;
use App\Repositories\Contracts\GovernorateRepositoryInterface;
use App\Repositories\GovernorateRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(MunicipalityRepositoryInterface::class, MunicipalityRepository::class);
        $this->app->bind(IncidentRepositoryInterface::class, IncidentRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(IncidentPredictionRepositoryInterface::class, IncidentPredictionRepository::class);
        $this->app->bind(ZoneRepositoryInterface::class, ZoneRepository::class);
        $this->app->bind(AssignmentRepositoryInterface::class, AssignmentRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        $this->app->bind(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(GovernorateRepositoryInterface::class, GovernorateRepository::class);

    }

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            error_reporting(E_ALL & ~E_USER_WARNING & ~E_USER_NOTICE);
        }
        require base_path('routes/channels.php');
    }
}