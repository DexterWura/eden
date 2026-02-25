<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

/**
 * Service for masking sensitive data when demo/test admin is logged in.
 * 
 * This service provides methods to mask sensitive information such as emails,
 * passwords, API keys, and notification keys to protect them in demo mode.
 */
class DemoDataMaskingService
{
    /**
     * Protected text shown in demo mode.
     */
    private const PROTECTED_TEXT = '{protected in demo mode}';

    /**
     * Check if data masking should be applied.
     * 
     * @return bool
     */
    public function shouldMask(): bool
    {
        $admin = Auth::guard('admin')->user();
        return $admin && $admin->isDemoUser();
    }

    /**
     * Mask email address.
     * 
     * @param string|null $email
     * @return string
     */
    public function maskEmail(?string $email): string
    {
        if (!$this->shouldMask() || empty($email)) {
            return $email ?? '';
        }
        return self::PROTECTED_TEXT;
    }

    /**
     * Mask password field.
     * 
     * @return string
     */
    public function maskPassword(): string
    {
        if (!$this->shouldMask()) {
            return '';
        }
        return self::PROTECTED_TEXT;
    }

    /**
     * Mask API key.
     * 
     * @param string|null $key
     * @return string
     */
    public function maskApiKey(?string $key): string
    {
        if (!$this->shouldMask() || empty($key)) {
            return $key ?? '';
        }
        return self::PROTECTED_TEXT;
    }

    /**
     * Mask notification key (Firebase, FCM, etc.).
     * 
     * @param string|null $key
     * @return string
     */
    public function maskNotificationKey(?string $key): string
    {
        if (!$this->shouldMask() || empty($key)) {
            return $key ?? '';
        }
        return self::PROTECTED_TEXT;
    }

    /**
     * Mask any sensitive field value.
     * 
     * @param mixed $value
     * @return mixed
     */
    public function maskSensitiveField($value)
    {
        if (!$this->shouldMask()) {
            return $value;
        }
        
        if (empty($value)) {
            return $value;
        }
        
        return self::PROTECTED_TEXT;
    }

    /**
     * Mask sensitive fields in an array or object.
     * 
     * @param array|object $data
     * @param array $sensitiveFields List of field names to mask
     * @return array|object
     */
    public function maskData($data, array $sensitiveFields = [])
    {
        if (!$this->shouldMask()) {
            return $data;
        }

        $defaultSensitiveFields = [
            'email',
            'password',
            'api_key',
            'api_secret',
            'secret_key',
            'access_token',
            'refresh_token',
            'notification_key',
            'fcm_key',
            'firebase_key',
            'private_key',
            'public_key',
            'encryption_key',
            'webhook_secret',
            'client_secret',
            'auth_token',
        ];

        $fieldsToMask = array_merge($defaultSensitiveFields, $sensitiveFields);
        $isArray = is_array($data);
        $dataArray = $isArray ? $data : (array) $data;

        foreach ($fieldsToMask as $field) {
            // Check both exact match and case-insensitive match
            $found = false;
            $actualKey = null;
            
            foreach (array_keys($dataArray) as $key) {
                if (strtolower($key) === strtolower($field)) {
                    $found = true;
                    $actualKey = $key;
                    break;
                }
            }
            
            if ($found && $actualKey !== null && isset($dataArray[$actualKey]) && !empty($dataArray[$actualKey])) {
                $dataArray[$actualKey] = self::PROTECTED_TEXT;
            }
        }

        return $isArray ? $dataArray : (object) $dataArray;
    }

    /**
     * Get the protected text constant.
     * 
     * @return string
     */
    public function getProtectedText(): string
    {
        return self::PROTECTED_TEXT;
    }
}
