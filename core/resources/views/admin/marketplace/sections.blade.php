@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Section')</th>
                                    <th>@lang('Heading')</th>
                                    <th>@lang('Items Limit')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sections as $key => $section)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="icon-wrapper bg--primary rounded p-2" style="font-size: 1.5rem;">
                                                    <i class="{{ $section['icon'] }} text-white"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $section['name'] }}</span>
                                                    <small class="text-muted">{{ $section['description'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-block">{{ Str::limit($section['heading'], 40) }}</span>
                                            @if($section['is_hero'] ?? false)
                                                @if($section['trending_fallback'] ?? '')
                                                    <small class="text-muted">@lang('Fallback'): {{ Str::limit($section['trending_fallback'], 50) }}</small>
                                                @endif
                                            @elseif($section['subheading'] ?? '')
                                                <small class="text-muted">{{ Str::limit($section['subheading'], 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($section['limit']))
                                                <span class="badge badge--primary">{{ $section['limit'] }} @lang('items')</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($section['is_hero'] ?? false)
                                                <span class="badge bg-success">@lang('Always on')</span>
                                            @else
                                                <label class="switch m-0">
                                                    <input type="checkbox" class="toggle-section" 
                                                           data-id="{{ $section['id'] }}"
                                                           {{ $section['status'] == '1' ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            @endif
                                        </td>
                                        <td>
                                            @if($key === 'marketplace_hero.content')
                                            <a href="{{ route('admin.frontend.sections', 'marketplace_hero') }}" class="btn btn-sm btn-outline--info me-1" title="@lang('Edit heading and fallback trending words in Frontend Manager')">
                                                <i class="las la-palette"></i> @lang('Edit Content')
                                            </a>
                                            @endif
                                            <button class="btn btn-sm btn-outline--primary edit-section" 
                                                    data-id="{{ $section['id'] }}"
                                                    data-heading="{{ $section['heading'] }}"
                                                    data-subheading="{{ $section['subheading'] }}"
                                                    data-trending-fallback="{{ $section['trending_fallback'] ?? '' }}"
                                                    data-is-hero="{{ ($section['is_hero'] ?? false) ? '1' : '0' }}"
                                                    data-limit="{{ $section['limit'] ?? '' }}"
                                                    data-status="{{ $section['status'] }}"
                                                    data-name="{{ $section['name'] }}">
                                                <i class="las la-pen"></i> @lang('Edit')
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">
                                            @lang('No marketplace sections found. Import the SQL file to add them.')
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4 b-radius--10">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="las la-info-circle"></i> @lang('Section Order on Homepage')
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        @lang('The sections are displayed on the homepage in the following order. To reorder sections, go to')
                        <a href="{{ route('admin.frontend.index') }}">@lang('Frontend Manager')</a> → @lang('Manage Pages') → @lang('Edit HOME page').
                    </p>
                    <div class="section-order d-flex flex-wrap gap-2">
                        <span class="badge badge--dark">1. Hero/Search</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">2. Statistics</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">3. Featured</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">4. Ending Soon</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">5. Popular</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">6. New</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">7. Domains</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">8. Websites</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">9. Apps</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">10. Social</span>
                        <i class="las la-arrow-right text-muted"></i>
                        <span class="badge badge--dark">11. CTA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Edit Section'): <span class="section-name"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="editForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Heading')</label>
                            <input type="text" name="heading" class="form-control" required>
                        </div>
                        <div class="form-group subheading-group">
                            <label>@lang('Subheading')</label>
                            <input type="text" name="subheading" class="form-control">
                        </div>
                        <div class="form-group trending-fallback-group d-none">
                            <label>@lang('Fallback 6 trending words')</label>
                            <input type="text" name="trending_fallback" class="form-control" placeholder="e.g. SaaS, Blogs, Shopify, Youtube, Ads, Store">
                        </div>
                        <div class="form-group limit-group">
                            <label>@lang('Number of Items to Display')</label>
                            <input type="number" name="limit" class="form-control" min="1" max="20">
                        </div>
                        <div class="form-group status-group">
                            <label>@lang('Status')</label>
                            <select name="status" class="form-control">
                                <option value="1">@lang('Enabled')</option>
                                <option value="0">@lang('Disabled')</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100">@lang('Update Section')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
    }
    input:checked + .slider {
        background-color: var(--base-color);
    }
    input:checked + .slider:before {
        transform: translateX(24px);
    }
    .slider.round {
        border-radius: 26px;
    }
    .slider.round:before {
        border-radius: 50%;
    }
    .icon-wrapper {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        'use strict';

        // Toggle section status
        $('.toggle-section').on('change', function() {
            const id = $(this).data('id');
            const toggle = $(this);
            
            $.ajax({
                url: '{{ route("admin.marketplace.section.toggle", "") }}/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        notify('success', response.message);
                    }
                },
                error: function() {
                    toggle.prop('checked', !toggle.prop('checked'));
                    notify('error', 'Failed to update section');
                }
            });
        });

        // Edit section modal
        $('.edit-section').on('click', function() {
            const modal = $('#editModal');
            const id = $(this).data('id');
            const isHero = $(this).data('is-hero') === '1';
            
            modal.find('.section-name').text($(this).data('name'));
            modal.find('[name=heading]').val($(this).data('heading'));
            modal.find('[name=subheading]').val($(this).data('subheading'));
            modal.find('[name=trending_fallback]').val($(this).data('trending-fallback'));
            modal.find('[name=status]').val($(this).data('status'));
            
            if (isHero) {
                modal.find('.subheading-group').addClass('d-none');
                modal.find('.trending-fallback-group').removeClass('d-none');
                modal.find('.status-group').addClass('d-none');
            } else {
                modal.find('.subheading-group').removeClass('d-none');
                modal.find('.trending-fallback-group').addClass('d-none');
                modal.find('.status-group').removeClass('d-none');
            }
            
            const limit = $(this).data('limit');
            if (limit) {
                modal.find('[name=limit]').val(limit);
                modal.find('.limit-group').show();
            } else {
                modal.find('.limit-group').hide();
            }
            
            modal.find('#editForm').attr('action', '{{ route("admin.marketplace.section.update", "") }}/' + id);
            modal.modal('show');
        });

    })(jQuery);
</script>
@endpush

