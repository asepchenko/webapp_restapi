<?php

namespace App\Console\Commands;

use File;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class APIGenerator extends Command
{

    /*
    Notes :
    - modelName                        : Cars      
    - modelNamePluralLowerCase         : cars
    - modelNameSingularLowerCase       : car
    - modelNameSingular                : Car

    run : php artisan api:generator Cars
    */

    //protected $signature = 'command:name';
    protected $signature = 'api:generator
    {name : Class (singular) for example User}';
    
    //protected $description = 'Command description';
    protected $description = 'Create API operations';
    

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $this->info('Creating API for : '.$name.'...');
        
        $this->controller($name);
        $this->info($name.'Controller created successfully!!');

        $this->model($name);
        $this->info($name.' Model created successfully!!');

        /*$this->requestStore($name);
        $this->info($name.'RequestStore created successfully!!');

        $this->requestUpdate($name);
        $this->info($name.'RequestUpdate created successfully!!');
        */

        //$this->migration($name);
        //$this->info($name.' Migration created successfully!!');

        $this->service($name);
        $this->info($name.' Service created successfully!!');

        $this->repository($name);
        $this->info($name.' Repository created successfully!!');

        //routes
        File::append(base_path('routes/api.php'), "\nuse App\Http\Controllers\API\\". $name . "Controller;\n");
        File::append(base_path('routes/api.php'), "Route::apiResource('" . strtolower($name) . "', ". $name . "Controller::class);\n");
        $this->info('Add new routes '.$name.' successfully!!');
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function migration($name)
    {
        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::singular($name))
            ],
            $this->getStub('Migration')
        );

        $t =time();
        $date = date("Y_m_d",$t);
        $name = strtolower($name);
        file_put_contents(base_path("/database/migrations/{$date}_000000_create_".strtolower($name)."_table.php"), $modelTemplate);
    }

    protected function model($name)
    {
        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNameSingular}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::singular($name)),
                Str::singular($name)
            ],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/Models/".Str::singular($name).".php"), $modelTemplate);
    }

    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNameSingular}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::singular($name)),
                Str::singular($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/API/".$name."Controller.php"), $controllerTemplate);
    }

    protected function requestStore($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('StoreRequest')
        );

        if(!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}StoreRequest.php"), $requestTemplate);
    }

    protected function requestUpdate($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('UpdateRequest')
        );

        if(!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}UpdateRequest.php"), $requestTemplate);
    }

    protected function service($name)
    {
        $serviceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNameSingular}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::singular($name)),
                Str::singular($name)
            ],
            $this->getStub('Service')
        );

        //create file
        file_put_contents(app_path("/Services/".Str::singular($name)."Service.php"), $serviceTemplate);
    }

    protected function repository($name)
    {
        $repositoryTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNameSingular}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::singular($name)),
                Str::singular($name)
            ],
            $this->getStub('Repository')
        );

        //create file
        file_put_contents(app_path("/Repositories/".Str::singular($name)."Repository.php"), $repositoryTemplate);
    }
}
