<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Exceptions\InvalidConfiguration;
use App\GoogleCalendar;
use App\GoogleCalendarFactory;

class GoogleCalendarServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/google-calendar.php' => config_path('google-calendar.php'),
        ], 'config');
    }
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/google-calendar.php', 'google-calendar');
        $this->app->bind(GoogleCalendar::class, function () {
            $config = config('google-calendar');
            $this->guardAgainstInvalidConfiguration($config);
            return GoogleCalendarFactory::createForCalendarId($config['calendar_id']);
        });
        $this->app->alias(GoogleCalendar::class, 'laravel-google-calendar');
    }
    protected function guardAgainstInvalidConfiguration(array $config = null)
    {
        if (empty($config['calendar_id'])) {
            throw InvalidConfiguration::calendarIdNotSpecified();
        }
        if (! file_exists($config['service_account_credentials_json'])) {
            throw InvalidConfiguration::credentialsJsonDoesNotExist($config['service_account_credentials_json']);
        }
    }
}