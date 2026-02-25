<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilestoneTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'milestones' => 'array', // JSON array of milestone definitions
    ];

    /**
     * Get milestone templates for a business type
     */
    public static function getTemplatesForBusinessType($businessType)
    {
        // Check if table exists first
        if (!\Illuminate\Support\Facades\Schema::hasTable('milestone_templates')) {
            return collect([]);
        }
        
        $query = self::where('business_type', $businessType)
            ->where('is_active', true);
        
        // Only order by sort_order if column exists
        if (\Illuminate\Support\Facades\Schema::hasColumn('milestone_templates', 'sort_order')) {
            $query->orderBy('sort_order');
        } else {
            $query->orderBy('id');
        }
        
        return $query->get();
    }

    /**
     * Get default template for a business type
     */
    public static function getDefaultTemplate($businessType)
    {
        // Check if table exists first
        if (!\Illuminate\Support\Facades\Schema::hasTable('milestone_templates')) {
            return null;
        }
        
        return self::where('business_type', $businessType)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Generate milestones from template
     */
    public function generateMilestones($escrow, $totalAmount)
    {
        $milestones = [];
        $templateMilestones = $this->milestones ?? [];
        
        foreach ($templateMilestones as $index => $template) {
            $amount = isset($template['percentage']) 
                ? ($totalAmount * $template['percentage'] / 100)
                : ($template['amount'] ?? 0);
            
            $milestoneData = [
                'escrow_id' => $escrow->id,
                'user_id' => $escrow->seller_id,
                'requested_by' => 'seller',
                'milestone_type' => $template['type'] ?? null,
                'note' => $template['note'] ?? $template['title'] ?? '',
                'amount' => $amount,
                'approval_status' => 'pending',
                'approved_by_seller' => true, // Seller auto-approves their own proposal
                'approved_by_buyer' => false,
                'payment_status' => \App\Constants\Status::MILESTONE_UNFUNDED,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Only add sort_order if column exists
            if (\Illuminate\Support\Facades\Schema::hasColumn('milestones', 'sort_order')) {
                $milestoneData['sort_order'] = $index + 1;
            }
            
            $milestones[] = $milestoneData;
        }
        
        return $milestones;
    }
}

