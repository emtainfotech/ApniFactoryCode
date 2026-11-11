<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentPayoutItem extends Model
{
    use HasFactory;

    protected $table = 'agent_payout_items';

    protected $fillable = [
        'agent_payout_id',
        'agent_incentive_ledger_id',
        'active_ledger_lock',
        'parent_order_id',
        'eligible_order_sequence',
        'ledger_gross_amount',
        'adjustment_amount',
        'item_net_payable_amount',
    ];

    protected $casts = [
        'ledger_gross_amount'     => 'float',
        'adjustment_amount'       => 'float',
        'item_net_payable_amount' => 'float',
        'eligible_order_sequence' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function () {
            throw new \LogicException('Payout line items cannot be deleted; cancel or fail the payout batch instead to preserve audit history.');
        });
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AgentPayout::class, 'agent_payout_id');
    }

    public function ledgerEntry(): BelongsTo
    {
        return $this->belongsTo(AgentIncentiveLedger::class, 'agent_incentive_ledger_id');
    }

    public function parentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_order_id');
    }
}

