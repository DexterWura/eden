<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\NdaDocument;
use App\Models\NdaAuditLog;
use App\Services\NdaDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// use Barryvdh\DomPDF\Facade\Pdf;

class NdaController extends Controller
{
    public function show($listingId)
    {
        $listing = Listing::active()->findOrFail($listingId);

        // Check if listing requires NDA
        if (!$listing->is_confidential || !$listing->requires_nda) {
            abort(404);
        }

        // Check if user is seller
        if (auth()->check() && auth()->id() === $listing->user_id) {
            return redirect()->route('marketplace.listing.show', $listing->slug);
        }

        // Check if user has already signed NDA
        if (auth()->check() && $listing->hasSignedNda()) {
            return redirect()->route('marketplace.listing.show', $listing->slug);
        }

        $pageTitle = 'Non-Disclosure Agreement Required';

        return view('Template::marketplace.nda.show', compact('pageTitle', 'listing'));
    }

    public function sign(Request $request, $listingId)
    {
        $request->validate([
            'signature' => 'required|string|max:255',
            'signature_image' => 'nullable|string',
            'agree_terms' => 'required|accepted',
            'read_time_seconds' => 'nullable|integer|min:0',
            'browser_fingerprint' => 'nullable|string|max:64',
            'device_type' => 'nullable|string|max:20',
            'screen_resolution' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:50',
            'referrer_url' => 'nullable|string|max:500',
        ]);

        try {
            $listing = Listing::active()->findOrFail($listingId);

            // Check if listing requires NDA
            if (!$listing->is_confidential || !$listing->requires_nda) {
                $notify[] = ['error', 'This listing does not require an NDA'];
                return back()->withNotify($notify);
            }

            // Check if user is seller
            if (auth()->id() === $listing->user_id) {
                $notify[] = ['error', 'You cannot sign an NDA for your own listing'];
                return back()->withNotify($notify);
            }

            // Check if already signed
            if ($listing->hasSignedNda()) {
                $notify[] = ['success', 'You have already signed the NDA'];
                return redirect()->route('marketplace.listing.show', $listing->slug)->withNotify($notify);
            }

            DB::beginTransaction();

            try {
                // Create NDA document record
                $nda = new NdaDocument();
                $nda->listing_id = $listing->id;
                $nda->user_id = auth()->id();
                $nda->signature = $request->signature;
                $nda->signature_image = $request->signature_image;
                $nda->signed_at = now();
                $nda->status = 'signed';
                $nda->ip_address = $request->ip();
                $nda->user_agent = $request->userAgent();
                $nda->read_time_seconds = $request->read_time_seconds ?? 0;
                $nda->browser_fingerprint = $request->browser_fingerprint;
                $nda->device_type = $request->device_type;
                $nda->screen_resolution = $request->screen_resolution;
                $nda->timezone = $request->timezone;
                $nda->referrer_url = $request->referrer_url;
                $nda->expires_at = now()->addYear(); // NDA valid for 1 year
                $nda->template_version = '1.0';
                $nda->save();

                // Generate and store NDA document PDF
                try {
                    $documentService = new NdaDocumentService();
                    $documentPath = $documentService->generatePdf($nda);
                    if ($documentPath && is_string($documentPath) && !str_starts_with($documentPath, '<')) {
                        // Server-side PDF generated
                        $nda->document_path = $documentPath;
                        $nda->save();
                    }
                } catch (\Exception $e) {
                    // Log PDF generation error but don't fail the NDA signing
                    Log::warning('NDA PDF generation failed, continuing without PDF: ' . $e->getMessage(), [
                        'nda_id' => $nda->id,
                        'listing_id' => $listing->id,
                        'user_id' => auth()->id()
                    ]);
                }

                // Create audit log for signing
                $this->logAuditAction($nda, 'signed', [
                    'signature' => $request->signature,
                    'read_time_seconds' => $request->read_time_seconds ?? 0,
                    'device_type' => $request->device_type,
                ]);

                // Log NDA signing
                \Log::info('NDA signed', [
                    'nda_id' => $nda->id,
                    'listing_id' => $listing->id,
                    'listing_number' => $listing->listing_number,
                    'user_id' => auth()->id(),
                    'username' => auth()->user()->username,
                    'signature' => $request->signature,
                    'expires_at' => $nda->expires_at,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                DB::commit();

                // Notify seller
                $signer = auth()->user();
                notify($listing->seller, 'NDA_SIGNED', [
                    'listing_title' => $listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                    'signer' => $signer->username,
                    'signer_username' => $signer->username,
                    'signer_name' => $signer->fullname ?? $signer->username,
                    'signed_at' => now()->format('Y-m-d H:i:s'),
                ]);

                // Notify signer with confirmation email
                try {
                    $seller = $listing->seller;
                    notify(auth()->user(), 'NDA_SIGNED_CONFIRMATION', [
                        'listing_title' => $listing->title,
                        'listing_url' => url(route('marketplace.listing.show', $listing->slug)),
                        'seller' => $seller->username,
                        'seller_username' => $seller->username,
                        'seller_name' => $seller->fullname ?? $seller->username,
                        'signed_at' => now()->format('F d, Y H:i:s'),
                        'expires_at' => $nda->expires_at->format('F d, Y'),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send NDA confirmation email', [
                        'nda_id' => $nda->id,
                        'error' => $e->getMessage()
                    ]);
                }

                $notify[] = ['success', 'NDA signed successfully. You can now view the listing details.'];
                return redirect()->route('marketplace.listing.show', $listing->slug)->withNotify($notify);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('NDA signing failed: ' . $e->getMessage(), [
                    'listing_id' => $listingId,
                    'user_id' => auth()->id(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('NDA signing error: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while signing the NDA. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function download($id)
    {
        $nda = NdaDocument::where('user_id', auth()->id())->findOrFail($id);

        // Log download action
        $this->logAuditAction($nda, 'downloaded');

        $documentService = new NdaDocumentService();
        
        // Check if server-side PDF exists
        if ($nda->document_path && Storage::disk('local')->exists($nda->document_path)) {
            return Storage::disk('local')->download($nda->document_path, 'nda-' . $nda->listing->listing_number . '-' . $nda->id . '.pdf');
        }

        // Generate HTML for browser-based PDF
        $html = $documentService->generateHtmlForBrowserPdf($nda);
        
        // Return view with HTML for browser PDF generation
        return view('pdf.nda-document-view', [
            'html' => $html,
            'nda' => $nda,
            'filename' => 'nda-' . $nda->listing->listing_number . '-' . $nda->id . '.pdf'
        ]);
    }

    public function view($id)
    {
        $nda = NdaDocument::where('user_id', auth()->id())->findOrFail($id);

        // Log view action
        $this->logAuditAction($nda, 'viewed');

        $documentService = new NdaDocumentService();
        $html = $documentService->generateHtmlForBrowserPdf($nda);

        return view('pdf.nda-document-view', [
            'html' => $html,
            'nda' => $nda,
            'filename' => 'nda-' . $nda->listing->listing_number . '-' . $nda->id . '.pdf'
        ]);
    }

    /**
     * Generate a text version of the NDA for download
     */
    private function generateNdaText(NdaDocument $nda)
    {
        $listing = $nda->listing;
        $signer = $nda->user;
        $seller = $listing->seller;

        $text = "NON-DISCLOSURE AGREEMENT\n";
        $text .= "========================\n\n";

        $text .= "Listing: {$listing->title}\n";
        $text .= "Listing Number: {$listing->listing_number}\n\n";

        $text .= "Seller: {$seller->username} ({$seller->email})\n";
        $text .= "Signer: {$signer->username} ({$signer->email})\n\n";

        $text .= "Signed At: {$nda->signed_at->format('F d, Y H:i:s')}\n";
        $text .= "Signature: {$nda->signature}\n";
        $text .= "IP Address: {$nda->ip_address}\n";
        $text .= "Expires: " . ($nda->expires_at ? $nda->expires_at->format('F d, Y') : 'Never') . "\n\n";

        $text .= "This agreement confirms that the signer has reviewed and agreed to the non-disclosure terms for this listing.\n\n";

        $text .= "Generated on: " . now()->format('F d, Y H:i:s') . "\n";

        return $text;
    }

    public function myNdas()
    {
        $pageTitle = 'My NDA Documents';
        $user = auth()->user();

        $ndas = NdaDocument::where('user_id', $user->id)
            ->with(['listing.images', 'listing.seller'])
            ->orderBy('signed_at', 'desc')
            ->paginate(getPaginate());

        return view('Template::user.nda.index', compact('pageTitle', 'ndas'));
    }

    public function revoke(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $nda = NdaDocument::with(['listing', 'user'])->findOrFail($id);

        // Check if user is the seller
        if (auth()->id() !== $nda->listing->user_id) {
            $notify[] = ['error', 'You are not authorized to revoke this NDA'];
            return back()->withNotify($notify);
        }

        // Check if already revoked
        if ($nda->isRevoked()) {
            $notify[] = ['error', 'This NDA has already been revoked'];
            return back()->withNotify($notify);
        }

        try {
            DB::beginTransaction();

            // Revoke the NDA
            $nda->revoke(auth()->id(), $request->reason);

            // Notify the signer
            try {
                $seller = $nda->listing->seller;
                notify($nda->user, 'NDA_REVOKED', [
                    'listing_title' => $nda->listing->title,
                    'listing_url' => url(route('marketplace.listing.show', $nda->listing->slug)),
                    'revoked_at' => now()->format('F d, Y H:i:s'),
                    'reason' => $request->reason ?? 'No reason provided',
                    'seller_username' => $seller->username ?? $seller->name ?? 'Seller',
                    'seller_name' => $seller->fullname ?? $seller->username ?? 'Seller',
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send revocation notification', [
                    'nda_id' => $nda->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            $notify[] = ['success', 'NDA revoked successfully. The signer has been notified.'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('NDA revocation failed: ' . $e->getMessage());
            $notify[] = ['error', 'An error occurred while revoking the NDA. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    /**
     * Log audit action for NDA
     */
    private function logAuditAction(NdaDocument $nda, $action, $metadata = [])
    {
        try {
            $deviceInfo = [
                'device_type' => $nda->device_type,
                'screen_resolution' => $nda->screen_resolution,
                'timezone' => $nda->timezone,
            ];

            NdaAuditLog::create([
                'nda_document_id' => $nda->id,
                'action' => $action,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'browser_fingerprint' => $nda->browser_fingerprint,
                'device_info' => $deviceInfo,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create audit log: ' . $e->getMessage());
        }
    }
}
