<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('investor_leads')) {
            return;
        }

        DB::table('investor_leads')->orderBy('id')->chunkById(500, function ($leads): void {
            foreach ($leads as $lead) {
                $normalized = mb_strtolower(trim((string) $lead->email));
                if ($normalized !== $lead->email) {
                    DB::table('investor_leads')->where('id', $lead->id)->update(['email' => $normalized]);
                }
            }
        });

        DB::table('investor_leads')
            ->select('startup_funding_round_id', 'email', DB::raw('MIN(id) as keep_id'))
            ->groupBy('startup_funding_round_id', 'email')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($duplicate): void {
                DB::table('investor_leads')
                    ->where('startup_funding_round_id', $duplicate->startup_funding_round_id)
                    ->where('email', $duplicate->email)
                    ->where('id', '<>', $duplicate->keep_id)
                    ->delete();
            });

        if (! Schema::hasIndex('investor_leads', 'investor_leads_round_email_unique')) {
            Schema::table('investor_leads', function (Blueprint $table): void {
                $table->unique(
                    ['startup_funding_round_id', 'email'],
                    'investor_leads_round_email_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('investor_leads')
            && Schema::hasIndex('investor_leads', 'investor_leads_round_email_unique')
        ) {
            Schema::table('investor_leads', function (Blueprint $table): void {
                $table->dropUnique('investor_leads_round_email_unique');
            });
        }
    }
};
