<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import History - CRM Automation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #666; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .btn { padding: 8px 16px; border-radius: 5px; text-decoration: none; display: inline-block; font-weight: 500; }
        .btn-secondary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-secondary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Import History</h1>
            <a href="{{ route('crm.automation.index') }}" class="btn btn-secondary">Back to Automation</a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Imported By</th>
                        <th>Source</th>
                        <th>File/Sheet</th>
                        <th>Total Leads</th>
                        <th>Imported</th>
                        <th>Failed</th>
                        <th>Rule</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{ $import->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $import->user->name }}</td>
                            <td>{{ strtoupper($import->source_type) }}</td>
                            <td>{{ $import->file_name ?? $import->google_sheet_name ?? 'N/A' }}</td>
                            <td>{{ $import->total_leads }}</td>
                            <td>{{ $import->imported_leads }}</td>
                            <td>{{ $import->failed_leads }}</td>
                            <td>{{ $import->assignmentRule->name ?? 'N/A' }}</td>
                            <td>
                                @if($import->status === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif($import->status === 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($import->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #666;">
                                No import history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($imports->hasPages())
                <div style="margin-top: 20px;">
                    {{ $imports->links() }}
                </div>
            @endif
        </div>
    </div>
</body>
</html>

