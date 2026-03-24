<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Rules - CRM Automation</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #F7F6F3; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #205A44; }
        .btn { padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: 500; text-decoration: none; display: inline-block; }
        .btn-primary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-primary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-secondary { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .btn-secondary:hover { background: linear-gradient(135deg, #15803d 0%, #166534 100%); transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
        .btn-danger { background: #dc3545; color: white; }
        .alert { padding: 12px; border-radius: 5px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #666; }
        .percentage-input { width: 80px; }
        .user-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .user-row select { flex: 1; }
        .user-row input { width: 100px; }
        .user-row button { padding: 8px 16px; }
        .total-percentage { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-weight: 600; }
        .total-percentage.valid { background: #d4edda; color: #155724; }
        .total-percentage.invalid { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Assignment Rules</h1>
            <p style="color: #666; margin-top: 5px;">Manage how leads are automatically assigned to users</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <h2 style="margin-bottom: 20px;">Create New Rule</h2>
            <form method="POST" action="{{ route('crm.automation.rules.store') }}" id="ruleForm">
                @csrf
                
                <div class="form-group">
                    <label>Rule Name</label>
                    <input type="text" name="name" required placeholder="e.g., Team Distribution">
                </div>

                <div class="form-group">
                    <label>Rule Type</label>
                    <select name="type" id="ruleType" required onchange="toggleRuleType()">
                        <option value="specific_user">Assign to Specific User</option>
                        <option value="percentage">Percentage-Based Distribution</option>
                    </select>
                </div>

                <div class="form-group" id="specificUserSection">
                    <label>Select User</label>
                    <select name="specific_user_id" id="specificUserId">
                        <option value="">-- Select User --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" id="percentageSection" style="display: none;">
                    <label>User Distribution</label>
                    <div id="usersContainer">
                        <div class="user-row">
                            <select name="users[0][user_id]" required>
                                <option value="">-- Select User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="users[0][percentage]" class="percentage-input" min="0" max="100" step="0.01" required placeholder="%" onchange="updateTotal()">
                            <button type="button" onclick="removeUserRow(this)" class="btn btn-danger">Remove</button>
                        </div>
                    </div>
                    <button type="button" onclick="addUserRow()" class="btn btn-secondary" style="margin-top: 10px;">Add User</button>
                    <div class="total-percentage" id="totalPercentage">Total: 0%</div>
                </div>

                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" rows="3" placeholder="Describe this rule..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create Rule</button>
                <a href="{{ route('crm.automation.index') }}" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 20px;">Existing Rules</h2>
            @if($rules->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $rule)
                            <tr>
                                <td><strong>{{ $rule->name }}</strong></td>
                                <td>
                                    @if($rule->type === 'specific_user')
                                        Specific User
                                    @else
                                        Percentage
                                    @endif
                                </td>
                                <td>
                                    @if($rule->type === 'specific_user')
                                        {{ $rule->specificUser->name ?? 'N/A' }}
                                    @else
                                        @foreach($rule->ruleUsers as $ruleUser)
                                            {{ $ruleUser->user->name }} ({{ $ruleUser->percentage }}%)@if(!$loop->last), @endif
                                        @endforeach
                                    @endif
                                    @if($rule->googleSheetsConfigs->count() > 0)
                                        <br><small style="color: #205A44;">Used by {{ $rule->googleSheetsConfigs->count() }} Google Sheet(s)</small>
                                    @endif
                                </td>
                                <td>
                                    @if($rule->is_active)
                                        <span style="color: green;">Active</span>
                                    @else
                                        <span style="color: red;">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $rule->created_at->format('M d, Y') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('crm.automation.rules.destroy', $rule) }}" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 14px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="color: #666; text-align: center; padding: 40px;">No rules created yet. Create your first assignment rule above.</p>
            @endif
        </div>
    </div>

    <script>
        let userRowIndex = 1;

        function toggleRuleType() {
            const type = document.getElementById('ruleType').value;
            const specificSection = document.getElementById('specificUserSection');
            const percentageSection = document.getElementById('percentageSection');
            const specificSelect = document.getElementById('specificUserId');

            if (type === 'specific_user') {
                specificSection.style.display = 'block';
                percentageSection.style.display = 'none';
                specificSelect.required = true;
            } else {
                specificSection.style.display = 'none';
                percentageSection.style.display = 'block';
                specificSelect.required = false;
            }
        }

        function addUserRow() {
            const container = document.getElementById('usersContainer');
            const row = document.createElement('div');
            row.className = 'user-row';
            row.innerHTML = `
                <select name="users[${userRowIndex}][user_id]" required>
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name }})</option>
                    @endforeach
                </select>
                <input type="number" name="users[${userRowIndex}][percentage]" class="percentage-input" min="0" max="100" step="0.01" required placeholder="%" onchange="updateTotal()">
                <button type="button" onclick="removeUserRow(this)" class="btn btn-danger">Remove</button>
            `;
            container.appendChild(row);
            userRowIndex++;
        }

        function removeUserRow(btn) {
            btn.closest('.user-row').remove();
            updateTotal();
        }

        function updateTotal() {
            const inputs = document.querySelectorAll('.percentage-input');
            let total = 0;
            inputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            const totalDiv = document.getElementById('totalPercentage');
            totalDiv.textContent = `Total: ${total.toFixed(2)}%`;
            if (Math.abs(total - 100) < 0.01) {
                totalDiv.className = 'total-percentage valid';
            } else {
                totalDiv.className = 'total-percentage invalid';
            }
        }

        document.getElementById('ruleForm').addEventListener('submit', function(e) {
            const type = document.getElementById('ruleType').value;
            if (type === 'percentage') {
                const totalDiv = document.getElementById('totalPercentage');
                const total = parseFloat(totalDiv.textContent.replace('Total: ', '').replace('%', ''));
                if (Math.abs(total - 100) >= 0.01) {
                    e.preventDefault();
                    alert('Percentages must sum to exactly 100%');
                    return false;
                }
            }
        });
    </script>
</body>
</html>

