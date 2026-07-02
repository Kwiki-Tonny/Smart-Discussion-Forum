@extends('layouts.workspace')

@section('title', 'Community Onboarding Gate')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <button class="mr-3 font-bold text-sm hover:opacity-60">←</button>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Engineering</h2>
    </div>
    <div class="p-2 space-y-1">
        <div class="p-2 text-xs font-bold bg-[#F5F5F5] border border-black">• Deployment Discussion</div>
        <div class="p-2 text-xs text-[#666666] hover:bg-[#F5F5F5]">• Server Maintenance</div>
        <div class="p-2 text-xs text-[#666666] hover:bg-[#F5F5F5]">• DevOps Updates</div>
    </div>
@endsection

@section('content')
    <div class="p-10 max-w-3xl mx-auto my-8">
        <div class="bg-white border border-[#E5E5E5] p-8 shadow-sm space-y-6">
            
            <div class="border-b border-[#000000] pb-4">
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#666666] block mb-1">Security Entry Protocol</span>
                <h1 class="text-xl font-bold uppercase tracking-tight">Engineering Workspace Guidelines</h1>
            </div>

            <div class="text-sm text-[#000000] space-y-4 leading-relaxed">
                <p>Welcome to the core Engineering coordination workspace node. To preserve operational integrity and stability, all members must actively acknowledge the following baseline protocols:</p>
                
                <ol class="list-decimal pl-5 space-y-3 font-medium">
                    <li>
                        <span class="font-bold">Production Configuration Secrecy:</span> Do not post unencrypted raw server credentials, private API keys, or security environmental configurations directly into standard threads. Use designated parameter keys.
                    </li>
                    <li>
                        <span class="font-bold">Deployment Sync Standards:</span> All codebase deployments must sync explicitly with the active calendar. Standard window cuts close at 17:00 UTC unless explicitly authorized.
                    </li>
                    <li>
                        <span class="font-bold">Peer Review Integrity:</span> Maintain factual, construct-focused telemetry review inputs. Disrespectful communication code will trip localized governance flags.
                    </li>
                </ol>
            </div>

            <div class="pt-6 border-t border-[#E5E5E5] space-y-4">
                <label class="flex items-start space-x-3 cursor-pointer">
                    <input type="checkbox" required class="mt-1 accent-black h-4 w-4 border border-[#E5E5E5]">
                    <span class="text-xs text-[#666666] leading-normal">
                        I certified that I have thoroughly parsed the operational parameters listed above and pledge to maintain execution alignment with these community structures.
                    </span>
                </label>

                <div class="flex justify-end pt-2">
                    <button type="button" class="bg-white border-2 border-black px-6 py-2.5 text-xs font-bold uppercase tracking-widest hover:bg-[#F5F5F5] transition-colors">
                        Accept and Enter Group
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection