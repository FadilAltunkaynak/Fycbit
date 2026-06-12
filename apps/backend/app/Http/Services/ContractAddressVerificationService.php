<?php

namespace App\Http\Services;

use App\Dtos\EvmResponseDto;
use App\Exceptions\RpcNotSetException;
use App\Http\Services\Evm\EvmWalletService;
use Exception;

class ContractAddressVerificationService
{
    public function __construct(private EvmWalletService $evmWalletService)
    {
    }

    public function verifyContractAddress(int $baseType, string $contractAddress, ?string $rpc = null): EvmResponseDto
    {
        try {
            if (!$this->checkIfEvmSupportedToken($baseType)) {
                return new EvmResponseDto(
                    true,
                    '',
                    true
                );
            }

            if(!$rpc)
            {
                throw new RpcNotSetException('Rpc Not Found For This Network');
            }

            $response = $this->evmWalletService->validateContractAddress(
                $baseType,
                $contractAddress,
                $rpc
            );

            return new EvmResponseDto(
                $response['success'],
                $response['message'],
                $response['success']
            );

        } catch (RpcNotSetException $e) {
            throw $e;
        }
        catch (Exception $exception) {
            return new EvmResponseDto(
                false,
                $exception->getMessage(),
                false
            );
        }

    }

    private function checkIfEvmSupportedToken(int $baseType): bool
    {
        return in_array($baseType, [EVM_BASE_COIN, TRC20_TOKEN, SOLANA_BASE_COIN], true);
    }
}
