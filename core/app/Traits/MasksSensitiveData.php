<?php

namespace App\Traits;

use App\Services\DemoDataMaskingService;

/**
 * Trait for controllers and models to easily mask sensitive data in demo mode.
 * 
 * This trait provides convenient methods to mask sensitive information
 * when the test admin user is logged in.
 */
trait MasksSensitiveData
{
    /**
     * Get the demo data masking service instance.
     * 
     * @return DemoDataMaskingService
     */
    protected function getMaskingService(): DemoDataMaskingService
    {
        return app(DemoDataMaskingService::class);
    }

    /**
     * Check if data masking should be applied.
     * 
     * @return bool
     */
    protected function shouldMaskData(): bool
    {
        return $this->getMaskingService()->shouldMask();
    }

    /**
     * Mask data for demo mode.
     * 
     * @param array|object $data
     * @param array $sensitiveFields Additional fields to mask
     * @return array|object
     */
    protected function maskForDemo($data, array $sensitiveFields = [])
    {
        return $this->getMaskingService()->maskData($data, $sensitiveFields);
    }

    /**
     * Mask a single sensitive field value.
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function maskField($value)
    {
        return $this->getMaskingService()->maskSensitiveField($value);
    }

    /**
     * Mask email address.
     * 
     * @param string|null $email
     * @return string
     */
    protected function maskEmail(?string $email): string
    {
        return $this->getMaskingService()->maskEmail($email);
    }

    /**
     * Mask password field.
     * 
     * @return string
     */
    protected function maskPassword(): string
    {
        return $this->getMaskingService()->maskPassword();
    }

    /**
     * Mask API key.
     * 
     * @param string|null $key
     * @return string
     */
    protected function maskApiKey(?string $key): string
    {
        return $this->getMaskingService()->maskApiKey($key);
    }

    /**
     * Mask notification key.
     * 
     * @param string|null $key
     * @return string
     */
    protected function maskNotificationKey(?string $key): string
    {
        return $this->getMaskingService()->maskNotificationKey($key);
    }
}
