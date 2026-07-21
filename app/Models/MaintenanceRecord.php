<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_code','item_unit_id','borrowing_id','reported_by','assigned_to','completed_by',
        'status','priority','issue_title','issue_description','condition_before','condition_after',
        'diagnosis','repair_action','repair_cost','started_at','completed_at','completion_notes',
    ];

    protected $casts = [
        'repair_cost' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function itemUnit(): BelongsTo { return $this->belongsTo(ItemUnit::class); }
    public function borrowing(): BelongsTo { return $this->belongsTo(Borrowing::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function completer(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
}
