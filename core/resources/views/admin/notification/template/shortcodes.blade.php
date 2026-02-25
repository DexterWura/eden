<div class="row">
    <div class="col-md-12">
        <div class="card overflow-hidden">
            <div class="card-body p-0">
                @php
                    $directActs = [
                        'DIRECT_ESCROW_SERVICE_FEE_PAID',
                        'DIRECT_ESCROW_MARKED_COMPLETE',
                        'DIRECT_ESCROW_ADMIN_ACTION',
                    ];
                @endphp
                @if(in_array($template->act, $directActs))
                    <div class="alert alert-warning m-3 mb-0">
                        <strong>@lang('Direct-payment escrow note:')</strong>
                        @lang('In this mode, the buyer is not charged any additional platform fee. The sale amount is paid outside the platform using the external payment link, and should NOT be described as “funds held” or “released”. Any platform monetization for Direct payout is via the seller-side Direct payout listing fee (charged on listing submission).')
                    </div>
                @endif
                <div class="table-responsive table-responsive--sm">
                    <table class="table align-items-center table--light">
                        <thead>
                        <tr>
                            <th>@lang('Short Code')</th>
                            <th>@lang('Description')</th>
                        </tr>
                        </thead>
                        <tbody class="list">
                            @foreach($template->shortcodes as $shortcode => $key)
                            <tr>
                                {{-- blade-formatter-disable --}}
                                <td><span class="short-codes">@php echo "{{". $shortcode ."}}"  @endphp</span></td>
                                {{-- blade-formatter-enable --}}
                                <td>{{ __($key) }}</td>
                            </tr>
                            @endforeach
                            @foreach(gs('global_shortcodes') as $shortCode => $codeDetails)
                            <tr>
                                {{-- blade-formatter-disable --}}
                                <td><span class="short-codes">@{{@php echo $shortCode @endphp}}</span></td>
                                {{-- blade-formatter-enable --}}
                                <td>{{ __($codeDetails) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- card end -->

    </div>
</div>
