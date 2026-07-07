@extends('layouts.workspace')

@section('title', 'System Configuration')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Configuration</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">System-wide settings</p>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">System Configuration</h1>
            <p class="text-sm text-[#666666] mt-1">Configure system-wide settings</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-2xl bg-white border border-[#E5E5E5] p-6">
                <form method="POST" action="{{ route('admin.configuration.update') }}" class="space-y-5">
                    @csrf

                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000] border-b border-[#E5E5E5] pb-2">Inactivity Settings</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Warning 1 (days)</label>
                            <input type="number" name="inactivity_warning_1" value="{{ $settings['inactivity_warning_1'] ?? 7 }}" min="1" max="30"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                            <p class="text-[9px] text-[#666666]">First warning after inactivity</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Warning 2 (days)</label>
                            <input type="number" name="inactivity_warning_2" value="{{ $settings['inactivity_warning_2'] ?? 14 }}" min="1" max="30"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                            <p class="text-[9px] text-[#666666]">Second warning after inactivity</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Blacklist (days)</label>
                            <input type="number" name="inactivity_blacklist" value="{{ $settings['inactivity_blacklist'] ?? 21 }}" min="1" max="60"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                            <p class="text-[9px] text-[#666666]">Auto-blacklist after inactivity</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Blacklist Duration (days)</label>
                            <input type="number" name="blacklist_duration" value="{{ $settings['blacklist_duration'] ?? 14 }}" min="1" max="365"
                                   class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                            <p class="text-[9px] text-[#666666]">How long users stay blacklisted</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts" value="{{ $settings['max_login_attempts'] ?? 5 }}" min="1" max="20"
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000]">
                        <p class="text-[9px] text-[#666666]">Maximum login attempts before lockout</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('admin.dashboard') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-[#000000] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                            Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection