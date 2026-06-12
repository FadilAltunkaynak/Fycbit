<?php

declare(ticks=1);

namespace App\Services\ParallelService;

use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Laravel\SerializableClosure\SerializableClosure;
use Throwable;

class ParallelService implements ParallelServiceContract
{
    private array $childrenSockets = [];

    private array $closures = [];

    private array $returnValues = [];

    private array $classMethods = [];

    public SocketProcess|RedisProcess $dataService;

    public function __construct() {}

    public function instance(): ParallelService
    {
        return new ParallelService;
    }

    public function createRedisProcess(?int $processId = null)
    {
        $this->dataService = new RedisProcess();
        if($processId){
            $this->dataService->setCurrentFireId($processId);
        }
    }

    public function createSocketProcess()
    {
        $this->dataService = new SocketProcess;
    }

    public function createSocket(): array|bool
    {
        return stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );
    }

    public function add(string $returnValueKey, Closure $callback): ParallelService
    {
        if (
            array_key_exists(
                $returnValueKey,
                $this->returnValues['closures'] ?? []
            )
        ) {
            return $this;
        }

        $this->returnValues['closures'][$returnValueKey] = null;
        $this->closures[] = $callback;

        return $this;
    }

    public function addClassMethod(string $returnValueKey, string $class, string $method, ...$params): self
    {
        if (
            array_key_exists(
                $returnValueKey,
                $this->returnValues['classMethods'] ?? []
            )
        ) {
            return $this;
        }

        $this->returnValues['classMethods'][$returnValueKey] = null;
        $this->classMethods[] = [$class, $method, $params];

        return $this;
    }

    public function setPayload(string $closuresAndReturnValueKeys): self
    {
        $closuresAndReturnValueKeys = unserialize(base64_decode($closuresAndReturnValueKeys));
        $this->closures = $closuresAndReturnValueKeys['closures'] ?? [];
        $this->returnValues = $closuresAndReturnValueKeys['returnValueKeys'] ?? [];
        $this->classMethods = $closuresAndReturnValueKeys['classMethods'] ?? [];

        return $this;
    }

    public function getValue(): array
    {
        return $this->returnValues;
    }

    public function fireCommand(): mixed
    {
        if (! $this->returnValues) {
            return null;
        }

        $serializePayload = $this->serializePayload();
        exec("cd ../ && php artisan parallel:process \"$serializePayload\"", $output, $returnStatus);

        if ($returnStatus === 1 && isset($output[0])) {
            return unserialize(base64_decode($output[0]));
        }

        return null;
    }

    public function async(Closure $callback): mixed
    {
        $this->add('', $callback);

        return $this->asyncFire();
    }

    public function asyncFire(): mixed
    {
        $this->createRedisProcess();
        if (! $this->returnValues) {
            return null;
        }
        if (! $this->dataService->checkMasterProcessStatus()) {
            return $this->offlineProcess();
        }

        $processId = random_int(10000, 99999);
        $this->dataService->async = true;
        $this->dataService->storeFireId($processId);
        $this->dataService->setCurrentFireId($processId);
        $serializePayload = $this->serializePayload();
        $this->dataService->storePayload($serializePayload);

        $this->dataService->pingSignal();

        return null;
    }

    public function wait(?int $processId = null): array
    {
        $ms = 10_000;
        if (! $processId ??= $this->fire()) {
            return [];
        }
        $this->createRedisProcess($processId);
        while ($this->dataService->hasProperty($processId, 'status')) {
            usleep(10);
            $ms -= 10;
            if ($ms <= 0) {
                break;
            }
        }
        $this->collectReturnValue();
        $returnValue = $this->getValue();
        $this->dataService->destroyFireId(id: $processId);

        return $returnValue;
    }

    public function fire(): int|array
    {
        $this->createRedisProcess();
        if (! $this->returnValues) {
            return 0;
        }
        if (! $this->dataService->checkMasterProcessStatus()) {
            return $this->offlineProcess();
        }

        $processId = random_int(10000, 99999);
        $this->dataService->storeFireId($processId);
        $this->dataService->setCurrentFireId($processId);
        $serializePayload = $this->serializePayload();
        $this->dataService->storePayload($serializePayload);

        $this->dataService->pingSignal();

        return $processId;
    }

    public function fireProcess()
    {
        $payload = $this->dataService->returnPayload();
        if ('pending' !== ($payload['status'] ?? '')) {
            return;
        }

        $serializePayload = $payload['payload'];
        $this->dataService->async = $payload['async'] ?? false;
        $this->setPayload($serializePayload);

        $this->process();
    }

    private function serializePayload()
    {
        if (! $this->returnValues) {
            return null;
        }

        $serializePassableArgument['returnValueKeys'] = $this->returnValues;

        if ($this->closures) {
            $closures = array_map(function ($closure) {
                $serialize = rescue(fn () => serialize($closure), null, false);
                if ($serialize) {
                    return $serialize;
                }

                return new SerializableClosure($closure);
            }, $this->closures);
            $serializePassableArgument['closures'] = $closures;
        }

        if ($this->classMethods) {
            $serializePassableArgument['classMethods'] = $this->classMethods;
        }

        return base64_encode(serialize($serializePassableArgument));
    }

    public function process(bool $parallelProcess = true)
    {
        $payloads = [];
        if ($this->closures) {
            $payloads['closures'] = $this->closures;
        }

        if ($this->classMethods) {
            $payloads['classMethods'] = $this->classMethods;
        }

        $masterProcess = false;

        foreach ($payloads as $key => $value) {
            $returnValueKeys = match ($key) {
                'closures' => array_keys($this->returnValues['closures']),
                'classMethods' => array_keys($this->returnValues['classMethods'])
            };

            $length = count($value) - 1;
            for ($i = 0; $i <= $length; $i++) {
                $returnValueKey = $returnValueKeys[$i];
                $processAble = match ($key) {
                    'closures' => $value[$i]->getClosure(),
                    'classMethods' => $value[$i]
                };

                match (true) {
                    $parallelProcess => $this->parallelProcessExecution(
                        returnValueKey: $returnValueKey,
                        processAble   : $processAble,
                        key           : $key,
                        masterProcess : $masterProcess
                    ),
                    default => $this->singleProcessExecution()
                };
            }
        }

        if ($parallelProcess) {
            if ($masterProcess && $this->dataService instanceof SocketProcess) {
                $this->collectReturnValue();
            } else {
                $this->waitCloneProcess();
            }
        }
    }

    private function childProcess(mixed $processAble, string $returnValueKey, string $processType)
    {
        if ($processType == 'closures') {
            $this->processCallable(
                callable      : $processAble,
                returnValueKey: $returnValueKey
            );
        }

        if ($processType == 'classMethods') {
            $this->processClassMethod(
                returnValueKey: $returnValueKey,
                class         : $processAble[0],
                method        : $processAble[1],
                params        : $processAble[2],
            );
        }

        exit(0);
    }

    private function processClassMethod(string $returnValueKey, string $class, string $method, array $params)
    {
        $this->processAndSendResult(
            callable      : fn () => app($class)->{$method}(...$params),
            returnValueKey: $returnValueKey
        );
        exit(0);
    }

    private function openCloneProcess(): int
    {
        return pcntl_fork();
    }

    private function waitCloneProcess(): void
    {
        // return pcntl_wait($status);
        foreach ($this->dataService->getChildSockets() as $pid) {
            pcntl_waitpid($pid, $status);
            $this->dataService->forgetPayload($this->dataService->getFireId());
        }

    }

    private function processCallable(Closure $callable, string $returnValueKey)
    {
        $this->processAndSendResult(
            callable      : $callable,
            returnValueKey: $returnValueKey
        );
        exit(0);
    }

    private function processAndSendResult(Closure $callable, string $returnValueKey): mixed
    {
        try {
            $value = serialize([
                'returnKey' => $returnValueKey,
                'value' => $callable(),
            ]);

            $data = base64_encode($value);
            $this->dataService->send($data);
            exit(0);
        } catch (Exception $e) {
            storeLog(processExceptionMsg($e), 'error');
            exit(0);
        }
    }

    private function collectReturnValue()
    {
        $this->dataService->receive($this->returnValues);
    }

    private function singleProcessExecution() {}

    private function parallelProcessExecution(string $returnValueKey, mixed $processAble, string $key, bool &$masterProcess)
    {
        if ($this->dataService instanceof SocketProcess) {
            $this->dataService->createSocket();
        }

        $pid = $this->openCloneProcess();
        rescue(fn()=> DB::disconnect(), null, false);
        DB::reconnect();
        if ($pid == -1) {
            throw new Exception('Failed to create a process fork');
        } elseif ($pid === 0) {
            try {
                $this->childProcess(
                    processAble   : $processAble,
                    returnValueKey: $returnValueKey,
                    processType   : $key
                );
            } catch (Throwable $e) {
            }
            exit(0);
        } else {
            $masterProcess = true;
            if ($this->dataService instanceof SocketProcess) {
                $this->dataService->closeParentSocket();
            }
            $this->dataService->storeChildSocket($pid);
        }
    }

    private function offlineProcess()
    {
        $response = [];
        foreach ($this->closures as $closure) {
            $response[] = $closure();
        }

        return $response;
    }
}
