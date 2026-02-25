<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for encrypting and storing license verification data.
 * 
 * This service uses Laravel's encryption to securely store license information
 * and prevent tampering or nulling attempts.
 */
class LicenseEncryptionService
{
    /**
     * License file path (stored outside web root for security).
     */
    private const LICENSE_FILE_PATH = '.license';

    /**
     * License storage disk.
     */
    private const LICENSE_DISK = 'local';

    /**
     * Encrypt license data.
     * 
     * @param array $data License data (purchase_code, username, item_id, etc.)
     * @return string Encrypted license data
     */
    public function encryptLicense(array $data): string
    {
        try {
            // Add timestamp and checksum for integrity verification
            $licenseData = [
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
                'checksum' => $this->generateChecksum($data),
            ];

            // Encrypt using Laravel's Crypt (uses APP_KEY)
            $encrypted = Crypt::encryptString(json_encode($licenseData));

            return $encrypted;
        } catch (\Exception $e) {
            Log::error('License encryption failed', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to encrypt license data: ' . $e->getMessage());
        }
    }

    /**
     * Decrypt license data.
     * 
     * @param string $encrypted Encrypted license data
     * @return array Decrypted license data
     * @throws \RuntimeException If decryption fails or data is tampered
     */
    public function decryptLicense(string $encrypted): array
    {
        try {
            // Decrypt
            $decrypted = Crypt::decryptString($encrypted);
            $licenseData = json_decode($decrypted, true);

            if (!isset($licenseData['data'], $licenseData['checksum'])) {
                throw new \RuntimeException('Invalid license data structure.');
            }

            // Verify checksum
            $expectedChecksum = $this->generateChecksum($licenseData['data']);
            if ($licenseData['checksum'] !== $expectedChecksum) {
                Log::warning('License data checksum mismatch - possible tampering detected');
                throw new \RuntimeException('License data integrity check failed. Data may have been tampered with.');
            }

            return $licenseData['data'];
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('License decryption failed', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Failed to decrypt license data. The license may be invalid or corrupted.');
        } catch (\Exception $e) {
            Log::error('License decryption error', [
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('License verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Store encrypted license to file.
     * 
     * @param array $licenseData License data to encrypt and store
     * @return bool Success status
     */
    public function storeLicense(array $licenseData): bool
    {
        try {
            $encrypted = $this->encryptLicense($licenseData);
            
            // Store in storage/app/.license (outside web root)
            Storage::disk(self::LICENSE_DISK)->put(self::LICENSE_FILE_PATH, $encrypted);
            
            // Set restrictive permissions (if possible)
            $filePath = storage_path('app/' . self::LICENSE_FILE_PATH);
            if (file_exists($filePath)) {
                @chmod($filePath, 0600); // Read/write for owner only
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store license', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Retrieve and decrypt license from file.
     * 
     * @return array|null Decrypted license data or null if not found
     */
    public function retrieveLicense(): ?array
    {
        try {
            if (!Storage::disk(self::LICENSE_DISK)->exists(self::LICENSE_FILE_PATH)) {
                return null;
            }

            $encrypted = Storage::disk(self::LICENSE_DISK)->get(self::LICENSE_FILE_PATH);
            
            if (empty($encrypted)) {
                return null;
            }

            return $this->decryptLicense($encrypted);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve license', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Verify license integrity.
     * 
     * @return bool True if license exists and is valid
     */
    public function verifyLicenseIntegrity(): bool
    {
        try {
            $licenseData = $this->retrieveLicense();
            return $licenseData !== null && !empty($licenseData);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete stored license file.
     * 
     * @return bool Success status
     */
    public function deleteLicense(): bool
    {
        try {
            if (Storage::disk(self::LICENSE_DISK)->exists(self::LICENSE_FILE_PATH)) {
                Storage::disk(self::LICENSE_DISK)->delete(self::LICENSE_FILE_PATH);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete license', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate checksum for license data integrity.
     * 
     * @param array $data License data
     * @return string Checksum hash
     */
    private function generateChecksum(array $data): string
    {
        // Sort data to ensure consistent checksum
        ksort($data);
        
        // Create checksum using APP_KEY and data
        $dataString = json_encode($data, JSON_UNESCAPED_SLASHES);
        $secret = config('app.key');
        
        return hash_hmac('sha256', $dataString, $secret);
    }

    /**
     * Check if license file exists.
     * 
     * @return bool
     */
    public function licenseExists(): bool
    {
        return Storage::disk(self::LICENSE_DISK)->exists(self::LICENSE_FILE_PATH);
    }
}
