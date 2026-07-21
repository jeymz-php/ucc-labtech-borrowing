<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowingItem extends Model
{
    use HasFactory;

    protected $fillable = ['borrowing_id','item_unit_id','condition_out','condition_in','remarks_out','remarks_in'];

    public function borrowing(): BelongsTo { return $this->belongsTo(Borrowing::class); }
    public function itemUnit(): BelongsTo { return $this->belongsTo(ItemUnit::class); }
}
