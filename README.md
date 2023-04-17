# Core

Base features and tools to use in the application

## Features

### 1) Process timer

This feature offers a mechanism to log information about a running process using a common format. When the method 
`start` is called, a starting message is logged including a unique identifier defined for this process.

When the method `stop` is called, a finishing message is logged, including the total processing time and the 
identifier as well, so a match between start and finish of the same process can be stated in latter reviews.

#### Where to log?

As different processes can be logged in different ways, this feature provides a mechanism to support its own 
[on-demmand logging channels](https://laravel.com/docs/9.x/logging#on-demand-channels). The summarized sequence 
would be this:

1. The implementations of `ProcessTimerInterface` must include an implementation of `LoggerFactoryInterface`
2. The objects produced by `LoggerFactoryInterface` are instances of `Ebolution\Logger\Infrastructure\Logger`, 
   and it requires an implementation of `BuilderInterface`.
3. The specific implementation of `BuilderInterface` hosts the details about the logger used by the Process timer.

Maybe it's easier to try an example:

1. Create a command in `app/Console/Commands` using the command `artisan make:command FooProcessTimer`.
2. Replace the signature of the command to: `foo:process-timer`
3. Create the `Logging` directory in app `mkdir app/Logging`
4. Create a new class `ProcessTimer` that extends `Ebolution\Core\Infrastructure\Repositories\ProcessTimer` (which 
   implements `ProcessTimerInterface`)
````
namespace App\Logging;

use Ebolution\Core\Infrastructure\Repositories\ProcessTimer as CoreProcessTimer;

class ProcessTimer extends CoreProcessTimer
{

}
````
5. Create a class `LoggerFactory` that extends from `Ebolution\Logger\Infrastructure\LoggerFactory`
````
namespace App\Logging;

use Ebolution\Logger\Infrastructure\LoggerFactory as CoreLoggerFactory;

class LoggerFactory extends CoreLoggerFactory
{

}
````
6. Create a new class `LoggerBuilder` that extends from `Ebolution\Logger\Domain\LoggerBuilder`. Here is where 
   the on-demand logger is defined. In this example, the log messages are written to a file, but many other options 
   are available. If you want a different behavior, just override the methods in this class. Make sure to provide a  
   path for you log file (required) and, also a prefix to add to the logs (optional).
````
namespace App\Logging;

use Ebolution\Logger\Domain\LoggerBuilder as CoreLoggerBuilder;

class LoggerBuilder extends CoreLoggerBuilder
{
    protected string $path = 'logs/foo.log';
    protected string $prefix = 'Foo';
}
````
7. As this approach is heavily based on dependency injection, we need to inform Laravel what concrete 
   implementations we want to pass to each class instead of just interfaces, if it's the case. So, create a class 
   `DependencyServicesProvider` extending `Illuminate\Support\ServiceProvider` and declare the right injection for 
   classes `LoggerFactory` and `ProcessTimer`.
````
namespace App\Logging;

use Illuminate\Support\ServiceProvider;
use Ebolution\Logger\Domain\BuilderInterface;
use Ebolution\Logger\Domain\LoggerFactoryInterface;

final class DependencyServicesProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->when(LoggerFactory::class)
            ->needs(BuilderInterface::class)
            ->give(LoggerBuilder::class);

        $this->app->when(ProcessTimer::class)
            ->needs(LoggerFactoryInterface::class)
            ->give(LoggerFactory::class);
    }
}
````
8. Finally, go to `config/app.php` and declare the newly created service provider into the `providers` section.
````
...

\App\Logging\DependencyServicesProvider::class

...
````
9. Go back to `App\Console\Commands\FooProcessTimer` and create a constructor injecting the newly created `ProcessTimer` 
   class (please notice that we use [properties promotion](https://stitcher.io/blog/constructor-promotion-in-php-8) 
   here).
````
    public function __construct(
        private ProcessTimer $processTimer
    ) {
        parent::__construct();
    }
````
10. Now the timer itself, we need to call the `start` and `stop` methods when the command is executed. Please notice 
    that `start` expect for a name for this process.
````
    public function handle()
    {
        $this->processTimer->start('Foo process');
        sleep(5);
        $this->processTimer->stop();

        return Command::SUCCESS;
    }
````

Now we are ready to see if everything is on its place executing the command `artisan foo:process-timer`, and going to 
`storage/logs/foo.log` to check the output.

All the instructions above are meant to follow a classic development pattern, however, we recommend to follow the 
modular approach using in combination with the `Ebolution_ModuleManager` module. The logic is the same, but the 
location of the files is different.

Of course this is a ten steps process, we're open to optimize, automatize and improve it. Just don't hesitate to make 
your suggestions :D