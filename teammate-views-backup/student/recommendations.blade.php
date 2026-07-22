@extends('layouts.workspace')

@section('title', 'Recommended Topics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000]">Recommendations</h2>
    </div>
    <div class="p-4 space-y-1">
        <p class="text-xs text-[#666666]">Personalized based on your activity</p>
        @if(isset($affinityScores) && count($affinityScores) > 0)
            <div class="mt-3 space-y-1">
                <p class="text-[10px] font-bold text-[#666666] uppercase tracking-wider">Your Top Categories</p>
                @foreach(array_slice($affinityScores, 0, 5) as $category => $score)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-[#666666]">{{ $category }}</span>
                        <span class="text-[10px] text-[#666666]">{{ $score }}%</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">Recommended Topics</h1>
            <p class="text-sm text-[#666666] mt-1">
                Topics we think you'll find interesting based on your activity
            </p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
            @if($recommendations->isEmpty())
                <div class="bg-white border border-[#E5E5E5] p-12 text-center">
                    <p class="text-sm text-[#666666]">No recommendations yet.</p>
                    <p class="text-xs text-[#666666] mt-2">Interact with more topics to get personalized suggestions.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($recommendations as $topic)
                        <a href="{{ route('topics.show', [$topic->group_id, $topic->id]) }}" 
                           class="bg-white border border-[#E5E5E5] p-4 hover:bg-[#F5F5F5] transition-colors">
                            <h3 class="text-sm font-bold text-[#000000]">{{ $topic->title }}</h3>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-xs text-[#666666]">{{ $topic->group->name }}</span>
                                @if($topic->ml_category)
                                    <span class="text-[8px] font-bold uppercase tracking-wider border border-[#000000] px-1.5 py-0.5">
                                        {{ $topic->ml_category }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-3 mt-2 text-[10px] text-[#666666]">
                                <span>by {{ $topic->creator->name ?? 'Unknown' }}</span>
                                <span>•</span>
                                <span>{{ $topic->created_at->diffForHumans() }}</span>
                                <span>•</span>
                                <span>{{ $topic->posts_count ?? 0 }} replies</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection