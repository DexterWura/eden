<?php

use App\Constants\Status;
use App\Models\NotificationTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        $templates = [
            [
                'act' => 'DIRECT_ESCROW_SERVICE_FEE_PAID',
                'name' => 'Direct Payment - Escrow Service Fee Paid (Seller)',
                'subject' => 'Escrow service fee paid for {{listing_title}} ({{escrow_number}})',
                'email_body' => "Buyer {{buyer}} has paid the escrow service fee of {{fee_amount}} {{currency}} for \"{{listing_title}}\" (Escrow #{{escrow_number}}).\n\nThis is a Direct-payment escrow. The buyer will pay you {{sale_amount}} {{currency}} outside the platform using the external payment link.\n\nYou can chat with the buyer inside escrow to complete the handover.",
                'sms_body' => 'Escrow fee paid for {{listing_title}} ({{escrow_number}}). Buyer will pay you {{sale_amount}} {{currency}} outside platform.',
                'push_title' => 'Escrow service fee paid',
                'push_body' => 'Buyer paid escrow fee for {{listing_title}}. Direct-payment: sale paid outside platform.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'escrow_number' => 'Escrow number',
                    'buyer' => 'Buyer username',
                    'fee_amount' => 'Escrow service fee amount paid to platform',
                    'sale_amount' => 'Sale amount paid outside platform',
                    'currency' => 'Currency code',
                ],
            ],
            [
                'act' => 'DIRECT_ESCROW_MARKED_COMPLETE',
                'name' => 'Direct Payment - Escrow Marked Complete (Seller)',
                'subject' => 'Escrow marked complete ({{escrow_number}})',
                'email_body' => "The buyer has marked the escrow as complete for \"{{title}}\" (Escrow #{{escrow_number}}).\n\nThis was a Direct-payment escrow. The platform did not hold the sale amount. Please confirm you have received the external payment of {{sale_amount}} {{currency}}.\n\nEscrow service fee paid to platform: {{fee_amount}} {{currency}}.",
                'sms_body' => 'Buyer marked escrow complete ({{escrow_number}}). Direct-payment sale amount: {{sale_amount}} {{currency}}.',
                'push_title' => 'Escrow completed',
                'push_body' => 'Buyer marked escrow complete for {{title}} ({{escrow_number}}).',
                'shortcodes' => [
                    'title' => 'Escrow title',
                    'escrow_number' => 'Escrow number',
                    'sale_amount' => 'Sale amount paid outside platform',
                    'fee_amount' => 'Escrow service fee amount paid to platform',
                    'currency' => 'Currency code',
                ],
            ],
        ];

        foreach ($templates as $t) {
            $exists = NotificationTemplate::where('act', $t['act'])->exists();
            if ($exists) {
                continue;
            }

            $nt = new NotificationTemplate();
            $nt->act = $t['act'];
            $nt->name = $t['name'];
            $nt->subject = $t['subject'];
            $nt->email_body = $t['email_body'];
            $nt->sms_body = $t['sms_body'];
            $nt->push_title = $t['push_title'];
            $nt->push_body = $t['push_body'];
            $nt->shortcodes = $t['shortcodes'] ?? [];
            $nt->email_status = Status::ENABLE;
            $nt->sms_status = Status::DISABLE;
            $nt->push_status = Status::DISABLE;
            $nt->save();
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        NotificationTemplate::whereIn('act', [
            'DIRECT_ESCROW_SERVICE_FEE_PAID',
            'DIRECT_ESCROW_MARKED_COMPLETE',
        ])->delete();
    }
};


