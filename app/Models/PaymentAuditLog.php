<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAuditLog extends Model
{
    use HasFactory;

    protected $table = 'payment_audit_logs';

    protected $fillable = [
        'transection_id',
        'order_id',
        'user_id',
        'actor_name',
        'actor_role',
        'action',
        'previous_status',
        'new_status',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload'    => 'array',
        'created_at' => 'datetime',
    ];

    public function transection()
    {
        return $this->belongsTo(Transection::class, 'transection_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record an immutable payment audit log.
     */
    public static function logAction(
        int $transectionId,
        int $orderId,
        string $action,
        ?string $previousStatus = null,
        ?string $newStatus = null,
        ?array $payload = null,
        ?int $userId = null,
        string $actorName = 'System',
        string $actorRole = 'gateway'
    ): self {
        return self::create([
            'transection_id'  => $transectionId,
            'order_id'        => $orderId,
            'user_id'         => $userId,
            'actor_name'      => $actorName,
            'actor_role'      => $actorRole,
            'action'          => $action,
            'previous_status' => $previousStatus,
            'new_status'      => $newStatus,
            'payload'         => $payload,
            'ip_address'      => request()->ip() ?? '127.0.0.1',
        ]);
    }
}

