<?php

namespace Fwt\Crud;

use Fwt\Crud\Classes\Column;
use Fwt\Crud\Enums\ColumnType;
use Illuminate\Support\Str;

class StubService
{
    protected string $model;

    protected string $models;

    protected array $tableArgs;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function setProperties(string $model, array $args) : void
    {
        $this->model = $model;
        $this->models = strtolower(Str::plural($model));
        $this->tableArgs = $args;
    }

    public function getStub($name) : string
    {
        return file_get_contents(__DIR__ . "/stubs/$name.stub");
    }

    public function putFromStub(string $stubName, array $keys, array $values, string $path) : void
    {
        file_put_contents($path, $this->getContent($stubName, $keys, $values));
    }

    /**
     * @param string $table
     * @return string
     */
    public function getRulesForRequest(string $table): string
    {
        $rulesLine = '';
        foreach ($this->tableArgs['columns'] as $column) {
            $rulesLine .= "'$column->columnName' => '" .
                $this->getRules($column, $table);
        }

        return $rulesLine;
    }

    private function getRules(Column $column, string $table): string
    {
        $rules[] = $column->isNullable ? 'nullable' : 'required';

        $rules[] = match ($column->columnType) {
            ColumnType::String, ColumnType::Text => 'string',
            ColumnType::Integer => 'integer',
            ColumnType::Date => 'date',
        };

        if ($column->isUnique) {
            $rules[] = "unique:$table,$column->columnName,'.".'$this->route(\''.strtolower($this->model).'\')->id';
        }

        return implode('|', $rules) . ($column->isUnique ? '' : "'") . ',' . $this->newLine(3);
    }

    public function generateRequest() : void
    {
        if ($this->checkFolderAndFile('app/Http/Requests', "{$this->model}Request.php")) {
            return;
        }

        $this->putFromStub(
            'request.cms',
            ['$namespace$', '$class$', '$rules$'],
            ['App\\Http\\Requests',
                $this->model . 'Request',
                $this->getRulesForRequest(
                    $this->tableArgs['tableName']
                )],
            base_path('/app/Http/Requests/' . $this->model . 'Request.php')
        );
    }

    public function generateController() : void
    {
        if ($this->checkFolderAndFile('app/Http/Controllers', "{$this->model}Controller.php")) {
            return;
        }

        $this->putFromStub(
            'controller.cms',
            [
                '$namespace$',
                '$namespaceModel$',
                '$namespaceRequest$',
                '$class$',
                '$model$',
                '$models$',
                '$modelVariable$'
            ],
            [
                'App\\Http\\Controllers',
                'App\\Models\\' . $this->model,
                'App\\Http\\Requests\\' . $this->model . 'Request',
                $this->model . 'Controller',
                $this->model,
                $this->models,
                strtolower($this->model)
            ],
            base_path('/app/Http/Controllers/' . $this->model . 'Controller.php')
        );
    }

    public function generateViews() : void
    {
        $this->createIndexView();
        $this->createShowView();
        $this->createFormView();
        $this->createViewForForm('create');
        $this->createViewForForm('edit');
    }

    public function createIndexView() : void
    {
        if ($this->checkFolderAndFile('resources/views/' . $this->models, "index.blade.php")) {
            return;
        }

        $this->putFromStub(
            'view.cms.index',
            [
                '$models$',
                '$modelVariable$',
                '$tHead$',
                '$tBody$'
            ],
            [
                $this->models,
                strtolower($this->model),
                $this->createRowsTable(true),
                $this->createRowsTable(),
            ],
            resource_path('/views/' . $this->models . '/index.blade.php')
        );
    }

    public function createShowView()
    {
        if ($this->checkFolderAndFile('resources/views/' . $this->models, "show.blade.php")) {
            return;
        }

        $properties = '';

        foreach ($this->tableArgs['columns'] as $column) {
            $properties .= $this->getContent('include.cms.show.property',
                [
                    '$modelVariable$',
                    '$columnName$',
                    '$column$',
                ],
                [
                    strtolower($this->model),
                    str_replace('_', ' ', ucfirst($column->columnName)),
                    $column->columnName,
                ]
            );
        }

        $this->putFromStub('view.cms.show',
            [
                '$properties$',
                '$modelVariable$',
                '$models$'
            ],
            [$properties, strtolower($this->model), $this->models],
            resource_path('/views/' . $this->models . '/show.blade.php')
        );
    }

