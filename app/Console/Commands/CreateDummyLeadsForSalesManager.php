<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\User;
use App\Models\Role;
use App\Models\Task;
use App\Events\LeadAssigned;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateDummyLeadsForSalesManager extends Command
{
    protected $signature = 'leads:create-dummy-for-manager {manager_email=salesmanager1@realtorcrm.com} {count=10}';
    protected $description = 'Create dummy leads for a Senior Manager';

    public function handle()
    {
        $managerEmail = $this->argument('manager_email');
        $count = (int) $this->argument('count');
        
        DB::beginTransaction();
        
        try {
            // Get Senior Manager
            $manager = User::where('email', $managerEmail)
                ->whereHas('role', function($q) {
                    $q->where('slug', 'sales_manager');
                })
                ->first();
            
            if (!$manager) {
                $this->error("Senior Manager with email '{$managerEmail}' not found!");
                return 1;
            }
            
            $this->info("Creating {$count} dummy leads for: {$manager->name} (ID: {$manager->id})");
            
            // Get admin user for created_by
            $adminRole = Role::where('slug', Role::ADMIN)->first();
            $admin = $adminRole ? User::where('role_id', $adminRole->id)->where('is_active', true)->first() : null;
            $createdBy = $admin ? $admin->id : 1;
            
            // Random Indian names
            $firstNames = ['Raj', 'Priya', 'Amit', 'Neha', 'Rahul', 'Sneha', 'Vikram', 'Anjali', 'Deepak', 'Kavita', 'Arjun', 'Swati', 'Rohit', 'Pooja', 'Sachin', 'Divya', 'Karan', 'Meera', 'Vishal', 'Tanvi'];
            $lastNames = ['Sharma', 'Patel', 'Singh', 'Kumar', 'Gupta', 'Verma', 'Yadav', 'Reddy', 'Malik', 'Chauhan', 'Shah', 'Mehta', 'Joshi', 'Desai', 'Agarwal', 'Gandhi', 'Nair', 'Rao', 'Iyer', 'Menon'];
            
            $leadsCreated = 0;
            $tasksCreated = 0;
            
            for ($i = 1; $i <= $count; $i++) {
                // Generate random Indian name
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $randomName = $firstName . ' ' . $lastName;
                
                // Generate random 10-digit phone number starting with 9
                $randomPhone = '9' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
                
                // Create lead (use 'google_sheets' as source since 'manual' might not be in enum)
                $lead = Lead::create([
                    'name' => $randomName,
                    'phone' => $randomPhone,
                    'source' => 'google_sheets',
                    'status' => 'new',
                    'created_by' => $createdBy,
                ]);
                
                // Create assignment to Senior Manager
                $assignment = LeadAssignment::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $manager->id,
                    'assigned_by' => $createdBy,
                    'assignment_type' => 'primary',
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);
                
                // Create calling task for Senior Manager (scheduled 15 minutes from now)
                $scheduledAt = now()->addMinutes(15);
                $task = Task::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $manager->id,
                    'type' => 'phone_call',
                    'title' => 'Prospect Verification: ' . $lead->name,
                    'status' => 'pending',
                    'scheduled_at' => $scheduledAt,
                    'created_by' => $createdBy,
                ]);
                
                $this->line("  ✓ Created lead: {$lead->name} (Phone: {$lead->phone})");
                $leadsCreated++;
                $tasksCreated++;
            }
            
            DB::commit();
            
            $this->info("");
            $this->info("✅ Successfully created {$leadsCreated} dummy leads for {$manager->name}!");
            $this->info("✅ Created {$leadsCreated} lead assignments");
            $this->info("✅ Created {$tasksCreated} calling tasks (scheduled 15 minutes from now)");
            $this->info("");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("");
            $this->error("❌ Failed to create dummy leads: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
