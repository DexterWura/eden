@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('NDA Details')</h4>
                    <a href="{{ route('admin.nda.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="las la-arrow-left"></i> @lang('Back')
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>@lang('Listing Information')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">@lang('Listing Title')</th>
                                <td><a href="{{ route('admin.listing.detail', $nda->listing_id) }}">{{ $nda->listing->title }}</a></td>
                            </tr>
                            <tr>
                                <th>@lang('Listing Number')</th>
                                <td>{{ $nda->listing->listing_number }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Business Type')</th>
                                <td>{{ ucfirst(str_replace('_', ' ', $nda->listing->business_type)) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>@lang('Signer Information')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">@lang('Username')</th>
                                <td><a href="{{ route('admin.users.detail', $nda->user_id) }}">{{ $nda->user->username }}</a></td>
                            </tr>
                            <tr>
                                <th>@lang('Email')</th>
                                <td>{{ $nda->user->email }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Full Name')</th>
                                <td>{{ $nda->signature }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>@lang('NDA Details')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">@lang('Status')</th>
                                <td>
                                    @if($nda->status === 'signed' && $nda->isActive())
                                        <span class="badge bg-success">@lang('Active')</span>
                                    @elseif($nda->status === 'expired' || $nda->isExpired())
                                        <span class="badge bg-warning">@lang('Expired')</span>
                                    @elseif($nda->status === 'revoked')
                                        <span class="badge bg-danger">@lang('Revoked')</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($nda->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>@lang('Signed At')</th>
                                <td>{{ $nda->signed_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Expires At')</th>
                                <td>{{ $nda->expires_at ? $nda->expires_at->format('F d, Y H:i:s') : 'Never' }}</td>
                            </tr>
                            @if($nda->isRevoked())
                            <tr>
                                <th>@lang('Revoked At')</th>
                                <td>{{ $nda->revoked_at->format('F d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Revoked By')</th>
                                <td>{{ $nda->revokedBy ? $nda->revokedBy->username : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Revocation Reason')</th>
                                <td>{{ $nda->revocation_reason ?: 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>@lang('Technical Details')</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">@lang('IP Address')</th>
                                <td>{{ $nda->ip_address }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Device Type')</th>
                                <td>{{ ucfirst($nda->device_type ?: 'Unknown') }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Screen Resolution')</th>
                                <td>{{ $nda->screen_resolution ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Timezone')</th>
                                <td>{{ $nda->timezone ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>@lang('Read Time')</th>
                                <td>{{ $nda->read_time_seconds ? $nda->read_time_seconds . ' seconds' : 'N/A' }}</td>
                            </tr>
                            @if($nda->signature_image)
                            <tr>
                                <th>@lang('Signature')</th>
                                <td>
                                    <img src="{{ $nda->signature_image }}" alt="Signature" style="max-width: 200px; border: 1px solid #ddd;">
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.nda.audit-logs', $nda->id) }}" class="btn btn-primary">
                        <i class="las la-history"></i> @lang('View Audit Logs')
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
