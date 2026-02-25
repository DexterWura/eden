<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('milestone_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('business_type', ['domain', 'website', 'social_media_account', 'mobile_app', 'desktop_app', 'all'])->default('all');
            $table->json('milestones')->comment('Array of milestone definitions with type, note, amount/percentage');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('business_type');
            $table->index('is_active');
        });

        // Seed default templates
        $this->seedDefaultTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestone_templates');
    }

    /**
     * Seed default milestone templates
     */
    private function seedDefaultTemplates()
    {
        $templates = [
            [
                'name' => 'Standard Domain Transfer',
                'description' => 'Standard milestones for domain-only sales',
                'business_type' => 'domain',
                'is_default' => true,
                'milestones' => [
                    ['type' => 'domain_transfer', 'note' => 'Domain transfer initiated and access provided', 'percentage' => 30],
                    ['type' => 'domain_verification', 'note' => 'Domain ownership verified by buyer', 'percentage' => 40],
                    ['type' => 'domain_complete', 'note' => 'Domain transfer completed successfully', 'percentage' => 30],
                ],
            ],
            [
                'name' => 'Website Business Transfer',
                'description' => 'Comprehensive milestones for website business sales',
                'business_type' => 'website',
                'is_default' => true,
                'milestones' => [
                    ['type' => 'account_access', 'note' => 'Hosting, domain registrar, and analytics accounts access provided', 'percentage' => 25],
                    ['type' => 'domain_transfer', 'note' => 'Domain transfer initiated', 'percentage' => 20],
                    ['type' => 'code_transfer', 'note' => 'Source code, databases, and files transferred', 'percentage' => 25],
                    ['type' => 'verification', 'note' => 'Buyer verifies all assets and functionality', 'percentage' => 20],
                    ['type' => 'training', 'note' => 'Knowledge transfer and training completed', 'percentage' => 10],
                ],
            ],
            [
                'name' => 'Social Media Account Transfer',
                'description' => 'Milestones for social media account sales',
                'business_type' => 'social_media_account',
                'is_default' => true,
                'milestones' => [
                    ['type' => 'account_access', 'note' => 'Social media account access provided', 'percentage' => 40],
                    ['type' => 'verification', 'note' => 'Buyer verifies account access and content', 'percentage' => 30],
                    ['type' => 'complete', 'note' => 'Account transfer completed and buyer has full control', 'percentage' => 30],
                ],
            ],
            [
                'name' => 'Mobile App Transfer',
                'description' => 'Milestones for mobile app business sales',
                'business_type' => 'mobile_app',
                'is_default' => true,
                'milestones' => [
                    ['type' => 'account_access', 'note' => 'App store accounts and developer accounts access provided', 'percentage' => 25],
                    ['type' => 'code_transfer', 'note' => 'Source code and assets transferred', 'percentage' => 30],
                    ['type' => 'verification', 'note' => 'Buyer verifies app functionality and assets', 'percentage' => 25],
                    ['type' => 'documentation', 'note' => 'Technical documentation and knowledge transfer', 'percentage' => 20],
                ],
            ],
            [
                'name' => 'Simple Transfer (All Types)',
                'description' => 'Simple 2-milestone template for quick transfers',
                'business_type' => 'all',
                'is_default' => false,
                'milestones' => [
                    ['type' => 'transfer', 'note' => 'Assets and access transferred', 'percentage' => 50],
                    ['type' => 'verification', 'note' => 'Buyer verifies and accepts transfer', 'percentage' => 50],
                ],
            ],
        ];

        foreach ($templates as $template) {
            \App\Models\MilestoneTemplate::create($template);
        }
    }
};

