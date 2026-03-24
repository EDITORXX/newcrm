<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Leads - CRM Automation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #205A44; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; }
        .btn-primary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-primary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-secondary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-secondary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .preview-section { margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 5px; display: none; }
        .preview-section.show { display: block; }
        .preview-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .preview-table th, .preview-table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .preview-table th { background: #e9ecef; }
        .file-upload-area { border: 2px dashed #205A44; border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; transition: background 0.3s; }
        .file-upload-area:hover { background: #f0f4ff; }
        .file-upload-area.dragover { background: #e0e8ff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Import Leads</h1>
            <p style="color: #666; margin-top: 5px;">Upload CSV file or connect Google Sheets</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form id="importForm" method="POST" action="{{ route('crm.automation.import.csv') }}" enctype="multipart/form-data">
            @csrf

            <div class="card">
                <h2 style="margin-bottom: 20px;">1. Select Source</h2>
                
                <div class="form-group">
                    <label>Source Type</label>
                    <select name="source_type" id="sourceType" required>
                        <option value="csv">CSV File Upload</option>
                        <option value="sheets" disabled>Google Sheets (Coming Soon)</option>
                    </select>
                </div>

                <div class="form-group" id="csvUploadSection">
                    <label>CSV File</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" required style="display: none;">
                        <p style="color: #205A44; font-size: 18px; margin-bottom: 10px;">📁 Click to upload or drag and drop</p>
                        <p style="color: #666; font-size: 14px;">CSV file with name and phone columns (required)</p>
                        <p id="fileName" style="margin-top: 10px; color: #333; font-weight: 500; display: none;"></p>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-bottom: 20px;">2. Preview (Optional)</h2>
                <button type="button" id="previewBtn" class="btn btn-secondary" style="margin-bottom: 15px;">Preview CSV</button>
                <div id="previewSection" class="preview-section">
                    <h3>Preview (First 10 rows)</h3>
                    <div id="previewContent"></div>
                </div>
            </div>

            <div class="card">
                <h2 style="margin-bottom: 20px;">3. Assignment Rule</h2>
                
                <div class="form-group">
                    <label>Select Assignment Rule</label>
                    <select name="assignment_rule_id" id="assignmentRule" required>
                        <option value="">-- Select Rule --</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->id }}">
                                {{ $rule->name }} 
                                @if($rule->type === 'specific_user')
                                    ({{ $rule->specificUser->name ?? 'N/A' }})
                                @else
                                    ({{ $rule->ruleUsers->count() }} users)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p style="color: #666; font-size: 12px; margin-top: 5px;">
                        <a href="{{ route('crm.automation.rules') }}" target="_blank">Create new rule</a>
                    </p>
                </div>
            </div>

            <div class="card">
                <button type="submit" class="btn btn-primary" id="submitBtn">Import Leads</button>
                <a href="{{ route('crm.automation.index') }}" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        const fileUploadArea = document.getElementById('fileUploadArea');
        const csvFile = document.getElementById('csvFile');
        const fileName = document.getElementById('fileName');
        const previewBtn = document.getElementById('previewBtn');
        const previewSection = document.getElementById('previewSection');
        const previewContent = document.getElementById('previewContent');

        fileUploadArea.addEventListener('click', () => csvFile.click());
        
        csvFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
                fileName.style.display = 'block';
            }
        });

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                csvFile.files = e.dataTransfer.files;
                fileName.textContent = e.dataTransfer.files[0].name;
                fileName.style.display = 'block';
            }
        });

        previewBtn.addEventListener('click', async () => {
            if (!csvFile.files.length) {
                alert('Please select a CSV file first');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', csvFile.files[0]);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const response = await fetch('{{ route("crm.automation.import.csv.preview") }}', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    previewContent.innerHTML = `
                        <p><strong>Total rows found: ${data.total}</strong></p>
                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.preview.map(row => `
                                    <tr>
                                        <td>${row.name || ''}</td>
                                        <td>${row.phone || ''}</td>
                                        <td>${row.email || ''}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                    previewSection.classList.add('show');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error previewing file: ' + error.message);
            }
        });
    </script>
</body>
</html>

