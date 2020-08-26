<?php

namespace IncendiaryBlue\ResourceBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BuildResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-builder:build {--name=} {--code=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fully built and tested API resources based on the Incendiary Blue API standards.';

    protected $names;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // Do we have what we need?
        if (!$this->option('name') || !$this->option('code')) {
            $this->error('Please ensure you have provided a resource name and a response code prefix e.g. php artisan resource-builder:build --name "Resource Name" --code 10');
            return false;
        }


        // Tell the user we are starting the process
        $this->comment('Hold on there Winston! We\'re checking the project suitability...');

        // Let's start by generating all the required class, route and resource names
        $names = $this->generateNames($this->option('name'));
                
        // Is the resource already built?
        // if (file_exists(getcwd() . '/app/Models/' . $this->names['model'] . '.php')) {
        //     $this->error('Oh Damn! This resource already exists.');
        //     return false;
        // }
        
        $this->info('Right! Well done, your code base is suitable let\'s get started');

        // If the ApiCode does not exist let's create it
        if (!$this->checkApiCodeFile()) {
            $this->info('Creating ApiCode.php');
            file_put_contents(getcwd() . '/app/ApiCode.php', file_get_contents(__DIR__ . '/stubs/api_code_base.stub'));
        }
        
        // If the ApiCode does not exist let's create it
        if (!$this->checkResponseBuilderConfigFile()) {
            $this->info('Creating config/response_builder.php');
            file_put_contents(getcwd() . '/config/response_builder.php', file_get_contents(__DIR__ . '/stubs/response_builder_config.stub'));
        }
        
        // If the UUID Trait does not exist let's create it
        if (!$this->checkUuidTraitsFile()) {
            $this->info('Creating app/Traits/Uuids.php');
            // Create a folder if it doesn't already exist
            if (!file_exists(getcwd() . '/app/Traits')) {
                mkdir(getcwd() . '/app/Traits');
            }
            file_put_contents(getcwd() . '/app/Traits/Uuids.php', file_get_contents(__DIR__ . '/stubs/uuid_trait.stub'));
        }

        // Create the Model
        $this->createModel();

        // Create the Controller
        $this->createController();

        // Create the Requests
        $this->createRequests();

        // Create the Migration
        $this->createMigration();

        // Create the Tests
        $this->createTests();
        
        // Create the Requests
        $this->createApiCodes();
        
        // Create the Requests
        $this->createRoute();
        
        // Create the Api Doc Json Order Values
        $this->addToApiDocJson();

        $this->info('Resource Built! Go Code ... ');

