@extends('layouts.workspace')

@section('title', 'Group Guidelines')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
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
        <div class="w-full max-w-2xl bg-white border border-[#E5E5E5] p-8 shadow-sm">
            
            {{-- Header --}}
            <div class="border-b border-[#000000] pb-4">
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#666666] block mb-1">Security Entry Protocol</span>
                <h1 class="text-xl font-bold uppercase tracking-tight">{{ $group->name }} Guidelines</h1>
                <p class="text-sm text-[#666666] mt-1">{{ $group->description ?? 'Please review and accept the group guidelines to continue.' }}</p>
            </div>

            {{-- Rules Content --}}
            <div class="text-sm text-[#000000] space-y-4 leading-relaxed mt-6">
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
                            class="bg-white border-2 border-[#DC2626] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#DC2626] hover:bg-[#FEF2F2] transition-colors">
                        Decline
                    </button>
                    <button type="button" id="agreeBtn" disabled
                            class="bg-[#E5E5E5] border-2 border-[#E5E5E5] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#999999] transition-colors cursor-not-allowed">
                        Accept and Enter Group
                    </button>
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

        // Enable/disable agree button based on checkbox
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                agreeBtn.disabled = false;
                agreeBtn.className = 'bg-[#000000] border-2 border-[#000000] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-white hover:bg-[#333333] transition-colors cursor-pointer';
            } else {
                agreeBtn.disabled = true;
                agreeBtn.className = 'bg-[#E5E5E5] border-2 border-[#E5E5E5] px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-[#999999] transition-colors cursor-not-allowed';
            }
        });

        // Handle Agree
        agreeBtn.addEventListener('click', function() {
            if (this.disabled) return;

            // Show loading state
            const originalText = this.textContent;
            this.textContent = 'Processing...';
            this.disabled = true;

            fetch('{{ route("groups.agree", $group->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '{{ route("groups.topics", $group->id) }}';
                } else {
                    alert('Something went wrong. Please try again.');
                    this.textContent = originalText;
                    this.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please try again.');
                this.textContent = originalText;
                this.disabled = false;
            });
        });

        // Handle Decline
        declineBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to decline access to this group? You will be redirected to the dashboard.')) {
                fetch('{{ route("groups.decline", $group->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
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
@endpush