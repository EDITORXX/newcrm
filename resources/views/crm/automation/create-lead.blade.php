<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Lead - CRM Automation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label .required { color: #dc3545; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #205A44; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: #205A44; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .section-title { font-size: 18px; font-weight: 600; color: #333; margin: 30px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; }
        .section-title:first-child { margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create New Lead</h1>
            <p style="color: #666; margin-top: 5px;">Add a new lead manually and assign to a user</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('crm.automation.leads.store') }}" class="card">
            @csrf

            <div class="info-box" style="margin-bottom: 30px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
                <strong>📝 Note:</strong> Only <strong>Name</strong> and <strong>Phone Number</strong> are required for initial creation. 
                After creating the lead, you will be redirected to fill all detailed requirement fields (Category, Location, Type, Purpose, Budget, Status, etc.) using the centralized form.
            </div>

            <h2 class="section-title">Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter lead name">
                </div>

                <div class="form-group">
                    <label>Phone/Number <span class="required">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="Enter phone number">
                </div>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="assigned_to">Assign To User (Optional)</label>
                <select name="assigned_to" id="assigned_to">
                    <option value="">— Do not assign —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role->name ?? $user->role->slug ?? '—' }})
                        </option>
                    @endforeach
                </select>
                <p style="font-size: 14px; color: #666; margin-top: 8px;">Assign now to create a calling task for the user immediately.</p>
            </div>

            <div style="margin-top: 30px; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Create Lead</button>
                <a href="{{ route('crm.automation.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

