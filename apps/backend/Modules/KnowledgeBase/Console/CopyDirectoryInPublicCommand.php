<?php

namespace Modules\KnowledgeBase\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;  

class CopyDirectoryInPublicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'copy-assets:knowledgebase';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
     * @return mixed
     */
    public function handle()
    {
        $source = base_path('Modules/KnowledgeBase/Assets');
        $destination = public_path('assets/modules');
        // Create the destination directory if it does not exist
        if (!File::exists($destination)) {
            File::makeDirectory($destination);
        }

        // Copy the directory
        File::copyDirectory($source, $destination);
              
    }
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
