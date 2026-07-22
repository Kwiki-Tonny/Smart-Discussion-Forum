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

    {{-- Quick Stats Preview --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="eye" style="width:14px;height:14px;color:#0A574F;"></i>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#666666]">Current Settings</span>
        </div>
        <div class="grid grid-cols-4 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $settings['inactivity_warning_1'] ?? 7 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Warning 1</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $settings['inactivity_warning_2'] ?? 14 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Warning 2</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $settings['inactivity_blacklist'] ?? 21 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Blacklist</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#DC2626]">{{ $settings['blacklist_duration'] ?? 14 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Duration</p>
            </div>
        </div>
        <div class="mt-2 text-[9px] text-[#666666] flex items-center gap-1 justify-center">
            <i data-lucide="info" style="width:10px;height:10px;"></i>
            Adjust the values below to update system policies
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="settings" style="width:28px;height:28px;color:#0A574F;"></i>
                        System Configuration
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="shield-check" style="width:14px;height:14px;color:#0A574F;"></i>
                        Configure system-wide settings and policies
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        Online
                    </span>
                    <span class="text-xs text-[#666666] flex items-center gap-1 border border-[#E5E5E5] px-3 py-1 rounded-full bg-[#F9F9F9]">
                        <i data-lucide="clock" style="width:12px;height:12px;"></i>
                        {{ now()->format('h:i A') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-3xl bg-white rounded-xl border-2 border-[#0A574F] shadow-sm p-6">

                {{-- Form Intro --}}
                <div class="flex items-center gap-2 border-b border-[#E5E5E5] pb-4 mb-6">
                    <i data-lucide="edit" style="width:18px;height:18px;color:#0A574F;"></i>
                    <p class="text-xs text-[#666666]">Update the values below to modify system behavior. Changes take effect immediately upon saving.</p>
                </div>

                <form method="POST" action="{{ route('admin.configuration.update') }}" class="space-y-6">
                    @csrf

                    {{-- Inactivity Settings --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                                <i data-lucide="clock" style="width:16px;height:16px;color:#0A574F;"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Inactivity Settings</h3>
                                <p class="text-[10px] text-[#666666]">Configure warnings and auto-blacklist rules</p>
                            </div>
                            <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full ml-auto">Auto-pilot</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Warning 1 --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="alert-triangle" style="width:14px;height:14px;color:#0A574F;"></i>
                                        Warning 1 (days)
                                    </label>
                                    <span class="text-[10px] font-bold text-[#0A574F] bg-white px-2 py-0.5 rounded-full border border-[#0A574F]">
                                        {{ $settings['inactivity_warning_1'] ?? 7 }}
                                    </span>
                                </div>
                                <input type="number" name="inactivity_warning_1"
                                       value="{{ $settings['inactivity_warning_1'] ?? 7 }}"
                                       min="1" max="30"
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                    <i data-lucide="info" style="width:10px;height:10px;"></i>
                                    First warning after inactivity
                                </p>
                            </div>

                            {{-- Warning 2 --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="alert-octagon" style="width:14px;height:14px;color:#2563EB;"></i>
                                        Warning 2 (days)
                                    </label>
                                    <span class="text-[10px] font-bold text-[#2563EB] bg-white px-2 py-0.5 rounded-full border border-[#2563EB]">
                                        {{ $settings['inactivity_warning_2'] ?? 14 }}
                                    </span>
                                </div>
                                <input type="number" name="inactivity_warning_2"
                                       value="{{ $settings['inactivity_warning_2'] ?? 14 }}"
                                       min="1" max="30"
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                    <i data-lucide="info" style="width:10px;height:10px;"></i>
                                    Second warning after inactivity
                                </p>
                            </div>

                            {{-- Blacklist --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="ban" style="width:14px;height:14px;color:#D97706;"></i>
                                        Blacklist (days)
                                    </label>
                                    <span class="text-[10px] font-bold text-[#D97706] bg-white px-2 py-0.5 rounded-full border border-[#D97706]">
                                        {{ $settings['inactivity_blacklist'] ?? 21 }}
                                    </span>
                                </div>
                                <input type="number" name="inactivity_blacklist"
                                       value="{{ $settings['inactivity_blacklist'] ?? 21 }}"
                                       min="1" max="60"
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                    <i data-lucide="info" style="width:10px;height:10px;"></i>
                                    Auto-blacklist after inactivity
                                </p>
                            </div>

                            {{-- Blacklist Duration --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                        <i data-lucide="clock" style="width:14px;height:14px;color:#DC2626;"></i>
                                        Blacklist Duration (days)
                                    </label>
                                    <span class="text-[10px] font-bold text-[#DC2626] bg-white px-2 py-0.5 rounded-full border border-[#DC2626]">
                                        {{ $settings['blacklist_duration'] ?? 14 }}
                                    </span>
                                </div>
                                <input type="number" name="blacklist_duration"
                                       value="{{ $settings['blacklist_duration'] ?? 14 }}"
                                       min="1" max="365"
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                    <i data-lucide="info" style="width:10px;height:10px;"></i>
                                    How long users stay blacklisted
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Security Settings --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                                <i data-lucide="shield" style="width:16px;height:16px;color:#2563EB;"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Security Settings</h3>
                                <p class="text-[10px] text-[#666666]">Configure authentication and access policies</p>
                            </div>
                            <span class="text-[8px] text-[#16A34A] bg-[#F0FDF4] px-2 py-0.5 rounded-full ml-auto">Secure</span>
                        </div>

                        <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition max-w-md">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] flex items-center gap-1">
                                    <i data-lucide="key" style="width:14px;height:14px;color:#0A574F;"></i>
                                    Max Login Attempts
                                </label>
                                <span class="text-[10px] font-bold text-[#0A574F] bg-white px-2 py-0.5 rounded-full border border-[#0A574F]">
                                    {{ $settings['max_login_attempts'] ?? 5 }}
                                </span>
                            </div>
                            <input type="number" name="max_login_attempts"
                                   value="{{ $settings['max_login_attempts'] ?? 5 }}"
                                   min="1" max="20"
                                   class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                            <p class="text-[10px] text-[#666666] mt-1 flex items-center gap-1">
                                <i data-lucide="info" style="width:10px;height:10px;"></i>
                                Maximum login attempts before lockout
                            </p>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-between pt-4 border-t border-[#E5E5E5]">
                        <div class="flex items-center gap-2 text-xs text-[#666666]">
                            <i data-lucide="info" style="width:14px;height:14px;color:#0A574F;"></i>
                            <span>Changes take effect immediately</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.dashboard') }}"
                               class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] px-4 py-2 rounded-lg hover:bg-[#F9F9F9] transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                                <i data-lucide="save" style="width:16px;height:16px;"></i>
                                Save Configuration
                            </button>
                        </div>
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