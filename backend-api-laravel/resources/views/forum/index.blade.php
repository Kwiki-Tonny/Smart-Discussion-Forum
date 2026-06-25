@extends('layouts.app')

@section('content')
<div class="workspace-split">
    
    <nav class="app-sidebar">
        <div class="app-icon" title="Groups">💬</div>
        <div class="app-icon" title="Profile">👤</div>
        <div class="app-icon" title="Performance">📊</div>
        <div class="app-icon" title="Recommendations">💡</div>
    </nav>

    <aside class="navigation-container">
        
        <div id="groups-view" class="nav-card">
            <div class="nav-header">Your Groups</div>
            <ul class="nav-list">
                <li onclick="window.openGroup('Web Development')">Web Development</li>
                <li onclick="window.openGroup('Software Engineering')">Software Engineering</li>
                <li onclick="window.openGroup('Numerical Analysis')">Numerical Analysis</li>
                <li onclick="window.openGroup('System Admin')">System Admin</li>
            </ul>
        </div>

        <div id="topics-view" class="nav-card card-hidden">
            <div class="nav-header-topics">
                <button class="back-btn" onclick="window.goBack()">◄ Back</button>
                <span id="current-group-title" style="color: #ccc; font-weight: bold;">Group Name</span>
            </div>
            <ul class="nav-list">
                <li>General Discussion</li>
                <li>Exam Prep Resources</li>
                <li id="dynamic-assignment">Assignments</li>
            </ul>
        </div>

    </aside>

    <main class="main-content">
        <div class="placeholder">Select a topic to view messages</div>
    </main>

</div>

<footer class="status-bar">
    <div class="status-online">● Status: Online Web Workspace</div>
    <div class="status-db">● Connected to Laravel Backend</div>
</footer>
@endsection