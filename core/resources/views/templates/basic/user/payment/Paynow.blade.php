@extends($activeTemplate.'layouts.frontend')
@section('content')
<div class="section bg--light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card custom--card">
                    <div class="card-body">
                        <form action="{{ $data->redirect_url }}" method="GET" class="text-center">
                            <ul class="list-group text-center">
                                <li class="list-group-item d-flex justify-content-between">
                                    @lang('You have to pay '):
                                    <strong>{{showAmount($deposit->final_amount,currencyFormat:false)}}
                                        {{__($deposit->method_currency)}}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    @lang('You will get '):
                                    <strong>{{showAmount($deposit->amount)}}</strong>
                                </li>
                            </ul>
                            <button type="submit" class="btn btn--base w-100 h-45 mt-3">
                                @lang('Pay Now')
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

