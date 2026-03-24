@extends('layouts.app')

@section('title', 'Integrations - Base CRM')
@section('page-title', 'Integrations')

@section('header-actions')
    <button onclick="showComingSoonNotification('Configuration')" class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-cog mr-2"></i>
        Configuration
    </button>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Integrations Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Email Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.email') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-envelope text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Email</h3>
                <p class="text-sm text-gray-500 mb-4">Email integration for sending and receiving emails</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Calendar Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.calendar') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Calendar</h3>
                <p class="text-sm text-gray-500 mb-4">Calendar integration for scheduling and events</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- WhatsApp API Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.whatsapp') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fab fa-whatsapp text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">WhatsApp API</h3>
                <p class="text-sm text-gray-500 mb-4">WhatsApp Business API integration via Engage API</p>
                @php
                    $whatsappSettings = \App\Models\WhatsAppApiSettings::getSettings();
                @endphp
                <div class="flex items-center mb-4">
                    @if($whatsappSettings->is_active && $whatsappSettings->is_verified)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active & Verified</span>
                    @elseif($whatsappSettings->is_active)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Active (Not Verified)</span>
                    @elseif($whatsappSettings->api_token)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Configured</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not Configured</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.whatsapp') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Sheet Integration (hub: Lead Import + Smart Import + Meta Sheet + Form Integration) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.sheet-integration') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-600 to-green-700 flex items-center justify-center">
                        <i class="fas fa-table text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sheet Integration</h3>
                <p class="text-sm text-gray-500 mb-4">Lead Import, Smart Import, Meta Sheet aur Form Integration — sab ek jagah</p>
                @php
                    try {
                        $sheetTotalActive = \App\Models\GoogleSheetsConfig::where('is_active', true)->count();
                    } catch (\Exception $e) {
                        $sheetTotalActive = 0;
                    }
                @endphp
                <div class="flex items-center mb-4">
                    @if($sheetTotalActive > 0)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $sheetTotalActive }} Active</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not Configured</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.sheet-integration') }}'"
                        class="w-full px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-arrow-right mr-2"></i>
                    Open
                </button>
            </div>
        </div>

        <!-- Facebook Lead Ads (standalone – direct webhook + Graph API) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.facebook-lead-ads.index') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fab fa-facebook text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Facebook Lead Ads</h3>
                <p class="text-sm text-gray-500 mb-4">Direct webhook + Graph API. One-click form mapping; leads sync here first (standalone).</p>
                @php
                    try {
                        $fbLeadAdsSettings = \App\Models\FbLeadAdsSettings::getSettings();
                        $fbLeadAdsConfigured = (!empty($fbLeadAdsSettings->page_access_token) && !empty($fbLeadAdsSettings->page_id))
                            || \App\Models\FbPage::whereNotNull('page_access_token')->exists();
                    } catch (\Exception $e) {
                        $fbLeadAdsConfigured = false;
                    }
                @endphp
                <div class="flex items-center mb-4">
                    @if($fbLeadAdsConfigured)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Configured</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not configured</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.facebook-lead-ads.index') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configure
                </button>
            </div>
        </div>

        <!-- Magic Bricks Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.magic-bricks') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Magic Bricks</h3>
                <p class="text-sm text-gray-500 mb-4">Magic Bricks real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Housing Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.housing') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-home text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Housing</h3>
                <p class="text-sm text-gray-500 mb-4">Housing.com real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- 99acres Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.99acres') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">99acres</h3>
                <p class="text-sm text-gray-500 mb-4">99acres real estate platform integration</p>
                <div class="flex items-center mb-4">
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Coming Soon</span>
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.configuration') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- Pabbly Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.pabbly') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-plug text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pabbly</h3>
                <p class="text-sm text-gray-500 mb-4">Pabbly webhook integration for lead automation</p>
                @php
                    try {
                        $pabblySettings = \App\Models\PabblyIntegrationSettings::getSettings();
                        $pabblyIsActive = $pabblySettings->is_active ?? false;
                    } catch (\Exception $e) {
                        $pabblyIsActive = false;
                    }
                @endphp
                <div class="flex items-center mb-4">
                    @if($pabblyIsActive)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.pabbly') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Configuration
                </button>
            </div>
        </div>

        <!-- MCube Integration -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('integrations.mcube.index') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 flex items-center justify-center">
                        <i class="fas fa-phone-alt text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">MCube</h3>
                <p class="text-sm text-gray-500 mb-4">Auto-capture call leads, assign agents & save recordings via MCube webhook</p>
                @php
                    try {
                        $mcubeActive = \App\Models\McubeSetting::getSettings()->is_enabled ?? false;
                    } catch (\Exception $e) {
                        $mcubeActive = false;
                    }
                @endphp
                <div class="flex items-center mb-4">
                    @if($mcubeActive)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('integrations.mcube.index') }}'"
                        class="w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i> Configuration
                </button>
            </div>
        </div>


        <!-- Lead Assignment System -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location.href='{{ route('lead-assignment.index') }}'">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Lead Assignment</h3>
                <p class="text-sm text-gray-500 mb-4">Manage lead assignments and sales executive configurations</p>
                @php
                    try {
                        $telecallerCount = \App\Models\User::whereHas('role', function($q) {
                            $q->where('name', 'telecaller');
                        })->count();
                        $leadAssignmentActive = $telecallerCount > 0;
                    } catch (\Exception $e) {
                        $leadAssignmentActive = false;
                        $telecallerCount = 0;
                    }
                @endphp
                <div class="flex items-center mb-4">
                    @if($leadAssignmentActive)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $telecallerCount }} Sales Executives</span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">No Sales Executives</span>
                    @endif
                </div>
                <button onclick="event.stopPropagation(); window.location.href='{{ route('lead-assignment.index') }}'" 
                        class="w-full px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors duration-200 text-sm font-medium">
                    <i class="fas fa-cog mr-2"></i>
                    Manage
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    function showComingSoonNotification(integrationName) {
        alert(integrationName + ' Integration\n\nComing Soon!\n\nThis integration is currently under development and will be available soon.');
    }
</script>
@endpush
@endsection
