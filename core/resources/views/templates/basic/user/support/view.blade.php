@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">
                            @php echo $myTicket->statusBadge; @endphp
                            [@lang('Ticket')#{{ $myTicket->ticket }}] {{ $myTicket->subject }}
                        </h5>
                        @if ($myTicket->status != Status::TICKET_CLOSE && $myTicket->user)
                            <button class="btn btn--danger close-button btn-sm confirmationBtn fw-bold" type="button"
                                data-question="@lang('Are you sure to close this ticket?')"
                                data-action="{{ route('ticket.close', $myTicket->id) }}"><i
                                    class="la la-lg la-times-circle"></i> @lang('Close Ticket')
                            </button>
                        @endif
                    </div>
                    <form method="post" class="disableSubmission" action="{{ route('ticket.reply', $myTicket->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row justify-content-between">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Reply Message')</label>
                                    <textarea name="message" class="form-control form--control" cols="4" rows="4" required>{{ old('message') }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <button type="button" class="btn btn--base btn--sm addAttachment my-2 fw-bold"> <i class="fas fa-plus"></i> @lang('Add Attachment') </button>
                                <p class="mb-2"><span class="text--info">@lang('Max 5 files can be uploaded | Maximum upload size is '.convertToReadableSize(ini_get('upload_max_filesize')) .' | Allowed File Extensions: .jpg, .jpeg, .png, .pdf, .doc, .docx')</span></p>
                                <div class="row fileUploadsContainer">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn--base btn--sm w-100 my-2 fw-bold" type="submit"><i class="la la-fw la-lg la-reply"></i> @lang('Reply')
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <div class="card b-radius--10 mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">@lang('Conversation History')</h5>
                    @foreach ($messages as $message)
                                @if ($message->admin_id == 0)
                                    <div class="row border border-primary border-radius-3 my-3 py-3 mx-2">
                                        <div class="col-md-3 border-end text-end">
                                            <h5 class="my-3">{{ $message->ticket->name }}</h5>
                                        </div>
                                        <div class="col-md-9">
                                            <p class="text-muted fw-bold my-3">
                                                @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}</p>
                                            <p>{{ $message->message }}</p>
                                            @if ($message->attachments->count() > 0)
                                                <div class="mt-2">
                                                    @foreach ($message->attachments as $k => $image)
                                                        <a href="{{ route('ticket.download', encrypt($image->id)) }}"
                                                            class="me-3"><i class="las la-file"></i> @lang('Attachment')
                                                            {{ ++$k }} </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="row border border-warning border-radius-3 my-3 py-3 mx-2"
                                        style="background-color: #ffd96729">
                                        <div class="col-md-3 border-end text-end">
                                            <h5 class="my-3">{{ $message->admin->name }}</h5>
                                            <p class="lead text-muted">@lang('Staff')</p>
                                        </div>
                                        <div class="col-md-9">
                                            <p class="text-muted fw-bold my-3">
                                                @lang('Posted on') {{ $message->created_at->format('l, dS F Y @ H:i') }}
                                            </p>
                                            <p>{{ $message->message }}</p>
                                            @if ($message->attachments->count() > 0)
                                                <div class="mt-2">
                                                    @foreach ($message->attachments as $k => $image)
                                                        <a href="{{ route('ticket.download', encrypt($image->id)) }}"
                                                            class="me-3">
                                                            <i class="las la-file"></i> @lang('Attachment')
                                                            {{ ++$k }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection
@push('style')
<style>
    .input-group-text:focus{
        box-shadow: none !important;
    }
    .reply-bg{
        background-color: #ffd96729
    }
    .empty-message img{
        width: 120px;
        margin-bottom: 15px;
    }
    .form--control{
        height: unset;
    }
    .btn--base {
        background: #{{ gs('base_color', '4bea76') }} !important;
        color: #fff !important;
        font-weight: 600 !important;
        border: none !important;
    }
    .btn--base:hover {
        background: #{{ gs('base_color', '4bea76') }} !important;
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(75, 234, 118, 0.3);
    }
</style>
@endpush

@push('style-lib')
<link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
<script src="{{ asset('assets/global/js/select2.min.js') }}"></script>    
@endpush

@push('script')
    <script>
        (function ($) {
            "use strict";
            var fileAdded = 0;
            $('.addAttachment').on('click',function(){
                fileAdded++;
                if (fileAdded == 5) {
                    $(this).attr('disabled',true)
                }
                $(".fileUploadsContainer").append(`
                    <div class="col-lg-4 col-md-12 removeFileInput">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="file" name="attachments[]" class="form-control" accept=".jpeg,.jpg,.png,.pdf,.doc,.docx" required>
                                <button type="button" class="input-group-text removeFile bg--danger border--danger"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `)
            });
            $(document).on('click','.removeFile',function(){
                $('.addAttachment').removeAttr('disabled',true)
                fileAdded--;
                $(this).closest('.removeFileInput').remove();
            });
        })(jQuery);

    </script>
@endpush
