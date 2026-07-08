@extends('layouts.app')

@section('content')
<!-- Outer grid wrapper setting full screen viewport limits using a clean light gray background -->
<div class="flex h-[calc(100vh-36px)] w-screen overflow-hidden bg-[#FAFAFA] text-gray-900 antialiased">
    
    <!-- 1. Left Side Rail App Sidebar (Clean Off-White) -->
    <nav class="w-[60px] bg-[#F0F2F5] flex flex-col items-center pt-4 space-y-5 border-r border-gray-200">
        <div class="text-xl cursor-pointer p-2 rounded-lg hover:bg-gray-200 transition-colors" title="Groups">💬</div>
        <div class="text-xl cursor-pointer p-2 rounded-lg hover:bg-gray-200 transition-colors" title="Profile">👤</div>
        <div class="text-xl cursor-pointer p-2 rounded-lg hover:bg-gray-200 transition-colors" title="Performance">📊</div>
        <div class="text-xl cursor-pointer p-2 rounded-lg hover:bg-gray-200 transition-colors" title="Recommendations">💡</div>
    </nav>

    <!-- 2. Channels & Navigation Container (Pure White) -->
    <aside class="w-[260px] bg-white border-r border-gray-200 flex flex-col">
        
        <!-- Primary View: Group Select -->
        <div id="groups-view" class="p-4 flex flex-col h-full">
            <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Your Groups</div>
            <ul class="flex flex-col space-y-1">
                <li onclick="window.openGroup('Web Development')" class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">Web Development</li>
                <li onclick="window.openGroup('Software Engineering')" class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">Software Engineering</li>
                <li onclick="window.openGroup('Numerical Analysis')" class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">Numerical Analysis</li>
                <li onclick="window.openGroup('System Admin')" class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">System Admin</li>
            </ul>
        </div>

        <!-- Secondary View: Sub-Topics (Hidden by Default) -->
        <div id="topics-view" class="p-4 flex flex-col h-full hidden">
            <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">
                <button class="text-[#0A66C2] font-semibold hover:underline" onclick="window.goBack()">◄ Back</button>
                <span id="current-group-title" class="font-bold truncate text-gray-800">Group Name</span>
            </div>
            <ul class="flex flex-col space-y-1">
                <li class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">General Discussion</li>
                <li class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">Exam Prep Resources</li>
                <li id="dynamic-assignment" class="p-2.5 text-sm rounded-md cursor-pointer text-gray-700 hover:bg-gray-100 hover:text-black transition-colors">Assignments</li>
            </ul>
        </div>

    </aside>

    <!-- 3. Main Center Workspace Viewport Area -->
    <main class="flex-1 bg-[#FAFAFA] flex items-center justify-center">
        <div class="text-sm text-gray-400 font-medium tracking-wide">Select a topic to view messages</div>
    </main>

</div>

<!-- 4. Fixed Pinned App Bottom Status Bar -->
<footer class="h-[36px] w-screen bg-[#F0F2F5] border-t border-gray-200 flex items-center px-4 space-x-5 text-[11px] font-semibold tracking-wide select-none text-gray-500">
    <div class="text-emerald-600">● Status: Online Web Workspace</div>
    <div class="text-blue-600">● Connected to Laravel Backend</div>
</footer>

<!-- JavaScript Sidebar Controller Logic -->
<script>
    window.openGroup = function(groupName) {
        document.getElementById('current-group-title').innerText = groupName;
        document.getElementById('groups-view').classList.add('hidden');
        document.getElementById('topics-view').classList.remove('hidden');
    }

    window.goBack = function() {
        document.getElementById('topics-view').classList.add('hidden');
        document.getElementById('groups-view').classList.remove('hidden');
    }
</script>
@endsection