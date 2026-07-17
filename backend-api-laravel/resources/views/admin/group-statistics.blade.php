@extends('layouts.workspace')

@section('title', $group->name . ' – Statistics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.groups') }}" class="mr-3 font-bold text-sm hover:opacity-60">←</a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $group->name }}</h2>
    </div>
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-2 gap-2 text-center">
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $group->topics_count }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Topics</p>
            </div>
            <div>
                <p class="text-lg font-bold text-[#000000]">{{ $group->users_count }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider">Members</p>
            </div>
        </div>
    </div>
    <div class="p-3 bg-[#FAFAFA] border-b border-[#E5E5E5]">
        <p class="text-xs text-[#666666]">Created by: {{ $lecturer->name ?? 'Unknown' }}</p>
        <p class="text-xs text-[#666666]">Created: {{ $group->created_at->format('M d, Y') }}</p>
    </div>
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1">Top Topics</p>
        @forelse($topTopics as $topic)
            <div class="px-3 py-2 bg-white border border-[#E5E5E5]">
                <p class="text-sm font-bold text-[#000000] truncate">{{ $topic->title }}</p>
                <span class="text-[10px] text-[#666666]">{{ $topic->posts_count }} replies</span>
            </div>
        @empty
            <p class="text-sm text-[#666666] px-3 py-2">No topics yet.</p>
        @endforelse
    </div>
@endsection

@section('content')
    <div class="flex flex-col h-full">
        <div class="bg-white border-b border-[#E5E5E5] px-8 py-6">
            <h1 class="text-xl font-bold text-[#000000]">{{ $group->name }} – Analytics</h1>
            <p class="text-sm text-[#666666] mt-1">Detailed statistics for this group</p>
        </div>

        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Category Distribution Pie Chart --}}
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Category Distribution</h3>
                    @if(count($categories) > 0)
                        <div style="height: 180px; width: 100%;">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-[#666666]">No categorized topics yet.</p>
                    @endif
                </div>

                {{-- Top Students --}}
                <div class="bg-white border border-[#E5E5E5] p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Top Students</h3>
                    @if($topStudents->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($topStudents->take(5) as $student)
                                <div class="flex justify-between items-center border-b border-[#E5E5E5] pb-1">
                                    <span class="text-sm text-[#000000] truncate">{{ $student->name }}</span>
                                    <span class="text-[10px] text-[#666666] flex-shrink-0 ml-2">{{ $student->posts_count }} posts</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-[#666666]">No student activity yet.</p>
                    @endif
                </div>
            </div>

            {{-- Daily Activity Chart (Bar) --}}
            <div class="bg-white border border-[#E5E5E5] p-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666] mb-3">Daily Activity (Last 30 Days)</h3>
                @if(count($dailyActivity) > 0)
                    <div style="height: 180px; width: 100%;">
                        <canvas id="dailyActivityChart"></canvas>
                    </div>
                @else
                    <p class="text-sm text-[#666666]">No activity in the last 30 days.</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ─── Pie Chart: Category Distribution ──────────────────
        const pieCtx = document.getElementById('categoryPieChart');
        if (pieCtx) {
            const categories = @json($categories);
            const labels = categories.map(c => c.ml_category);
            const counts = categories.map(c => c.count);
            const colors = [
                '#16A34A', '#2563EB', '#D97706', '#DC2626', '#0D9488',
                '#7C3AED', '#EC4899', '#F59E0B', '#6366F1', '#14B8A6',
                '#F97316', '#8B5CF6', '#EF4444', '#10B981', '#3B82F6'
            ];

            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: colors.slice(0, labels.length),
                        borderColor: '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 8,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        // ─── Bar Chart: Daily Activity ──────────────────────────
        const barCtx = document.getElementById('dailyActivityChart');
        if (barCtx) {
            const daily = @json($dailyActivity);
            const dates = Object.keys(daily);
            const counts = Object.values(daily);

            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: dates.map(d => {
                        const parts = d.split('-');
                        return parts[2] + '/' + parts[1];
                    }),
                    datasets: [{
                        label: 'Posts',
                        data: counts,
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        borderColor: '#000000',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 8 }, maxTicksLimit: 15 }
                        },
                        y: {
                            grid: { display: true },
                            ticks: { font: { size: 8 }, stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush