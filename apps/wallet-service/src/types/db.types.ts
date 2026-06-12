import { networksPayload } from "@prisma/client";
import { DefaultArgs, Types } from "@prisma/client/runtime";

export type NetworkData = Types.GetResult<networksPayload, DefaultArgs>