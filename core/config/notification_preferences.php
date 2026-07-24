<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notifications that cannot be disabled (security / account recovery)
    |--------------------------------------------------------------------------
    */
    'always_send' => [
        'PASS_RESET_CODE',
        'PASS_RESET_DONE',
        'EVER_CODE',
        'SVER_CODE',
    ],

    /*
    |--------------------------------------------------------------------------
    | User-togglable notification types (key => label, grouped by category)
    |--------------------------------------------------------------------------
    */
    'types' => [
        // Bids & Auctions
        'NEW_BID_RECEIVED' => ['label' => 'New bid received on my listing', 'category' => 'Bids & Auctions'],
        'BID_OUTBID' => ['label' => 'I have been outbid', 'category' => 'Bids & Auctions'],
        'WATCHED_LISTING_NEW_BID' => ['label' => 'New bid on a watched listing', 'category' => 'Bids & Auctions'],
        'LISTING_SOLD_BUY_NOW' => ['label' => 'My listing sold (Buy Now)', 'category' => 'Bids & Auctions'],
        'PURCHASE_BUY_NOW' => ['label' => 'Purchase confirmation (Buy Now)', 'category' => 'Bids & Auctions'],
        'AUCTION_WON' => ['label' => 'I won an auction', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_SOLD' => ['label' => 'My auction ended - item sold', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_OUTBID' => ['label' => 'Auction ended - I was outbid', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_NO_BIDS' => ['label' => 'Auction ended with no bids', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_RESERVE_NOT_MET' => ['label' => 'Auction ended - reserve not met', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_RESERVE_NOT_MET_BIDDER' => ['label' => 'Auction ended - reserve not met (as bidder)', 'category' => 'Bids & Auctions'],
        'AUCTION_ENDED_INVALID_WINNER' => ['label' => 'Auction processing - invalid winner', 'category' => 'Bids & Auctions'],
        'AUCTION_PROCESSING_ERROR' => ['label' => 'Auction processing error', 'category' => 'Bids & Auctions'],
        'BID_CANCELLED_ADMIN' => ['label' => 'My bid was cancelled by admin', 'category' => 'Bids & Auctions'],

        // Offers
        'NEW_OFFER_RECEIVED' => ['label' => 'New offer received on my listing', 'category' => 'Offers'],
        'OFFER_ACCEPTED' => ['label' => 'My offer was accepted', 'category' => 'Offers'],
        'OFFER_REJECTED' => ['label' => 'My offer was rejected', 'category' => 'Offers'],
        'OFFER_COUNTERED' => ['label' => 'Counter offer received', 'category' => 'Offers'],
        'COUNTER_OFFER_ACCEPTED' => ['label' => 'My counter offer was accepted', 'category' => 'Offers'],
        'OFFER_CANCELLED_ADMIN' => ['label' => 'My offer was cancelled by admin', 'category' => 'Offers'],

        // Listings & My Listings
        'LISTING_APPROVED' => ['label' => 'My listing was approved', 'category' => 'Listings'],
        'LISTING_REJECTED' => ['label' => 'My listing was rejected', 'category' => 'Listings'],
        'LISTING_CANCELLED_ADMIN' => ['label' => 'My listing was cancelled by admin', 'category' => 'Listings'],
        'LISTING_DEACTIVATED' => ['label' => 'My listing was deactivated', 'category' => 'Listings'],
        'LISTING_REACTIVATED' => ['label' => 'My listing was reactivated', 'category' => 'Listings'],

        // Q&A
        'QUESTION_ANSWERED' => ['label' => 'My question was answered', 'category' => 'Q&A'],
        'NEW_QUESTION_RECEIVED' => ['label' => 'New question on my listing', 'category' => 'Q&A'],

        // NDA
        'NDA_SIGNED' => ['label' => 'Someone signed NDA on my listing', 'category' => 'NDA'],
        'NDA_SIGNED_CONFIRMATION' => ['label' => 'NDA signed confirmation (to me)', 'category' => 'NDA'],
        'NDA_REVOKED' => ['label' => 'NDA was revoked', 'category' => 'NDA'],
        'NDA_EXPIRED' => ['label' => 'My NDA has expired', 'category' => 'NDA'],
        'NDA_EXPIRED_SELLER' => ['label' => 'NDA expired on my listing', 'category' => 'NDA'],
        'NDA_EXPIRING_SOON' => ['label' => 'NDA expiring soon reminder', 'category' => 'NDA'],

        // Escrow & Milestones
        'MILESTONE_CREATED' => ['label' => 'New milestone created (escrow)', 'category' => 'Escrow & Milestones'],
        'MILESTONES_GENERATED' => ['label' => 'Milestones generated from template', 'category' => 'Escrow & Milestones'],
        'MILESTONE_APPROVED' => ['label' => 'Milestone approved', 'category' => 'Escrow & Milestones'],
        'MILESTONE_REJECTED' => ['label' => 'Milestone rejected', 'category' => 'Escrow & Milestones'],
        'ESCROW_CANCELLED' => ['label' => 'Escrow cancelled', 'category' => 'Escrow & Milestones'],
        'ESCROW_ACCEPTED' => ['label' => 'Escrow accepted', 'category' => 'Escrow & Milestones'],
        'ESCROW_DISPUTED' => ['label' => 'Escrow disputed', 'category' => 'Escrow & Milestones'],
        'ESCROW_PAYMENT_DISPATCHED' => ['label' => 'Escrow payment dispatched', 'category' => 'Escrow & Milestones'],
        'ESCROW_FULLY_PAID' => ['label' => 'Escrow fully paid', 'category' => 'Escrow & Milestones'],
        'DIRECT_ESCROW_MARKED_COMPLETE' => ['label' => 'Direct escrow marked complete', 'category' => 'Escrow & Milestones'],
        'DIRECT_ESCROW_SERVICE_FEE_PAID' => ['label' => 'Direct escrow service fee paid', 'category' => 'Escrow & Milestones'],
        'ESCROW_ADMIN_ACTION' => ['label' => 'Escrow admin action / resolution', 'category' => 'Escrow & Milestones'],
        'DIRECT_ESCROW_ADMIN_ACTION' => ['label' => 'Direct escrow admin action', 'category' => 'Escrow & Milestones'],

        // Account & Payments
        'WITHDRAW_REQUEST' => ['label' => 'Withdrawal request confirmation', 'category' => 'Account & Payments'],
        'WITHDRAW_APPROVE' => ['label' => 'Withdrawal approved', 'category' => 'Account & Payments'],
        'WITHDRAW_REJECT' => ['label' => 'Withdrawal rejected', 'category' => 'Account & Payments'],
        'DEPOSIT_REQUEST' => ['label' => 'Deposit request confirmation', 'category' => 'Account & Payments'],
        'DEPOSIT_APPROVE' => ['label' => 'Deposit approved', 'category' => 'Account & Payments'],
        'DEPOSIT_COMPLETE' => ['label' => 'Deposit completed', 'category' => 'Account & Payments'],
        'DEPOSIT_REJECT' => ['label' => 'Deposit rejected', 'category' => 'Account & Payments'],
        'KYC_APPROVE' => ['label' => 'KYC approved', 'category' => 'Account & Payments'],
        'KYC_REJECT' => ['label' => 'KYC rejected', 'category' => 'Account & Payments'],
        'BAL_ADD' => ['label' => 'Balance added', 'category' => 'Account & Payments'],
        'BAL_SUB' => ['label' => 'Balance subtracted', 'category' => 'Account & Payments'],

        // Discovery
        'NEW_LISTING_ALERT' => ['label' => 'New listing alerts (emails when new businesses are listed)', 'category' => 'Discovery'],
        'FUNDRAISING_OPPORTUNITIES' => ['label' => 'Investment opportunities from other founders', 'category' => 'Discovery'],

        // Support & Other
        'ADMIN_SUPPORT_REPLY' => ['label' => 'Support ticket reply from admin', 'category' => 'Support & Other'],
        'DEFAULT' => ['label' => 'Custom messages from admin', 'category' => 'Support & Other'],
        'INVITATION_LINK' => ['label' => 'Escrow invitation link', 'category' => 'Support & Other'],
    ],
];
