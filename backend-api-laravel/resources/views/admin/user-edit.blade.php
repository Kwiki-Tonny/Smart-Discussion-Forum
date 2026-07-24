@extends('layouts.workspace')

@section('title', 'Edit User - ' . $user->name)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.users') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity flex items-center gap-1">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">Edit User</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick User Info --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#0A574F] to-[#16A34A] text-white flex items-center justify-center text-2xl font-bold uppercase rounded-lg flex-shrink-0 shadow-sm">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                    {{ $user->name }}
                    @if($user->status === 'active')
                        <i data-lucide="badge-check" style="width:14px;height:14px;color:#0A574F;"></i>
                    @elseif($user->status === 'blacklisted')
                        <i data-lucide="ban" style="width:14px;height:14px;color:#DC2626;"></i>
                    @endif
                </h3>
                <p class="text-xs text-[#666666] truncate flex items-center gap-1">
                    <i data-lucide="mail" style="width:12px;height:12px;color:#2563EB;"></i>
                    {{ $user->email }}
                </p>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-0.5 rounded-full">
                        {{ ucfirst($user->role) }}
                    </span>
                    <span class="text-[8px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5 rounded-full">
                        {{ ucfirst($user->status) }}
                    </span>
                    @if($user->blacklist_expires_at && $user->status === 'blacklisted')
                        <span class="text-[8px] font-bold uppercase tracking-wider text-[#DC2626] border border-[#DC2626] px-2 py-0.5 rounded-full flex items-center gap-1">
                            <i data-lucide="clock" style="width:8px;height:8px;"></i>
                            Expires: {{ $user->blacklist_expires_at->format('M d, Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Groups (if needed) --}}
    @if($groups->isNotEmpty())
        <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
            <div class="flex items-center gap-2">
                <i data-lucide="users" style="width:14px;height:14px;color:#0A574F;"></i>
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#666666]">Member of {{ $user->groups->count() }} groups</span>
            </div>
            <div class="flex flex-wrap gap-1 mt-1">
                @foreach($user->groups as $group)
                    <span class="text-[9px] font-medium text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full border border-[#0A574F]">{{ $group->name }}</span>
                @endforeach
            </div>
        </div>
    @endif
@endsection

@section('content')
    <div class="flex flex-col h-full bg-[#F9F9F9]">

        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[#000000] flex items-center gap-3">
                        <i data-lucide="user-cog" style="width:28px;height:28px;color:#0A574F;"></i>
                        Edit User
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="shield" style="width:14px;height:14px;color:#0A574F;"></i>
                        Manage user roles, status, and profile information
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $user->role }}
                    </span>
                    <span class="text-xs text-[#666666] flex items-center gap-1 border border-[#E5E5E5] px-3 py-1 rounded-full bg-[#F9F9F9]">
                        <i data-lucide="calendar" style="width:12px;height:12px;"></i>
                        Joined: {{ $user->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mx-6 mt-4 bg-[#F0FDF4] border border-[#16A34A] text-[#16A34A] px-4 py-3 rounded-lg flex items-center gap-2">
                <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 bg-[#FEF2F2] border border-[#DC2626] text-[#DC2626] px-4 py-3 rounded-lg flex items-center gap-2">
                <i data-lucide="alert-circle" style="width:18px;height:18px;"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- Edit Form --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-3xl bg-white rounded-xl border-2 border-[#0A574F] shadow-sm p-6">

                <form method="POST" action="{{ route('admin.user.update', $user->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Basic Information --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                                <i data-lucide="user" style="width:16px;height:16px;color:#0A574F;"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Basic Information</h3>
                                <p class="text-[10px] text-[#666666]">Update user's personal details</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Name --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] mb-1 flex items-center gap-1">
                                    <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                                    Full Name
                                </label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                @error('name')
                                    <p class="text-[10px] text-[#DC2626] mt-1 flex items-center gap-1">
                                        <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] mb-1 flex items-center gap-1">
                                    <i data-lucide="mail" style="width:14px;height:14px;color:#2563EB;"></i>
                                    Email Address
                                </label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                       class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                @error('email')
                                    <p class="text-[10px] text-[#DC2626] mt-1 flex items-center gap-1">
                                        <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="mt-4 bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] mb-1 flex items-center gap-1">
                                <i data-lucide="key" style="width:14px;height:14px;color:#D97706;"></i>
                                Password <span class="text-[10px] text-[#666666] font-normal">(leave blank to keep current)</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="password" name="password" placeholder="New password"
                                       class="bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                <input type="password" name="password_confirmation" placeholder="Confirm new password"
                                       class="bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                            </div>
                            @error('password')
                                <p class="text-[10px] text-[#DC2626] mt-1 flex items-center gap-1">
                                    <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Role & Status --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                                <i data-lucide="shield" style="width:16px;height:16px;color:#2563EB;"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Role & Status</h3>
                                <p class="text-[10px] text-[#666666]">Set user permissions and account state</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Role --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] mb-1 flex items-center gap-1">
                                    <i data-lucide="badge" style="width:14px;height:14px;color:#0A574F;"></i>
                                    Role
                                </label>
                                <select name="role" required
                                        class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="lecturer" {{ old('role', $user->role) === 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                                    <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Student</option>
                                </select>
                                @error('role')
                                    <p class="text-[10px] text-[#DC2626] mt-1 flex items-center gap-1">
                                        <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="bg-[#F9F9F9] rounded-lg p-4 border border-[#E5E5E5] hover:border-[#0A574F] transition">
                                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000] mb-1 flex items-center gap-1">
                                    <i data-lucide="activity" style="width:14px;height:14px;color:#2563EB;"></i>
                                    Status
                                </label>
                                <select name="status" required
                                        class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#0A574F] focus:ring-2 focus:ring-[#0A574F]/20 outline-none transition">
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="warned_once" {{ old('status', $user->status) === 'warned_once' ? 'selected' : '' }}>Warned Once</option>
                                    <option value="warned_twice" {{ old('status', $user->status) === 'warned_twice' ? 'selected' : '' }}>Warned Twice</option>
                                    <option value="blacklisted" {{ old('status', $user->status) === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                                </select>
                                @error('status')
                                    <p class="text-[10px] text-[#DC2626] mt-1 flex items-center gap-1">
                                        <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Blacklist Expiry (only if blacklisted) --}}
                        <div class="mt-4 bg-[#FEF2F2] rounded-lg p-4 border-2 border-[#DC2626] transition" id="blacklist-expiry-container" style="{{ old('status', $user->status) === 'blacklisted' ? 'display:block;' : 'display:none;' }}">
                            <div class="flex items-center gap-2 mb-2">
                                <i data-lucide="clock" style="width:16px;height:16px;color:#DC2626;"></i>
                                <span class="text-xs font-bold uppercase tracking-wider text-[#DC2626]">Blacklist Expiry</span>
                            </div>
                            <p class="text-[10px] text-[#666666] mb-2">Set a date when the blacklist will be lifted. Leave blank for permanent ban.</p>
                            <input type="datetime-local" name="blacklist_expires_at"
                                   value="{{ old('blacklist_expires_at', $user->blacklist_expires_at ? $user->blacklist_expires_at->format('Y-m-d\TH:i') : '') }}"
                                   class="w-full bg-white border border-[#E5E5E5] rounded-lg px-3 py-2 text-sm focus:border-[#DC2626] focus:ring-2 focus:ring-[#DC2626]/20 outline-none transition">
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-[#E5E5E5]">
                        <div class="flex items-center gap-2 text-xs text-[#666666]">
                            <i data-lucide="info" style="width:14px;height:14px;color:#0A574F;"></i>
                            <span>Changes take effect immediately</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.users') }}"
                               class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] px-4 py-2 rounded-lg hover:bg-[#F9F9F9] transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="flex items-center gap-2 bg-[#0A574F] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                                <i data-lucide="save" style="width:16px;height:16px;"></i>
                                Update User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();

            // ─── SHOW/HIDE BLACKLIST EXPIRY ──────────────────────────
            const statusSelect = document.querySelector('select[name="status"]');
            const expiryContainer = document.getElementById('blacklist-expiry-container');

            statusSelect.addEventListener('change', function() {
                if (this.value === 'blacklisted') {
                    expiryContainer.style.display = 'block';
                } else {
                    expiryContainer.style.display = 'none';
                }
            });
        });
    </script>
@endpush