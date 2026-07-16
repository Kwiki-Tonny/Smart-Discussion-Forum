@extends('layouts.workspace')

@section('title', 'Edit User - ' . $user->name)

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.users') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">Edit User</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-[#000000] text-white flex items-center justify-center text-sm font-bold uppercase">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-sm font-bold text-[#000000]">{{ $user->name }}</h3>
                <p class="text-xs text-[#666666]">{{ $user->email }}</p>
            </div>
        </div>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <div class="bg-white border border-[#E5E5E5] p-3">
            <p class="text-xs font-bold text-[#666666] uppercase tracking-wider">User Information</p>
            <dl class="mt-2 space-y-1 text-sm">
                <div class="flex justify-between border-b border-[#E5E5E5] py-1">
                    <dt class="text-[#666666]">Role</dt>
                    <dd class="font-bold text-[#000000]">{{ ucfirst($user->role) }}</dd>
                </div>
                <div class="flex justify-between border-b border-[#E5E5E5] py-1">
                    <dt class="text-[#666666]">Status</dt>
                    <dd class="font-bold text-[#000000]">{{ ucfirst($user->status) }}</dd>
                </div>
                <div class="flex justify-between py-1">
                    <dt class="text-[#666666]">Joined</dt>
                    <dd class="font-bold text-[#000000]">{{ $user->created_at->format('M d, Y') }}</dd>
                </div>
            </dl>
        </div>
        <div class="bg-white border border-[#E5E5E5] p-3">
            <p class="text-xs font-bold text-[#666666] uppercase tracking-wider">Groups ({{ $user->groups->count() }})</p>
            @if($user->groups->count() > 0)
                <ul class="mt-1 space-y-1">
                    @foreach($user->groups as $group)
                        <li class="text-sm text-[#000000]">• {{ $group->name }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-[#666666]">Not a member of any group.</p>
            @endif
        </div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Edit User</h1>
            <p class="text-sm text-[#666666] mt-1">Update user details and permissions</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            <div class="max-w-2xl bg-white border border-[#E5E5E5] p-6">
                <form method="POST" action="{{ route('admin.user.update', $user->id) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('name') border-[#DC2626] @enderror">
                        @error('name')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('email') border-[#DC2626] @enderror">
                        @error('email')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Role</label>
                            <select name="role" required
                                    class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('role') border-[#DC2626] @enderror">
                                <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="lecturer" {{ old('role', $user->role) == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Status</label>
                            <select name="status" required
                                    class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('status') border-[#DC2626] @enderror">
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="warned_once" {{ old('status', $user->status) == 'warned_once' ? 'selected' : '' }}>Warned Once</option>
                                <option value="warned_twice" {{ old('status', $user->status) == 'warned_twice' ? 'selected' : '' }}>Warned Twice</option>
                                <option value="blacklisted" {{ old('status', $user->status) == 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                            </select>
                            @error('status')
                                <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">New Password (optional)</label>
                        <input type="password" name="password"
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('password') border-[#DC2626] @enderror"
                               placeholder="Leave blank to keep current password">
                        @error('password')
                            <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                               class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E5E5E5]">
                        <a href="{{ route('admin.users') }}"
                           class="text-xs font-bold uppercase tracking-wider text-[#666666] hover:text-[#000000] transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-[#000000] text-white px-6 py-2.5 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection