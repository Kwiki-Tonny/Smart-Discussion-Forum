@extends('layouts.workspace')

@section('title', $group->name . ' - Topics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('groups.index') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">{{ $group->name }}</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Group Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#0A574F]">{{ $topics->count() }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#2563EB]">{{ $group->users_count ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Members</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-sm transition">
                <p class="text-xl font-bold text-[#D97706]">{{ $topics->sum('posts_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Replies</p>
            </div>
        </div>
    </div>

    {{-- Group Info --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <div class="flex items-center gap-2 text-xs text-[#666666]">
            <i data-lucide="calendar" style="width:12px;height:12px;color:#0A574F;"></i>
            <span>Created: {{ $group->created_at->format('M d, Y') }}</span>
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
                        <i data-lucide="folder" style="width:28px;height:28px;color:#0A574F;"></i>
                        {{ $group->name }}
                    </h1>
                    @if($group->description)
                        <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                            <i data-lucide="info" style="width:14px;height:14px;color:#0A574F;"></i>
                            {{ $group->description }}
                        </p>
                    @endif
                </div>
                <a href="{{ route('topics.create') }}"
                   class="flex items-center gap-2 bg-[#0A574F] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#08443e] transition hover:shadow-sm">
                    <i data-lucide="plus-circle" style="width:16px;height:16px;"></i>
                    New Topic
                </a>
            </div>
        </div>

        {{-- Topics List --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            @if($topics->isEmpty())
                <div class="bg-white rounded-lg border border-dashed border-[#E5E5E5] p-12 text-center">
                    <i data-lucide="message-circle" style="width:48px;height:48px;color:#94A3B8;margin:0 auto 0.75rem;display:block;"></i>
                    <p class="text-sm font-medium text-[#000000]">No topics yet</p>
                    <p class="text-xs text-[#666666] mt-1">Be the first to start a discussion in this group.</p>
                    <a href="{{ route('topics.create') }}"
                       class="inline-block mt-4 text-sm font-bold text-[#0A574F] border border-[#0A574F] px-6 py-2 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                        <i data-lucide="plus-circle" style="width:14px;height:14px;display:inline;"></i>
                        Create First Topic
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($topics as $topic)
                        <a href="{{ route('topics.show', [$group->id, $topic->id]) }}"
                           class="block bg-white rounded-lg border border-[#E5E5E5] shadow-sm hover:shadow-md hover:border-[#0A574F] transition p-5">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="message-circle" style="width:16px;height:16px;color:#0A574F;"></i>
                                        <h3 class="text-base font-bold text-[#000000] truncate">{{ $topic->title }}</h3>
                                    </div>
                                    <div class="flex items-center flex-wrap gap-2 mt-1 text-xs text-[#666666]">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="user" style="width:12px;height:12px;"></i>
                                            {{ $topic->creator->name ?? 'Unknown' }}
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="clock" style="width:12px;height:12px;"></i>
                                            {{ $topic->created_at->diffForHumans() }}
                                        </span>
                                        <span>•</span>
                                        <span class="flex items-center gap-1 text-[#2563EB]">
                                            <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                            {{ $topic->posts_count ?? 0 }} replies
                                        </span>
                                    </div>
                                    @if($topic->ml_category)
                                        <span class="inline-block mt-2 text-[8px] font-bold uppercase tracking-wider text-[#D97706] border border-[#D97706] px-2 py-0.5 rounded-full">
                                            <i data-lucide="tag" style="width:8px;height:8px;display:inline;"></i>
                                            {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </div>
                                @if($topic->posts_count > 0 && $topic->posts->last())
                                    <div class="text-right text-[10px] text-[#666666] flex-shrink-0 ml-4 hidden sm:block">
                                        <span class="flex items-center gap-1 justify-end">
                                            <i data-lucide="clock" style="width:10px;height:10px;"></i>
                                            Latest reply
                                        </span>
                                        <span class="block font-medium text-[#0A574F]">{{ $topic->posts->last()->created_at->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer Status --}}
        <div class="border-t border-[#E5E5E5] bg-white px-8 py-3 flex items-center justify-between text-[11px] text-[#666666]">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1">
                    <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                    System Status: <span class="text-[#000000] font-medium">Online</span>
                </span>
                <span class="flex items-center gap-1">
                    <i data-lucide="database" style="width:12px;height:12px;color:#2563EB;"></i>
                    Database: <span class="text-[#000000] font-medium">Connected</span>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-[#0A574F] transition">Privacy Policy</a>
                <span class="text-[#D1D5DB]">·</span>
                <a href="#" class="hover:text-[#0A574F] transition">Terms of Service</a>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();
    </script>
@endpush