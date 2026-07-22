@extends('layouts.workspace')

@section('title', $group->name . ' – Statistics')

@section('context_panel')
    <div class="p-4 border-b border-[#E5E5E5] flex items-center bg-white sticky top-0">
        <a href="{{ route('admin.groups') }}" class="mr-3 font-bold text-sm hover:opacity-60 transition-opacity">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        </a>
        <h2 class="text-sm font-bold uppercase tracking-wider text-[#000000] truncate">{{ $group->name }}</h2>
        <span class="ml-2 text-[10px] text-[#94A3B8] font-medium">v2.0</span>
    </div>

    {{-- Quick Stats --}}
    <div class="p-4 bg-white border-b border-[#E5E5E5]">
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#0A574F]">{{ $group->topics_count }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Topics</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#2563EB]">{{ $group->users_count }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Members</p>
            </div>
            <div class="text-center p-2.5 rounded-lg bg-white border border-[#E5E5E5] hover:shadow-md hover:border-[#0A574F] transition-all">
                <p class="text-xl font-bold text-[#D97706]">{{ $topTopics->sum('posts_count') }}</p>
                <p class="text-[8px] text-[#666666] uppercase tracking-wider font-medium">Replies</p>
            </div>
        </div>
    </div>

    {{-- Group Info --}}
    <div class="p-3 bg-[#F9F9F9] border-b border-[#E5E5E5]">
        <div class="flex items-center gap-4 text-xs text-[#666666]">
            <span class="flex items-center gap-1">
                <i data-lucide="user" style="width:12px;height:12px;color:#0A574F;"></i>
                Created by: <span class="font-medium text-[#000000]">{{ $lecturer->name ?? 'Unknown' }}</span>
            </span>
            <span>•</span>
            <span class="flex items-center gap-1">
                <i data-lucide="calendar" style="width:12px;height:12px;color:#0A574F;"></i>
                {{ $group->created_at->format('M d, Y') }}
            </span>
        </div>
    </div>

    {{-- Top Topics Sidebar --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-3 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-wider text-[#666666] px-2 py-1 flex items-center gap-1">
            <i data-lucide="message-circle" style="width:12px;height:12px;"></i>
            Top Topics
        </p>
        @forelse($topTopics as $topic)
            <div class="px-3 py-2.5 bg-white border border-[#E5E5E5] rounded-lg hover:border-[#0A574F] transition">
                <p class="text-sm font-bold text-[#000000] truncate flex items-center gap-2">
                    <i data-lucide="message-square" style="width:12px;height:12px;color:#0A574F;"></i>
                    {{ $topic->title }}
                </p>
                <span class="text-[10px] text-[#2563EB] flex items-center gap-1 mt-0.5">
                    <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                    {{ $topic->posts_count }} replies
                </span>
            </div>
        @empty
            <div class="p-4 text-center border border-dashed border-[#E5E5E5] rounded-lg bg-white">
                <i data-lucide="inbox" style="width:24px;height:24px;color:#94A3B8;margin:0 auto 0.25rem;display:block;"></i>
                <p class="text-sm text-[#666666]">No topics yet.</p>
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
                        <i data-lucide="bar-chart-2" style="width:28px;height:28px;color:#0A574F;"></i>
                        {{ $group->name }} – Analytics
                    </h1>
                    <p class="text-sm text-[#666666] mt-1 flex items-center gap-1">
                        <i data-lucide="activity" style="width:14px;height:14px;color:#0A574F;"></i>
                        Detailed statistics for this group
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-[#16A34A] flex items-center gap-1 border border-[#16A34A] px-3 py-1 rounded-full bg-[#F0FDF4]">
                        <i data-lucide="circle" style="width:8px;height:8px;fill:#16A34A;color:#16A34A;"></i>
                        Active
                    </span>
                    <button class="bg-[#F9F9F9] border border-[#E5E5E5] px-3 py-1.5 text-xs rounded-lg hover:border-[#0A574F] hover:bg-white transition flex items-center gap-1">
                        <i data-lucide="refresh-cw" style="width:12px;height:12px;"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#0A574F]">{{ $group->topics_count }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Topics</p>
                </div>
                <div class="w-10 h-10 bg-[#ECFDF5] rounded-lg flex items-center justify-center">
                    <i data-lucide="message-circle" style="width:20px;height:20px;color:#0A574F;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#2563EB]">{{ $group->users_count }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Members</p>
                </div>
                <div class="w-10 h-10 bg-[#E0F2FE] rounded-lg flex items-center justify-center">
                    <i data-lucide="users" style="width:20px;height:20px;color:#2563EB;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#D97706]">{{ $topTopics->sum('posts_count') }}</p>
                    <p class="text-xs text-[#666666] font-medium">Total Replies</p>
                </div>
                <div class="w-10 h-10 bg-[#FEF3C7] rounded-lg flex items-center justify-center">
                    <i data-lucide="message-square" style="width:20px;height:20px;color:#D97706;"></i>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-[#E5E5E5] p-4 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-[#16A34A]">{{ $topStudents->count() }}</p>
                    <p class="text-xs text-[#666666] font-medium">Active Students</p>
                </div>
                <div class="w-10 h-10 bg-[#F0FDF4] rounded-lg flex items-center justify-center">
                    <i data-lucide="award" style="width:20px;height:20px;color:#16A34A;"></i>
                </div>
            </div>
        </div>

        {{-- Charts & Top Students --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-6 flex-1 overflow-y-auto">

            {{-- Category Distribution Pie Chart --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="pie-chart" style="width:18px;height:18px;color:#0A574F;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Category Distribution</h3>
                    </div>
                    <span class="text-[8px] text-[#0A574F] bg-[#ECFDF5] px-2 py-0.5 rounded-full">ML</span>
                </div>
                @if(count($categories) > 0)
                    <div style="height: 200px; width: 100%;">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                        <p class="text-sm text-[#666666]">No categorized topics yet.</p>
                        <p class="text-xs text-[#94A3B8]">Topics will appear here once ML categorization is applied.</p>
                    </div>
                @endif
            </div>

            {{-- Top Students --}}
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="award" style="width:18px;height:18px;color:#D97706;"></i>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Top Students</h3>
                    </div>
                    <span class="text-[8px] text-[#D97706] bg-[#FEF3C7] px-2 py-0.5 rounded-full">{{ $topStudents->count() }} active</span>
                </div>
                @if($topStudents->isNotEmpty())
                    <div class="divide-y divide-[#F5F5F5] max-h-[200px] overflow-y-auto">
                        @foreach($topStudents->take(5) as $student)
                            <div class="py-3 flex items-center justify-between hover:bg-[#F9F9F9] transition px-2 -mx-2 rounded-lg">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-7 h-7 bg-[#ECFDF5] rounded-full flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="user" style="width:12px;height:12px;color:#0A574F;"></i>
                                    </div>
                                    <span class="text-sm font-medium text-[#000000] truncate">{{ $student->name }}</span>
                                </div>
                                <span class="text-[10px] text-[#16A34A] font-bold flex-shrink-0 ml-2">{{ $student->posts_count }} posts</span>
                            </div>
                        @endforeach
                    </div>
                    @if($topStudents->count() > 5)
                        <p class="text-[9px] text-[#666666] text-center mt-2 flex items-center gap-1 justify-center">
                            <i data-lucide="plus" style="width:10px;height:10px;"></i>
                            {{ $topStudents->count() - 5 }} more students
                        </p>
                    @endif
                @else
                    <div class="text-center py-8">
                        <i data-lucide="users" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                        <p class="text-sm text-[#666666]">No student activity yet.</p>
                        <p class="text-xs text-[#94A3B8]">Students will appear here as they participate.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Daily Activity Chart (Bar) --}}
        <div class="px-6 pb-6">
            <div class="bg-white rounded-lg border border-[#E5E5E5] shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <i data-lucide="bar-chart-3" style="width:18px;height:18px;color:#0A574F;"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#000000]">Daily Activity (Last 30 Days)</h3>
                    <span class="text-[8px] text-[#666666] bg-[#F9F9F9] px-2 py-0.5 rounded-full ml-auto">{{ count($dailyActivity) }} days</span>
                </div>
                @if(count($dailyActivity) > 0)
                    <div style="height: 180px; width: 100%;">
                        <canvas id="dailyActivityChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i data-lucide="inbox" style="width:32px;height:32px;color:#94A3B8;margin:0 auto 0.5rem;display:block;"></i>
                        <p class="text-sm text-[#666666]">No activity in the last 30 days.</p>
                    </div>
                @endif
            </div>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

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

            const colors = counts.map(c => {
                if (c >= 5) return '#0A574F';
                if (c >= 3) return '#2563EB';
                if (c >= 1) return '#D97706';
                return '#E5E5E5';
            });

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
                        backgroundColor: colors,
                        borderColor: colors.map(c => c),
                        borderWidth: 1,
                        borderRadius: 2
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
                                    return context.parsed.y + ' posts';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 8 }, maxTicksLimit: 15 }
                        },
                        y: {
                            grid: { display: true, color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { size: 8 }, stepSize: 1 }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush