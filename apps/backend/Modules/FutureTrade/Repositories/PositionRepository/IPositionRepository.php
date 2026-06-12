<?php

namespace Modules\FutureTrade\Repositories\PositionRepository;

use App\Http\Repositories\ICommonRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\FutureTrade\Entities\FuturePosition;

interface IPositionRepository extends ICommonRepository
{
    public static function getPosition(int $coin_pair_id, int $user_id): ?FuturePosition;
    public static function getAllOpenPosition(int $user_id): Builder;
}
