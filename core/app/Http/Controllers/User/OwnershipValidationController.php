<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\OwnershipValidationService;
use App\Lib\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Socialite;

class OwnershipValidationController extends Controller
{
    protected $validationService;
    
    public function __construct(OwnershipValidationService $validationService)
    {
        $this->validationService = $validationService;
    }
    
    /**
     * Clear ownership validation session data
     */
    public function clear(Request $request)
    {
        session()->forget([
            'ownership_verified',
            'ownership_verification_token',
            'ownership_verification_asset',
            'ownership_verification_business_type',
            'ownership_verification_method',
            'ownership_verification_platform'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Ownership validation cleared'
        ]);
    }
    
    /**
     * Get available validation methods for a business type
     */
    public function getMethods(Request $request)
    {
        $request->validate([
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'primary_asset_url' => 'nullable|string|max:500',
            'platform' => 'nullable|string|max:100'
        ]);
        
        $methods = $this->validationService->getAvailableMethods($request->business_type, $request->input('platform'));
        
        // If user has a token in session, include it in response
        $token = session()->get('ownership_verification_token');
        $assetUrl = session()->get('ownership_verification_asset');
        $businessType = session()->get('ownership_verification_business_type');
        
        $response = [
            'success' => true,
            'methods' => $methods
        ];
        
        // Normalize URLs for comparison
        $normalizeUrl = function($url) {
            if (!$url) return '';
            return rtrim(strtolower(trim($url)), '/');
        };
        
        // Only return token and instructions if:
        // 1. Token exists in session
        // 2. Business type matches
        // 3. Asset URL matches (if provided in request)
        $requestAssetUrl = $request->input('primary_asset_url');
        $urlMatches = !$requestAssetUrl || $normalizeUrl($assetUrl) === $normalizeUrl($requestAssetUrl);
        
        if ($token && $assetUrl && $businessType === $request->business_type && $urlMatches) {
            $response['token'] = $token;
            $response['instructions'] = $this->getInstructions($businessType, $token, $assetUrl);
        }
        
        return response()->json($response);
    }
    
    /**
     * Generate verification token and instructions
     */
    public function generateToken(Request $request)
    {
        $request->validate([
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'primary_asset_url' => 'required|string|max:500',
            'platform' => 'nullable|string|max:100'
        ]);
        
        $user = auth()->user();
        $token = $this->validationService->generateToken($user->id, $request->primary_asset_url);
        
        // Store token in session for validation
        session()->put('ownership_verification_token', $token);
        session()->put('ownership_verification_asset', $request->primary_asset_url);
        session()->put('ownership_verification_business_type', $request->business_type);
        
        $methods = $this->validationService->getAvailableMethods($request->business_type, $request->input('platform'));
        
        return response()->json([
            'success' => true,
            'token' => $token,
            'methods' => $methods,
            'instructions' => $this->getInstructions($request->business_type, $token, $request->primary_asset_url)
        ]);
    }
    
