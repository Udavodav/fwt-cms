<?php

namespace Fwt\Crud;

use Illuminate\Console\Command;
use ReflectionClass;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:crud {modelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create crud for model';

    /**
     * Execute the console command.
     */
    public function handle(StubService $service)
    {
        $modelName = ucfirst($this->argument('modelName'));
        try {
            $reflection = new ReflectionClass(new ('App\\Models\\' . $modelName)());

            $attributes = $reflection->getAttributes('Fwt\\Crud\\Attributes\\Table');
            if (empty($attributes)) {
                throw new \ReflectionException(
                    "Don't find attributes with name 'Fwt\\Crud\\Attributes\\Table' in model $modelName");
            }

            $service->setProperties($modelName, $attributes[0]->getArguments());

            $service->generateMigration();
            $service->checkLayoutView();
            $service->generateRequest();
            $service->generateController();
            $service->generateViews();
            $service->appendRoutes();

        } catch (\ReflectionException $e) {
            echo $e->getMessage();
        }
    }
}
