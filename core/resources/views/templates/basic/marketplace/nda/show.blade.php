@extends($activeTemplate . 'layouts.frontend')
@section('content')
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Multi-Step Wizard -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex align-items-center">
                            <i class="las la-file-signature la-2x me-3"></i>
                            <div>
                                <h4 class="mb-0">@lang('Non-Disclosure Agreement Required')</h4>
                                <small>@lang('You must sign an NDA to view this confidential listing')</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Progress Indicator -->
                        <div class="nda-progress mb-4">
                            <div class="progress-steps d-flex justify-content-between">
                                <div class="step active" data-step="1">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">@lang('Preview')</div>
                                </div>
                                <div class="step" data-step="2">
                                    <div class="step-circle">2</div>
                                    <div class="step-label">@lang('Read Terms')</div>
                                </div>
                                <div class="step" data-step="3">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">@lang('Sign')</div>
                                </div>
                                <div class="step" data-step="4">
                                    <div class="step-circle">4</div>
                                    <div class="step-label">@lang('Confirm')</div>
                                </div>
                            </div>
                        </div>

                        @auth
                        <form id="ndaSignForm" action="{{ route('marketplace.nda.sign', $listing->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="signature_image" id="signature_image">
                            <input type="hidden" name="read_time_seconds" id="read_time_seconds" value="0">
                            <input type="hidden" name="browser_fingerprint" id="browser_fingerprint">
                            <input type="hidden" name="device_type" id="device_type">
                            <input type="hidden" name="screen_resolution" id="screen_resolution">
                            <input type="hidden" name="timezone" id="timezone">
                            <input type="hidden" name="referrer_url" id="referrer_url">

                            <!-- Step 1: Preview -->
                            <div class="nda-step" data-step="1">
                                <h5 class="mb-3">@lang('Listing Preview')</h5>
                                <div class="listing-preview mb-4 p-3 bg-light rounded">
                                    <div class="row">
                                        <div class="col-md-4">
                                            @if($listing->primaryImage)
                                                <img src="{{ getImage(getFilePath('listing') . '/' . $listing->primaryImage->image) }}"
                                                     alt="{{ $listing->title }}" class="img-fluid rounded">
                                            @else
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="las la-image la-3x text-white"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <h5 class="mb-2">{{ $listing->title }}</h5>
                                            <p class="text-muted mb-2">{{ Str::limit($listing->tagline ?: 'No description available', 100) }}</p>
                                            <div class="row text-sm">
                                                <div class="col-6">
                                                    <span class="text-muted">@lang('Business Type'):</span><br>
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</strong>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted">@lang('Seller'):</span><br>
                                                    <strong>{{ $listing->seller->username }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary btn-next-step">@lang('Continue') <i class="las la-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- Step 2: Read Terms -->
                            <div class="nda-step d-none" data-step="2">
                                <h5 class="mb-3">@lang('Non-Disclosure Agreement Terms')</h5>
                                
                                <div class="alert alert-info">
                                    <i class="las la-info-circle"></i>
                                    @lang('Please read all terms carefully. You must scroll to the bottom before proceeding.')
                                </div>

                                <div class="nda-terms-container p-3 border rounded bg-light" id="termsContainer" style="max-height: 400px; overflow-y: auto;">
                                    <h6>@lang('Terms of Agreement')</h6>
                                    <ol class="mb-0">
                                        <li>@lang('I agree to keep all information regarding this business listing confidential.')</li>
                                        <li>@lang('I will not share, disclose, or distribute any confidential information to third parties without written permission from the seller.')</li>
                                        <li>@lang('I understand that this NDA is legally binding and violations may result in legal action.')</li>
                                        <li>@lang('This agreement remains in effect for 1 year from the date of signing.')</li>
                                        <li>@lang('I acknowledge that I am signing this agreement electronically and it has the same legal effect as a handwritten signature.')</li>
                                        <li>@lang('I understand that any breach of this agreement may result in legal consequences including but not limited to monetary damages.')</li>
                                        <li>@lang('I agree to return or destroy all confidential information upon request or termination of this agreement.')</li>
                                        <li>@lang('I acknowledge that this NDA does not create any obligation for the seller to complete a transaction.')</li>
                                    </ol>

                                    <div class="mt-4 p-3 bg-white rounded border">
                                        <h6 class="text-danger">@lang('Legal Notice')</h6>
                                        <p class="small mb-0">
                                            @lang('By signing this agreement electronically, you acknowledge that:')
                                        </p>
                                        <ul class="small mb-0">
                                            <li>@lang('Electronic signatures have the same legal effect as handwritten signatures under applicable law.')</li>
                                            <li>@lang('You have the ability to access and retain this agreement electronically.')</li>
                                            <li>@lang('You consent to conduct business electronically.')</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="form-check mt-3" id="termsCheckContainer">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" value="1" required disabled>
                                    <label class="form-check-label" for="agree_terms">
                                        @lang('I have read and agree to all terms of this Non-Disclosure Agreement') *
                                    </label>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-step"><i class="las la-arrow-left"></i> @lang('Back')</button>
                                    <button type="button" class="btn btn-primary btn-next-step" id="termsNextBtn" disabled>@lang('Continue to Sign') <i class="las la-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- Step 3: Sign -->
                            <div class="nda-step d-none" data-step="3">
                                <h5 class="mb-3">@lang('Sign the Agreement')</h5>

                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="signature_name" class="form-label">@lang('Full Name') <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="signature_name" name="signature"
                                                   value="{{ auth()->user()->fullname ?? '' }}" placeholder="@lang('Your full legal name')">
                                            <small class="form-text text-muted">@lang('Enter your full legal name')</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">@lang('Date')</label>
                                            <input type="text" class="form-control" value="{{ now()->format('Y-m-d') }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="signature-section mb-4">
                                    <label class="form-label">@lang('Draw Your Signature') *</label>
                                    <div class="signature-canvas-container border rounded p-3 bg-white">
                                        <canvas id="signatureCanvas" width="700" height="200" style="width: 100%; height: 200px; cursor: crosshair; touch-action: none;"></canvas>
                                        <div class="signature-controls mt-2 d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="clearSignature">
                                                <i class="las la-redo"></i> @lang('Clear')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="redoSignature" disabled>
                                                <i class="las la-undo"></i> @lang('Redo')
                                            </button>
                                        </div>
                                        <small class="form-text text-muted d-block mt-2">
                                            @lang('Draw your signature above using your mouse or touch screen')
                                        </small>
                                    </div>
                                </div>

                                <div class="signature-preview d-none mb-3">
                                    <label class="form-label">@lang('Signature Preview')</label>
                                    <div class="border rounded p-2 bg-light">
                                        <img id="signaturePreview" src="" alt="Signature Preview" style="max-width: 100%; height: auto;">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-step"><i class="las la-arrow-left"></i> @lang('Back')</button>
                                    <button type="button" class="btn btn-primary btn-next-step" id="signNextBtn" disabled>@lang('Continue') <i class="las la-arrow-right"></i></button>
                                </div>
                            </div>

                            <!-- Step 4: Confirm -->
                            <div class="nda-step d-none" data-step="4">
                                <h5 class="mb-3">@lang('Confirm and Submit')</h5>

                                <div class="alert alert-success">
                                    <i class="las la-check-circle"></i>
                                    @lang('Please review your information before submitting.')
                                </div>

                                <div class="confirmation-details p-3 border rounded bg-light mb-4">
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>@lang('Listing'):</strong></div>
                                        <div class="col-md-8">{{ $listing->title }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>@lang('Your Name'):</strong></div>
                                        <div class="col-md-8" id="confirmName">{{ auth()->user()->fullname }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>@lang('Signature'):</strong></div>
                                        <div class="col-md-8">
                                            <img id="confirmSignature" src="" alt="Signature" style="max-width: 200px; height: auto; border: 1px solid #ddd;">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>@lang('Date'):</strong></div>
                                        <div class="col-md-8">{{ now()->format('F d, Y') }}</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4"><strong>@lang('Expires'):</strong></div>
                                        <div class="col-md-8">{{ now()->addYear()->format('F d, Y') }}</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-prev-step"><i class="las la-arrow-left"></i> @lang('Back')</button>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="las la-check-circle"></i> @lang('Sign NDA & View Listing')
                                    </button>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="text-center py-4">
                            <div class="alert alert-warning">
                                <i class="las la-exclamation-triangle"></i>
                                @lang('You must be logged in to sign an NDA.')
                            </div>
                            <a href="{{ route('user.login', ['redirect' => route('marketplace.nda.show', $listing->id)]) }}" class="btn btn-primary">
                                <i class="las la-sign-in-alt"></i> @lang('Login to Continue')
                            </a>
                        </div>
                        @endauth
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h6 class="card-title">@lang('What happens after signing?')</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('You will be able to view all listing details and contact the seller')
                            </li>
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('Your signed NDA will be legally binding for 1 year')
                            </li>
                            <li class="mb-2">
                                <i class="las la-check-circle text-success me-2"></i>
                                @lang('The seller will be notified of your NDA signature')
                            </li>
                            <li class="mb-0">
                                <i class="las la-info-circle text-info me-2"></i>
                                @lang('If you have questions, contact our support team')
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.nda-progress {
    margin-bottom: 2rem;
}

.progress-steps {
    position: relative;
    padding: 0 20px;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 40px;
    right: 40px;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.step {
    position: relative;
    z-index: 1;
    text-align: center;
    flex: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 8px;
    transition: all 0.3s;
}

.step.active .step-circle {
    background: #0d6efd;
    color: white;
}

.step.completed .step-circle {
    background: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #666;
}

.step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.signature-canvas-container {
    position: relative;
}

#signatureCanvas {
    border: 2px dashed #ddd;
    border-radius: 4px;
}

.signature-controls {
    display: flex;
    gap: 0.5rem;
}

.nda-terms-container {
    scrollbar-width: thin;
}

.nda-terms-container::-webkit-scrollbar {
    width: 8px;
}

.nda-terms-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.nda-terms-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.nda-terms-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

@media (max-width: 768px) {
    .step-label {
        font-size: 0.75rem;
    }
    
    .step-circle {
        width: 35px;
        height: 35px;
        font-size: 0.875rem;
    }
    
    #signatureCanvas {
        height: 150px !important;
    }
}
</style>
@endsection

@push('script')
<script>
(function($) {
    "use strict";

    let currentStep = 1;
    let readStartTime = null;
    let signatureCanvas = null;
    let signatureCtx = null;
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    let signatureHistory = [];
    let historyStep = -1;

    // Initialize
    $(document).ready(function() {
        initializeSignatureCanvas();
        collectDeviceInfo();
        setupStepNavigation();
        setupTermsScrolling();
    });

    function initializeSignatureCanvas() {
        signatureCanvas = document.getElementById('signatureCanvas');
        if (!signatureCanvas) return;

        signatureCtx = signatureCanvas.getContext('2d');
        signatureCtx.strokeStyle = '#000';
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';

        // Mouse events
        signatureCanvas.addEventListener('mousedown', startDrawing);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', stopDrawing);
        signatureCanvas.addEventListener('mouseout', stopDrawing);

        // Touch events
        signatureCanvas.addEventListener('touchstart', handleTouch);
        signatureCanvas.addEventListener('touchmove', handleTouch);
        signatureCanvas.addEventListener('touchend', stopDrawing);

        // Clear button
        $('#clearSignature').on('click', clearSignature);
        $('#redoSignature').on('click', redoSignature);
    }

    function handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0] || e.changedTouches[0];
        const rect = signatureCanvas.getBoundingClientRect();
        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;

        if (e.type === 'touchstart') {
            startDrawing({ offsetX: x, offsetY: y });
        } else if (e.type === 'touchmove') {
            draw({ offsetX: x, offsetY: y });
        }
    }

    function startDrawing(e) {
        isDrawing = true;
        lastX = e.offsetX;
        lastY = e.offsetY;
    }

    function draw(e) {
        if (!isDrawing) return;
        
        signatureCtx.beginPath();
        signatureCtx.moveTo(lastX, lastY);
        signatureCtx.lineTo(e.offsetX, e.offsetY);
        signatureCtx.stroke();
        
        lastX = e.offsetX;
        lastY = e.offsetY;
        
        // Enable next button if signature exists
        if (isSignatureDrawn()) {
            $('#signNextBtn').prop('disabled', false);
            updateSignaturePreview();
        }
    }

    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            saveSignatureState();
            if (isSignatureDrawn()) {
                $('#signNextBtn').prop('disabled', false);
                updateSignaturePreview();
            }
        }
    }

    function clearSignature() {
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        signatureHistory = [];
        historyStep = -1;
        $('#signNextBtn').prop('disabled', true);
        $('#signature_image').val('');
        $('#signaturePreview').closest('.signature-preview').addClass('d-none');
        $('#redoSignature').prop('disabled', true);
    }

    function saveSignatureState() {
        const imageData = signatureCanvas.toDataURL('image/png');
        signatureHistory.push(imageData);
        historyStep = signatureHistory.length - 1;
        $('#redoSignature').prop('disabled', true);
    }

    function redoSignature() {
        if (historyStep < signatureHistory.length - 1) {
            historyStep++;
            const image = new Image();
            image.onload = function() {
                signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                signatureCtx.drawImage(image, 0, 0);
                $('#signNextBtn').prop('disabled', false);
                if (historyStep >= signatureHistory.length - 1) {
                    $('#redoSignature').prop('disabled', true);
                }
            };
            image.src = signatureHistory[historyStep];
        }
    }

    function isSignatureDrawn() {
        if (signatureHistory.length > 0) return true;
        const imageData = signatureCanvas.toDataURL();
        const blankCanvas = document.createElement('canvas');
        blankCanvas.width = signatureCanvas.width;
        blankCanvas.height = signatureCanvas.height;
        const blankCtx = blankCanvas.getContext('2d');
        if (blankCtx) blankCtx.clearRect(0, 0, blankCanvas.width, blankCanvas.height);
        const blankData = blankCanvas.toDataURL();
        return imageData !== blankData;
    }

    function updateSignaturePreview() {
        if (isSignatureDrawn()) {
            const signatureData = signatureCanvas.toDataURL('image/png');
            $('#signature_image').val(signatureData);
            $('#signaturePreview').attr('src', signatureData);
            $('#signaturePreview').closest('.signature-preview').removeClass('d-none');
        }
    }

    function setupStepNavigation() {
        $(document).on('click', '.btn-next-step', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if ($(this).prop('disabled')) return false;
            if (validateCurrentStep()) {
                if (currentStep === 3) {
                    updateSignaturePreview();
                }
                goToStep(currentStep + 1);
            }
            return false;
        });

        $(document).on('click', '.btn-prev-step', function(e) {
            e.preventDefault();
            e.stopPropagation();
            goToStep(currentStep - 1);
            return false;
        });
    }

    function validateCurrentStep() {
        if (currentStep === 2) {
            if (!$('#agree_terms').is(':checked')) {
                alert('@lang("Please agree to the terms before continuing.")');
                return false;
            }
        } else if (currentStep === 3) {
            var nameEl = document.getElementById('signature_name');
            var nameVal = (nameEl && nameEl.value) ? nameEl.value : ($('#signature_name').val() || $('input[name="signature"]').val() || '');
            var nameStr = nameVal != null ? String(nameVal).trim() : '';
            if (!nameStr) {
                alert('@lang("Please enter your full name.")');
                return false;
            }
            if (!isSignatureDrawn()) {
                alert('@lang("Please draw your signature.")');
                return false;
            }
        }
        return true;
    }

    function goToStep(step) {
        if (step < 1 || step > 4) return;

        // Hide all steps
        $('.nda-step').addClass('d-none');
        
        // Show current step
        $(`.nda-step[data-step="${step}"]`).removeClass('d-none');
        
        // Update progress
        $('.step').each(function() {
            const stepNum = parseInt($(this).data('step'));
            $(this).removeClass('active completed');
            if (stepNum < step) {
                $(this).addClass('completed');
            } else if (stepNum === step) {
                $(this).addClass('active');
            }
        });

        currentStep = step;

        // Step-specific actions
        if (step === 2) {
            startReadingTimer();
        } else if (step === 4) {
            updateConfirmationDetails();
        }
    }

    function setupTermsScrolling() {
        const termsContainer = $('#termsContainer');
        const agreeCheckbox = $('#agree_terms');
        const termsNextBtn = $('#termsNextBtn');

        termsContainer.on('scroll', function() {
            const scrollTop = $(this).scrollTop();
            const scrollHeight = $(this)[0].scrollHeight;
            const clientHeight = $(this).innerHeight();
            const scrollPercentage = (scrollTop + clientHeight) / scrollHeight;

            // Enable checkbox when scrolled 90% down
            if (scrollPercentage >= 0.9) {
                agreeCheckbox.prop('disabled', false);
            }
        });

        agreeCheckbox.on('change', function() {
            if ($(this).is(':checked')) {
                termsNextBtn.prop('disabled', false);
            } else {
                termsNextBtn.prop('disabled', true);
            }
        });
    }

    function startReadingTimer() {
        readStartTime = Date.now();
    }

    function collectDeviceInfo() {
        // Browser fingerprint (simple version)
        const fingerprint = [
            navigator.userAgent,
            navigator.language,
            screen.width + 'x' + screen.height,
            new Date().getTimezoneOffset()
        ].join('|');
        $('#browser_fingerprint').val(btoa(fingerprint).substring(0, 64));

        // Device type
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const isTablet = /iPad|Android/i.test(navigator.userAgent) && !isMobile;
        $('#device_type').val(isMobile ? 'mobile' : (isTablet ? 'tablet' : 'desktop'));

        // Screen resolution
        $('#screen_resolution').val(screen.width + 'x' + screen.height);

        // Timezone
        $('#timezone').val(Intl.DateTimeFormat().resolvedOptions().timeZone);

        // Referrer
        $('#referrer_url').val(document.referrer || 'direct');
    }

    function updateConfirmationDetails() {
        $('#confirmName').text($('#signature_name').val());
        const signatureData = signatureCanvas.toDataURL('image/png');
        $('#confirmSignature').attr('src', signatureData);
        
        // Update read time
        if (readStartTime) {
            const readTime = Math.floor((Date.now() - readStartTime) / 1000);
            $('#read_time_seconds').val(readTime);
        }
    }

    // Form submission
    $('#ndaSignForm').on('submit', function(e) {
        if (!validateCurrentStep()) {
            e.preventDefault();
            return false;
        }

        // Ensure signature is captured
        if (isSignatureDrawn()) {
            updateSignaturePreview();
        }
    });

})(jQuery);
</script>
@endpush
