@extends('layouts.workspace')

@section('title', $group->name . ' Guidelines')

@section('context_panel')
    <div class="p-4 border-b border-slate-200 flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 text-slate-600 hover:text-[#0A66C2] transition-colors">
            <i data-lucide="arrow-left" class="size-5"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">{{ $group->name }}</h2>
    </div>

    <div class="p-4 space-y-2">
        <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
            <i data-lucide="file-text" class="size-4 text-[#0A66C2]"></i>
            <span class="text-xs font-medium text-slate-700">Group Guidelines</span>
        </div>
        <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
            <i data-lucide="message-square" class="size-4 text-[#1DA1F2]"></i>
            <span class="text-xs text-slate-600">{{ $group->topics_count ?? 0 }} topics</span>
        </div>
        <div class="flex items-center gap-3 px-3 py-2 bg-white rounded-lg border border-slate-200 shadow-sm">
            <i data-lucide="users" class="size-4 text-[#25D366]"></i>
            <span class="text-xs text-slate-600">{{ $group->users_count ?? 0 }} members</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="flex items-center justify-center min-h-full p-6 bg-gradient-to-br from-slate-50 via-white to-slate-50">
        <div class="w-full max-w-3xl bg-white rounded-3xl shadow-xl border border-slate-200/80 p-8 relative overflow-hidden">

            {{-- Subtle gradient border accent --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#0A66C2] via-[#1DA1F2] to-[#E4405F]"></div>

            {{-- Header --}}
            <div class="border-b border-slate-200 pb-4 flex items-start gap-3">
                <div class="p-2.5 rounded-xl bg-gradient-to-br from-[#833AB4] to-[#E4405F] text-white shadow-md">
                    <i data-lucide="shield-check" class="size-6"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-1">Security Entry Protocol</span>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $group->name }} Guidelines</h1>
                    <p class="text-sm text-slate-500 mt-1">Academic workspace for collaboration, discussions, and project development.</p>
                </div>
            </div>

            {{-- Rules --}}
            <div class="mt-6 space-y-4 text-sm text-slate-700 leading-relaxed">
                <p class="text-slate-600">Welcome to the {{ $group->name }} workspace. To preserve operational integrity and stability, all members must actively acknowledge the following baseline protocols:</p>

                <ol class="list-decimal pl-5 space-y-3 font-medium">
                    <li>
                        <span class="font-bold text-slate-900">Respectful Communication:</span>
                        <span class="text-slate-600">Maintain factual, constructive contributions. Disrespectful or inappropriate content will trigger governance flags and potential account restrictions.</span>
                    </li>
                    <li>
                        <span class="font-bold text-slate-900">Academic Integrity:</span>
                        <span class="text-slate-600">Do not share exam materials, unauthorized solutions, or proprietary course content outside the designated channels.</span>
                    </li>
                    <li>
                        <span class="font-bold text-slate-900">Active Participation:</span>
                        <span class="text-slate-600">Regular engagement is expected. Inactive members will receive warnings and may be automatically blacklisted after extended periods of inactivity.</span>
                    </li>
                    <li>
                        <span class="font-bold text-slate-900">Privacy & Confidentiality:</span>
                        <span class="text-slate-600">Do not post unencrypted credentials, private API keys, or sensitive personal information in public threads.</span>
                    </li>
                </ol>
            </div>

            {{-- Acceptance --}}
            <div class="pt-6 mt-6 border-t border-slate-200 space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" id="agreeCheckbox"
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#0A66C2] focus:ring-2 focus:ring-[#1DA1F2] focus:ring-offset-2">
                    <span class="text-xs text-slate-600 leading-normal">
                        I certify that I have thoroughly reviewed the operational parameters listed above and pledge to maintain alignment with these community structures.
                    </span>
                </label>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                    <button type="button" id="declineBtn"
                            class="inline-flex items-center justify-center gap-2 bg-white border-2 border-red-500 px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-red-500 rounded-xl hover:bg-red-50 hover:border-red-600 hover:text-red-600 transition-all duration-200 shadow-sm">
                        <i data-lucide="x-circle" class="size-4"></i> Decline
                    </button>
                    <button type="button" id="agreeBtn" disabled
                            class="inline-flex items-center justify-center gap-2 bg-slate-200 border-2 border-slate-200 px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-slate-400 rounded-xl cursor-not-allowed transition-all duration-200">
                        <i data-lucide="check-circle" class="size-4"></i> Accept and Enter Group
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const checkbox = document.getElementById('agreeCheckbox');
            const agreeBtn = document.getElementById('agreeBtn');
            const declineBtn = document.getElementById('declineBtn');

            function updateAgreeButton() {
                if (checkbox.checked) {
                    agreeBtn.disabled = false;
                    agreeBtn.classList.remove('bg-slate-200', 'border-slate-200', 'text-slate-400', 'cursor-not-allowed');
                    agreeBtn.classList.add('bg-[#25D366]', 'border-[#25D366]', 'text-white', 'hover:bg-[#1DA851]', 'hover:border-[#1DA851]', 'cursor-pointer', 'shadow-md');
                } else {
                    agreeBtn.disabled = true;
                    agreeBtn.classList.remove('bg-[#25D366]', 'border-[#25D366]', 'text-white', 'hover:bg-[#1DA851]', 'hover:border-[#1DA851]', 'cursor-pointer', 'shadow-md');
                    agreeBtn.classList.add('bg-slate-200', 'border-slate-200', 'text-slate-400', 'cursor-not-allowed');
                }
            }

            checkbox.addEventListener('change', updateAgreeButton);
            updateAgreeButton();

            // Agree button
            agreeBtn.addEventListener('click', function() {
                if (this.disabled) return;

                const originalText = this.innerHTML;
                this.innerHTML = '<i data-lucide="loader-circle" class="size-4 animate-spin"></i> Processing...';
                this.disabled = true;

                const url = '{{ route("groups.agree", $group->id) }}';
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                if (!token) {
                    alert('CSRF token missing. Please refresh the page.');
                    this.innerHTML = originalText;
                    this.disabled = false;
                    return;
                }

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({}),
                    redirect: 'follow'
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.status === 'success') {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = '{{ route("groups.topics", $group->id) }}';
                        }
                    } else if (data && data.message) {
                        alert(data.message);
                        this.innerHTML = originalText;
                        this.disabled = false;
                    } else {
                        console.warn('Unexpected response:', data);
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error: ' + error.message);
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            });

            // Decline button
            declineBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to decline access to this group?')) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    fetch('{{ route("groups.decline", $group->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({})
                    })
                    .then(() => { window.location.href = '{{ route("dashboard") }}'; })
                    .catch(() => { window.location.href = '{{ route("dashboard") }}'; });
                }
            });
        });
    </script>
@endpush