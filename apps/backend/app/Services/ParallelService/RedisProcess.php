<?php

namespace App\Services\ParallelService;

use Illuminate\Cache\RedisStore;
use Illuminate\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class RedisProcess
{
    use RedisPayloadManager;

    private int $fireID;
    private array $childProcessIds;
    public bool $async = false;
    public $cacheManager;

    public function __construct()
    {
        $this->cacheManager = cache()->store('parallel_cache_store');
    }

    public function checkMasterProcessStatus(): bool
    {
        $master = $this->cacheManager->has("alive_parallel_process_id");
        if(! $master) {
            $runningWatcher = $this->cacheManager->has("process_watcher_running");
            if(! $runningWatcher) {
                Redis::connection('parallel_cache')->flushdb();
            }
        }
        return $master;
    }

    public function storeMasterProcessId(): bool
    {
        return $this->cacheManager->put("alive_parallel_process_id", getmypid(), 60 * 30);
    }

    public function storeChildSocket(int $pid)
    {
        $this->childProcessIds[] = $pid;
    }
    
    public function getChildSockets(): array
    {
        return $this->childProcessIds;
    }

    public function getMasterPid(): mixed
    {
        return $this->cacheManager->get("alive_parallel_process_id");
    }

    public function storeFireId(int $id): bool
    {
        // return $this->cacheManager->put("parallel_process_id", $id);
        return $this->addProcessIdSet($id);
    }

    public function setCurrentFireId(int $id): void
    {
        $this->fireID = $id;
    }

    public  function getFireIds(): array
    {
        return $this->getProcessIdSet();
    }

    public function getFireId(): mixed
    {
        // return $this->cacheManager->get("parallel_process_id");
        return $this->fireID;
    }

    public function destroyFireId(int $id): bool
    {
        // return $this->cacheManager->forget("parallel_process_id");
        return $this->removeProcessIdSet($id);
    }

    public function storePayload(string $payload): bool
    {
        return $this->setRedisPayload(
        $this->getFireId(),
        [
            "payload" => $payload,
            "status"  => "pending",
            "async"   => $this->async,
            "time"    => Carbon::now()->timestamp
        ]);
    }

    public function returnPayload(): array
    {
        /** @var string $fireID */
        $fireID = $this->getFireId();
        return $this->getPayload($fireID);
    }

    public function pingSignal(): void
    {
        posix_kill($this->getMasterPid(), SIGUSR1);
    }

    public function checkProcessStatus(): bool
    {
        return 
            $this->hasProperty($this->getFireId(), "status")
            && $this->getPayloadProperty($this->getFireId(), "status") !== "complete";
    }

    public function send(string $data): void
    {
        if($this->async){
            $this->destroyFireId($this->getFireId());
            $this->forgetPayload($this->getFireId());
            return;
        }
        $this->setPayloadProperty(
            $this->getFireId(),
            "status", "complete"
        );
        $this->addChildProcessResult($this->getFireId(), $data);
    }

    public function receive(array &$returnValues)
    {
        $results = $this->getChildProcessResult($this->getFireId());
        $returnValues = [];
        foreach($results as $result){
            if($receivedData = unserialize(base64_decode($result)))
            $returnValues[$receivedData["returnKey"]] = $receivedData['value'];
        }

        $this->forgetPayload($this->getFireId());
    }
}