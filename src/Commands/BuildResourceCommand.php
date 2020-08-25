<?php

namespace ProjectMayhem\ResourceBuilder\Commands;

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
    protected $description = 'Generate fully built and tested API resources based on the Project Mayhem API standards.';

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
        $this->info('Starting the build...');
        $this->line(' ');

        // Let's start by generating all the required class, route and resource names
        $names = $this->generateNames($this->option('name'));

        // Does ApiCode.php already exist
        $apiCodeFileExists = $this->checkApiCodeFile();

        // Does config/response_builder already exist
        $responseBuilderConfigFileExists = $this->checkResponseBuilderConfigFile();

        // This is the base Laravel App Path
        $baseLaravelPath = getcwd();

        // If the ApiCode does not exist let's create it
        if (!$apiCodeFileExists) {
            $this->info('Creating ApiCode.php...');
            file_put_contents($baseLaravelPath . '/app/ApiCode.php', file_get_contents(__DIR__ . '/stubs/api_code_base.stub'));
        }
        
        // If the ApiCode does not exist let's create it
        if (!$responseBuilderConfigFileExists) {
            $this->info('Creating config/response_builder.php...');
            file_put_contents($baseLaravelPath . '/config/response_builder.php', file_get_contents(__DIR__ . '/stubs/response_builder_config.stub'));
        }

        $this->comment('ApiCode Prefix: ' . $this->getNextApiCode());
        $this->line(' ');

        // Create the Model
        $this->createModel();

        // Create the Controller
        $this->createController();

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
        $names['permissions_name']  = str_replace(' ','.', Str::lower((Str::singular($this->option('name')))));
        $names['model']             = str_replace(' ','', ucwords(Str::singular($this->option('name'))));
        $names['route']             = Str::kebab(Str::plural(strtolower($this->option('name'))));
        $names['controller']        = Str::studly(Str::singular($this->option('name')));
        $names['request']           = Str::studly(Str::singular($this->option('name')));
        $names['table_name']        = Str::snake(Str::plural($this->option('name')));
        $names['response_code']     = Str::upper(Str::snake(Str::singular($this->option('name'))));

        foreach ($names as $k => $v) {
            $this->comment(str_replace('_',' ', ucwords($k)) . ': ' . $v);
        }

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
    public function getNextApiCode()
    {        
        return $this->option('code');
    }

    /**
     * Generate the required class names, singulars and plurals.
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
     * Generate the required class names, singulars and plurals.
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
                $this->names['response_code'],
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
        file_put_contents(getcwd() . '/app/Http/Controllers/API/' . $this->names['controller'] . '.php', $controllerContent);
        
        return true;
    }

}


// // CREATE MODEL
// // We get the contents of the stub and replace the class name
// $modelContent = str_replace(['{{className}}','{{tableName}}'], [$names['class_name'], $names['table']],  file_get_contents(__DIR__ . '/stubs/model.stub') );

// // Then we write the contents to the file
// file_put_contents($basePath . '/app/Models/' . $names['controller'] . '.php', $modelContent);

// // CREATE CONTROLLER
// // We get the contents of the stub and replace the class name
// $controllerContent = str_replace(
//     [
//         '{{className}}',
//         '{{responseName}}',
//         '{{plural}}',
//         '{{singular}}',
//         '{{variableName}}',
//         '{{routeName}}',
//         '{{codePrefix}}',
//     ], 
//     [
//         $names['response_code'],
//         $names['response_code'],
//         Str::snake(Str::plural(strtolower($this->option('name')))),
//         Str::snake(Str::singular(strtolower($this->option('name')))),
//         Str::camel(Str::plural($this->option('name'))),
//         $names['route'],
//         $codePrefix,
//     ],  
// file_get_contents(__DIR__ . '/stubs/controller.stub') );

// // Then we write the contents to the file
// file_put_contents($basePath . '/app/Http/Controllers/API/' . $names['controller'] . 'Controller.php', $controllerContent);

// // CREATE REQUESTS
// // We get the contents of the stub and replace the method name
// $requestStoreContent = str_replace(['{{className}}','{{permissionName}}'], [$names['class_name'], $names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/store_request.stub') );
// $requestUpdateContent = str_replace(['{{className}}','{{permissionName}}'], [$names['class_name'], $names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/update_request.stub') );
// $requestDestroyContent = str_replace(['{{className}}','{{permissionName}}'], [$names['class_name'], $names['permissions_name']],  file_get_contents(__DIR__ . '/stubs/destroy_request.stub') );

// // Then we write the contents to the file
// file_put_contents($basePath . '/app/Http/Requests/' . $names['request'] . 'Store.php', $requestStoreContent);
// file_put_contents($basePath . '/app/Http/Requests/' . $names['request'] . 'Update.php', $requestUpdateContent);
// file_put_contents($basePath . '/app/Http/Requests/' . $names['request'] . 'Destroy.php', $requestDestroyContent);

// // Now lets append the API CODE to ApiCode.php
// $apiCodeContent = str_replace('}', '', file_get_contents($basePath . '/app/ApiCode.php') );
// file_put_contents($basePath . '/app/ApiCode.php', $apiCodeContent);

// $newApiCodeContent = str_replace(
//     [
//         '{{className}}',
//         '{{responseCode}}',
//         '{{codePrefix}}'
//     ], 
//     [
//         strtoupper(Str::singular($this->option('name'))), 
//         $names['response_code'], 
//         $codePrefix
//     ],  
// file_get_contents(__DIR__ . '/stubs/api_code.stub') );

// file_put_contents($basePath . '/app/ApiCode.php', $newApiCodeContent, FILE_APPEND);

// $this->artisan('make:migration create_' . $names['table'] . '_table');

// // Now let's output the code that needs to be copied
// $this->line( '---------- COPY THE FOLLOWING INTO routes/api.php --------------' );
// $this->info( 'Route::resource(\'' . $names['route'] . '\', \'API\\' . $names['controller'] . 'Controller\');' );
// $this->info('');

// return true;