    public function createViewForForm(string $type)
    {
        if ($this->checkFolderAndFile('resources/views/' . $this->models, "$type.blade.php")) {
            return;
        }

        $this->putFromStub(
            "view.cms.$type",
            [
                '$models$',
                '$modelVariable$'
            ],
            [
                $this->models,
                strtolower($this->model)
            ],
            resource_path('/views/' . $this->models . "/$type.blade.php")
        );
    }

    public function createFormView()
    {
        if ($this->checkFolderAndFile('resources/views/' . $this->models, "form.blade.php")) {
            return;
        }

        $filds = '';

        foreach ($this->tableArgs['columns'] as $column) {
            $filds .= $this->getContent('include.cms.form.field',
                [
                    '$modelVariable$',
                    '$columnName$',
                    '$column$',
                    '$inputType$'
                ],
                [
                    strtolower($this->model),
                    str_replace('_', ' ', ucfirst($column->columnName)),
                    $column->columnName,
                    $column->columnType->convertToHtml()
                ]
            );
        }

        file_put_contents(resource_path('/views/' . $this->models . '/form.blade.php'), $filds);
    }

    public function generateMigration()
    {
        if (glob(base_path("database/migrations/*create_{$this->models}_table*"))) {
            return;
        }

        $this->putFromStub(
            "migration.cms.create",
            [
                '$table$',
                '$fields$'
            ],
            [
                empty($this->tableArgs['tableName']) ? $this->models : $this->tableArgs['tableName'],
                $this->getMigrationFields()
            ],
            base_path('/database/migrations/' . now()->format('Y_m_d_His') . "_create_{$this->models}_table.php")
        );
    }

    public function getContent(string $stubName, array $keys, array $values): string
    {
        return str_replace($keys, $values, $this->getStub($stubName));
    }

    public function createRowsTable(bool $isHead = false)
    {
        $lines = '';
        $last = array_key_last($this->tableArgs['columns']);
        $callable = $isHead ? 'lineTableHead' : 'lineTableBody';

        foreach ($this->tableArgs['columns'] as $key => $column) {
            if ($column->isShowInView) {
                $lines .= $this->{$callable}($column);
            }

            if ($last != $key) {
                $lines .= $this->newLine(5);
            }
        }

        return $lines;
    }

    public function lineTableHead(Column $column)
    {
        return '<th scope="col">' . str_replace('_', ' ', ucfirst($column->columnName)) . '</th>';
    }

    public function lineTableBody(Column $column)
    {
        return '<td>{{ $' . strtolower($this->model) . '->' . $column->columnName . ' }}</td>';
    }

    public function checkFolderAndFile(string $folder, string $fileName)
    {
        if (!is_dir(base_path($folder))) {
            mkdir(base_path($folder));
        }

        if (is_file(base_path($folder . '/' . $fileName))) {
            return true;
        }

        return false;
    }

    public function appendRoutes()
    {
        $route = "Route::resource('$this->models', {$this->model}Controller::class);";
        $namespace = "App\\Http\\Controllers\\{$this->model}Controller";
        $content = file_get_contents(base_path('routes/web.php'));

        $content .= Str::contains($content, $route) ? '' : "\n".$route;
        if(!strpos($content, $namespace)){
            $index = strpos($content, 'use');
            $content = substr($content, 0, $index)."use $namespace;\n".substr($content, $index, strlen($content));
        }

        file_put_contents(base_path('/routes/web.php'), $content);
    }

    public function checkLayoutView()
    {
        if (!$this->checkFolderAndFile('resources/views/layouts', 'cms.blade.php')) {
            $this->putFromStub('view.cms.layout', [], [], resource_path('views/layouts/cms.blade.php'));
        }
    }

    private function newLine(int $countTabs = 0)
    {
        return "\n" . str_repeat("\t", $countTabs);
    }

    private function getMigrationFields()
    {
        $fields = '';

        foreach ($this->tableArgs['columns'] as $column) {
            $fields .= '$table->'.$column->columnType->value."('{$column->columnName}')";
            $fields .= is_null($column->defaultValue) ? '' : '->default('.$column->defaultValue.')';
            $fields .= $column->isUnique ? '->unique()' : '';
            $fields .= $column->isNullable ? '->nullable()' : '';
            $fields .= ';'.$this->newLine(3);
        }

        return $fields;
    }
}
