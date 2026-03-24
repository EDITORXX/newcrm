@extends('layouts.app')

@section('title', 'Select Form - Facebook Lead Ads - Base CRM')
@section('page-title', 'Facebook Lead Ads – Select Form')

@section('header-actions')
    @if(isset($page) && $page)
    <a href="{{ route('integrations.facebook-lead-ads.forms') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back to pages
    </a>
    @else
    <a href="{{ route('integrations.facebook-lead-ads.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-sm font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
    @endif
@endsection

@section('content')
<div class="max-w-3xl mx-auto">
    @if(isset($pages) && $pages->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Choose a page</h2>
        <p class="text-gray-600 text-sm mb-4">Select a page to view and configure its Lead Gen forms.</p>
        <ul class="space-y-3">
            @foreach($pages as $p)
            <li class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                <div>
                    <span class="font-medium text-gray-900">{{ $p->page_name ?: $p->page_id }}</span>
                    <span class="text-xs text-gray-500 ml-2">ID: {{ $p->page_id }}</span>
                </div>
                <a href="{{ route('integrations.facebook-lead-ads.forms', ['page_id' => $p->page_id]) }}" class="px-3 py-1.5 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm hover:from-[#205A44] hover:to-[#15803d]">Select forms</a>
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Lead Gen forms – {{ $page->page_name ?: $page->page_id }}</h2>
        <p class="text-gray-600 text-sm mb-4">Select a form to configure field mapping and enable sync.</p>

        @if(empty($forms))
            <p class="text-gray-500">No forms found for this page. Create a Lead Ad form in Meta Business Suite / Ads Manager first.</p>
        @else
            <ul class="space-y-3">
                @foreach($forms as $form)
                    @php
                        $metaId = $form['id'] ?? '';
                        $name = $form['name'] ?? 'Form ' . $metaId;
                        $created = $form['created_time'] ?? '';
                        $exists = isset($existingFormIds) && in_array($metaId, array_values($existingFormIds));
                    @endphp
                    <li class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div>
                            <span class="font-medium text-gray-900">{{ $name }}</span>
                            @if($created)
                                <span class="text-xs text-gray-500 ml-2">{{ $created }}</span>
                            @endif
                        </div>
                        <a href="{{ route('integrations.facebook-lead-ads.mapping', ['formId' => $metaId, 'form_name' => $name, 'page_id' => $page->page_id]) }}" class="px-3 py-1.5 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg text-sm hover:from-[#205A44] hover:to-[#15803d]">
                            {{ $exists ? 'Edit mapping' : 'Configure' }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif
</div>
@endsection
