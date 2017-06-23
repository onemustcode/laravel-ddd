<?php

namespace OneMustCode\LaravelDDD\Commands;

class CreateCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ddd:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new Entity, Repository or Service.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $group = $this->ask('What is the group?');
        $entity = $this->ask('What is the entity?');

        $variables = [
            'table' => $this->getTable($group),
            'group' => $this->getGroupNamespace($group),
            'entity' => $entity,
            'entity_plural' => str_plural($entity),
            'entity_variable' => camel_case($entity),
            'entity_human' => ucfirst(str_replace('_', ' ', snake_case($entity))),
        ];

        if ($this->confirm('Want to create a Entity?')) {
            $this->createEntity($group, $entity, $variables);
        }

        if ($this->confirm('Want to create a Repository?')) {
            $this->createRepository($group, $entity, $variables);
        }

        if ($this->confirm('Want to create a Service?')) {
            $this->createService($group, $entity, $variables);
        }

        $this->line('');
    }

    /**
     * Creates entity
     *
     * @param string $group
     * @param string $entity
     * @param array $variables
     */
    private function createEntity(string $group, string $entity, array $variables)
    {
        $fileName = sprintf(
            '%s/Domain/%s/Entities/%s.php', $this->getSource(), $group, $entity
        );

        $this->parseStub('domain/core/entities/Example.stub', $fileName, $variables);

        // Create yaml
        $fileName = sprintf(
            '%s/Infrastructure/Persistence/Doctrine/yaml/%s.Domain.%s.Entities.%s.dcm.yml',
            $this->getSource(),
            $this->getNamespace(),
            $this->getGroupYamlName($group),
            $entity
        );

        $this->parseStub('infrastructure/persistence/doctrine/yaml/Entity.dcm.stub', $fileName, $variables);
    }

    /**
     * Creates repository
     *
     * @param string $group
     * @param string $entity
     * @param array $variables
     */
    private function createRepository(string $group, string $entity, array $variables)
    {
        // Interface
        $fileName = sprintf(
            '%s/Domain/%s/Repositories/%sRepositoryInterface.php', $this->getSource(), $group, $entity
        );
        $this->parseStub('domain/core/repositories/EntityRepositoryInterface.stub', $fileName, $variables);

        // Repository
        $fileName = sprintf(
            '%s/Infrastructure/Persistence/Doctrine/Repositories/%s/%sRepository.php', $this->getSource(), $group, $entity
        );
        $this->parseStub('infrastructure/persistence/doctrine/repositories/EntityRepository.stub', $fileName, $variables);

        $this->info('Entity created!');
        $this->info('Bind the following repository in your service provider!');
        $this->line('$this->app->bind('. $entity .'RepositoryInterface::class, function ($app) {
    return new '. $entity .'Repository(
        $app[\'em\'],
        $app[\'em\']->getClassMetaData('. $entity .'::class)
    );
});');
    }

    /**
     * Creates service
     *
     * @param string $group
     * @param string $entity
     * @param array $variables
     */
    private function createService(string $group, string $entity, array $variables)
    {
        // Interface
        $fileName = sprintf(
            '%s/Domain/%s/Services/%sServiceInterface.php', $this->getSource(), $group, $entity
        );
        $this->parseStub('domain/core/services/EntityServiceInterface.stub', $fileName, $variables);

        // Service
        $fileName = sprintf(
            '%s/Domain/%s/Services/%sService.php', $this->getSource(), $group, $entity
        );
        $this->parseStub('domain/core/services/EntityService.stub', $fileName, $variables);

        // Exception
        $fileName = sprintf(
            '%s/Domain/%s/Exceptions/%sNotFoundException.php', $this->getSource(), $group, $entity
        );
        $this->parseStub('domain/core/exceptions/EntityNotFoundException.stub', $fileName, $variables);

        $this->info('Bind the following service in your service provider!');
        $this->line('$this->app->bind('. $entity .'ServiceInterface::class, '. $entity .'Service::class);');
    }

    /**
     * Returns plural table name
     *
     * @param string $group
     * @return string
     */
    private function getTable($group)
    {
        $parts = explode('/', $group);

        $lastPart = array_pop($parts);

        $parts[] = str_plural($lastPart);

        return snake_case(implode('', $parts));
    }

    /**
     * Returns the group namespace
     *
     * @param string $group
     * @return string
     */
    private function getGroupNamespace($group)
    {
        return str_replace('/', '\\', $group);
    }

    /**
     * Returns the group yaml name
     *
     * @param string $group
     * @return string
     */
    private function getGroupYamlName($group)
    {
        return str_replace('/', '.', $group);
    }
}
