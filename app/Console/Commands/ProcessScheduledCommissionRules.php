<?php

namespace App\Console\Commands;

use App\Models\CommissionAuditLog;
use App\Models\CommissionRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCommissionRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activates scheduled commission rules whose effective_from timestamp has arrived.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rules = CommissionRule::where('status', 'scheduled')
            ->where('effective_from', '<=', now())
            ->get();

        if ($rules->isEmpty()) {
            $this->info('No scheduled commission rules to activate.');
            return 0;
        }

        $this->info("Activating {$rules->count()} scheduled commission rule(s)...");

        foreach ($rules as $rule) {
            $prev = $rule->toArray();
            $rule->update(['status' => 'active']);

            CommissionAuditLog::create([
                'commission_rule_id' => $rule->id,
                'actor_user_id'      => null,
                'event_type'         => 'activated',
                'previous_values'    => $prev,
                'new_values'         => $rule->fresh()->toArray(),
                'notes'              => 'Scheduled activation by automated cron runner.',
            ]);

            $this->info("✓ Activated rule [{$rule->rule_code}] ({$rule->name})");
        }

        return 0;
    }
}

