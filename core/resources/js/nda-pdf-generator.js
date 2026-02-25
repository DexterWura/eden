/**
 * Browser-based PDF Generator for NDA Documents
 * Uses html2pdf.js library (loaded via CDN)
 */

class NdaPdfGenerator {
    constructor() {
        this.html2pdf = null;
        this.init();
    }

    async init() {
        // Load html2pdf.js from CDN if not already loaded
        if (typeof html2pdf === 'undefined') {
            await this.loadHtml2Pdf();
        }
        this.html2pdf = html2pdf;
    }

    loadHtml2Pdf() {
        return new Promise((resolve, reject) => {
            if (typeof html2pdf !== 'undefined') {
                resolve();
                return;
            }

            // Load html2pdf.js and its dependencies
            const script1 = document.createElement('script');
            script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            script1.onload = () => {
                const script2 = document.createElement('script');
                script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                script2.onload = () => {
                    const script3 = document.createElement('script');
                    script3.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                    script3.onload = () => resolve();
                    script3.onerror = reject;
                    document.head.appendChild(script3);
                };
                script2.onerror = reject;
                document.head.appendChild(script2);
            };
            script1.onerror = reject;
            document.head.appendChild(script1);
        });
    }

    /**
     * Generate PDF from HTML element
     */
    async generatePdf(element, options = {}) {
        await this.init();

        const defaultOptions = {
            margin: [10, 10, 10, 10],
            filename: 'nda-document.pdf',
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

        const mergedOptions = { ...defaultOptions, ...options };

        try {
            const pdf = await this.html2pdf().set(mergedOptions).from(element).save();
            return pdf;
        } catch (error) {
            console.error('PDF generation failed:', error);
            throw error;
        }
    }

    /**
     * Generate PDF from HTML string
     */
    async generatePdfFromHtml(html, options = {}) {
        await this.init();

        // Create temporary element
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        document.body.appendChild(tempDiv);

        try {
            const pdf = await this.generatePdf(tempDiv, options);
            document.body.removeChild(tempDiv);
            return pdf;
        } catch (error) {
            document.body.removeChild(tempDiv);
            throw error;
        }
    }

    /**
     * Add watermark to PDF (via CSS before generation)
     */
    addWatermark(element, watermarkText) {
        const watermark = document.createElement('div');
        watermark.className = 'pdf-watermark';
        watermark.textContent = watermarkText;
        watermark.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(0, 0, 0, 0.1);
            pointer-events: none;
            z-index: 1000;
            white-space: nowrap;
        `;
        element.appendChild(watermark);
    }
}

// Make it globally available
window.NdaPdfGenerator = NdaPdfGenerator;
