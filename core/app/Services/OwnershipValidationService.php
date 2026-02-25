<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OwnershipValidationService
{
    /**
     * Validate ownership for domain/website using DNS TXT record
     */
    public function validateDnsTxt($domain, $token)
    {
        try {
            // Remove protocol and www
            $domain = $this->normalizeDomain($domain);
            
            // Check DNS TXT records
            $txtRecords = dns_get_record($domain, DNS_TXT);
            
            if (!$txtRecords) {
                return [
                    'success' => false,
                    'message' => 'No TXT records found for this domain'
                ];
            }
            
            // Look for our verification token in TXT records
            $verificationString = "marketplace-verification={$token}";
            
            foreach ($txtRecords as $record) {
                if (isset($record['txt']) && strpos($record['txt'], $verificationString) !== false) {
                    return [
                        'success' => true,
                        'message' => 'Ownership verified via DNS TXT record'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Verification token not found in DNS TXT records'
            ];
            
        } catch (\Exception $e) {
            Log::error('DNS TXT validation error', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error checking DNS records: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for domain/website using HTML meta tag
     */
    public function validateHtmlMeta($url, $token)
    {
        try {
            // Ensure URL has protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            
            // Fetch the page
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Could not fetch the website. Please ensure the URL is accessible.'
                ];
            }
            
            $html = $response->body();
            
            // Look for meta tag
            $verificationString = "marketplace-verification={$token}";
            $pattern = '/<meta\s+name=["\']marketplace-verification["\']\s+content=["\']' . preg_quote($token, '/') . '["\']/i';
            
            if (preg_match($pattern, $html)) {
                return [
                    'success' => true,
                    'message' => 'Ownership verified via HTML meta tag'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Verification meta tag not found in the HTML. Please add: <meta name="marketplace-verification" content="' . $token . '">'
            ];
            
        } catch (\Exception $e) {
            Log::error('HTML Meta validation error', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error fetching website: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for domain/website using file upload
     */
    public function validateFileUpload($url, $filename, $token)
    {
        try {
            // Ensure URL has protocol
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            
            // Construct file URL
            $fileUrl = rtrim($url, '/') . '/' . ltrim($filename, '/');
            
            // Fetch the file
            $response = Http::timeout(10)->get($fileUrl);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Could not access the verification file. Please ensure the file is accessible at: ' . $fileUrl
                ];
            }
            
            $fileContent = $response->body();
            
            // Check if token is in file content
            if (strpos($fileContent, $token) !== false) {
                return [
                    'success' => true,
                    'message' => 'Ownership verified via file upload'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Verification token not found in the file. Please ensure the file contains: ' . $token
            ];
            
        } catch (\Exception $e) {
            Log::error('File upload validation error', [
                'url' => $url,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error accessing verification file: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ownership for social media using OAuth login
     * If user can successfully login with their social account, they own it
     */
    public function validateSocialMediaOAuth($platform, $oauthUser, $expectedHandle = null)
    {
        try {
            // Map platform names to socialite providers
            $platformMap = [
                'instagram' => 'instagram',
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'youtube' => 'google', // YouTube uses Google OAuth
                'tiktok' => 'tiktok',
                'linkedin' => 'linkedin',
                'google' => 'google',
            ];
            
            $provider = $platformMap[strtolower($platform)] ?? strtolower($platform);
            
            // Verify the OAuth user data
            if (!$oauthUser || !isset($oauthUser->id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid OAuth response. Please try again.'
                ];
            }
            
            // If expected handle is provided, verify it matches
            if ($expectedHandle) {
                $normalizedHandle = str_replace('@', '', strtolower(trim($expectedHandle)));
                $oauthUsername = strtolower(trim($oauthUser->nickname ?? $oauthUser->name ?? ''));
                $oauthEmail = strtolower(trim($oauthUser->email ?? ''));
                
                // Check if handle matches username or email
                $handleMatches = (
                    $oauthUsername === $normalizedHandle ||
                    strpos($oauthUsername, $normalizedHandle) !== false ||
                    $oauthEmail === $normalizedHandle ||
                    (isset($oauthUser->user) && strtolower($oauthUser->user['username'] ?? '') === $normalizedHandle)
                );
                
                if (!$handleMatches) {
                    return [
                        'success' => false,
                        'message' => 'The logged-in account does not match the provided handle. Please login with the correct account.'
                    ];
                }
            }
            
            // OAuth login successful = ownership verified
            Log::info('Social media ownership verified via OAuth', [
                'platform' => $platform,
                'provider' => $provider,
                'oauth_id' => $oauthUser->id,
                'username' => $oauthUser->nickname ?? $oauthUser->name ?? 'N/A',
                'email' => $oauthUser->email ?? 'N/A'
            ]);
            
            return [
                'success' => true,
                'message' => 'Ownership verified successfully via ' . ucfirst($platform) . ' login',
                'oauth_data' => [
                    'id' => $oauthUser->id,
                    'username' => $oauthUser->nickname ?? $oauthUser->name ?? null,
                    'email' => $oauthUser->email ?? null,
                    'name' => $oauthUser->name ?? null,
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Social media OAuth validation error', [
                'platform' => $platform,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error validating social media ownership: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate ownership for social media by checking that the verification token exists on the public profile page
     * (e.g., in bio/about/pinned post text). This avoids collecting passwords.
     */
    public function validateSocialMediaProfileToken(string $platform, string $profileUrl, string $token, ?string $expectedHandle = null): array
    {
        try {
            if (!preg_match('/^https?:\/\//i', $profileUrl)) {
                return [
                    'success' => false,
                    'message' => 'Invalid profile URL. Please include https://'
                ];
            }

            $host = strtolower(parse_url($profileUrl, PHP_URL_HOST) ?? '');
            $allowedHosts = $this->getAllowedProfileHosts($platform);
            if (!empty($allowedHosts) && !in_array($host, $allowedHosts, true)) {
                return [
                    'success' => false,
                    'message' => 'Profile URL does not match the selected platform. Please provide a valid ' . ucfirst($platform) . ' profile URL.'
                ];
            }

            if ($expectedHandle) {
                $expectedHandle = ltrim(strtolower(trim($expectedHandle)), '@');
                if ($expectedHandle && stripos($profileUrl, $expectedHandle) === false) {
                    return [
                        'success' => false,
                        'message' => 'The provided profile URL does not appear to match the username/handle you entered.'
                    ];
                }
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Marketplace Ownership Verification)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(15)->get($profileUrl);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Could not fetch the profile URL. Make sure the profile is public and accessible.'
                ];
            }

            $body = $response->body();
            if (stripos($body, $token) !== false) {
                return [
                    'success' => true,
                    'message' => 'Ownership verified via profile token check'
                ];
            }

            return [
                'success' => false,
                'message' => 'Verification token not found on the profile page yet. Ensure you added it to your bio/about text (or pinned post if supported) and the profile is public.'
            ];
        } catch (\Exception $e) {
            Log::error('Social media profile token validation error', [
                'platform' => $platform,
                'url' => $profileUrl,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error validating social media profile: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a unique verification token
     */
    public function generateToken($userId, $assetUrl)
    {
        // Short enough to paste into a profile bio, still unguessable
        return 'mpv-' . bin2hex(random_bytes(8));
    }
    
    /**
     * Normalize domain (remove protocol, www, etc.)
     */
    public function normalizeDomain($domain)
    {
        // Remove protocol
        $domain = preg_replace('#^https?://#', '', $domain);
        // Remove www
        $domain = preg_replace('#^www\.#', '', $domain);
        // Remove path
        $domain = preg_replace('#/.*$#', '', $domain);
        // Remove query string
        $domain = preg_replace('#\?.*$#', '', $domain);
        // Remove fragment
        $domain = preg_replace('#\#.*$#', '', $domain);
        
        return trim($domain);
    }
    
    /**
     * Get available validation methods for a business type
     */
    public function getAvailableMethods($businessType, ?string $platform = null)
    {
        switch ($businessType) {
            case 'domain':
            case 'website':
                return [
                    'dns_txt' => [
                        'name' => 'DNS TXT Record',
                        'description' => 'Add a TXT record to your domain\'s DNS settings',
                        'instructions' => 'Add a TXT record with name: @ and value: marketplace-verification={TOKEN}'
                    ],
                    'html_meta' => [
                        'name' => 'HTML Meta Tag',
                        'description' => 'Add a meta tag to your website\'s HTML',
                        'instructions' => 'Add this tag to your website\'s <head> section: <meta name="marketplace-verification" content="{TOKEN}">'
                    ],
                    'file_upload' => [
                        'name' => 'File Upload',
                        'description' => 'Upload a verification file to your website',
                        'instructions' => 'Create a file named marketplace-verification.txt in your website root containing: {TOKEN}'
                    ]
                ];
                
            case 'social_media_account':
                $methods = [
                    'profile_token' => [
                        'name' => 'Verification Code in Bio/About',
                        'description' => 'Add a verification code to your profile bio/about text and we will check it on your public profile page.',
                        'instructions' => 'Paste the verification code into your profile bio/about text (or pinned post if supported), then click Validate Ownership.'
                    ],
                ];

                if ($platform && $this->hasOAuthCredentialsForPlatform($platform)) {
                    $methods['oauth_login'] = [
                        'name' => 'Login with Social Account (OAuth)',
                        'description' => 'Verify ownership by securely connecting your social media account via OAuth (no password stored).',
                        'instructions' => 'Click the button below to connect your account. If successful, ownership will be verified automatically.'
                    ];
                }

                return $methods;
                
            default:
                return [];
        }
    }

    protected function hasOAuthCredentialsForPlatform(string $platform): bool
    {
        try {
            $platform = strtolower($platform);
            $platformMap = [
                'instagram' => 'instagram',
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'youtube' => 'google',
                'linkedin' => 'linkedin',
                'google' => 'google',
                'tiktok' => 'tiktok',
            ];

            $provider = $platformMap[$platform] ?? $platform;
            $creds = gs('socialite_credentials');
            if (!$creds || !isset($creds->$provider)) {
                return false;
            }
            $cfg = $creds->$provider;
            return !empty($cfg->client_id) && !empty($cfg->client_secret);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getAllowedProfileHosts(string $platform): array
    {
        $platform = strtolower($platform);
        return match ($platform) {
            'instagram' => ['instagram.com', 'www.instagram.com'],
            'twitter' => ['twitter.com', 'www.twitter.com', 'x.com', 'www.x.com'],
            'facebook' => ['facebook.com', 'www.facebook.com'],
            'linkedin' => ['linkedin.com', 'www.linkedin.com'],
            'tiktok' => ['tiktok.com', 'www.tiktok.com'],
            'youtube' => ['youtube.com', 'www.youtube.com', 'm.youtube.com'],
            'pinterest' => ['pinterest.com', 'www.pinterest.com'],
            'snapchat' => ['snapchat.com', 'www.snapchat.com'],
            'twitch' => ['twitch.tv', 'www.twitch.tv'],
            default => [],
        };
    }
}

