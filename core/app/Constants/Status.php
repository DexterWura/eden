<?php

namespace App\Constants;

class Status{

    const ENABLE = 1;
    const DISABLE = 0;

    const YES = 1;
    const NO = 0;

    const VERIFIED = 1;
    const UNVERIFIED = 0;

    const PAYMENT_INITIATE = 0;
    const PAYMENT_SUCCESS = 1;
    const PAYMENT_PENDING = 2;
    const PAYMENT_REJECT = 3;

    CONST TICKET_OPEN = 0;
    CONST TICKET_ANSWER = 1;
    CONST TICKET_REPLY = 2;
    CONST TICKET_CLOSE = 3;

    CONST PRIORITY_LOW = 1;
    CONST PRIORITY_MEDIUM = 2;
    CONST PRIORITY_HIGH = 3;

    const USER_ACTIVE = 1;
    const USER_BAN = 0;

    const KYC_UNVERIFIED = 0;
    const KYC_PENDING = 2;
    const KYC_VERIFIED = 1;

    const GOOGLE_PAY = 5001;

    const CUR_BOTH = 1;
    const CUR_TEXT = 2;
    const CUR_SYM = 3;

    const CHARGE_PAYER_SELLER = 1;
    const CHARGE_PAYER_BUYER  = 2;

    const ESCROW_NOT_ACCEPTED = 0;
    const ESCROW_COMPLETED    = 1;
    const ESCROW_ACCEPTED     = 2;
    const ESCROW_DISPUTED     = 8;
    const ESCROW_CANCELLED    = 9;

    const MILESTONE_FUNDED   = 1;
    const MILESTONE_UNFUNDED = 0;

    const CONVERSION_RUNNING = 1;
    const CONVERSION_CLOSE   = 0;

    // Listing Status
    const LISTING_DRAFT      = 0;
    const LISTING_PENDING    = 1;
    const LISTING_ACTIVE     = 2;
    const LISTING_SOLD       = 3;
    const LISTING_EXPIRED    = 4;
    const LISTING_CANCELLED  = 5;
    const LISTING_REJECTED   = 6;

    // Bid Status
    const BID_ACTIVE    = 0;
    const BID_OUTBID    = 1;
    const BID_WINNING   = 2;
    const BID_WON       = 3;
    const BID_LOST      = 4;
    const BID_CANCELLED = 5;

    // Offer Status
    const OFFER_PENDING    = 0;
    const OFFER_ACCEPTED   = 1;
    const OFFER_REJECTED   = 2;
    const OFFER_COUNTERED  = 3;
    const OFFER_EXPIRED    = 4;
    const OFFER_CANCELLED  = 5;
    const OFFER_COMPLETED  = 6;

    // Question Status
    const QUESTION_PENDING  = 0;
    const QUESTION_ANSWERED = 1;
    const QUESTION_HIDDEN   = 2;

    // Review Status
    const REVIEW_PENDING  = 0;
    const REVIEW_APPROVED = 1;
    const REVIEW_HIDDEN   = 2;

    // Business Types
    const BUSINESS_TYPE_DOMAIN        = 'domain';
    const BUSINESS_TYPE_WEBSITE       = 'website';
    const BUSINESS_TYPE_SOCIAL_MEDIA  = 'social_media_account';
    const BUSINESS_TYPE_MOBILE_APP    = 'mobile_app';
    const BUSINESS_TYPE_DESKTOP_APP   = 'desktop_app';

    // Sale Types
    const SALE_TYPE_FIXED_PRICE = 'fixed_price';
    const SALE_TYPE_AUCTION     = 'auction';

    // Social Media Platforms
    const PLATFORM_INSTAGRAM = 'instagram';
    const PLATFORM_YOUTUBE   = 'youtube';
    const PLATFORM_TIKTOK    = 'tiktok';
    const PLATFORM_TWITTER   = 'twitter';
    const PLATFORM_FACEBOOK  = 'facebook';
    const PLATFORM_LINKEDIN  = 'linkedin';
    const PLATFORM_PINTEREST = 'pinterest';
    const PLATFORM_SNAPCHAT  = 'snapchat';
    const PLATFORM_TWITCH    = 'twitch';

}
