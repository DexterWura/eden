@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4 border-bottom pb-2">@lang('Add Staff')</h5>
                    <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Name')</label>
                                    <input class="form-control" type="text" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Username')</label>
                                    <input class="form-control" type="text" name="username" value="{{ old('username') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Email')</label>
                                    <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Password')</label>
                                    <input class="form-control" type="password" name="password" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Confirm Password')</label>
                                    <input class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Image')</label>
                                    <x-image-uploader image="" class="w-100" type="adminProfile" :required="false" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="d-block">@lang('Module Access') <span class="text--danger">*</span></label>
                                    <small class="text-muted d-block mb-2">@lang('Select at least one section this staff can access.')</small>
                                    @error('modules')
                                        <small class="text--danger d-block mb-2">{{ $message }}</small>
                                    @enderror
                                    <div class="row">
                                        @foreach($moduleTitles as $key => $title)
                                        <div class="col-md-4 col-lg-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $key }}" id="mod_{{ $key }}" {{ in_array($key, old('modules', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mod_{{ $key }}">{{ __($title) }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary">@lang('Create Staff')</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn--dark">@lang('Cancel')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
