<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NdaDocument;
use App\Models\NdaAuditLog;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NdaController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'NDA Management';

        $ndas = NdaDocument::with(['listing', 'user', 'revokedBy'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('listing', function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('listing_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->active();
                } elseif ($status === 'expired') {
                    $q->expired();
                } elseif ($status === 'revoked') {
                    $q->where('status', 'revoked');
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($request->listing_id, function ($q, $listingId) {
                $q->where('listing_id', $listingId);
            })
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->date_from, function ($q, $date) {
                $q->whereDate('signed_at', '>=', $date);
            })
            ->when($request->date_to, function ($q, $date) {
                $q->whereDate('signed_at', '<=', $date);
            })
            ->orderBy('signed_at', 'desc')
            ->paginate(getPaginate());

        // Statistics
        $stats = [
            'total' => NdaDocument::count(),
            'active' => NdaDocument::active()->count(),
            'expired' => NdaDocument::expired()->count(),
            'revoked' => NdaDocument::where('status', 'revoked')->count(),
            'signed_today' => NdaDocument::whereDate('signed_at', today())->count(),
            'signed_this_week' => NdaDocument::whereBetween('signed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'signed_this_month' => NdaDocument::whereMonth('signed_at', now()->month)->count(),
        ];

        // Most protected listings
        $mostProtected = Listing::where('requires_nda', true)
            ->where('is_confidential', true)
            ->withCount('ndaDocuments')
            ->orderBy('nda_documents_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.nda.index', compact('pageTitle', 'ndas', 'stats', 'mostProtected'));
    }

    public function show($id)
    {
        $pageTitle = 'NDA Details';
        $nda = NdaDocument::with(['listing', 'user', 'revokedBy', 'auditLogs.user'])->findOrFail($id);
        
        return view('admin.nda.show', compact('pageTitle', 'nda'));
    }

    public function auditLogs(Request $request, $id)
    {
        $pageTitle = 'NDA Audit Logs';
        $nda = NdaDocument::findOrFail($id);
        
        $logs = NdaAuditLog::where('nda_document_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(getPaginate());

        return view('admin.nda.audit-logs', compact('pageTitle', 'nda', 'logs'));
    }

    public function export(Request $request)
    {
        $ndas = NdaDocument::with(['listing', 'user'])
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->active();
                } elseif ($status === 'expired') {
                    $q->expired();
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($request->date_from, function ($q, $date) {
                $q->whereDate('signed_at', '>=', $date);
            })
            ->when($request->date_to, function ($q, $date) {
                $q->whereDate('signed_at', '<=', $date);
            })
            ->orderBy('signed_at', 'desc')
            ->get();

        $filename = 'nda_export_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($ndas) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Listing Title',
                'Listing Number',
                'Signer Username',
                'Signer Email',
                'Signed At',
                'Expires At',
                'Status',
                'IP Address',
                'Device Type',
                'Revoked At',
                'Revoked By'
            ]);

            // Data
            foreach ($ndas as $nda) {
                fputcsv($file, [
                    $nda->id,
                    $nda->listing->title,
                    $nda->listing->listing_number,
                    $nda->user->username,
                    $nda->user->email,
                    $nda->signed_at->format('Y-m-d H:i:s'),
                    $nda->expires_at ? $nda->expires_at->format('Y-m-d H:i:s') : 'Never',
                    $nda->status,
                    $nda->ip_address,
                    $nda->device_type,
                    $nda->revoked_at ? $nda->revoked_at->format('Y-m-d H:i:s') : '',
                    $nda->revokedBy ? $nda->revokedBy->username : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
