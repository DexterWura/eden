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

        $act = 'NEW_LISTING_ALERT';
        if (NotificationTemplate::where('act', $act)->exists()) {
            return;
        }

        $nt = new NotificationTemplate();
        $nt->act = $act;
        $nt->name = 'New listing alert';
        $nt->subject = 'New business listed: {{listing_title}}';
        $nt->email_body = "Hi {{fullname}},\n\nA new online business has just been listed on {{site_name}}.\n\n{{tagline}}\n\nType: {{business_type}}\nCategory: {{category_name}}\n{{price}}\n\nCheck it out: {{listing_url}}\n\nListing #{{listing_number}}\n\n— {{site_name}}";
        $nt->sms_body = 'New listing: {{listing_title}}. {{listing_url}}';
        $nt->push_title = 'New business listed';
        $nt->push_body = '{{listing_title}} - {{category_name}}. View: {{listing_url}}';
        $nt->shortcodes = [
            'listing_title' => 'Listing title',
            'listing_url' => 'URL to view the listing',
            'listing_number' => 'Listing number',
            'category_name' => 'Category name',
            'tagline' => 'Listing tagline',
            'price' => 'Price or starting bid (e.g. Asking: $X or Starting bid: $X)',
            'business_type' => 'Business type (e.g. Social media account, Website)',
            'fullname' => 'Recipient full name',
            'site_name' => 'Site name',
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

        NotificationTemplate::where('act', 'NEW_LISTING_ALERT')->delete();
    }
};
