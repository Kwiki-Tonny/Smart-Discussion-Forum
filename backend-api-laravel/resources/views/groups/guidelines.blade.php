@extends('layouts.workspace')

@section('title', 'Group Guidelines')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">{{ $group->name }}</h2>
    </div>
    <div class="p-2 space-y-1">
        <div class="p-2 text-xs font-bold bg-[#F5F5F5] border border-black">• Group Guidelines</div>
        <div class="p-2 text-xs text-[#666666]">• {{ $group->topics_count ?? 0 }} topics</div>
        <div class="p-2 text-xs text-[#666666]">• {{ $group->users_count ?? 0 }} members</div>
    </div>
@endsection

@section('content')
    <div class="flex items-center justify-center min-h-full p-6">
        <div class="w-full max-w-2xl bg-white border border-[#E5E5E5] p-0 shadow-sm">

            {{-- Teal Green Header --}}
            <div class="bg-[#0A574F] px-8 pt-6 pb-4">
                <div class="flex items-center gap-2">
                    <i data-lucide="shield" style="width:14px;height:14px;color:#ffffff;"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-white/80">Security Entry Protocol</span>
                </div>
                <h1 class="text-xl font-bold uppercase tracking-tight mt-1 text-white">{{ $group->name }} Guidelines</h1>
                <p class="text-sm text-white/80 mt-1">{{ $group->description ?? 'Please review and accept the group guidelines to continue.' }}</p>
            </div>

            {{-- Body --}}
            <div class="p-8">

                {{-- Rules Content --}}
                <div class="text-sm text-[#000000] space-y-4 leading-relaxed">
                    <p>Welcome to the {{ $group->name }} workspace. To preserve operational integrity and stability, all members must actively acknowledge the following baseline protocols:</p>

                    <ol class="list-decimal pl-5 space-y-3 font-medium">
                        <li>
                            <span class="font-bold">Respectful Communication:</span> Maintain factual, constructive contributions. Disrespectful or inappropriate content will trigger governance flags and potential account restrictions.
                        </li>
                        <li>
                            <span class="font-bold">Academic Integrity:</span> Do not share exam materials, unauthorized solutions, or proprietary course content outside the designated channels.
                        </li>
                        <li>
                            <span class="font-bold">Active Participation:</span> Regular engagement is expected. Inactive members will receive warnings and may be automatically blacklisted after extended periods of inactivity.
                        </li>
                        <li>
                            <span class="font-bold">Privacy & Confidentiality:</span> Do not post unencrypted credentials, private API keys, or sensitive personal information in public threads.
                        </li>
                    </ol>
                </div>

                {{-- Acceptance --}}
                <div class="pt-6 mt-6 border-t border-[#E5E5E5] space-y-4">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" id="agreeCheckbox"
                               class="mt-1 accent-black h-4 w-4 border border-[#E5E5E5]">
                        <span class="text-xs text-[#666666] leading-normal">
                            I certify that I have thoroughly reviewed the operational parameters listed above and pledge to maintain alignment with these community structures.
                        </span>
                    </label>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                        <button type="button" id="declineBtn"
                                class="flex items-center justify-center gap-2 bg-white border-2 border-[#DC2626] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#DC2626] transition-colors hover:bg-[#DC2626] hover:text-white">
                            <i data-lucide="x" style="width:14px;height:14px;"></i>
                            Decline
                        </button>
                        <button type="button" id="agreeBtn" disabled
                                class="flex items-center justify-center gap-2 bg-[#E5E5E5] border-2 border-[#E5E5E5] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#999999] transition-colors cursor-not-allowed">
                            <i data-lucide="check" style="width:14px;height:14px;"></i>
                            Accept and Enter Group
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('agreeCheckbox');
        const agreeBtn = document.getElementById('agreeBtn');
        const declineBtn = document.getElementById('declineBtn');

        const groupId = {{ $group->id }};
        console.log('[Guidelines] Group ID:', groupId);

        // Enable/disable agree button based on checkbox
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                agreeBtn.disabled = false;
                agreeBtn.className = 'flex items-center justify-center gap-2 bg-[#000000] border-2 border-[#000000] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-white transition-colors cursor-pointer hover:bg-[#0A574F] hover:border-[#0A574F]';
            } else {
                agreeBtn.disabled = true;
                agreeBtn.className = 'flex items-center justify-center gap-2 bg-[#E5E5E5] border-2 border-[#E5E5E5] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#999999] transition-colors cursor-not-allowed';
            }
        });

        // Handle Agree
        agreeBtn.addEventListener('click', function() {
            if (this.disabled) return;

            console.log('[Guidelines] Agree button clicked');

            const originalText = this.innerHTML;
            this.innerHTML = '<i data-lucide="loader-circle" style="width:14px;height:14px;animation:spin 1s linear infinite;"></i> Processing...';
            this.disabled = true;
            lucide.createIcons();

            const url = '{{ route("groups.agree", $group->id) }}';
            const token = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!token) {
                alert('CSRF token missing. Please refresh the page.');
                this.innerHTML = originalText;
                this.disabled = false;
                lucide.createIcons();
                return;
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '{{ route("groups.topics", $group->id) }}';
                } else {
                    alert('Something went wrong: ' + (data.message || 'Unknown error'));
                    this.innerHTML = originalText;
                    this.disabled = false;
                    lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('[Guidelines] Error:', error);
                alert('Network error: ' + error.message + '\nPlease try again.');
                this.innerHTML = originalText;
                this.disabled = false;
                lucide.createIcons();
            });
        });

        // Handle Decline
        declineBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to decline access to this group? You will be redirected to the dashboard.')) {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;

                fetch('{{ route("groups.decline", $group->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({})
                })
                .then(() => {
                    window.location.href = '{{ route("dashboard") }}';
                })
                .catch(() => {
                    window.location.href = '{{ route("dashboard") }}';
                });
            }
        });
    });
</script>
<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush