@extends('layouts.workspace')

@section('title', $group->name . ' - Analytics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('lecturer.dashboard') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity flex items-center gap-1">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $group->name }}</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $group->topics_count ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $group->users_count ?? 0 }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Students</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ count($categories) }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Categories</p>
            </div>
        </div>
    </div>

    {{-- Top Topics List --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="list" style="width:12px;height:12px;"></i>
            Top Topics
        </p>
        @forelse($topTopics as $topic)
            <div class="bg-white border border-[#E5E5E5] rounded-lg p-3 hover:border-[#0A574F] hover:shadow-sm transition">
                <p class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                    <i data-lucide="message-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                    {{ $topic->title }}
                </p>
                <span class="text-[10px] text-[#666666] flex items-center gap-1">
                    <i data-lucide="message-square" style="width:10px;height:10px;"></i>
                    {{ $topic->posts_count }} replies
                </span>
            </div>
        @empty
            <div class="p-6 text-center bg-white border border-dashed border-[#E5E5E5] rounded-lg">
                <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No topics yet.</p>
                <p class="text-xs text-[#94A3B8]">Start creating topics to see them here.</p>
            </div>
        @endforelse
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
                        {{ $group->name }} – Analytics
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width:14px;height:14px;color:#0A574F;"></i>
                        Detailed statistics for this group
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        {{ $group->users_count ?? 0 }} students
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-6">

            {{-- Two‑column: Category Distribution + Top Students --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Category Distribution --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="pie-chart" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Category Distribution</h3>
                    </div>
                    @if(count($categories) > 0)
                        @php
                            $maxCount = max($categories) ?: 1;
                        @endphp
                        <div class="space-y-3">
                            @foreach($categories as $category => $count)
                                <div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#000000] font-medium flex items-center gap-1">
                                            <i data-lucide="tag" style="width:12px;height:12px;color:#0A574F;"></i>
                                            {{ $category }}
                                        </span>
                                        <span class="text-[#666666]">{{ $count }}</span>
                                    </div>
                                    <div class="w-full h-2 bg-[#E5E5E5] rounded-full mt-0.5 overflow-hidden">
                                        <div class="h-full rounded-full bg-[#0A574F]" style="width: {{ ($count / $maxCount) * 100 }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No categorized topics yet.</p>
                            <p class="text-xs text-[#94A3B8]">Topics will appear here once they have an ML category.</p>
                        </div>
                    @endif
                </div>

                {{-- Top Students --}}
                <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-3">
                        <i data-lucide="award" style="width:18px;height:18px;color:#D97706;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Top Active Students</h3>
                    </div>
                    @if($studentParticipation->isNotEmpty())
                        <div class="space-y-3 max-h-60 overflow-y-auto custom-scrollbar pr-1">
                            @foreach($studentParticipation->take(5) as $student)
                                <div class="flex items-center justify-between border-b border-[#E5E5E5] pb-2 last:border-0">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-8 h-8 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="user" style="width:14px;height:14px;color:#0A574F;"></i>
                                        </div>
                                        <span class="text-sm font-medium text-[#000000] truncate">{{ $student->name }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-[#2563EB] flex items-center gap-1">
                                        <i data-lucide="message-square" style="width:12px;height:12px;"></i>
                                        {{ $student->posts_count }} posts
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                            <p class="text-sm text-[#666666]">No student activity yet.</p>
                            <p class="text-xs text-[#94A3B8]">Students will appear here once they participate.</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Daily Activity (Last 30 Days) – Chart.js Bar Chart --}}
            <div class="bg-white rounded-xl border border-[#E5E5E5] shadow-sm p-4 hover:shadow-md transition">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="activity" style="width:18px;height:18px;color:#0A574F;"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#666666]">Daily Activity (Last 30 Days)</h3>
                </div>
                <div style="height: 220px;">
                    <canvas id="dailyActivityChart"></canvas>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // ─── DAILY ACTIVITY CHART ──────────────────────────────────
        const dailyData = @json($dailyActivity); // array of [date => count]
        const labels = Object.keys(dailyData);
        const counts = Object.values(dailyData);

        if (labels.length > 0) {
            // Sort by date (oldest to newest)
            const sorted = labels.map((date, i) => ({ date, count: counts[i] }))
                .sort((a, b) => new Date(a.date) - new Date(b.date));
            const sortedLabels = sorted.map(item => item.date);
            const sortedCounts = sorted.map(item => item.count);

            const maxCount = Math.max(...sortedCounts, 1);
            const backgroundColors = sortedCounts.map(count => {
                const opacity = 0.2 + 0.7 * (count / maxCount);
                return `rgba(10, 87, 79, ${opacity})`;
            });

            new Chart(document.getElementById('dailyActivityChart'), {
                type: 'bar',
                data: {
                    labels: sortedLabels.map(date => {
                        const d = new Date(date);
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    }),
                    datasets: [{
                        label: 'Posts',
                        data: sortedCounts,
                        backgroundColor: backgroundColors,
                        borderColor: '#0A574F',
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' post' + (context.parsed.y > 1 ? 's' : '');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxTicksLimit: 15,
                                font: { size: 9 }
                            }
                        }
                    }
                }
            });
        } else {
            // Show a message if no data
            const canvas = document.getElementById('dailyActivityChart');
            const ctx = canvas.getContext('2d');
            ctx.font = '14px sans-serif';
            ctx.fillStyle = '#999';
            ctx.textAlign = 'center';
            ctx.fillText('No activity in the last 30 days', canvas.width/2, canvas.height/2);
        }
    });
</script>
@endpush