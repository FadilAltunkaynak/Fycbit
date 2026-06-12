<?php

namespace Modules\FutureTrade\Console;

use App\User;
use Illuminate\Console\Command;
use Modules\FutureTrade\Services\BotService\BotService;

class FutureBotRunnerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'future:bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Keep running future bot orders for active future coin pairs.';

    protected $signature = 'future:bot
                            {--sleep=1 : Sleep time in seconds between cycles}
                            {--amount= : Optional fixed order amount for all pairs}
                            {--provider= : Optional ticker provider}
                            {--once : Run only one cycle and stop}';

    public function handle(BotService $botService)
    {
        $userId = get_super_admin_id();
        $sleep = (int) $this->option('sleep');
        $amount = $this->option('amount');
        $provider = $this->option('provider');
        $once = (bool) $this->option('once');
        $cleanupIntervalSeconds = 60 * 60;
        $lastCleanupAt = now();

        if ($userId <= 0) {
            $this->error(__('Invalid --user-id value'));
            return 1;
        }

        if ($sleep < 1) {
            $sleep = 1;
        }

        $amountValue = null;
        if ($amount !== null && $amount !== '') {
            $amountValue = (float) $amount;
            if ($amountValue <= 0) {
                $this->error(__('Invalid --amount value'));
                return 1;
            }
        }

        $this->info(__('Future bot runner started'));
        $this->line(__('user_id: :id, sleep: :sleep sec, once: :once', [
            'id' => $userId,
            'sleep' => $sleep,
            'once' => $once ? 'yes' : 'no',
        ]));

        while (true) {
            $startedAt = now();

            try {
                $result = $botService->createOrdersForAllActivePairs(
                    user_id: $userId,
                    amount: $amountValue,
                    provider: $provider ?: null
                );

                $this->line(sprintf(
                    '[%s] total:%d processed:%d failed:%d buy:%d sell:%d',
                    $startedAt->format('Y-m-d H:i:s'),
                    (int) ($result['total_pairs'] ?? 0),
                    (int) ($result['processed_pairs'] ?? 0),
                    (int) ($result['failed_pairs'] ?? 0),
                    (int) ($result['buy_orders'] ?? 0),
                    (int) ($result['sell_orders'] ?? 0),
                ));
            } catch (\Throwable $th) {
                $this->error(__('Future bot runner cycle failed: :msg', ['msg' => $th->getMessage()]));
                storeException('futureBotRunnerCommand', $th->getMessage());
            }

            if (! $once && $lastCleanupAt->diffInSeconds(now()) >= $cleanupIntervalSeconds) {
                $this->call('future:clean');
                $lastCleanupAt = now();
            }

            if ($once) {
                break;
            }

            sleep($sleep);
        }

        $this->info(__('Future bot runner stopped'));
        return 0;
    }
}
