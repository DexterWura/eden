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

        $act = 'DIRECT_ESCROW_ADMIN_ACTION';
        if (NotificationTemplate::where('act', $act)->exists()) {
            return;
        }

        $nt = new NotificationTemplate();
        $nt->act = $act;
        $nt->name = 'Direct Payment - Admin Escrow Action';
        $nt->subject = 'Admin updated escrow (Direct payment) - {{title}}';
        $nt->email_body = "Admin has taken action on a Direct-payment escrow.\n\nListing/Title: {{title}}\nPayment Mode: {{payment_mode}}\nSale amount (paid outside platform): {{external_amount}} {{currency}}\nEscrow service fee (paid to platform): {{escrow_service_fee}} {{currency}}\nTotal funded on platform: {{total_fund}} {{currency}}\n\nDistribution (admin decision):\n- Buyer receives: {{buyer_amount}} {{currency}}\n- Seller receives: {{seller_amount}} {{currency}}\n\nReference: {{trx}}";
        $nt->sms_body = 'Admin action on Direct-payment escrow: {{title}}. Sale: {{external_amount}} {{currency}}. Funded: {{total_fund}} {{currency}}. Ref: {{trx}}';
        $nt->push_title = 'Admin updated escrow (Direct payment)';
        $nt->push_body = 'Admin took action on {{title}}. Ref: {{trx}}';
        $nt->shortcodes = [
            'title' => 'Escrow title',
            'payment_mode' => 'Payment mode (direct/system)',
            'external_amount' => 'Sale amount paid outside platform',
            'escrow_service_fee' => 'Escrow service fee paid to platform (buyer charge)',
            'total_fund' => 'Total funded on platform',
            'buyer_amount' => 'Amount refunded/paid to buyer (admin decision)',
            'seller_amount' => 'Amount paid to seller (admin decision)',
            'trx' => 'Reference/transaction id',
            'currency' => 'Currency code',
        ];
        $nt->email_status = Status::ENABLE;
        $nt->sms_status = Status::DISABLE;
        $nt->push_status = Status::DISABLE;
        $nt->save();
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        NotificationTemplate::where('act', 'DIRECT_ESCROW_ADMIN_ACTION')->delete();
    }
};


