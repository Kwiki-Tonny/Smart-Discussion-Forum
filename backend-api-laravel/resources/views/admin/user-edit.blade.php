@extends('layouts.workspace')

@section('title', 'System Configuration')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Configuration</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="sliders" style="width:24px;height:24px;color:#16A34A;"></i>
                        System Configuration
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="shield-check" style="width:14px;height:14px;color:#16A34A;"></i>
                        Configure system-wide settings
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        Online
                    </span>
                    <span class="text-xs text-[#666666] flex items-center gap-1">
                        <i data-lucide="clock" style="width:14px;height:14px;"></i>
                        {{ now()->format('h:i A') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto p-6">
            <form method="POST" action="{{ route('admin.configuration.update') }}" class="max-w-2xl space-y-6">
                @csrf

                {{-- Inactivity Settings --}}
                <div class="bg-white rounded-xl border-2 border-[#16A34A] p-6 shadow-sm">
                    <div class="border-b border-[#E5E5E5] pb-3 mb-4 flex items-center gap-2">
                        <i data-lucide="clock" style="width:20px;height:20px;color:#16A34A;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Inactivity Settings</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Warning 1 (days)</label>
                            <input type="number" name="inactivity_warning_1"
                                   value="{{ $settings['inactivity_warning_1'] ?? 7 }}"
                                   min="1" max="30"
                                   class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#16A34A] focus:ring-2 focus:ring-[#16A34A]/20 outline-none transition">
                            <p class="text-[10px] text-[#666666] mt-1">First warning after inactivity</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Warning 2 (days)</label>
                            <input type="number" name="inactivity_warning_2"
                                   value="{{ $settings['inactivity_warning_2'] ?? 14 }}"
                                   min="1" max="30"
                                   class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#16A34A] focus:ring-2 focus:ring-[#16A34A]/20 outline-none transition">
                            <p class="text-[10px] text-[#666666] mt-1">Second warning after inactivity</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Blacklist (days)</label>
                            <input type="number" name="inactivity_blacklist"
                                   value="{{ $settings['inactivity_blacklist'] ?? 21 }}"
                                   min="1" max="60"
                                   class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#16A34A] focus:ring-2 focus:ring-[#16A34A]/20 outline-none transition">
                            <p class="text-[10px] text-[#666666] mt-1">Auto-blacklist after inactivity</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Blacklist Duration (days)</label>
                            <input type="number" name="blacklist_duration"
                                   value="{{ $settings['blacklist_duration'] ?? 14 }}"
                                   min="1" max="365"
                                   class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#16A34A] focus:ring-2 focus:ring-[#16A34A]/20 outline-none transition">
                            <p class="text-[10px] text-[#666666] mt-1">How long users stay blacklisted</p>
                        </div>
                    </div>
                </div>

                {{-- Security Settings --}}
                <div class="bg-white rounded-xl border-2 border-[#16A34A] p-6 shadow-sm">
                    <div class="border-b border-[#E5E5E5] pb-3 mb-4 flex items-center gap-2">
                        <i data-lucide="shield" style="width:20px;height:20px;color:#16A34A;"></i>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Security Settings</h3>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Max Login Attempts</label>
                        <input type="number" name="max_login_attempts"
                               value="{{ $settings['max_login_attempts'] ?? 5 }}"
                               min="1" max="20"
                               class="w-full bg-[#F9F9F9] border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#16A34A] focus:ring-2 focus:ring-[#16A34A]/20 outline-none transition">
                        <p class="text-[10px] text-[#666666] mt-1">Maximum login attempts before lockout</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                    <a href="{{ route('admin.dashboard') }}"
                       class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] px-4 py-2 rounded-lg hover:bg-[#F9F9F9] transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 bg-[#16A34A] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#15803D] transition shadow-sm">
                        <i data-lucide="check" style="width:16px;height:16px;"></i>
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush