@extends('admin.layouts.app')
@section('panel')
    @php
        $admin = auth('admin')->user();
        $showUsers = $admin->isSuperAdmin() || $admin->hasModule('manage_users');
        $showDeposits = $admin->isSuperAdmin() || $admin->hasModule('deposits');
        $showWithdrawals = $admin->isSuperAdmin() || $admin->hasModule('withdrawals');
        $showMarketplace = $admin->isSuperAdmin() || $admin->hasModule('marketplace');
        $showFinanceCharts = $showDeposits || $showWithdrawals;
        $hasAnyDashboardSection = $showUsers || $showDeposits || $showWithdrawals || $showMarketplace;
    @endphp

    @if(!$hasAnyDashboardSection)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h4 class="mb-0">@lang('Welcome') {{ $admin->name ?: $admin->username }}</h4>
                    </div>
                </div>
            </div>
        </div>
    @else
    {{-- User stats (manage_users) --}}
    @if($showUsers)
    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.all') }}" icon="las la-users" title="Total Users" value="{{ $widget['total_users'] }}"
                bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.active') }}" icon="las la-user-check" title="Active Users"
                value="{{ $widget['verified_users'] }}" bg="success" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.email.unverified') }}" icon="lar la-envelope" title="Email Unverified Users"
                value="{{ $widget['email_unverified_users'] }}" bg="danger" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget style="6" link="{{ route('admin.users.mobile.unverified') }}" icon="las la-comment-slash" title="Mobile Unverified Users"
                value="{{ $widget['mobile_unverified_users'] }}" bg="warning" />
        </div>
    </div>
    @endif

    <div class="row mt-2 gy-4">
        @if($showDeposits)
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Deposits')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_amount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Deposited')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a href="{{ route('admin.deposit.pending') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_pending'] }}</h6>
                                    <p class="widget-card-title">@lang('Pending Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a href="{{ route('admin.deposit.rejected') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $deposit['total_deposit_rejected'] }}</h6>
                                    <p class="widget-card-title">@lang('Rejected Deposits')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a href="{{ route('admin.deposit.list') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($deposit['total_deposit_charge']) }}</h6>
                                    <p class="widget-card-title">@lang('Deposited Charge')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($showWithdrawals)
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Withdrawals')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a href="{{ route('admin.withdraw.data.all') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="lar la-credit-card"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($withdrawals['total_withdraw_amount']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Withdrawn')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a href="{{ route('admin.withdraw.data.pending') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-spinner"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $withdrawals['total_withdraw_pending'] }}</h6>
                                    <p class="widget-card-title">@lang('Pending Withdrawals')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a href="{{ route('admin.withdraw.data.rejected') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-times-circle"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $withdrawals['total_withdraw_rejected'] }}</h6>
                                    <p class="widget-card-title">@lang('Rejected Withdrawals')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a href="{{ route('admin.withdraw.data.all') }}" class="widget-card-link"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-percent"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($withdrawals['total_withdraw_charge']) }}</h6>
                                    <p class="widget-card-title">@lang('Withdrawal Charge')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>


    {{-- Marketplace Statistics (Primary) --}}
    @if($showMarketplace)
    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-store" link="{{ route('admin.listing.index') }}" style="7" type="2"
                title="Total Listings" value="{{ number_format($marketplace['total_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-check-circle" link="{{ route('admin.listing.index', ['status' => Status::LISTING_ACTIVE]) }}" style="7" type="2"
                title="Active Listings" value="{{ number_format($marketplace['active_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="warning" icon="las la-clock" link="{{ route('admin.listing.index', ['status' => Status::LISTING_PENDING]) }}" style="7" type="2"
                title="Pending Approval" value="{{ number_format($marketplace['pending_listings']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-check-double" link="{{ route('admin.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Sold Listings" value="{{ number_format($marketplace['sold_listings']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-dollar-sign" link="{{ route('admin.listing.index', ['status' => Status::LISTING_SOLD]) }}" style="7" type="2"
                title="Total Sales Value" value="{{ showAmount($marketplace['total_sales_value']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-gavel" link="{{ route('admin.bid.index') }}" style="7" type="2"
                title="Total Bids" value="{{ number_format($marketplace['total_bids']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="info" icon="las la-handshake" link="{{ route('admin.offer.index') }}" style="7" type="2"
                title="Total Offers" value="{{ number_format($marketplace['total_offers']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="warning" icon="las la-clock" link="{{ route('admin.listing.index', ['sale_type' => 'auction']) }}" style="7" type="2"
                title="Active Auctions" value="{{ number_format($marketplace['active_auctions']) }}" />
        </div>
    </div>

    <div class="row gy-4 mt-2">
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="success" icon="las la-eye" link="{{ route('admin.listing.index') }}" style="7" type="2"
                title="Total Views" value="{{ number_format($marketplace['total_views']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="primary" icon="las la-money-bill-wave" link="{{ route('admin.escrow.index') }}" style="7" type="2"
                title="Marketplace Revenue" value="{{ showAmount($marketplace['marketplace_revenue']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="14" icon="fa fa-hand-holding-usd" link="{{ route('admin.escrow.index') }}" style="7" type="2"
                title="Total Escrowed" value="{{ showAmount($dataEscrow['total']) }}" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget bg="19" icon="fa fa-spinner" link="{{ route('admin.escrow.disputed') }}" style="7" type="2"
                title="Disputed Escrows" value="{{ number_format($dataEscrow['disputed']) }}" />
        </div>
    </div>
    @endif




    @if($showFinanceCharts)
    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title">@lang('Deposit & Withdraw Report')</h5>

                        <div id="dwDatePicker" class="border p-1 cursor-pointer rounded">
                            <i class="la la-calendar"></i>&nbsp;
                            <span></span> <i class="la la-caret-down"></i>
                        </div>
                    </div>
                    <div id="dwChartArea"> </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h5 class="card-title">@lang('Transactions Report')</h5>

                        <div id="trxDatePicker" class="border p-1 cursor-pointer rounded">
                            <i class="la la-calendar"></i>&nbsp;
                            <span></span> <i class="la la-caret-down"></i>
                        </div>
                    </div>

                    <div id="transactionChartArea"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($showUsers)
    <div class="row mb-none-30 mt-5">
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Browser') (@lang('Last 30 days'))</h5>
                    <canvas id="userBrowserChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By OS') (@lang('Last 30 days'))</h5>
                    <canvas id="userOsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">@lang('Login By Country') (@lang('Last 30 days'))</h5>
                    <canvas id="userCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
@endsection


@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/charts.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script')
    <script>
        "use strict";

        const start = moment().subtract(14, 'days');
        const end = moment();

        const dateRangeOptions = {
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
            maxDate: moment()
        }

        const changeDatePickerText = (element, startDate, endDate) => {
            $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
        }

        @if($showUsers)
        if (document.getElementById('userBrowserChart')) {
            piChart(
                document.getElementById('userBrowserChart'),
                @json(@$chart['user_browser_counter']->keys()),
                @json(@$chart['user_browser_counter']->flatten())
            );
        }
        if (document.getElementById('userOsChart')) {
            piChart(
                document.getElementById('userOsChart'),
                @json(@$chart['user_os_counter']->keys()),
                @json(@$chart['user_os_counter']->flatten())
            );
        }
        if (document.getElementById('userCountryChart')) {
            piChart(
                document.getElementById('userCountryChart'),
                @json(@$chart['user_country_counter']->keys()),
                @json(@$chart['user_country_counter']->flatten())
            );
        }
        @endif

        @if($showFinanceCharts)
        // Deposit & Withdraw Report Chart
        let dwChart = barChart(
            document.querySelector("#dwChartArea"),
            @json(__(gs('cur_text'))),
            [{
                name: 'Deposited',
                data: []
            },
            {
                name: 'Withdrawn',
                data: []
            }],
            []
        );

        // Transaction Report Chart
        let transactionChart = barChart(
            document.querySelector("#transactionChartArea"),
            @json(__(gs('cur_text'))),
            [{
                name: 'Plus Transactions',
                data: []
            },
            {
                name: 'Minus Transactions',
                data: []
            }],
            []
        );

        // Function to fetch and update Deposit & Withdraw chart
        const dwChartData = (startDate, endDate) => {
            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.deposit.withdraw'));

            $.get(url, data,
                function(response, status) {
                    if (status == 'success') {
                        dwChart.updateSeries(response.data);
                        dwChart.updateOptions({
                            xaxis: {
                                categories: response.created_on,
                            }
                        });
                    }
                }
            );
        }

        // Function to fetch and update Transaction chart
        const transactionChartData = (startDate, endDate) => {
            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.transaction'));

            $.get(url, data,
                function(response, status) {
                    if (status == 'success') {
                        transactionChart.updateSeries(response.data);
                        transactionChart.updateOptions({
                            xaxis: {
                                categories: response.created_on,
                            }
                        });
                    }
                }
            );
        }

        // Initialize date pickers for Deposit & Withdraw and Transaction charts
        $('#dwDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#dwDatePicker span', start, end));
        $('#trxDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#trxDatePicker span', start, end));

        changeDatePickerText('#dwDatePicker span', start, end);
        changeDatePickerText('#trxDatePicker span', start, end);

        // Load initial chart data
        dwChartData(start, end);
        transactionChartData(start, end);

        // Update charts when date range changes
        $('#dwDatePicker').on('apply.daterangepicker', (event, picker) => dwChartData(picker.startDate, picker.endDate));
        $('#trxDatePicker').on('apply.daterangepicker', (event, picker) => transactionChartData(picker.startDate, picker.endDate));
        @endif
    </script>
@endpush
@push('style')
    <style>
        .apexcharts-menu {
            min-width: 120px !important;
        }
        #dwChartArea, #transactionChartArea {
            min-height: 380px;
        }
    </style>
@endpush
