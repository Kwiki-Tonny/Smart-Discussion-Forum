@extends('layouts.workspace')

@section('title', $group->name . ' - Topics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('groups.index') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">{{ $group->name }}</h2>
    </div>
    <div class="p-2 space-y-1">
        <div class="p-2 text-xs font-bold bg-[#F5F5F5] border border-black">• {{ $topics->count() }} topics</div>
        <div class="p-2 text-xs text-[#666666]">• {{ $group->users_count ?? 0 }} members</div>
        <div class="p-2 text-xs text-[#666666]">• Created: {{ $group->created_at->format('M d, Y') }}</div>
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-[#000000]">{{ $group->name }}</h1>
                    @if($group->description)
                        <p class="text-sm text-[#666666] mt-1">{{ $group->description }}</p>
                    @endif
                </div>
                <a href="{{ route('topics.create') }}" 
                   class="bg-[#000000] text-white px-4 py-2 text-xs font-bold uppercase tracking-wider hover:bg-[#333333] transition-colors">
                    + New Topic
                </a>
            </div>
        </div>

        {{-- Topics List --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            @if($topics->isEmpty())
                <div class="bg-white border border-[#E5E5E5] p-12 text-center">
                    <p class="text-sm text-[#666666]">No topics have been created in this group yet.</p>
                    <a href="{{ route('topics.create') }}" 
                       class="inline-block mt-4 text-sm font-bold text-[#000000] border border-[#000000] px-4 py-2 hover:bg-[#F5F5F5] transition-colors">
                        Be the first to create a topic
                    </a>
                </div>
            @else
                <div class="bg-white border border-[#E5E5E5] divide-y divide-[#E5E5E5]">
                    @foreach($topics as $topic)
                        <a href="{{ route('topics.show', [$group->id, $topic->id]) }}" 
                           class="block px-6 py-4 hover:bg-[#F5F5F5] transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-[#000000]">{{ $topic->title }}</h3>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="text-xs text-[#666666]">
                                            by {{ $topic->creator->name ?? 'Unknown' }}
                                        </span>
                                        <span class="text-[10px] text-[#666666]">•</span>
                                        <span class="text-[10px] text-[#666666]">
                                            {{ $topic->created_at->diffForHumans() }}
                                        </span>
                                        <span class="text-[10px] text-[#666666]">•</span>
                                        <span class="text-[10px] text-[#666666]">
                                            {{ $topic->posts_count ?? 0 }} replies
                                        </span>
                                    </div>
                                    @if($topic->ml_category)
                                        <span class="inline-block mt-2 text-[8px] font-bold uppercase tracking-wider border border-[#000000] px-1.5 py-0.5">
                                            {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </div>
                                @if($topic->posts_count > 0 && $topic->posts->last())
                                    <div class="text-right text-[10px] text-[#666666] flex-shrink-0 ml-4">
                                        <span>Latest reply</span>
                                        <span class="block">{{ $topic->posts->last()->created_at->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection