@extends('layouts.workspace')

@section('title', $group->name . ' Statistics')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold">{{ $group->name }}</h1>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
            <div class="bg-white p-4 border shadow-sm">
                <p class="text-2xl font-bold">{{ $group->topics_count }}</p>
                <p class="text-sm text-gray-500">Topics</p>
            </div>
            <div class="bg-white p-4 border shadow-sm">
                <p class="text-2xl font-bold">{{ $group->users_count }}</p>
                <p class="text-sm text-gray-500">Members</p>
            </div>
            <div class="bg-white p-4 border shadow-sm">
                <p class="text-2xl font-bold">{{ $dailyActivity->sum() }}</p>
                <p class="text-sm text-gray-500">Total posts (last 30 days)</p>
            </div>
            <div class="bg-white p-4 border shadow-sm">
                <p class="text-2xl font-bold">{{ $categories->count() }}</p>
                <p class="text-sm text-gray-500">Distinct categories</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Daily activity chart --}}
            <div class="bg-white p-4 border">
                <h3 class="font-bold text-sm mb-2">Daily Activity (Last 30 days)</h3>
                <canvas id="dailyChart" height="200"></canvas>
            </div>

            {{-- Category distribution --}}
            <div class="bg-white p-4 border">
                <h3 class="font-bold text-sm mb-2">Topic Categories</h3>
                <canvas id="categoryChart" height="200"></canvas>
            </div>

            {{-- Top topics --}}
            <div class="bg-white p-4 border">
                <h3 class="font-bold text-sm mb-2">Top Topics</h3>
                <ul class="divide-y">
                    @foreach($topTopics as $topic)
                        <li class="py-2 flex justify-between">
                            <span>{{ $topic->title }}</span>
                            <span class="text-gray-500">{{ $topic->posts_count }} replies</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Top users --}}
            <div class="bg-white p-4 border">
                <h3 class="font-bold text-sm mb-2">Most Active Users</h3>
                <ul class="divide-y">
                    @foreach($userEngagement as $user)
                        <li class="py-2 flex justify-between">
                            <span>{{ $user->name }}</span>
                            <span class="text-gray-500">{{ $user->posts_count }} posts</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Daily activity chart
            const dailyCtx = document.getElementById('dailyChart').getContext('2d');
            new Chart(dailyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($dailyActivity->keys()) !!},
                    datasets: [{
                        label: 'Posts',
                        data: {!! json_encode($dailyActivity->values()) !!},
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        borderColor: 'rgba(0,0,0,1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            // Category chart
            const catCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categories->pluck('ml_category')) !!},
                    datasets: [{
                        data: {!! json_encode($categories->pluck('count')) !!},
                        backgroundColor: ['#000', '#333', '#666', '#999', '#ccc'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        });
    </script>
    @endpush
@endsection