import prisma from "../../client";

export type CoinType = {
    coinId: number;
    coinType: string;
}

const getContractAddressMaps = async (networkId: number): Promise<{
    coin_id_map: Map<string, CoinType>, decmial_map: Map<string, number>
}> => {
    let map: Map<string, CoinType> = new Map<string, CoinType>();
    let decimalMap: Map<string, number> = new Map<string, number>();

    const coinNetworkData = await prisma.$queryRaw<
        { contract_address: string; currency_id: number; coin_type: string, coin_decimal: number }[]
    >`
    SELECT 
      cn.contract_address, 
      cn.coin_decimal, 
      cn.currency_id, 
      c.coin_type 
    FROM 
      coin_networks AS cn 
    JOIN 
      coins AS c 
    ON 
      cn.currency_id = c.id 
    WHERE 
      cn.network_id = ${networkId};
  `;

    for (const data of coinNetworkData) {
        const coinIdAndType: CoinType = {
            coinId: Number(data.currency_id),
            coinType: data.coin_type,
        }

        if (data.contract_address && !map.has(data.contract_address)) {
            decimalMap.set(data.contract_address.toLowerCase(), Number(data.coin_decimal));
            map.set(data.contract_address.toLowerCase(), coinIdAndType);
        } else {
            decimalMap.set('native', Number(data.coin_decimal));
            map.set('native', coinIdAndType);
        }
    }

    return { coin_id_map: map, decmial_map: decimalMap };
}

export {
    getContractAddressMaps,
}