@extends('layouts.admin')

@section('title', 'Import Items from CSV')

<style>
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 1.2rem;
        background: transparent;
        border: 1.5px solid #d4c4b4;
        border-radius: 40px;
        color: #5c4b3a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    @media (max-width: 768px) {
        .btn-back {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            white-space: normal;
            word-break: keep-all;
        }
    }
    .chunk-progress {
        background-color: #f5f5f4;
        border-radius: 9999px;
        height: 1.25rem;
        overflow: hidden;
        position: relative;
    }
    .chunk-progress-bar {
        width: 0%;
        height: 100%;
        background: linear-gradient(90deg, #059669, #10b981);
        border-radius: 9999px;
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        color: white;
        font-weight: 600;
    }
    .chunk-upload-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
    }
    .chunk-upload-row .file-input-wrapper {
        flex: 1 1 200px;
    }
    .chunk-upload-row .upload-btn-wrapper {
        flex: 0 0 auto;
        margin-left: auto;
    }
    @media (max-width: 640px) {
        .chunk-upload-row {
            flex-direction: column;
            align-items: stretch;
        }
        .chunk-upload-row .upload-btn-wrapper {
            margin-left: 0;
        }
    }
</style>

@section('content')
<div class="space-y-8 min-h-screen p-4 md:p-6" style="padding: 1rem;">
    @php
        $isMobile = true;
    @endphp

    {{-- HEADER --}}
    <div style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
        <div style="flex: 1;">
            <h2 style="font-size: clamp(1.5rem, 6vw, 1.875rem); font-weight: 700; color: #2a1a05; font-family: Georgia, serif; margin: 0; line-height: 1.2;">
                Import Items from CSV
            </h2>
            <p style="margin-top: 0.5rem; font-size: 0.813rem; color: #57534e;">Bulk upload products using CSV file + local images</p>
        </div>
      
        <div style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; width: 100%;">
                <a href="{{ asset('csv/product_import_template.csv') }}" download
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; text-decoration: none; font-weight: 500; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; flex: 1 1 auto; min-width: 140px;">
                    <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download CSV
                </a>
                <a href="{{ route('admin.items.import.images') }}"
                    style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 0.5rem; background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); color: white; text-decoration: none; font-weight: 500; font-size: 0.875rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease; flex: 1 1 auto; min-width: 140px;">
                    <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upload Images
                </a>
            </div>
            <div style="display: flex; width: 100%;">
                <a href="{{ route('admin.items.index') }}" class="btn-back" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; background: transparent; border: 1.5px solid #d4c4b4; border-radius: 40px; color: #5c4b3a; font-weight: 600; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; width: 100%;">
                    ← Back to Items
                </a>
            </div>
        </div>
    </div>

    {{-- INFO BOX --}}
    <div style="border-radius: 0.75rem; background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 1rem; margin-top: 0.5rem;">
        <div style="display: flex;">
            <div style="flex-shrink: 0;">
                <svg style="height: 1.25rem; width: 1.25rem; color: #f59e0b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div style="margin-left: 0.75rem; flex: 1;">
                <h3 style="font-size: 0.875rem; font-weight: 500; color: #92400e; margin-bottom: 0.5rem;">CSV Format Instructions</h3>
                <div style="font-size: 0.813rem; color: #b45309;">
                    <ul style="list-style: disc; padding-left: 1.25rem; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li>First upload all images using <strong>"Upload Images First"</strong> button.</li>
                        <li>CSV must have columns: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">name, category, type, model, price, sku, warehouses, description, specifications, images, is_featured</code></li>
                        <li><strong>warehouses</strong> format: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">WH1:20,WH2:10</code> (codes must match your warehouse codes)</li>
                        <li><strong>specifications</strong> format: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">Brand:Logitech;Color:Black</code></li>
                        <li><strong>images</strong> column: comma separated filenames (e.g., <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">mouse1.jpg,mouse2.jpg</code>) – if empty, images will be skipped.</li>
                        <li><strong>is_featured</strong> column: <code style="background: #ffedd5; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">Best Seller</code> or <code>Yes</code> or <code>1</code> to mark as featured</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== CHUNKED IMPORT ========== --}}
    <div style="background-color: white; border-radius: 1rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); border: 1px solid #f5f5f4; overflow: hidden; margin-top: 1.5rem;">
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid #f5f5f4; background: rgba(22, 12, 0, 0.02);">
            <h3 style="font-size: 1rem; font-weight: 600; display: flex; align-items: center; color: #160c00;">
                <svg style="width: 1.125rem; height: 1.125rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Chunked Import (Supports large files up to 100MB+)
            </h3>
        </div>
        <div style="padding: 1rem;">
            <div class="chunk-upload-row">
                <div class="file-input-wrapper">
                    <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #160c00;">Choose CSV File</label>
                    <input type="file" id="csvFile" accept=".csv,.txt" style="width: 100%; padding: 0.75rem 1rem; background-color: #fafaf9; border: 1px solid #e7e5e4; border-radius: 0.75rem; color: #1c1917; font-size: 0.875rem; cursor: pointer;">
                    <p style="font-size: 0.75rem; color: #78716c; margin-top: 0.25rem;">No size limit – automatically splits into chunks</p>
                </div>
                <div class="upload-btn-wrapper">
                    <button type="button" id="uploadBtn" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1.5rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s ease; font-size: 0.875rem; white-space: nowrap;">
                        <svg style="width: 1.125rem; height: 1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Start Chunked Import
                    </button>
                </div>
            </div>

            <div id="progressWrapper" style="display: none; margin-top: 1.5rem;">
                <div class="chunk-progress">
                    <div id="progressBar" class="chunk-progress-bar" style="width:0%;">0%</div>
                </div>
                <p id="statusMsg" style="margin-top: 0.5rem; font-size: 0.875rem; color: #44403c;">Preparing...</p>
            </div>

            <div id="result" style="margin-top: 1rem;"></div>
        </div>
    </div>

    @if(session('errors') && count(session('errors')) > 0)
        <div style="border-radius: 0.75rem; background-color: #fff1f2; border-left: 4px solid #e11d48; padding: 1rem;">
            <div style="display: flex;">
                <div style="flex-shrink: 0;">
                    <svg style="height: 1.25rem; width: 1.25rem; color: #fb7185;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div style="margin-left: 0.75rem; flex: 1;">
                    <h3 style="font-size: 0.875rem; font-weight: 500; color: #9f1239;">Import Errors</h3>
                    <ul style="margin-top: 0.5rem; font-size: 0.813rem; color: #be123c; list-style: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.25rem;">
                        @foreach(session('errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- JavaScript for chunked upload --}}
<script>
document.getElementById('uploadBtn').addEventListener('click', function() {
    const fileInput = document.getElementById('csvFile');
    const file = fileInput.files[0];
    if (!file) {
        alert('Please select a CSV file.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const content = e.target.result;
        const lines = content.split(/\r?\n/).filter(line => line.trim() !== '');
        if (lines.length === 0) {
            alert('File is empty.');
            return;
        }
        const header = lines[0];
        const dataLines = lines.slice(1);
        const chunkSizeLines = 2000; // Number of data rows per chunk
        const totalDataLines = dataLines.length;
        const totalChunks = Math.ceil(totalDataLines / chunkSizeLines);

        document.getElementById('progressWrapper').style.display = 'block';
        const progressBar = document.getElementById('progressBar');
        const statusMsg = document.getElementById('statusMsg');
        const resultDiv = document.getElementById('result');
        resultDiv.innerHTML = '';

        const uploadToken = Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        function sendChunk(index) {
            const start = index * chunkSizeLines;
            const end = Math.min(start + chunkSizeLines, totalDataLines);
            let chunkData;
            if (index === 0) {
                chunkData = [header, ...dataLines.slice(start, end)].join('\n');
            } else {
                chunkData = dataLines.slice(start, end).join('\n');
            }

            const formData = new FormData();
            formData.append('chunk', chunkData);
            formData.append('chunk_index', index);
            formData.append('total_chunks', totalChunks);
            formData.append('upload_token', uploadToken);
            formData.append('original_name', file.name);
            formData.append('chunk_size_lines', chunkSizeLines);

            fetch('{{ route("admin.items.importChunk") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => {
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
        if (data.success) {
            const progress = Math.round((index + 1) / totalChunks * 100);
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
            statusMsg.textContent = `Uploading chunk ${index+1} of ${totalChunks}...`;
    
            if (index + 1 < totalChunks) {
                sendChunk(index + 1);
            } else {
                progressBar.style.width = '100%';
                progressBar.textContent = '100%';
                statusMsg.textContent = 'Processing complete!';
                if (data.warnings && data.warnings.length) {
                    resultDiv.innerHTML = `
                        <div style="background:#d1fae5;border:1px solid #059669;padding:0.75rem;border-radius:0.5rem;color:#065f46;">
                            ${data.message}
                            <ul style="margin-top:0.5rem;list-style:disc;padding-left:1.25rem;">
                                ${data.warnings.map(w => `<li>${w}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<div style="background:#d1fae5;border:1px solid #059669;padding:0.75rem;border-radius:0.5rem;color:#065f46;">${data.message}</div>`;
                }
            }
        } else {
        // Error case – display full validation errors if available
        statusMsg.textContent = 'Error: ' + (data.error || 'Unknown error');
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';

        let errorHtml = `
            <div style="background:#fecaca;border:1px solid #dc2626;padding:0.75rem;border-radius:0.5rem;color:#7f1d1d;">
                <strong>${data.error || 'Import failed'}</strong>
        `;

        // If detailed validation_errors exist, show them as a bullet list
        if (data.validation_errors && data.validation_errors.length) {
            errorHtml += `<ul style="margin-top:0.5rem;list-style:disc;padding-left:1.25rem;">`;
            data.validation_errors.forEach(err => {
                errorHtml += `<li>${err}</li>`;
            });
            errorHtml += `</ul>`;
        } else if (data.error) {
            errorHtml += `<p style="margin-top:0.5rem;">${data.error}</p>`;
        }

        errorHtml += `</div>`;
        resultDiv.innerHTML = errorHtml;
    }
})
            .catch(err => {
                statusMsg.textContent = 'Error: ' + err.message;
                progressBar.style.width = '0%';
                resultDiv.innerHTML = `<div style="background:#fecaca;border:1px solid #dc2626;padding:0.75rem;border-radius:0.5rem;color:#7f1d1d;">${err.message}</div>`;
            });
        }

        sendChunk(0);
    };
    reader.readAsText(file);
});
</script>

<style>
    @media (min-width: 768px) {
        [style*="padding: 1rem"] {
            padding: 1.5rem;
        }
        .btn-back {
            width: auto !important;
        }
    }
    @media (min-width: 640px) {
        form > div:last-child {
            flex-direction: row !important;
            justify-content: flex-end !important;
        }
        form > div:last-child a {
            width: auto !important;
        }
    }
    button, a {
        transition: all 0.2s ease;
    }
    button:active, .btn-back:active, a:active {
        transform: scale(0.98);
    }
    input[type="file"]:hover {
        background-color: #f5f5f4;
        border-color: #d6d3d1;
    }
    @media (max-width: 640px) {
        button, .btn-back, a[style*="padding"] {
            min-height: 44px;
        }
        input, select, textarea {
            font-size: 16px !important;
        }
    }
</style>
@endsection