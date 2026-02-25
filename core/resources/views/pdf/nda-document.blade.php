<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Non-Disclosure Agreement - {{ $listing->listing_number }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 40px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24pt;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header h2 {
            font-size: 16pt;
            margin: 10px 0 0 0;
            font-weight: normal;
        }

        .parties {
            margin: 30px 0;
            display: table;
            width: 100%;
        }

        .party {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 0 10px;
        }

        .party:first-child {
            border-right: 1px solid #ccc;
        }

        .party h3 {
            margin: 0 0 10px 0;
            font-size: 14pt;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }

        .terms {
            margin: 30px 0;
        }

        .terms h3 {
            font-size: 16pt;
            margin: 20px 0 10px 0;
            text-decoration: underline;
        }

        .terms ol {
            margin: 15px 0;
            padding-left: 30px;
        }

        .terms li {
            margin-bottom: 10px;
        }

        .signature-section {
            margin: 40px 0;
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        .signature-block {
            display: table;
            width: 100%;
            margin: 30px 0;
        }

        .signature-left, .signature-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            margin: 40px 0 5px 0;
            padding-bottom: 5px;
        }

        .signature-text {
            font-size: 10pt;
            color: #666;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 10pt;
            color: #666;
            text-align: center;
        }

        .business-details {
            margin: 20px 0;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .business-details h4 {
            margin: 0 0 10px 0;
            font-size: 14pt;
        }

        .business-details p {
            margin: 5px 0;
        }

        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 2px;
        }

        .signature-image {
            margin-bottom: 10px;
        }

        .signature-image img {
            max-width: 200px;
            height: auto;
            border: 1px solid #333;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(0, 0, 0, 0.08);
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">
        {{ $signer->fullname }} - {{ $signed_date }} - NDA-{{ $nda->id }}
    </div>

    <div class="header">
        <h1>Non-Disclosure Agreement</h1>
        <h2>Confidential Business Information</h2>
    </div>

    <div class="parties">
        <div class="party">
            <h3>Disclosing Party (Seller)</h3>
            <p><strong>Name:</strong> {{ $seller->firstname }} {{ $seller->lastname }}</p>
            <p><strong>Username:</strong> {{ $seller->username }}</p>
            @if($seller->email)
                <p><strong>Email:</strong> {{ $seller->email }}</p>
            @endif
        </div>

        <div class="party">
            <h3>Receiving Party (Buyer)</h3>
            <p><strong>Name:</strong> {{ $signer->firstname }} {{ $signer->lastname }}</p>
            <p><strong>Username:</strong> {{ $signer->username }}</p>
            @if($signer->email)
                <p><strong>Email:</strong> {{ $signer->email }}</p>
            @endif
        </div>
    </div>

    <div class="business-details">
        <h4>Business Listing Details</h4>
        <p><strong>Listing Title:</strong> {{ $listing->title }}</p>
        <p><strong>Listing Number:</strong> {{ $listing->listing_number }}</p>
        <p><strong>Business Type:</strong> {{ ucfirst(str_replace('_', ' ', $listing->business_type)) }}</p>
        @if($listing->tagline)
            <p><strong>Tagline:</strong> {{ $listing->tagline }}</p>
        @endif
    </div>

    <div class="terms">
        <h3>1. Purpose</h3>
        <p>This Non-Disclosure Agreement (the "Agreement") is entered into on {{ $signed_date }} between the Disclosing Party and the Receiving Party for the purpose of protecting confidential business information related to the business listing identified above.</p>

        <h3>2. Confidential Information</h3>
        <p>For purposes of this Agreement, "Confidential Information" shall include all information or material that has or could have commercial value or other utility in the business of the Disclosing Party, including but not limited to:</p>
        <ul>
            <li>Business plans, strategies, and financial information</li>
            <li>Customer lists, pricing information, and sales data</li>
            <li>Technical data, trade secrets, and proprietary processes</li>
            <li>Any other information disclosed in connection with the business listing</li>
        </ul>

        <h3>3. Obligations of Receiving Party</h3>
        <ol>
            <li>The Receiving Party agrees to hold and maintain the Confidential Information in strict confidence and take all reasonable precautions to protect it.</li>
            <li>The Receiving Party agrees not to disclose, reproduce, or disseminate any Confidential Information to any third party without the prior written consent of the Disclosing Party.</li>
            <li>The Receiving Party agrees to use the Confidential Information solely for the purpose of evaluating the business opportunity and shall not use it for any other purpose.</li>
            <li>The Receiving Party agrees to limit access to the Confidential Information to only those individuals who have a legitimate need to know and who are bound by similar confidentiality obligations.</li>
        </ol>

        <h3>4. Term</h3>
        <p>This Agreement shall remain in effect for a period of one (1) year from the date of signing, unless terminated earlier by mutual written agreement or extended by written amendment.</p>

        <h3>5. Return of Materials</h3>
        <p>Upon termination of this Agreement or at the Disclosing Party's request, the Receiving Party shall promptly return or destroy all Confidential Information and any copies thereof.</p>

        <h3>6. Remedies</h3>
        <p>The Receiving Party acknowledges that any breach of this Agreement may cause irreparable harm to the Disclosing Party and agrees that the Disclosing Party shall be entitled to seek injunctive relief in addition to any other remedies available at law or in equity.</p>

        <h3>7. Governing Law</h3>
        <p>This Agreement shall be governed by and construed in accordance with the laws of the jurisdiction where the business is located, without regard to its conflict of laws principles.</p>

        <h3>8. Entire Agreement</h3>
        <p>This Agreement constitutes the entire understanding between the parties with respect to the subject matter hereof and supersedes all prior agreements, whether written or oral.</p>
    </div>

    <div class="signature-section">
        <h3>Agreement Acknowledgment</h3>
        <p>By signing below, the parties acknowledge that they have read, understood, and agree to be bound by the terms of this Non-Disclosure Agreement.</p>

        <div class="signature-block">
            <div class="signature-left">
                @if($nda->signature_image)
                    <div class="signature-image mb-2">
                        <img src="{{ $nda->signature_image }}" alt="Signature" style="max-width: 200px; height: auto; border: 1px solid #ddd;">
                    </div>
                @endif
                <div class="signature-line">
                    {{ $signer->firstname }} {{ $signer->lastname }}
                </div>
                <div class="signature-text">
                    Receiving Party Signature<br>
                    Signed electronically on {{ $signed_date }} at {{ $signed_time }}<br>
                    IP Address: {{ $nda->ip_address }}
                    @if($nda->browser_fingerprint)
                        <br>Browser ID: {{ substr($nda->browser_fingerprint, 0, 16) }}...
                    @endif
                </div>
            </div>

            <div class="signature-right">
                <div class="signature-line">
                    {{ $seller->firstname }} {{ $seller->lastname }}
                </div>
                <div class="signature-text">
                    Disclosing Party (Auto-generated)<br>
                    Business Owner - {{ $listing->listing_number }}
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This document was electronically generated on {{ date('F d, Y \a\t H:i:s') }}</p>
        <p><strong>Document ID:</strong> NDA-{{ $nda->id }}-{{ $listing->listing_number }}</p>
        <p><strong>Expires:</strong> {{ $expires_date }}</p>
        <p style="font-size: 9pt; margin-top: 10px; color: #999;">
            This is a legally binding electronic signature under applicable electronic signature laws.
        </p>
    </div>
</body>
</html>
