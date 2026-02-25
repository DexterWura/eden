@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Database schema will be updated')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Missing tables will be created')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('New columns will be added to existing tables')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Indexes and constraints will be created')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Migration rollback will be available if needed')</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <form action="{{ route('admin.system.migrations.run') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            <i class="las la-database"></i> @lang('Run Database Migrations')
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
<style>
  .list-group-item span{
    font-size: 22px !important;
    padding: 8px 0px
  }
</style>
@endpush
