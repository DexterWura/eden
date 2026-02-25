<?php

namespace App\Lib;

use GuzzleHttp\RequestOptions;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\LinkedInOpenIdProvider as BaseLinkedInOpenIdProvider;
use Laravel\Socialite\Two\User;

/**
 * LinkedIn OpenID Connect provider aligned with Microsoft Learn documentation:
 * https://learn.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/sign-in-with-linkedin-v2
 *
 * - Scopes: openid, profile, email
 * - Userinfo: GET https://api.linkedin.com/v2/userinfo with Bearer token only (no projection)
 * - Response: sub, name, given_name, family_name, picture, locale, email (optional), email_verified (optional)
 */
class LinkedInOpenIdProvider extends BaseLinkedInOpenIdProvider
{
    /**
     * Get member details from userinfo endpoint per docs (Bearer only, no query params).
     *
     * @param  string  $token
     * @return array
     */
    protected function getBasicProfile($token)
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * Map userinfo response to Socialite User. Handles optional email per docs.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'] ?? null,
            'nickname' => null,
            'name' => $user['name'] ?? trim(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')),
            'first_name' => $user['given_name'] ?? null,
            'last_name' => $user['family_name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,
            'avatar_original' => $user['picture'] ?? null,
        ]);
    }
}
