@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4 border-bottom pb-2">@lang('Edit Staff')</h5>
                    <form action="{{ route('admin.staff.update', $admin->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Name')</label>
                                    <input class="form-control" type="text" name="name" value="{{ old('name', $admin->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Username')</label>
                                    <input class="form-control" type="text" name="username" value="{{ old('username', $admin->username) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Email')</label>
                                    <input class="form-control" type="email" name="email" value="{{ old('email', maskForDemo($admin->email)) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status')</label>
                                    <select class="form-control" name="status" required>
                                        <option value="1" {{ old('status', $admin->status) == 1 ? 'selected' : '' }}>@lang('Enabled')</option>
                                        <option value="0" {{ old('status', $admin->status) == 0 ? 'selected' : '' }}>@lang('Disabled')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('New Password')</label>
                                    <input class="form-control" type="password" name="password" autocomplete="new-password" placeholder="@lang('Leave blank to keep current')">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Confirm Password')</label>
                                    <input class="form-control" type="password" name="password_confirmation" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Image')</label>
                                    <x-image-uploader image="{{ $admin->image }}" class="w-100" type="adminProfile" :required="false" />
                                </div>
                            </div>
                            @if(!$admin->is_super_admin)
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="d-block">@lang('Module Access') <span class="text--danger">*</span></label>
                                    <small class="text-muted d-block mb-2">@lang('Select at least one section this staff can access.')</small>
                                    @error('modules')
                                        <small class="text--danger d-block mb-2">{{ $message }}</small>
                                    @enderror
                                    <div class="row">
                                        @php $allowed = is_array($admin->allowed_modules) ? $admin->allowed_modules : []; @endphp
                                        @foreach($moduleTitles as $key => $title)
                                        <div class="col-md-4 col-lg-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="modules[]" value="{{ $key }}" id="mod_{{ $key }}" {{ in_array($key, old('modules', $allowed)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mod_{{ $key }}">{{ __($title) }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @else
                                <div class="col-12">
                                    <p class="text-muted">@lang('Super administrators have full access to all modules.')</p>
                                </div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn--primary">@lang('Update Staff')</button>
                        <a href="{{ route('admin.staff.index') }}" class="btn btn--dark">@lang('Cancel')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
