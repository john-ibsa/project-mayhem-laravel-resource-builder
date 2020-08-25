<?php

namespace ProjectMayhem\ResourceBuilder\Commands;

use Illuminate\Console\Command;

class BuildResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-builder:build {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fully built and tested API resources based on the Project Mayhem API standards.';

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
        $this->info('Building resource with name ' . $this->option('name'));

        $names = $this->generateNames($this->option('name'));

        $this->info($names);
        
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
        return strtolower($name);
    }
}
