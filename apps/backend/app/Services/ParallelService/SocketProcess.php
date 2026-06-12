<?php

namespace App\Services\ParallelService;

use Illuminate\Support\Facades\Log;

class SocketProcess
{
    private array $socket;
    public  array $sockets;

    /**
     * Summary of createSocket
     * @return array
     */
    public function createSocket(): array|bool
    {
        $this->socket = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );
        return $this->socket;
    }

    public function storeChildSocket(int $pid)
    {
        $this->sockets[$pid] = $this->socket[0];
    }

    /**
     * Summary of closeParentSocket
     * @return bool
     */
    public function closeParentSocket(): bool
    {
        return fclose($this->socket[1]);
    }

    /**
     * Summary of closeChildSocket
     * @return bool
     */
    public function closeChildSocket(): bool
    {
        return fclose($this->socket[0]);
    }

    /**
     * Summary of send
     * @param string $data
     * @return void
     */
    public function send(string $data): void
    {
        $this->closeChildSocket();

        $length = strlen($data);
        fwrite($this->socket[1], pack('N', $length));

        $written = 0;
        while ($written < $length) {
            $n = fwrite($this->socket[1], substr($data, $written));
            if ($n === false) {
                storeLog("Sending data failed", "error");
                exit(0);
            }
            $written += $n;
        }

        $this->closeParentSocket();
    }

    /**
     * Summary of receive
     * @return void
     */
    public function receive(array &$returnValues)
    {

        $returnValues = [];
        foreach ($this->sockets as $pid => $socket) {
            Log::info("start");
            $dataLength = fread($socket, 4);
            Log::info($dataLength);
            if (strlen($dataLength) !== 4) {
                storeLog("Receive data length not 4", "error");
                exit(0);
            }

            $length = unpack('N', $dataLength)[1];

            $received = '';
            while (strlen($received) < $length) {
                $chunk = fread($socket, $length - strlen($received));
                if ($chunk === false) {
                    storeLog("Receiving chunk data failed", "error");
                    exit(0);
                }
                $received .= $chunk;
            }

            if($receivedData = unserialize(base64_decode($received)))
                $returnValues[$receivedData["returnKey"]] = $receivedData['value'];

            fclose($socket);
            pcntl_waitpid($pid, $status);
        }
    }
}