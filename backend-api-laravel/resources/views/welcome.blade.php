<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Discussion Forum - Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full overflow-hidden flex flex-col bg-[#eae6df]">

    <!-- Top Status / Navbar Header -->
    <header class="bg-[#f0f2f5] border-b border-gray-200 px-6 py-3 flex items-center justify-between text-sm text-gray-700 shrink-0">
        <div class="flex items-center gap-3">
            <span class="font-bold text-gray-900 tracking-tight">TRINA Smart Discussion Forum</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800 font-medium border border-green-200">● Live Workspace</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-gray-600">Welcome, <strong class="text-gray-900">Student</strong></span>
            <form action="/logout" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">Logout</button>
            </form>
        </div>
    </header>

    <!-- Main Container Layout -->
    <div class="flex flex-1 overflow-hidden w-full">

        <!-- 1. Deep Green Left Icon Navigation -->
        <nav class="w-16 bg-[#111b21] flex flex-col items-center py-4 justify-between shrink-0">
            <div class="flex flex-col gap-6 w-full items-center">
                <!-- Active App Icon -->
                <div class="p-3 bg-[#2a3942] text-[#00a884] rounded-xl cursor-pointer transition-all" title="Discussion Groups">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <!-- Secondary App Icons -->
                <div class="p-3 text-gray-400 hover:text-gray-200 cursor-pointer transition-all" title="Analytics">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <!-- Recommendations -->
                <div class="p-3 text-gray-400 hover:text-gray-200 cursor-pointer transition-all" title="Recommendations">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="p-3 text-gray-400 hover:text-gray-200 cursor-pointer transition-all" title="Profile Settings">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
        </nav>

        <!-- 2. Better Group List Side Panel -->
        <aside class="w-80 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <!-- Sidebar Header & Action -->
            <div class="p-4 bg-[#f0f2f5] flex items-center justify-between">
                <h2 class="font-bold text-gray-800 text-lg tracking-tight">All Groups</h2>
                <!-- Green New Group Button -->
                <button class="p-2 bg-[#00a884] text-white rounded-full hover:bg-[#008f72] transition-colors shadow-sm focus:outline-none" title="Create New Group">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
                </button>
            </div>

            <!-- Search Bars for Group Filtering -->
            <div class="p-2.5 bg-white border-b border-gray-100">
                <div class="bg-[#f0f2f5] rounded-lg flex items-center px-3 py-1.5">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Search or start new chat" class="bg-transparent text-xs w-full focus:outline-none text-gray-700 placeholder-gray-400">
                </div>
            </div>

            <!-- Polished Group Cards (Course Names) -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100">
                
                <!-- Active Highlighting Card: Software Engineering -->
                <a href="#" class="flex items-start gap-3 p-3.5 bg-[#f0f2f5] border-l-4 border-[#00a884] transition-all block">
                    <div class="w-11 h-11 bg-[#00a884] rounded-full flex items-center justify-center font-bold text-white shadow-sm shrink-0">SE</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 truncate">Software Engineering</h3>
                            <span class="text-[10px] font-medium text-[#00a884]">12:42 PM</span>
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-0.5 font-medium text-[#00a884]">Active workspace session open</p>
                    </div>
                </a>

                <!-- Computer Science Card -->
                <a href="#" class="flex items-start gap-3 p-3.5 hover:bg-gray-50 transition-all block">
                    <div class="w-11 h-11 bg-[#00a884] rounded-full flex items-center justify-center font-bold text-white shadow-sm shrink-0">CS</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 truncate">Computer Science</h3>
                            <span class="text-[10px] text-gray-400">Yesterday</span>
                        </div>
                        <p class="text-xs text-gray-400 truncate mt-0.5">Alex: Compiler design structures loaded...</p>
                    </div>
                </a>

                <!-- BIST Card -->
                <a href="#" class="flex items-start gap-3 p-3.5 hover:bg-gray-50 transition-all block">
                    <div class="w-11 h-11 bg-[#00a884] rounded-full flex items-center justify-center font-bold text-white shadow-sm shrink-0">BI</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 truncate">BIST</h3>
                            <span class="text-[10px] text-gray-400">Tuesday</span>
                        </div>
                        <p class="text-xs text-gray-400 truncate mt-0.5">Database scaling issues resolved.</p>
                    </div>
                </a>

                <!-- BCOM Card -->
                <a href="#" class="flex items-start gap-3 p-3.5 hover:bg-gray-50 transition-all block">
                    <div class="w-11 h-11 bg-[#00a884] rounded-full flex items-center justify-center font-bold text-white shadow-sm shrink-0">BC</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 truncate">BCOM</h3>
                            <span class="text-[10px] text-gray-400">Jul 5</span>
                        </div>
                        <p class="text-xs text-gray-400 truncate mt-0.5">Reviewing business case parameters.</p>
                    </div>
                </a>
            </div>
        </aside>

        <!-- 3. Modern Conversation Workspace Area -->
        <main class="flex-1 bg-[#efeae2] flex flex-col overflow-hidden relative">
            <!-- Subtle chat background pattern container alternative -->
            <div class="absolute inset-0 opacity-[0.06] pointer-events-none bg-[radial-gradient(#111b21_1px,transparent_1px)] [background-size:16px_16px]"></div>

            <!-- Chat Active Room Header -->
            <div class="bg-[#f0f2f5] p-3.5 border-b border-gray-200 flex items-center justify-between shrink-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#00a884] rounded-full flex items-center justify-center font-bold text-white shadow-sm">SE</div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Software Engineering Node</h2>
                        <p class="text-[11px] text-gray-500">Collaborative stream for tasks, code architecture, and topics</p>
                    </div>
                </div>
                <div>
                    <!-- Rules Badge and Context Action -->
                    <span class="inline-flex items-center gap-1.5 text-xs bg-green-50 text-[#00a884] font-semibold px-2.5 py-1 rounded-full border border-green-200 shadow-sm">
                        🛡️ Rules Accepted
                    </span>
                </div>
            </div>

            <!-- Dynamic Conversation Feed Panel -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4 z-10">
                
                <!-- Topic Node Container Block -->
                @forelse($topics as $topic)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 max-w-3xl mx-auto p-4 flex flex-col justify-between transition-all hover:shadow-md">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base font-bold text-gray-900 hover:underline cursor-pointer">{{ $topic->title }}</h2>
                                    @if(!empty($topic->ml_category))
                                        <span class="inline-flex items-center text-[10px] bg-green-50 text-[#00a884] font-semibold px-1.5 py-0.5 rounded border border-green-200">
                                            🏷️ {{ $topic->ml_category }}
                                        </span>
                                    @endif
                                </div>
                                <!-- Aggregated Tracker Counter Block -->
                                <div class="bg-gray-50 border border-gray-200 rounded px-3 py-1 text-center shrink-0 min-w-[55px]">
                                    <span class="block text-sm font-bold text-gray-700">{{ $topic->posts_count ?? 0 }}</span>
                                    <span class="block text-[9px] text-gray-400 uppercase tracking-wider font-bold">Posts</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed">System thread context dynamically running from model collections parameters.</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-600">{{ $topic->creator->name ?? 'Anonymous Student' }}</span>
                                <span>•</span>
                                <span>Posted {{ $topic->created_at ? $topic->created_at->diffForHumans() : 'Just now' }}</span>
                            </div>
<a href="{{ route('topics.show', $topic->id) }}" class="text-[#00a884] font-bold hover:underline flex items-center gap-1">
    View Cascading Feed →
</a>
                            
                            </a>
                        </div>
                    </div>
                @empty
                    <!-- Safe Empty State State Frame -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 max-w-md mx-auto p-8 text-center mt-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                            💬
                        </div>
                        <h3 class="text-sm font-bold text-gray-800">No threads active in channel</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-xs mx-auto">No discussion topics found in this group yet. Be the first to start a conversation!</p>
                    </div>
                @endforelse

            </div>

            <!-- Bottom Discussion Creation Action Panel (Behaves like WhatsApp input bar) -->
            <div class="bg-[#f0f2f5] p-3 flex items-center justify-between gap-3 shrink-0 border-t border-gray-200 z-10">
                <button class="text-gray-500 hover:text-gray-700 px-1 focus:outline-none" title="Attach Document Reference">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </button>
                
                <!-- Main Inline Prompt Form Input Field Trigger -->
                <div class="flex-1">
                    <input type="text" placeholder="Type a new topic discussion thread..." class="w-full bg-white rounded-lg px-4 py-2 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#00a884] border border-gray-200 shadow-sm">
                </div>

                <button class="bg-[#00a884] hover:bg-[#008f72] text-white rounded-lg px-4 py-2 text-xs font-bold transition-colors shadow-sm focus:outline-none">
                    Create Topic
                </button>
            </div>
        </main>
    </div>

    <!-- Bottom Footer System Tracking Bar -->
    <footer class="bg-[#222e35] text-gray-400 text-[10px] px-4 py-1.5 flex items-center justify-between shrink-0 z-20">
        <div>System Status: <span class="text-green-400 font-medium">Online</span> | Database: <span class="text-green-400 font-medium">Connected</span></div>
        <div class="flex gap-4">
            <a href="#" class="hover:underline">Privacy Policy</a>
            <a href="#" class="hover:underline">Terms of Service</a>
        </div>
    </footer>

</body>
</html>