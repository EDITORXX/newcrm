@extends('layouts.app')

@section('title', 'Import CSV - Base CRM')
@section('page-title', 'Import CSV')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="importForm" method="POST" action="{{ route('lead-import.csv.import') }}" enctype="multipart/form-data">
                @csrf

                <!-- File Upload -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">1. Upload CSV File</h3>
                    
                    <div class="border-2 border-dashed border-indigo-300 rounded-xl p-8 text-center cursor-pointer hover:bg-indigo-50 transition-colors duration-200" 
                         id="fileUploadArea">
                        <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" required class="hidden">
                        <svg class="w-12 h-12 mx-auto text-indigo-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p class="text-indigo-600 font-medium mb-2">Click to upload or drag and drop</p>
                        <p class="text-gray-500 text-sm">CSV file with name and phone columns (required)</p>
                        <p id="fileName" class="mt-4 text-gray-700 font-medium hidden"></p>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">2. Preview (Optional)</h3>
                    <button type="button" id="previewBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 mb-4">
                        Preview CSV
                    </button>
                    <div id="previewSection" class="hidden bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Preview (First 10 rows)</h4>
                        <div id="previewContent"></div>
                    </div>
                </div>

                <!-- Lead Automation -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">3. Lead Automation</h3>
                    
                    <div class="mb-4">
                        <label for="automation_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Automation <span class="text-red-500">*</span>
                        </label>
                        <select name="automation_id" id="automation_id" required
                                class="w-full px-4 py-2 bg-white text-gray-900 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select Automation --</option>
                            @foreach($automations as $automation)
                                <option value="{{ $automation->id }}">
                                    {{ $automation->name }} 
                                    ({{ ucfirst(str_replace('_', ' ', $automation->distribution_mode ?? 'N/A')) }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Automation configuration is managed through Lead Assignment settings.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('lead-import.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200">
                        Import Leads
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
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
                fileName.classList.remove('hidden');
            }
        });

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('bg-indigo-100');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('bg-indigo-100');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('bg-indigo-100');
            if (e.dataTransfer.files.length > 0) {
                csvFile.files = e.dataTransfer.files;
                fileName.textContent = e.dataTransfer.files[0].name;
                fileName.classList.remove('hidden');
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
                const response = await fetch('{{ route("lead-import.csv.preview") }}', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    previewContent.innerHTML = `
                        <p class="mb-3"><strong>Total rows found: ${data.total}</strong></p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${data.preview.map(row => `
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">${row.name || ''}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">${row.phone || ''}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">${row.email || ''}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                    previewSection.classList.remove('hidden');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                alert('Error previewing file: ' + error.message);
            }
        });
    </script>
    @endpush
@endsection

