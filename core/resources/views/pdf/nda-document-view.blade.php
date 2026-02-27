<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDA Document - {{ $nda->listing->listing_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .pdf-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(0, 0, 0, 0.1);
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
        }
        .download-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1001;
            padding: 10px 20px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .download-btn:hover {
            background: #0b5ed7;
        }
    </style>
</head>
<body>
    <button class="download-btn" onclick="generatePDF()">
        <i class="las la-download"></i> Download PDF
    </button>
    
    <div class="watermark" id="watermark">
        {{ $nda->user->fullname }} - {{ $nda->signed_at->format('Y-m-d') }}
    </div>

    <div class="pdf-container" id="pdfContent">
        {{-- SAFE: $html is pre-rendered from nda-document.blade.php where all user data is escaped --}}
        {!! $html !!}
    </div>

    <!-- Load html2pdf.js from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        async function generatePDF() {
            const button = document.querySelector('.download-btn');
            button.disabled = true;
            button.textContent = 'Generating PDF...';

            try {
                const element = document.getElementById('pdfContent');
                const opt = {
                    margin: [10, 10, 10, 10],
                    filename: '{{ $filename }}',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 2,
                        useCORS: true,
                        letterRendering: true
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a4', 
                        orientation: 'portrait' 
                    }
                };

                await html2pdf().set(opt).from(element).save();
            } catch (error) {
                console.error('PDF generation failed:', error);
                alert('Failed to generate PDF. Please try again.');
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="las la-download"></i> Download PDF';
            }
        }
    </script>
</body>
</html>
