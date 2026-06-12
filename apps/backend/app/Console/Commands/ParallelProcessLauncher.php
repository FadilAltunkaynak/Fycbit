<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\ParallelService\ProcessWatcher;
use App\Services\ParallelService\ParallelService;

class ParallelProcessLauncher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parallel:process {payload?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will execute multiple closures in parallel process';

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
    public function handle(ParallelService $parallelService): int
    {
        if(
            $this->hasArgument('payload')
            && $payload = $this->argument('payload')
        ){
            $parallelService->setPayload($payload);
            $parallelService->createSocketProcess();
            $parallelService->process();
            $data = $parallelService->getValue();
            echo base64_encode(serialize($data));
            return 1;
        }

        $parallelService->createRedisProcess();
        $parallelService->dataService->storeMasterProcessId();
        $worker = new ProcessWatcher($parallelService);
        $worker->waitForJob();
        return 1;
    }
}