    /**
     * Validate ownership
     */
    public function validate(Request $request)
    {
        $request->validate([
            'business_type' => 'required|in:domain,website,social_media_account,mobile_app,desktop_app',
            'primary_asset_url' => 'required|string|max:500',
            'method' => 'required|string',
            'token' => 'required|string',
            'additional_data' => 'nullable|array'
        ]);
        
        $user = auth()->user();
        $businessType = $request->business_type;
        $method = $request->method;
        $assetUrl = $request->primary_asset_url;
        $token = $request->token;
        
        $result = null;
        
        // Validate based on business type and method
        if (in_array($businessType, ['domain', 'website'])) {
            switch ($method) {
                case 'dns_txt':
                    $result = $this->validationService->validateDnsTxt($assetUrl, $token);
                    break;
                    
                case 'html_meta':
                    $result = $this->validationService->validateHtmlMeta($assetUrl, $token);
                    break;
                    
                case 'file_upload':
                    $filename = $request->input('additional_data.filename', 'marketplace-verification.txt');
                    $result = $this->validationService->validateFileUpload($assetUrl, $filename, $token);
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid validation method'
                    ], 400);
            }
        } elseif ($businessType === 'social_media_account') {
            if ($method === 'oauth_login') {
                return response()->json([
                    'success' => false,
                    'message' => 'Please use the OAuth login button to verify ownership'
                ], 400);
            }

            if ($method === 'profile_token') {
                $platform = (string)($request->input('additional_data.platform') ?? '');
                $handle = (string)($request->input('additional_data.handle') ?? '');
                if (!$platform) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please select a platform first.'
                    ], 422);
                }

                $result = $this->validationService->validateSocialMediaProfileToken($platform, $assetUrl, $token, $handle ?: null);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid validation method'
                ], 400);
            }
        } else {
            // For mobile_app and desktop_app, we might skip validation or use different methods
            return response()->json([
                'success' => false,
                'message' => 'Ownership validation not required for this business type'
            ], 400);
        }
        
        // If validation successful, store in session
        if ($result['success']) {
            session()->put('ownership_verified', true);
            session()->put('ownership_verification_method', $method);
            
            Log::info('Ownership validated', [
                'user_id' => $user->id,
                'business_type' => $businessType,
                'method' => $method,
                'asset_url' => $assetUrl
            ]);
        }
        
        return response()->json($result);
    }
    
    /**
     * Get instructions for validation method
     */
    protected function getInstructions($businessType, $token, $assetUrl)
    {
        $instructions = [];
        
        if (in_array($businessType, ['domain', 'website'])) {
            $domain = $this->validationService->normalizeDomain($assetUrl);
            
            $instructions['dns_txt'] = [
                'title' => 'DNS TXT Record Method',
                'steps' => [
                    '1. Log in to your domain registrar or DNS provider',
                    '2. Navigate to DNS settings for: ' . $domain,
                    '3. Add a new TXT record with:',
                    '   - Name/Host: @ (or leave blank)',
                    '   - Value: marketplace-verification=' . $token,
                    '4. Save the record and wait a few minutes for DNS propagation',
                    '5. Click "Validate Ownership" below'
                ]
            ];
            
            $instructions['html_meta'] = [
                'title' => 'HTML Meta Tag Method',
                'steps' => [
                    '1. Access your website\'s HTML files',
                    '2. Open the <head> section of your main page (usually index.html or similar)',
                    '3. Add this line before </head>:',
                    '   <meta name="marketplace-verification" content="' . $token . '">',
                    '4. Save and publish your website',
                    '5. Click "Validate Ownership" below'
                ]
            ];
            
            $instructions['file_upload'] = [
                'title' => 'File Upload Method',
                'steps' => [
                    '1. Create a new text file named: marketplace-verification.txt',
                    '2. Add this content to the file: ' . $token,
                    '3. Upload the file to your website\'s root directory (same location as index.html)',
                    '4. Ensure the file is accessible at: ' . rtrim($assetUrl, '/') . '/marketplace-verification.txt',
                    '5. Click "Validate Ownership" below'
                ]
            ];
        } elseif ($businessType === 'social_media_account') {
            $instructions['oauth_login'] = [
                'title' => 'Login with Social Account',
                'steps' => [
                    '1. Click the "Login with Social Media" button below',
                    '2. You will be redirected to the platform to login',
                    '3. After successful login, ownership will be automatically verified',
                    '4. You will be redirected back to continue with your listing'
                ]
            ];

            $instructions['profile_token'] = [
                'title' => 'Verification Code in Bio/About',
                'steps' => [
                    '1. Copy this verification code: ' . $token,
                    '2. Open your social profile settings on the selected platform',
                    '3. Paste the code into your bio/about text (or pinned post if supported)',
                    '4. Make sure the profile is public temporarily',
                    '5. Click "Validate Ownership" below',
                    '6. After verification, you may remove the code from your bio'
                ]
            ];
        }
        
        return $instructions;
    }
    
    /**
     * Redirect to OAuth provider for ownership validation
     */
    public function redirectToOAuth(Request $request, $platform)
    {
        try {
            // Get validation context from request
            $businessType = $request->input('business_type', 'social_media_account');
            $handle = $request->input('handle', '');
            $token = $request->input('token', '');
            $assetUrl = $request->input('asset_url', '');
            
            // Map platform to socialite provider
            $platformMap = [
                'instagram' => 'instagram',
                'facebook' => 'facebook',
                'twitter' => 'twitter',
                'youtube' => 'google',
                'tiktok' => 'tiktok',
                'linkedin' => 'linkedin',
                'google' => 'google',
            ];
            
            $provider = $platformMap[strtolower($platform)] ?? strtolower($platform);
            
            // Store validation context in session for callback
            session()->put('ownership_validation_context', [
                'business_type' => $businessType,
                'platform' => $platform,
                'handle' => $handle,
                'token' => $token,
                'asset_url' => $assetUrl
            ]);
            
            // Store that this is for ownership validation
            session()->put('oauth_for_ownership_validation', true);
            
            $ownershipCallbackUrl = route('user.ownership.validation.oauth.callback', $provider);
            $socialLogin = new SocialLogin($provider);
            return $socialLogin->redirectDriverTo($ownershipCallbackUrl);
            
        } catch (\Exception $e) {
            Log::error('OAuth redirect error for ownership validation', [
                'platform' => $platform,
                'error' => $e->getMessage()
            ]);
            
            session()->forget('oauth_for_ownership_validation');
            session()->forget('ownership_validation_context');
            
            $notify[] = ['error', 'Failed to initiate OAuth login: ' . $e->getMessage()];
            return redirect()->route('user.listing.create')->withNotify($notify);
        }
    }
    
    /**
     * Handle OAuth callback for ownership validation
     */
    public function oauthCallback($provider)
    {
        try {
            // Check if this OAuth is for ownership validation
            if (!session()->has('oauth_for_ownership_validation')) {
                // Not for ownership validation, let SocialiteController handle it normally
                // This shouldn't happen as we're using the ownership validation callback route
                $notify[] = ['error', 'Invalid OAuth callback. Please try again.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }
            
            // Get validation context
            $context = session()->get('ownership_validation_context');
            if (!$context) {
                session()->forget('oauth_for_ownership_validation');
                $notify[] = ['error', 'Ownership validation session expired. Please try again.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }

            if (!request()->filled('code')) {
                session()->forget('oauth_for_ownership_validation');
                session()->forget('ownership_validation_context');
                $notify[] = ['error', 'Authorization was cancelled or not granted. Please try again.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }

            $ownershipCallbackUrl = route('user.ownership.validation.oauth.callback', $provider);
            (new SocialLogin($provider))->setRedirectForCallback($ownershipCallbackUrl);
            $driverProvider = $provider === 'linkedin' ? 'linkedin-openid' : $provider;
            $driver = Socialite::driver($driverProvider);
            $oauthUser = $driver->user();
            
            if ($driverProvider === 'linkedin-openid') {
                $oauthUser->id = $oauthUser->sub;
            }
            
            // Validate ownership
            $result = $this->validationService->validateSocialMediaOAuth(
                $context['platform'],
                $oauthUser,
                $context['handle'] ?? null
            );
            
            // Clear OAuth session flags
            session()->forget('oauth_for_ownership_validation');
            session()->forget('ownership_validation_context');
            
            if ($result['success']) {
                // Store verification in session
                session()->put('ownership_verified', true);
                session()->put('ownership_verification_method', 'oauth_login');
                session()->put('ownership_verification_platform', $context['platform']);
                
                Log::info('Ownership validated via OAuth', [
                    'user_id' => auth()->id(),
                    'platform' => $context['platform'],
                    'oauth_id' => $oauthUser->id
                ]);
                
                $notify[] = ['success', 'Ownership verified successfully! You can now continue with your listing.'];
                return redirect()->route('user.listing.create')->withNotify($notify);
            } else {
                $notify[] = ['error', $result['message']];
                return redirect()->route('user.listing.create')->withNotify($notify);
            }
            
        } catch (\Exception $e) {
            Log::error('OAuth callback error for ownership validation', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            session()->forget('oauth_for_ownership_validation');
            session()->forget('ownership_validation_context');
            
            $notify[] = ['error', 'Failed to verify ownership: ' . $e->getMessage()];
            return redirect()->route('user.listing.create')->withNotify($notify);
        }
    }
}

