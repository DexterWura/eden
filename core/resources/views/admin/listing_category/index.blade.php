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
                                    <th>@lang('Name')</th>
                                    <th>@lang('Business Type')</th>
                                    <th>@lang('Listings')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>
                                            <i class="{{ $category->icon ?? 'las la-folder' }} me-2"></i>
                                            {{ $category->name }}
                                            <br>
                                            <small class="text-muted">{{ $category->slug }}</small>
                                        </td>
                                        <td>{{ $businessTypes[$category->business_type] ?? $category->business_type }}</td>
                                        <td>{{ $category->listings_count }}</td>
                                        <td>
                                            @if($category->status)
                                                <span class="badge badge--success">@lang('Active')</span>
                                            @else
                                                <span class="badge badge--danger">@lang('Inactive')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button type="button" class="btn btn-sm btn-outline--primary editBtn"
                                                        data-category="{{ json_encode($category) }}">
                                                    <i class="las la-pencil-alt"></i> @lang('Edit')
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline--{{ $category->status ? 'danger' : 'success' }} confirmationBtn"
                                                        data-action="{{ route('admin.listing.category.status', $category->id) }}"
                                                        data-question="@lang('Are you sure to change status?')">
                                                    <i class="las la-{{ $category->status ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($categories->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($categories) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Add Category')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><i class="las la-times"></i></button>
                </div>
                <form action="{{ route('admin.listing.category.store') }}" method="POST" id="categoryForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Business Type')</label>
                            <select name="business_type" class="form-control" required>
                                @foreach($businessTypes as $key => $name)
                                    <option value="{{ $key }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Icon') (@lang('Line Awesome class'))</label>
                            <input type="text" name="icon" class="form-control" placeholder="las la-globe">
                        </div>
                        <div class="form-group">
                            <label>@lang('Description')</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label>@lang('Sort Order')</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary addBtn">
        <i class="las la-plus"></i> @lang('Add New')
    </button>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";
        
        $('.addBtn').on('click', function() {
            var modal = $('#categoryModal');
            modal.find('.modal-title').text('@lang("Add Category")');
            modal.find('form').attr('action', '{{ route("admin.listing.category.store") }}');
            modal.find('form')[0].reset();
            modal.modal('show');
        });
        
        $('.editBtn').on('click', function() {
            var category = $(this).data('category');
            var modal = $('#categoryModal');
            var url = '{{ route("admin.listing.category.store", ":id") }}';
            url = url.replace(':id', category.id);
            
            modal.find('.modal-title').text('@lang("Edit Category")');
            modal.find('form').attr('action', url);
            modal.find('[name=name]').val(category.name);
            modal.find('[name=business_type]').val(category.business_type);
            modal.find('[name=icon]').val(category.icon);
            modal.find('[name=description]').val(category.description);
            modal.find('[name=sort_order]').val(category.sort_order);
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush

