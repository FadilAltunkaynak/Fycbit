import { Request, Response } from "express";
import { errorResponse, successResponse } from "../utils/common";
import prisma from "../client";
import { BlockProcessorFactory } from "../block_processor/block.processor.factory";

const processSingleBlock = async (req: Request, res: Response) => {
    const request = req.body;
    if(!request.network_id) {
        return errorResponse(res, "network id is required");
    }

    if (!Number.isInteger(Number(request.network_id))) {
        return errorResponse(res, "network id must be an integer");
    }

    if(!request.block_number) {
        return errorResponse(res, "block number is required");
    }

    if (!Number.isInteger(Number(request.block_number))) {
        return errorResponse(res, "block number must be an integer");
    }

    const networkData = await prisma.networks.findFirst({
        where: {
            id: Number(request.network_id),
        },
    });
    console.log(networkData);
    if(!networkData) {
        return errorResponse(res, "network not found");
    }

    try {
        await new BlockProcessorFactory()
            .getBlockProcessor(networkData)
            .handleBlocks(networkData, request.block_number, request.block_number, false);
        return successResponse(res, "Successfully processed")
    } catch (err) {
        return errorResponse(res, err.message || "Something went wrong");
    }
}

export default {
    processSingleBlock,
}