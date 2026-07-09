<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $topic->title }} - TRINA Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">

    <!-- Top Navigation Bar -->
    <header class="bg-[#00a884] text-white px-6 py-4 shadow-md sticky top-0 z-50 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="/" class="text-white hover:text-gray-200 transition font-medium flex items-center">
                ← Back to Dashboard
            </a>
            <h1 class="text-lg font-semibold tracking-wide border-l border-white/30 pl-4">
                TRINA Smart-Discussion-Forum
            </h1>
        </div>
        <span class="bg-white/20 text-xs px-3 py-1 rounded-full uppercase tracking-wider font-semibold">
            {{ $topic->posts_count }} {{ Str::plural('Post', $topic->posts_count) }}
        </span>
    </header>

    <!-- Main Container -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        
        <!-- Header Root Topic Box -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-[#00a884]/10 flex items-center justify-center text-[#00a884] font-bold text-sm uppercase">
                    {{ substr($topic->creator->name ?? 'U', 0, 2) }}
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $topic->creator->name ?? 'Anonymous Student' }}</h3>
                    <p class="text-xs text-gray-500">Started a discussion thread</p>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-gray-900 mb-3 leading-tight">
                {{ $topic->title }}
            </h2>
            <p class="text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg border-l-4 border-[#00a884]">
                {{ $topic->body ?? 'No description provided for this topic.' }}
            </p>
        </div>

        <!-- Cascading Conversation Stream Header -->
        <div class="flex items-center justify-between mb-4 px-2">
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Discussion Feed Updates</h4>
            <div class="h-px bg-gray-300 flex-grow ml-4"></div>
        </div>

        <!-- The Cascading Feed Stack -->
        <div class="space-y-4">
            @forelse($topic->posts as $post)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 transition hover:border-gray-300">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold text-sm text-gray-800">
                                {{ $post->user->name ?? 'Student' }}
                            </span>
                            <span class="text-xs text-gray-400">•</span>
                            <span class="text-xs text-gray-500">
                                {{ $post->created_at ? $post->created_at->diffForHumans() : 'Just now' }}
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
                        {{ $post->body }}
                    </p>
                </div>
            @empty
                <div class="bg-white rounded-lg border-2 border-dashed border-gray-200 p-12 text-center">
                    <p class="text-gray-500 font-medium">No one has replied to this topic stream yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Be the first contributor to share insights!</p>
                </div>
            @endforelse
        </div>

    </main>

</body>
</html>