<?php

namespace OneMustCode\LaravelDDD\Commands;

class ProjectCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ddd:project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (config('ddd.namespace')) {
            $this->error('There is already an project created!');
            return;
        }

        $existing = $this->ask('Is it an existing project?');

        $source = $this->ask('What is the source directory' . ($existing ? '' : ' (default src)'), 'src');
        $namespace = $this->ask('What is the project namespace?');

        $this->createConfig($source, $namespace);

        if ($existing) {
            $this->info('All done!');
            return;
        }

        $this->info('Creating new project in directory ['. $this->getSource() .'] with namespace ['. $this->getNamespace() . ']');

        $this->createDomain();
        $this->createInfrastructure();

        $this->line('');

        $this->info('Publishing Laravel Doctrine config..');
        $this->call('vendor:publish', ['--provider' => \LaravelDoctrine\ORM\DoctrineServiceProvider::class]);

        $this->line('');

        $this->info('Publishing Twigbride config..');
        $this->call('vendor:publish', ['--provider' => \TwigBridge\ServiceProvider::class]);

        $this->line('');

        $this->info('Register the namespace in the composer file (psr-4) and run; composer dump');
        $this->line('"'. $this->getNamespace() .'\\\": "src/"');

        $this->line('');

        $this->info('Bind the hydrator in app/Providers/AppServiceProvider.php');
        $this->line('$this->app->bind(HydratorInterface::class, Hydrator::class);');

        $this->line('');
    }

    /**
     * Creates the config file
     *
     * @param string $source
     * @param string $namespace
     */
    protected function createConfig(string $source, string $namespace)
    {
        config([
            'ddd.source' => $source,
            'ddd.namespace' => $namespace
        ]);

        $this->parseStub('laravel/config/ddd.stub', 'config/ddd.php', [
            'source' => $source,
            'namespace' => $namespace,
        ]);
    }

    /**
     * Creates the infrastructure
     */
    protected function createInfrastructure()
    {
        // Hydrator
        $this->parseStub('infrastructure/hydrator/Hydrator.stub', 'src/Infrastructure/Hydrator/Hydrator.php');

        // Fixtures
        $this->parseStub('infrastructure/persistence/doctrine/seeders/DatabaseSeeder.stub', $this->getSource() .'/Infrastructure/Persistence/Doctrine/Seeders/DatabaseSeeder.php');
        $this->parseStub('infrastructure/persistence/doctrine/seeders/ExampleSeeder.stub', $this->getSource() .'/Infrastructure/Persistence/Doctrine/Seeders/ExampleSeeder.php');

        // Types
        $this->parseStub('infrastructure/persistence/doctrine/types/CarbonType.stub', $this->getSource() .'/Infrastructure/Persistence/Doctrine/Types/CarbonType.php');

        // Repositories
        $this->parseStub('infrastructure/persistence/doctrine/repositories/AbstractRepository.stub', $this->getSource() .'/Infrastructure/Persistence/Doctrine/Repositories/AbstractRepository.php');
    }

    /**
     * Creates the domain
     */
    protected function createDomain()
    {
        // Entities
        $this->parseStub('domain/core/entities/CreatedAtTrait.stub', $this->getSource() .'/Domain/Core/Entities/CreatedAtTrait.php');
        $this->parseStub('domain/core/entities/UpdatedAtTrait.stub', $this->getSource() .'/Domain/Core/Entities/UpdatedAtTrait.php');
        $this->parseStub('domain/core/entities/TimestampsTrait.stub', $this->getSource() .'/Domain/Core/Entities/TimestampsTrait.php');
        $this->parseStub('domain/core/entities/PrimaryKeyTrait.stub', $this->getSource() .'/Domain/Core/Entities/PrimaryKeyTrait.php');

        // Hydrator
        $this->parseStub('domain/core/hydrator/HydratorInterface.stub', $this->getSource() .'/Domain/Core/Hydrator/HydratorInterface.php');

        // Events
        $this->parseStub('domain/core/events/EventDispatcherTrait.stub', $this->getSource() .'/Domain/Core/Events/EventDispatcherTrait.php');
        $this->parseStub('domain/core/events/EventInterface.stub', $this->getSource() .'/Domain/Core/Events/EventInterface.php');

        // Exceptions
        $this->parseStub('domain/core/exceptions/AlreadyExistsException.stub', $this->getSource() .'/Domain/Core/Exceptions/AlreadyExistsException.php');
        $this->parseStub('domain/core/exceptions/InvalidArgumentException.stub', $this->getSource() .'/Domain/Core/Exceptions/InvalidArgumentException.php');
        $this->parseStub('domain/core/exceptions/LogicException.stub', $this->getSource() .'/Domain/Core/Exceptions/LogicException.php');
        $this->parseStub('domain/core/exceptions/NotFoundException.stub', $this->getSource() .'/Domain/Core/Exceptions/NotFoundException.php');

        // Repositories
        $this->parseStub('domain/core/repositories/RepositoryInterface.stub', $this->getSource() .'/Domain/Core/Repositories/RepositoryInterface.php');
    }
}
