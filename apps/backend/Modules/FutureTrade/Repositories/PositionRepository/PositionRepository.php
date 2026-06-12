<?php

namespace Modules\FutureTrade\Repositories\PositionRepository;

use App\Http\Repositories\CommonRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\FutureTrade\Emum\PositionStatusEnum;
use Modules\FutureTrade\Entities\FuturePosition;
use Modules\FutureTrade\Repositories\PositionRepository\IPositionRepository;



class PositionRepository extends CommonRepository implements IPositionRepository
{
    public function __construct()
    {
        $this->model = FuturePosition::class;
    }

    #[\Override()]
    public function create($data)
    {
        return $this->model::create($data);
    }

    public static function getPositionBuilder(int $coin_pair_id): Builder
    {
        return FuturePosition::query()
            ->where('future_coin_pair_id', $coin_pair_id);
    }
    public static function getPositionBuilderByUid(string $position_uid): Builder
    {
        return FuturePosition::query()
            ->where('future_positions.uid', $position_uid);
    }

    public static function getPosition(int $coin_pair_id, int $user_id): ?FuturePosition
    {
        return self::getPositionBuilder($coin_pair_id)
            ->where('user_id', $user_id)
            ->first();
    }

    public static function getAllOpenPosition(int $user_id): Builder
    {
        return FuturePosition::query()
            ->where('user_id', $user_id)
            ->where('status', PositionStatusEnum::OPEN->value)
            ->where('amount','<>', 0);
    }


}
