<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseEncryptionService;
use App\Services\EnvatoVerificationService;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command to manually verify license.
 * 
 * This command can be run via cron or manually to verify
 * the license status with Envato API.
 */
class VerifyLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:verify 
                            {--force : Force verification even if recently checked}
                            {--quiet : Suppress output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify license with Envato API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $licenseEncryptionService = app(LicenseEncryptionService::class);
            
            if (!$licenseEncryptionService->licenseExists()) {
                if (!$this->option('quiet')) {
                    $this->error('License file not found. Please run the installer.');
                }
                Log::warning('License verification command: License file not found');
                return Command::FAILURE;
            }

            $licenseData = $licenseEncryptionService->retrieveLicense();
            
            if (empty($licenseData)) {
                if (!$this->option('quiet')) {
                    $this->error('Failed to retrieve license data.');
                }
                Log::error('License verification command: Failed to retrieve license data');
                return Command::FAILURE;
            }

            if (!$this->option('quiet')) {
                $this->info('Verifying license with Envato API...');
            }

            $envatoService = app(EnvatoVerificationService::class);
            $verificationResult = $envatoService->verifyPurchase(
                $licenseData['purchase_code'] ?? '',
                $licenseData['username'] ?? '',
                $licenseData['item_id'] ?? null
            );

            if ($verificationResult['valid']) {
                if (!$this->option('quiet')) {
                    $this->info('✅ License verified successfully!');
                    if (isset($verificationResult['data']['item_name'])) {
                        $this->line('Item: ' . $verificationResult['data']['item_name']);
                    }
                    if (isset($verificationResult['data']['license'])) {
                        $this->line('License: ' . ucfirst($verificationResult['data']['license']));
                    }
                }
                Log::info('License verification successful');
                return Command::SUCCESS;
            } else {
                if (!$this->option('quiet')) {
                    $this->error('❌ License verification failed: ' . ($verificationResult['message'] ?? 'Unknown error'));
                }
                Log::warning('License verification failed', [
                    'message' => $verificationResult['message'] ?? 'Unknown error',
                ]);
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            if (!$this->option('quiet')) {
                $this->error('Error during license verification: ' . $e->getMessage());
            }
            Log::error('License verification command error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