        return true;
    }

    /**
     * Generate the required class names, singulars and plurals.
     *
     * @return int
     */
    public function generateNames($name)
    {
        $names['class_name']        = str_replace(' ','', ucwords(Str::singular($this->option('name'))));
        $names['migration_name']    = str_replace(' ','', ucwords(Str::plural($this->option('name'))));
        $names['permissions_name']  = str_replace(' ','.', Str::lower((Str::singular($this->option('name')))));
        $names['model']             = str_replace(' ','', ucwords(Str::singular($this->option('name'))));
        $names['route']             = Str::kebab(Str::plural(strtolower($this->option('name'))));
        $names['controller']        = Str::studly(Str::singular($this->option('name')));
        $names['request']           = Str::studly(Str::singular($this->option('name')));
        $names['table_name']        = Str::snake(Str::plural($this->option('name')));
        $names['response_code']     = Str::upper(Str::snake(Str::singular($this->option('name'))));
        
        $this->names = $names;

        return true;
    }
    
    /**
     * Generate the required class names, singulars and plurals.
     *
     * @return int
     */
    public function checkApiCodeFile()
    {
        return file_exists(getcwd() . '/app/ApiCode.php');
    }
    
    /**
     * Generate the required class names, singulars and plurals.
     *
     * @return int
     */
    public function checkResponseBuilderConfigFile()
    {
        return file_exists(getcwd() . '/config/response_builder.php');
    }
    
    /**
     * Generate the required class names, singulars and plurals.
     *
     * @return int
     */
    public function checkUuidTraitsFile()
    {
        return file_exists(getcwd() . '/app/Traits/Uuids.php');
    }

    /**
     * Generate the required class names, singulars and plurals.
     *
     * @return int
     */
    public function getNextApiCode()
    {        
        return $this->option('code');
    }

    /**
     * Generate the Model File.
     *
     * @return int
     */
    public function createModel()
    {        
        // We get the contents of the stub and replace the class name and table name
        $modelContent = str_replace(
            [
                '{{className}}',
                '{{tableName}}'
            ], 
            [
                $this->names['class_name'], 
                $this->names['table_name']
            ],  
            file_get_contents(__DIR__ . '/stubs/model.stub') 
        );

        // Create a folder if it doesn't already exist
        if (!file_exists(getcwd() . '/app/Models')) {
            mkdir(getcwd() . '/app/Models');
        }

        // Then we write the contents to the file
        file_put_contents(getcwd() . '/app/Models/' . $this->names['controller'] . '.php', $modelContent);
        
        return true;
    }

    /**
     * Generate the Controller File.
     *
     * @return int
     */
    public function createController()
    {        
        // We get the contents of the stub and replace the class name and table name
        $controllerContent = str_replace(
            [
                '{{className}}',
                '{{responseName}}',
                '{{plural}}',
                '{{singular}}',
                '{{variableName}}',
                '{{routeName}}',
                '{{codePrefix}}',
            ], 
            [
                $this->names['class_name'],
                $this->names['response_code'],
                Str::snake(Str::plural(strtolower($this->option('name')))),
                Str::snake(Str::singular(strtolower($this->option('name')))),
                Str::camel(Str::plural($this->option('name'))),
                $this->names['route'],
                $this->option('code'),
            ],  
            file_get_contents(__DIR__ . '/stubs/controller.stub') 
        );

        // Create a folder if it doesn't already exist
        if (!file_exists(getcwd() . '/app/Http/Controllers/API')) {
            mkdir(getcwd() . '/app/Http/Controllers/API');
        }

        // Then we write the contents to the file
        file_put_contents(getcwd() . '/app/Http/Controllers/API/' . $this->names['controller'] . 'Controller.php', $controllerContent);
        
        return true;
    }

    /**
     * Generate the Requests Files.
     *
     * @return int
     */
    public function createRequests()
    {        

        // We get the contents of the stub and replace the names
        $requestIndexContent = str_replace(['{{className}}','{{permissionName}}'], [$this->names['class_name'], $this->names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/index_request.stub') );
        $requestShowContent = str_replace(['{{className}}','{{permissionName}}'], [$this->names['class_name'], $this->names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/show_request.stub') );
        $requestStoreContent = str_replace(['{{className}}','{{permissionName}}'], [$this->names['class_name'], $this->names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/store_request.stub') );
        $requestUpdateContent = str_replace(['{{className}}','{{permissionName}}'], [$this->names['class_name'], $this->names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/update_request.stub') );
        $requestDestroyContent = str_replace(['{{className}}','{{permissionName}}'], [$this->names['class_name'], $this->names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/destroy_request.stub') );

        // Create a folder if it doesn't already exist
        if (!file_exists(getcwd() . '/app/Http/Requests')) {
            mkdir(getcwd() . '/app/Http/Requests');
        }

        // Then we write the contents to the files
        file_put_contents(getcwd() . '/app/Http/Requests/' . $this->names['request'] . 'Index.php', $requestIndexContent);
        file_put_contents(getcwd() . '/app/Http/Requests/' . $this->names['request'] . 'Show.php', $requestShowContent);
        file_put_contents(getcwd() . '/app/Http/Requests/' . $this->names['request'] . 'Store.php', $requestStoreContent);
        file_put_contents(getcwd() . '/app/Http/Requests/' . $this->names['request'] . 'Update.php', $requestUpdateContent);
        file_put_contents(getcwd() . '/app/Http/Requests/' . $this->names['request'] . 'Destroy.php', $requestDestroyContent);
        
        return true;
    }

    /**
     * Generate the Requests Files.
     *
     * @return int
     */
    public function createApiCodes()
    {        

        // Now lets append the API CODE to ApiCode.php
        // First we remove the last line of the file
        $apiCodeContent = str_replace('} // DO NOT PLACE ANY CODE BELOW THIS LINE OR CHANGE THIS COMMENT! THIS IS NEEDED FOR APPENDING NEW RESOURCE CODES', '', file_get_contents(getcwd() . '/app/ApiCode.php') );
        file_put_contents(getcwd() . '/app/ApiCode.php', $apiCodeContent);

        // Then we get the new append content
        $newApiCodeContent = str_replace(
            [
                '{{className}}',
                '{{responseCode}}',
                '{{codePrefix}}'
            ], 
            [
                strtoupper(Str::singular($this->option('name'))), 
                $this->names['response_code'], 
                $this->option('code')
            ],  
        file_get_contents(__DIR__ . '/stubs/api_code.stub') );

        file_put_contents(getcwd() . '/app/ApiCode.php', $newApiCodeContent, FILE_APPEND);
        
        return true;
    }

    /**
     * Generate the migration file.
     *
     * @return int
     */
    public function createMigration()
    {        
        // We get the contents of the stub and replace the class name and table name
        $migrationContent = str_replace(
            [
                '{{className}}',
                '{{tableName}}'
            ], 
            [
                $this->names['migration_name'], 
                $this->names['table_name']
            ],  
            file_get_contents(__DIR__ . '/stubs/migration.stub') 
        );

        // Then we write the contents to the file
        file_put_contents(getcwd() . '/database/migrations/' . date('Y_m_d_His') . '_create_' . $this->names['table_name'] . '_table.php', $migrationContent);
        
        return true;
    }

    /**
     * Generate the tests file.
     *
     * @return int
     */
    public function createTests()
    {        
        // We get the contents of the stub and replace the tags
        $testContent = str_replace(
            [
                '{{className}}',
                '{{routeName}}',
                '{{plural}}',
                '{{singular}}',
                '{{tableName}}',
                '{{codePrefix}}',
            ], 
            [
                $this->names['class_name'], 
                $this->names['route'], 
                Str::snake(Str::plural(strtolower($this->option('name')))),
                Str::snake(Str::singular(strtolower($this->option('name')))),
                $this->names['table_name'], 
                $this->option('code'), 
            ],  
            file_get_contents(__DIR__ . '/stubs/test.stub') 
        );

        // Create a folder if it doesn't already exist
        if (!file_exists(getcwd() . '/tests/Feature/API')) {
            mkdir(getcwd() . '/tests/Feature/API');
        }

        // Then we write the contents to the file
        file_put_contents(getcwd() . '/tests/Feature/API/' . $this->names['class_name'] . 'Test.php', $testContent);
        
        return true;
    }

    /**
     * Append the resource to the routes file.
     *
     * @return int
     */
    public function createRoute()
    {        
        // Define the line to append
        $routeContent = str_replace(
            [
                '{{routeName}}',
                '{{controllerName}}'
            ], 
            [
                $this->names['route'], 
                $this->names['controller']
            ],  
            file_get_contents(__DIR__ . '/stubs/routes.stub') 
        );

        // Then we write the contents to the file
        file_put_contents(getcwd() . '/routes/api.php', $routeContent, FILE_APPEND);
        
        return true;
    }

    /**
     * Generate the apidoc.json order array and append.
     *
     * @return int
     */
    public function addToApiDocJson()
    {        

        if (!file_exists(getcwd() . '/apidoc.json')) {
            $this->info('apidoc.json not found, skipping.');
            return true;
        }

        // First we get the file decode then rewrite
        $apiDocJsonContent = json_decode(file_get_contents(getcwd() . '/apidoc.json'));
        
        $appendContent = (array) array_merge( (array) $apiDocJsonContent->order, array( 
            $this->names['class_name'],
            $this->names['class_name'] . 'All',
            $this->names['class_name'] . 'Show',
            $this->names['class_name'] . 'Store',
            $this->names['class_name'] . 'Update',
            $this->names['class_name'] . 'Destroy',
        ) );
        $apiDocJsonContent->order = (array) $appendContent;
        file_put_contents(getcwd() . '/apidoc.json', json_encode($apiDocJsonContent));
        
        return true;
    }

}