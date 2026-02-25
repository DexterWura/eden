<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use GlobalStatus;

    protected $casts = [
        'approved_by_seller' => 'boolean',
        'approved_by_buyer' => 'boolean',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function escrow()
    {
        return $this->belongsTo(Escrow::class);
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopeUnFunded($query)
    {
        return $query->where('payment_status', Status::MILESTONE_UNFUNDED);
    }
    
    public function scopeFunded($query)
    {
        return $query->where('payment_status', Status::MILESTONE_FUNDED);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Check if milestone is ready for payment (approved and not funded)
     */
    public function isReadyForPayment()
    {
        return $this->approval_status === 'approved' 
            && $this->payment_status === Status::MILESTONE_UNFUNDED;
    }

    /**
     * Check if milestone can be paid by buyer
     */
    public function canBePaidBy($userId)
    {
        return $this->escrow->buyer_id == $userId 
            && $this->isReadyForPayment()
            && $this->escrow->status == Status::ESCROW_ACCEPTED;
    }
}
