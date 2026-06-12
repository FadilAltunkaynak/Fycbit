import { errorResponse, processException, successResponse } from "../utils/common";
import { Request, Response } from "express";

import BigNumber from "bignumber.js";
import Web3 from "web3";
import {
    createAddress,
    createSystemAddress,
    walletWithdrawalService,
    adminAcceptPendingWithdrawal,
    validateToken,
    createWalletAddressService,
} from "../services/wallet.service";
import { generateErrorResponse, generateSuccessResponse } from "../utils/commonObject";
import { EVM_BASE_COIN, SOLANA_BASE_COIN, TRON_BASE_COIN } from "../utils/coreConstant";
import { processError } from "../utils/helper";

const createWallet = async (req: Request, res: Response) => {
    try {
        const wallet: any = await createAddress(req.user, req.body.coin_type, req.body.network);
        if (wallet.success) {
            return successResponse(res, wallet.message, wallet.data);
        } else {
            return errorResponse(res, wallet.message, wallet.data);
        }
    } catch (err) {
        processException(res, err);
    }
};

const createWalletAddress = async (req: Request, res: Response) => {
    try {
        const wallet: any = await createWalletAddressService(Number(req.body.network));
        if (wallet.success) {
            return successResponse(res, wallet.message, wallet.data);
        } else {
            return errorResponse(res, wallet.message, wallet.data);
        }
    } catch (err) {
        processException(res, err);
    }
};

const createSystemWallet = async (req: Request, res: Response) => {
    try {
        const wallet: any = await createSystemAddress(req.user, req.body.network);
        if (wallet.success) {
            return successResponse(res, wallet.message, wallet.data);
        } else {
            return errorResponse(res, wallet.message, wallet.data);
        }
    } catch (err) {
        processException(res, err);
    }
};

const convertCoinAmountToInt = (amount: number, decimal: number = 18): string => {
    // return (amount*powerOfTen(decimal)).toString()
    const isDecimal = !Number.isInteger(amount);
    if (isDecimal) {
        const tokenDecimals: BigNumber = new BigNumber(10).pow(decimal);
        const tokenToSend: BigNumber = new BigNumber(10).times(tokenDecimals);
        // @ts-ignore: Object is possibly 'null'
        return tokenToSend.toString();
    } else {
        const amountData = Web3.utils
            // @ts-ignore: Object is possibly 'null'
            .toBN(amount)
            .mul(Web3.utils.toBN(10).pow(Web3.utils.toBN(decimal)));
        return amountData.toString();
    }
};

const walletWithdrawalProcess = async (req: Request, res: Response) => {
    let request = req.body;
    request.user = req.user;
    let response = await walletWithdrawalService(request);
    if (response.success) {
        return successResponse(res, response?.message);
    } else {
        return errorResponse(res, response?.message);
    }
};

const withdrawalExternalApproval = async (req: Request, res: Response) => {
    let request = req.body;
    let response = await adminAcceptPendingWithdrawal(request);

    if (response.success) {
        return successResponse(res, response?.message, response?.data);
    } else {
        return errorResponse(res, response?.message);
    }
};

const checkContractAddressValidity = async (req: Request, res: Response) => {
    try {
        const contractAddress = req.body.contract_address;
        const rpcUrl = req.body.rpc_url;
        const baseType = Number(req.body.base_type);

        if (!contractAddress) {
            return errorResponse(res, "Contract address is required");
        }

        if (!rpcUrl) {
            return errorResponse(res, "Rpc not found for this network");
        }

        if (!baseType) {
            return errorResponse(res, "Base type is required");
        }

        const supportedBaseTypes = [SOLANA_BASE_COIN, EVM_BASE_COIN, TRON_BASE_COIN];

        if (!supportedBaseTypes.includes(baseType)) {
            return errorResponse(res, `Base type ${baseType} not supported yet.`);
        }
        const result = await validateToken(contractAddress, baseType, rpcUrl);
        return result.success ? successResponse(res, "Valid address") : errorResponse(res, result.message);
    } catch (error: any) {
        return processError(error, res);
    }
};

export default {
    createWallet,
    createWalletAddress,
    createSystemWallet,
    walletWithdrawalProcess,
    withdrawalExternalApproval,
    checkContractAddressValidity,
};
