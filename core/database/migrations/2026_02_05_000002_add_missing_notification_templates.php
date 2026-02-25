<?php

use App\Constants\Status;
use App\Models\NotificationTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        $templates = [
            [
                'act' => 'OFFER_ACCEPTED',
                'name' => 'Offer Accepted',
                'subject' => 'Your offer on "{{listing_title}}" has been accepted!',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nGreat news! Your offer on \"{{listing_title}}\" has been accepted!\n\nAccepted Amount: {{offer_amount}} {{site_currency}}\n\nSeller: {{seller_username}}\n\nAn escrow has been created for this transaction. Please complete the payment to proceed with the purchase.\n\nEscrow Number: {{escrow_number}}\n\nComplete Payment Now",
                'sms_body' => 'Your offer of {{offer_amount}} on {{listing_title}} has been accepted. Escrow #{{escrow_number}} created.',
                'push_title' => 'Offer Accepted!',
                'push_body' => 'Your offer on {{listing_title}} has been accepted. Escrow created.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'offer_amount' => 'Accepted offer amount',
                    'seller_username' => 'Seller username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                ],
            ],
            [
                'act' => 'COUNTER_OFFER_ACCEPTED',
                'name' => 'Counter Offer Accepted',
                'subject' => 'Your counter offer on "{{listing_title}}" has been accepted!',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nGreat news! The buyer has accepted your counter offer on \"{{listing_title}}\"!\n\nAccepted Amount: {{amount}} {{site_currency}}\n\nBuyer: {{buyer_username}}\n\nAn escrow has been created for this transaction.\n\nEscrow Number: {{escrow_number}}\n\nView Escrow",
                'sms_body' => 'Counter offer of {{amount}} on {{listing_title}} accepted by {{buyer_username}}. Escrow #{{escrow_number}} created.',
                'push_title' => 'Counter Offer Accepted!',
                'push_body' => 'Your counter offer on {{listing_title}} has been accepted. Escrow created.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'amount' => 'Accepted amount',
                    'buyer' => 'Buyer username',
                    'buyer_username' => 'Buyer username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                ],
            ],
            [
                'act' => 'PURCHASE_BUY_NOW',
                'name' => 'Purchase Buy Now',
                'subject' => 'Purchase successful: "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nCongratulations! You have successfully purchased \"{{listing_title}}\" using Buy Now.\n\nPurchase Amount: {{amount}} {{site_currency}}\n\nSeller: {{seller_username}}\n\nAn escrow has been created for this transaction. Please complete the payment to proceed.\n\nEscrow Number: {{escrow_number}}\n\nComplete Payment Now",
                'sms_body' => 'Purchase successful: {{listing_title}} for {{amount}}. Escrow #{{escrow_number}} created.',
                'push_title' => 'Purchase Successful!',
                'push_body' => 'You purchased {{listing_title}} for {{amount}}. Escrow created.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'amount' => 'Purchase amount',
                    'seller_username' => 'Seller username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                ],
            ],
            [
                'act' => 'LISTING_SOLD_BUY_NOW',
                'name' => 'Listing Sold - Buy Now',
                'subject' => 'Your listing "{{listing_title}}" has been sold!',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nCongratulations! Your listing \"{{listing_title}}\" has been sold via Buy Now!\n\nSale Amount: {{amount}} {{site_currency}}\n\nBuyer: {{buyer_username}}\n\nAn escrow has been created for this transaction.\n\nEscrow Number: {{escrow_number}}\n\nView Escrow",
                'sms_body' => 'Your listing {{listing_title}} sold for {{amount}} to {{buyer_username}}. Escrow #{{escrow_number}} created.',
                'push_title' => 'Listing Sold!',
                'push_body' => 'Your listing {{listing_title}} has been sold for {{amount}}. Escrow created.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'amount' => 'Sale amount',
                    'buyer' => 'Buyer username',
                    'buyer_username' => 'Buyer username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                ],
            ],
            [
                'act' => 'AUCTION_WON',
                'name' => 'Auction Won',
                'subject' => 'Congratulations! You won the auction for "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nCongratulations! You have won the auction for \"{{listing_title}}\"!\n\nWinning Bid: {{winning_bid}} {{site_currency}}\n\nSeller: {{seller_username}}\n\nListing Number: {{listing_number}}\n\nAn escrow has been automatically created for this transaction.\n\nEscrow Number: {{escrow_number}}\n\n{{action_required}}\n\nView Escrow",
                'sms_body' => 'You won auction for {{listing_title}} with bid {{winning_bid}}. Escrow #{{escrow_number}} created.',
                'push_title' => 'Auction Won!',
                'push_body' => 'Congratulations! You won the auction for {{listing_title}} with bid {{winning_bid}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                    'winning_bid' => 'Winning bid amount',
                    'seller_username' => 'Seller username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                    'action_required' => 'Action required message',
                ],
            ],
            [
                'act' => 'AUCTION_ENDED_SOLD',
                'name' => 'Auction Ended - Sold',
                'subject' => 'Your auction "{{listing_title}}" has ended - Item Sold!',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour auction for \"{{listing_title}}\" has ended successfully!\n\nFinal Price: {{final_price}} {{site_currency}}\n\nWinner: {{winner_username}}\n\nListing Number: {{listing_number}}\n\nAn escrow has been automatically created for this transaction.\n\nEscrow Number: {{escrow_number}}\n\nView Escrow",
                'sms_body' => 'Auction ended: {{listing_title}} sold for {{final_price}} to {{winner_username}}. Escrow #{{escrow_number}} created.',
                'push_title' => 'Auction Ended - Sold!',
                'push_body' => 'Your auction {{listing_title}} sold for {{final_price}} to {{winner_username}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                    'final_price' => 'Final sale price',
                    'winner' => 'Winner username',
                    'winner_username' => 'Winner username',
                    'escrow_number' => 'Escrow number',
                    'escrow_id' => 'Escrow ID',
                ],
            ],
            [
                'act' => 'AUCTION_ENDED_OUTBID',
                'name' => 'Auction Ended - Outbid',
                'subject' => 'Auction ended: You were outbid on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nThe auction for \"{{listing_title}}\" has ended.\n\nUnfortunately, your bid of {{your_bid}} {{site_currency}} was not the winning bid.\n\nWinning Bid: {{winning_bid}} {{site_currency}}\n\nWinner: {{winner_username}}\n\nThank you for participating!",
                'sms_body' => 'Auction ended: You were outbid on {{listing_title}}. Winning bid: {{winning_bid}}.',
                'push_title' => 'Auction Ended',
                'push_body' => 'Auction for {{listing_title}} ended. You were outbid. Winning bid: {{winning_bid}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'your_bid' => 'Your bid amount',
                    'winning_bid' => 'Winning bid amount',
                    'winner_username' => 'Winner username',
                ],
            ],
            [
                'act' => 'BID_OUTBID',
                'name' => 'Bid Outbid',
                'subject' => 'You have been outbid on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYou have been outbid on \"{{listing_title}}\".\n\nYour Bid: {{your_bid}} {{site_currency}}\n\nNew Highest Bid: {{new_bid}} {{site_currency}}\n\nBidder: {{bidder_username}}\n\nPlace a higher bid to stay in the running!",
                'sms_body' => 'You were outbid on {{listing_title}}. New bid: {{new_bid}} by {{bidder_username}}.',
                'push_title' => 'You\'ve Been Outbid',
                'push_body' => 'You were outbid on {{listing_title}}. New bid: {{new_bid}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'your_bid' => 'Your bid amount',
                    'new_bid' => 'New highest bid amount',
                    'bidder' => 'Bidder username',
                    'bidder_username' => 'Bidder username',
                ],
            ],
            [
                'act' => 'NEW_BID_RECEIVED',
                'name' => 'New Bid Received',
                'subject' => 'New bid received on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYou have received a new bid on your listing \"{{listing_title}}\".\n\nBid Amount: {{bid_amount}} {{site_currency}}\n\nBidder: {{bidder_username}}\n\nCurrent Highest Bid: {{current_highest}} {{site_currency}}\n\nView Listing",
                'sms_body' => 'New bid {{bid_amount}} received on {{listing_title}} from {{bidder_username}}.',
                'push_title' => 'New Bid Received',
                'push_body' => 'New bid of {{bid_amount}} received on {{listing_title}} from {{bidder_username}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'bid_amount' => 'Bid amount',
                    'bidder' => 'Bidder username',
                    'bidder_username' => 'Bidder username',
                    'current_highest' => 'Current highest bid',
                ],
            ],
            [
                'act' => 'WATCHED_LISTING_NEW_BID',
                'name' => 'Watched Listing - New Bid',
                'subject' => 'New bid on watched listing "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nA new bid has been placed on a listing you're watching: \"{{listing_title}}\".\n\nBid Amount: {{bid_amount}} {{site_currency}}\n\nView Listing",
                'sms_body' => 'New bid {{bid_amount}} on watched listing {{listing_title}}.',
                'push_title' => 'New Bid on Watched Listing',
                'push_body' => 'New bid of {{bid_amount}} on watched listing {{listing_title}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'bid_amount' => 'Bid amount',
                ],
            ],
            [
                'act' => 'NEW_OFFER_RECEIVED',
                'name' => 'New Offer Received',
                'subject' => 'New offer received on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYou have received a new offer on your listing \"{{listing_title}}\".\n\nOffer Amount: {{offer_amount}} {{site_currency}}\n\nAsking Price: {{asking_price}} {{site_currency}}\n\nBuyer: {{buyer_username}}\n\nMessage: {{message}}\n\nView Offer",
                'sms_body' => 'New offer {{offer_amount}} received on {{listing_title}} from {{buyer_username}}.',
                'push_title' => 'New Offer Received',
                'push_body' => 'New offer of {{offer_amount}} received on {{listing_title}} from {{buyer_username}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'offer_amount' => 'Offer amount',
                    'asking_price' => 'Asking price',
                    'buyer' => 'Buyer username',
                    'buyer_username' => 'Buyer username',
                    'message' => 'Offer message',
                ],
            ],
            [
                'act' => 'OFFER_REJECTED',
                'name' => 'Offer Rejected',
                'subject' => 'Your offer on "{{listing_title}}" has been rejected',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour offer on \"{{listing_title}}\" has been rejected by the seller.\n\nOffer Amount: {{offer_amount}} {{site_currency}}\n\nSeller: {{seller_username}}\n\nReason: {{reason}}\n\nYou can make a new offer or browse other listings.",
                'sms_body' => 'Your offer {{offer_amount}} on {{listing_title}} was rejected. Reason: {{reason}}.',
                'push_title' => 'Offer Rejected',
                'push_body' => 'Your offer on {{listing_title}} has been rejected.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'offer_amount' => 'Offer amount',
                    'reason' => 'Rejection reason',
                    'seller_username' => 'Seller username',
                ],
            ],
            [
                'act' => 'OFFER_COUNTERED',
                'name' => 'Offer Countered',
                'subject' => 'Counter offer received on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nThe seller has made a counter offer on \"{{listing_title}}\".\n\nYour Original Offer: {{original_amount}} {{site_currency}}\n\nCounter Offer: {{counter_amount}} {{site_currency}}\n\nSeller: {{seller_username}}\n\nMessage: {{message}}\n\nYou can accept, reject, or make another counter offer.\n\nView Offer",
                'sms_body' => 'Counter offer {{counter_amount}} received on {{listing_title}}. Your offer was {{original_amount}}.',
                'push_title' => 'Counter Offer Received',
                'push_body' => 'Counter offer of {{counter_amount}} received on {{listing_title}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'original_amount' => 'Original offer amount',
                    'counter_amount' => 'Counter offer amount',
                    'message' => 'Counter offer message',
                    'seller_username' => 'Seller username',
                ],
            ],
            [
                'act' => 'QUESTION_ANSWERED',
                'name' => 'Question Answered',
                'subject' => 'Your question about "{{listing_title}}" has been answered',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour question about \"{{listing_title}}\" has been answered.\n\nQuestion: {{question}}\n\nAnswer: {{answer}}\n\nSeller: {{seller_username}}\n\nView Listing",
                'sms_body' => 'Your question about {{listing_title}} has been answered.',
                'push_title' => 'Question Answered',
                'push_body' => 'Your question about {{listing_title}} has been answered.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'question' => 'Question text',
                    'answer' => 'Answer text',
                    'seller_username' => 'Seller username',
                ],
            ],
            [
                'act' => 'NEW_QUESTION_RECEIVED',
                'name' => 'New Question Received',
                'subject' => 'New question received on "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYou have received a new question on your listing \"{{listing_title}}\".\n\nQuestion: {{question}}\n\nAsked by: {{asker_username}}\n\nPlease answer the question to help potential buyers.",
                'sms_body' => 'New question received on {{listing_title}} from {{asker_username}}.',
                'push_title' => 'New Question Received',
                'push_body' => 'New question received on {{listing_title}} from {{asker_username}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'question' => 'Question text',
                    'asker' => 'Asker username',
                    'asker_username' => 'Asker username',
                ],
            ],
            [
                'act' => 'NDA_SIGNED',
                'name' => 'NDA Signed',
                'subject' => 'NDA signed for "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nAn NDA has been signed for your listing \"{{listing_title}}\".\n\nSigner: {{signer_name}}\n\nSigned At: {{signed_at}}\n\nView Listing",
                'sms_body' => 'NDA signed for {{listing_title}} by {{signer_name}}.',
                'push_title' => 'NDA Signed',
                'push_body' => 'NDA signed for {{listing_title}} by {{signer_name}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'signer' => 'Signer username',
                    'signer_username' => 'Signer username',
                    'signer_name' => 'Signer full name',
                    'signed_at' => 'Signed date and time',
                ],
            ],
            [
                'act' => 'NDA_SIGNED_CONFIRMATION',
                'name' => 'NDA Signed Confirmation',
                'subject' => 'NDA signed successfully for "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYou have successfully signed the NDA for \"{{listing_title}}\".\n\nSeller: {{seller_name}}\n\nSigned At: {{signed_at}}\n\nExpires At: {{expires_at}}\n\nYou can now access the confidential information for this listing.",
                'sms_body' => 'NDA signed successfully for {{listing_title}}. Expires: {{expires_at}}.',
                'push_title' => 'NDA Signed Successfully',
                'push_body' => 'NDA signed successfully for {{listing_title}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'seller' => 'Seller username',
                    'seller_username' => 'Seller username',
                    'seller_name' => 'Seller full name',
                    'signed_at' => 'Signed date and time',
                    'expires_at' => 'Expiration date',
                ],
            ],
            [
                'act' => 'NDA_REVOKED',
                'name' => 'NDA Revoked',
                'subject' => 'NDA revoked for "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nThe NDA for \"{{listing_title}}\" has been revoked.\n\nSeller: {{seller_name}}\n\nRevoked At: {{revoked_at}}\n\nReason: {{reason}}\n\nYou no longer have access to the confidential information for this listing.",
                'sms_body' => 'NDA revoked for {{listing_title}}. Reason: {{reason}}.',
                'push_title' => 'NDA Revoked',
                'push_body' => 'NDA revoked for {{listing_title}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'revoked_at' => 'Revoked date and time',
                    'reason' => 'Revocation reason',
                    'seller_username' => 'Seller username',
                    'seller_name' => 'Seller full name',
                ],
            ],
            [
                'act' => 'MILESTONE_CREATED',
                'name' => 'Milestone Created',
                'subject' => 'New milestone created for escrow #{{escrow_number}}',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nA new milestone has been created for escrow #{{escrow_number}}.\n\nMilestone Note: {{milestone_note}}\n\nMilestone Amount: {{milestone_amount}} {{site_currency}}\n\nCreated By: {{created_by}}\n\n{{action_required}}\n\nView Escrow",
                'sms_body' => 'New milestone created for escrow #{{escrow_number}}: {{milestone_amount}}.',
                'push_title' => 'New Milestone Created',
                'push_body' => 'New milestone created for escrow #{{escrow_number}}.',
                'shortcodes' => [
                    'escrow_number' => 'Escrow number',
                    'milestone_note' => 'Milestone note',
                    'milestone_amount' => 'Milestone amount',
                    'action_required' => 'Action required message',
                    'created_by' => 'Created by username',
                ],
            ],
            [
                'act' => 'MILESTONES_GENERATED',
                'name' => 'Milestones Generated',
                'subject' => 'Milestones generated for escrow #{{escrow_number}}',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nMilestones have been generated for escrow #{{escrow_number}}.\n\nTemplate: {{template_name}}\n\nNumber of Milestones: {{milestone_count}}\n\nCreated By: {{created_by}}\n\n{{action_required}}\n\nView Escrow",
                'sms_body' => '{{milestone_count}} milestones generated for escrow #{{escrow_number}}.',
                'push_title' => 'Milestones Generated',
                'push_body' => '{{milestone_count}} milestones generated for escrow #{{escrow_number}}.',
                'shortcodes' => [
                    'escrow_number' => 'Escrow number',
                    'template_name' => 'Template name',
                    'milestone_count' => 'Number of milestones',
                    'action_required' => 'Action required message',
                    'created_by' => 'Created by username',
                ],
            ],
            [
                'act' => 'MILESTONE_APPROVED',
                'name' => 'Milestone Approved',
                'subject' => 'Milestone approved for escrow #{{escrow_number}}',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nA milestone has been approved for escrow #{{escrow_number}}.\n\nMilestone Note: {{milestone_note}}\n\nMilestone Amount: {{milestone_amount}} {{site_currency}}\n\nApproved By: {{approved_by_username}}\n\nView Escrow",
                'sms_body' => 'Milestone approved for escrow #{{escrow_number}}: {{milestone_amount}}.',
                'push_title' => 'Milestone Approved',
                'push_body' => 'Milestone approved for escrow #{{escrow_number}}.',
                'shortcodes' => [
                    'escrow_number' => 'Escrow number',
                    'milestone_note' => 'Milestone note',
                    'milestone_amount' => 'Milestone amount',
                    'approved_by' => 'Approved by username',
                    'approved_by_username' => 'Approved by username',
                ],
            ],
            [
                'act' => 'MILESTONE_REJECTED',
                'name' => 'Milestone Rejected',
                'subject' => 'Milestone rejected for escrow #{{escrow_number}}',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nA milestone has been rejected for escrow #{{escrow_number}}.\n\nMilestone Note: {{milestone_note}}\n\nMilestone Amount: {{milestone_amount}} {{site_currency}}\n\nRejected By: {{rejected_by_username}}\n\nRejection Reason: {{rejection_reason}}\n\nView Escrow",
                'sms_body' => 'Milestone rejected for escrow #{{escrow_number}}. Reason: {{rejection_reason}}.',
                'push_title' => 'Milestone Rejected',
                'push_body' => 'Milestone rejected for escrow #{{escrow_number}}.',
                'shortcodes' => [
                    'escrow_number' => 'Escrow number',
                    'milestone_note' => 'Milestone note',
                    'rejection_reason' => 'Rejection reason',
                    'rejected_by' => 'Rejected by username',
                    'rejected_by_username' => 'Rejected by username',
                ],
            ],
            [
                'act' => 'LISTING_APPROVED',
                'name' => 'Listing Approved',
                'subject' => 'Your listing "{{listing_title}}" has been approved',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nCongratulations! Your listing \"{{listing_title}}\" has been approved and is now live.\n\nListing Number: {{listing_number}}\n\nYour listing is now visible to all users and ready to receive offers and bids.\n\nView Listing",
                'sms_body' => 'Your listing {{listing_title}} ({{listing_number}}) has been approved.',
                'push_title' => 'Listing Approved',
                'push_body' => 'Your listing {{listing_title}} has been approved and is now live.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                ],
            ],
            [
                'act' => 'LISTING_REJECTED',
                'name' => 'Listing Rejected',
                'subject' => 'Your listing "{{listing_title}}" has been rejected',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nUnfortunately, your listing \"{{listing_title}}\" has been rejected.\n\nListing Number: {{listing_number}}\n\nReason: {{reason}}\n\nPlease review the reason and make necessary changes before resubmitting.\n\nView Listing",
                'sms_body' => 'Your listing {{listing_title}} ({{listing_number}}) was rejected. Reason: {{reason}}.',
                'push_title' => 'Listing Rejected',
                'push_body' => 'Your listing {{listing_title}} has been rejected.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                    'reason' => 'Rejection reason',
                ],
            ],
            [
                'act' => 'LISTING_CANCELLED_ADMIN',
                'name' => 'Listing Cancelled by Admin',
                'subject' => 'Your listing "{{listing_title}}" has been cancelled',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour listing \"{{listing_title}}\" has been cancelled by an administrator.\n\nListing Number: {{listing_number}}\n\nReason: {{reason}}\n\nIf you have any questions, please contact support.\n\nView Listing",
                'sms_body' => 'Your listing {{listing_title}} ({{listing_number}}) has been cancelled by admin. Reason: {{reason}}.',
                'push_title' => 'Listing Cancelled',
                'push_body' => 'Your listing {{listing_title}} has been cancelled.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                    'reason' => 'Cancellation reason',
                ],
            ],
            [
                'act' => 'LISTING_DEACTIVATED',
                'name' => 'Listing Deactivated by Admin',
                'subject' => 'Your listing "{{listing_title}}" has been deactivated',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour listing \"{{listing_title}}\" has been deactivated by an administrator.\n\nListing Number: {{listing_number}}\n\nReason: {{reason}}\n\nThe listing is no longer visible to buyers, but it can be reactivated. If you have any questions, please contact support.\n\nView Listing",
                'sms_body' => 'Your listing {{listing_title}} ({{listing_number}}) has been deactivated. Reason: {{reason}}.',
                'push_title' => 'Listing Deactivated',
                'push_body' => 'Your listing {{listing_title}} has been deactivated.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                    'reason' => 'Deactivation reason',
                ],
            ],
            [
                'act' => 'LISTING_REACTIVATED',
                'name' => 'Listing Reactivated by Admin',
                'subject' => 'Your listing "{{listing_title}}" has been reactivated',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nGreat news! Your listing \"{{listing_title}}\" has been reactivated by an administrator.\n\nListing Number: {{listing_number}}\n\nYour listing is now visible to buyers again and ready to receive offers and bids.\n\nView Listing",
                'sms_body' => 'Your listing {{listing_title}} ({{listing_number}}) has been reactivated.',
                'push_title' => 'Listing Reactivated',
                'push_body' => 'Your listing {{listing_title}} has been reactivated.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'listing_number' => 'Listing number',
                ],
            ],
            [
                'act' => 'AUCTION_ENDED_NO_BIDS',
                'name' => 'Auction Ended - No Bids',
                'subject' => 'Auction ended with no bids: "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour auction for \"{{listing_title}}\" has ended with no bids.\n\nThe listing has been marked as expired. You can relist it or try a different sale method.\n\nView Listing",
                'sms_body' => 'Auction ended with no bids for {{listing_title}}.',
                'push_title' => 'Auction Ended - No Bids',
                'push_body' => 'Your auction {{listing_title}} ended with no bids.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                ],
            ],
            [
                'act' => 'AUCTION_ENDED_RESERVE_NOT_MET',
                'name' => 'Auction Ended - Reserve Not Met',
                'subject' => 'Auction ended - Reserve not met: "{{listing_title}}"',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour auction for \"{{listing_title}}\" has ended, but the reserve price was not met.\n\nHighest Bid: {{highest_bid}} {{site_currency}}\n\nReserve Price: {{reserve_price}} {{site_currency}}\n\nThe listing has been marked as expired. You can relist it or try a different sale method.\n\nView Listing",
                'sms_body' => 'Auction ended: reserve not met for {{listing_title}}. Highest: {{highest_bid}}, Reserve: {{reserve_price}}.',
                'push_title' => 'Auction Ended - Reserve Not Met',
                'push_body' => 'Auction ended: reserve not met for {{listing_title}}.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'highest_bid' => 'Highest bid amount',
                    'reserve_price' => 'Reserve price',
                ],
            ],
            [
                'act' => 'BID_CANCELLED_ADMIN',
                'name' => 'Bid Cancelled by Admin',
                'subject' => 'Your bid on "{{listing_title}}" has been cancelled',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour bid on \"{{listing_title}}\" has been cancelled by an administrator.\n\nBid Amount: {{bid_amount}} {{site_currency}}\n\nIf you have any questions, please contact support.\n\nView Listing",
                'sms_body' => 'Your bid {{bid_amount}} on {{listing_title}} was cancelled by admin.',
                'push_title' => 'Bid Cancelled',
                'push_body' => 'Your bid on {{listing_title}} has been cancelled.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'bid_amount' => 'Bid amount',
                ],
            ],
            [
                'act' => 'OFFER_CANCELLED_ADMIN',
                'name' => 'Offer Cancelled by Admin',
                'subject' => 'Your offer on "{{listing_title}}" has been cancelled',
                'email_body' => "Hello {{fullname}} ({{username}})\n\nYour offer on \"{{listing_title}}\" has been cancelled by an administrator.\n\nOffer Amount: {{offer_amount}} {{site_currency}}\n\nIf you have any questions, please contact support.\n\nView Listing",
                'sms_body' => 'Your offer {{offer_amount}} on {{listing_title}} was cancelled by admin.',
                'push_title' => 'Offer Cancelled',
                'push_body' => 'Your offer on {{listing_title}} has been cancelled.',
                'shortcodes' => [
                    'listing_title' => 'Listing title',
                    'offer_amount' => 'Offer amount',
                ],
            ],
        ];

        foreach ($templates as $t) {
            $exists = NotificationTemplate::where('act', $t['act'])->exists();
            if ($exists) {
                continue;
            }

            $nt = new NotificationTemplate();
            $nt->act = $t['act'];
            $nt->name = $t['name'];
            $nt->subject = $t['subject'];
            $nt->email_body = $t['email_body'];
            $nt->sms_body = $t['sms_body'];
            $nt->push_title = $t['push_title'];
            $nt->push_body = $t['push_body'];
            $nt->shortcodes = $t['shortcodes'] ?? [];
            $nt->email_status = Status::ENABLE;
            $nt->sms_status = Status::DISABLE;
            $nt->push_status = Status::DISABLE;
            $nt->save();
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_templates')) {
            return;
        }

        NotificationTemplate::whereIn('act', [
            'OFFER_ACCEPTED',
            'COUNTER_OFFER_ACCEPTED',
            'PURCHASE_BUY_NOW',
            'LISTING_SOLD_BUY_NOW',
            'AUCTION_WON',
            'AUCTION_ENDED_SOLD',
            'AUCTION_ENDED_OUTBID',
            'BID_OUTBID',
            'NEW_BID_RECEIVED',
            'WATCHED_LISTING_NEW_BID',
            'NEW_OFFER_RECEIVED',
            'OFFER_REJECTED',
            'OFFER_COUNTERED',
            'QUESTION_ANSWERED',
            'NEW_QUESTION_RECEIVED',
            'NDA_SIGNED',
            'NDA_SIGNED_CONFIRMATION',
            'NDA_REVOKED',
            'MILESTONE_CREATED',
            'MILESTONES_GENERATED',
            'MILESTONE_APPROVED',
            'MILESTONE_REJECTED',
            'LISTING_APPROVED',
            'LISTING_REJECTED',
            'LISTING_CANCELLED_ADMIN',
            'LISTING_DEACTIVATED',
            'LISTING_REACTIVATED',
            'AUCTION_ENDED_NO_BIDS',
            'AUCTION_ENDED_RESERVE_NOT_MET',
            'BID_CANCELLED_ADMIN',
            'OFFER_CANCELLED_ADMIN',
        ])->delete();
    }
};
