<?php

namespace App\Services\ParallelService;

use Generator;
use Illuminate\Support\Facades\Redis;

class ProcessWatcher
{
    public function __construct(
        protected ParallelService $parallel
    ) {
        $this->listenForStopSignals();
        $this->listenForSignals();
    }

    /**
     * Keep running and Listen for incoming process signals
     *
     * @return never
     */
    public function waitForJob()
    {
        while (true) {
            /** @var RedisProcess $dataService */
            $dataService = $this->parallel->dataService;
            $hasAlive = $dataService->checkMasterProcessStatus();

            if (! $hasAlive) {
                $this->stopProcess();
            }

            sleep(60);
        }
    }

    /**
     * Listen for incoming process signals and fire process
     *
     * @return void
     */
    private function listenForStopSignals()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGUSR2, fn () => $this->stopProcess());
    }

    /**
     * Listen for incoming process signals and fire process
     *
     * @return void
     */
    private function listenForSignals()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGUSR1, fn () => $this->handle());
    }

    private function stopProcess()
    {
        Redis::connection('parallel_cache')->flushdb();
        exit();
    }

    // private function handle()
    // {
    //     /** @var RedisProcess $dataService */
    //     $dataService = $this->parallel->dataService;
    //     $dataService->cacheManager->put('process_watcher_running', true, 60);
    //     $ids = $this->parallel->dataService->getFireIds();
    //     $length = count($ids) - 1;

    //     for ($i = 0; $i <= $length; $i++) {
    //         $this->parallel->dataService->setCurrentFireId($ids[$i]);
    //         $this->parallel->fireProcess();
    //     }
    // }

    private function handle()
    {
        /** @var RedisProcess $dataService */
        $dataService = $this->parallel->dataService;
        $idGenerator = $this->getIds();

        while ($idGenerator) {
            $ids = $idGenerator->current();
            $length = count($ids) - 1;

            for ($i = 0; $i <= $length; $i++) {
                $parallel = new ParallelService;
                $parallel->createRedisProcess();
                $parallel->dataService->cacheManager->put('process_watcher_running', true, 60);
                $parallel->dataService->setCurrentFireId($ids[$i]);
                $parallel->fireProcess();
            }

            if (is_null($idGenerator->next())) {
                break;
            }
        }
    }

    private function getIds(): Generator {
        $isEmpty = true;
        while ($isEmpty) {
            $ids = $this->parallel->dataService->getFireIds();

            if (count($ids) > 0) {
                $isEmpty = false;
            }

            yield $ids;
        }
    }
}
