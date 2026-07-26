<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        h1 {
            font-size: 18px;
            color: #0A574F;
            border-bottom: 2px solid #0A574F;
            padding-bottom: 6px;
            margin-bottom: 16px;
        }
        h2 {
            font-size: 14px;
            color: #2563EB;
            margin-top: 18px;
            margin-bottom: 8px;
            border-bottom: 1px solid #E5E5E5;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th {
            background-color: #0A574F;
            color: white;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }
        td {
            padding: 4px 6px;
            border-bottom: 1px solid #E5E5E5;
        }
        tr:nth-child(even) td {
            background-color: #F9F9F9;
        }
        .stat-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }
        .stat-box {
            border: 1px solid #E5E5E5;
            border-radius: 4px;
            padding: 6px 12px;
            min-width: 80px;
            background: #fefefe;
        }
        .stat-box .number {
            font-size: 16px;
            font-weight: bold;
            color: #0A574F;
        }
        .stat-box .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-box.warning .number { color: #D97706; }
        .stat-box.danger .number { color: #DC2626; }
        .stat-box.info .number { color: #2563EB; }
        .footer {
            margin-top: 24px;
            border-top: 1px solid #E5E5E5;
            padding-top: 8px;
            font-size: 8px;
            color: #94A3B8;
            text-align: center;
        }
        .rank-badge {
            background: #0A574F;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-block;
            text-align: center;
            line-height: 18px;
            font-size: 8px;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <h1>📊 Admin Report – {{ $generatedAt }}</h1>

    {{-- Summary Stats --}}
    <div class="stat-grid">
        <div class="stat-box"><span class="number">{{ $totalUsers }}</span> <div class="label">Total Users</div></div>
        <div class="stat-box"><span class="number">{{ $totalStudents }}</span> <div class="label">Students</div></div>
        <div class="stat-box"><span class="number">{{ $totalLecturers }}</span> <div class="label">Lecturers</div></div>
        <div class="stat-box"><span class="number">{{ $totalAdmins }}</span> <div class="label">Admins</div></div>
        <div class="stat-box"><span class="number">{{ $totalGroups }}</span> <div class="label">Groups</div></div>
        <div class="stat-box"><span class="number">{{ $totalTopics }}</span> <div class="label">Topics</div></div>
        <div class="stat-box"><span class="number">{{ $totalPosts }}</span> <div class="label">Posts</div></div>
        <div class="stat-box"><span class="number">{{ $totalQuizzes }}</span> <div class="label">Quizzes</div></div>
        <div class="stat-box"><span class="number">{{ $totalSubmissions }}</span> <div class="label">Submissions</div></div>
        <div class="stat-box warning"><span class="number">{{ $pendingRegistrations }}</span> <div class="label">Pending</div></div>
        <div class="stat-box danger"><span class="number">{{ $blacklistedUsers }}</span> <div class="label">Blacklisted</div></div>
        <div class="stat-box"><span class="number">{{ $warnedOnce }}</span> <div class="label">Warned (1x)</div></div>
        <div class="stat-box"><span class="number">{{ $warnedTwice }}</span> <div class="label">Warned (2x)</div></div>
    </div>

    {{-- Group Rankings --}}
    <div class="page-break"></div>

    <h2>🏆 Group Rankings by Topics</h2>
    <table>
        <thead>
            <tr><th>Rank</th><th>Group</th><th>Topics</th><th>Members</th></tr>
        </thead>
        <tbody>
            @forelse($groupRankByTopics as $index => $g)
                <tr>
                    <td><span class="rank-badge">{{ $index+1 }}</span></td>
                    <td>{{ $g->name }}</td>
                    <td>{{ $g->topics_count }}</td>
                    <td>{{ $g->users_count ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No groups found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>🏆 Group Rankings by Replies (Posts)</h2>
    <table>
        <thead>
            <tr><th>Rank</th><th>Group</th><th>Replies</th><th>Members</th></tr>
        </thead>
        <tbody>
            @forelse($groupRankByReplies as $index => $g)
                <tr>
                    <td><span class="rank-badge">{{ $index+1 }}</span></td>
                    <td>{{ $g->name }}</td>
                    <td>{{ $g->posts_count ?? 0 }}</td>
                    <td>{{ $g->users_count ?? 0 }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No groups found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- User Rankings --}}
    <div class="page-break"></div>

    <h2>👤 Top Students by Total Posts</h2>
    <table>
        <thead>
            <tr><th>Rank</th><th>Student</th><th>Posts</th></tr>
        </thead>
        <tbody>
            @forelse($topPosters as $index => $user)
                <tr>
                    <td><span class="rank-badge">{{ $index+1 }}</span></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->posts_count }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>👤 Top Students by Replies</h2>
    <table>
        <thead>
            <tr><th>Rank</th><th>Student</th><th>Replies</th></tr>
        </thead>
        <tbody>
            @forelse($topRepliers as $index => $user)
                <tr>
                    <td><span class="rank-badge">{{ $index+1 }}</span></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->posts_count }}</td> <!-- same as posts, but we can keep separate -->
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">No students found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Quiz Performance --}}
    <div class="page-break"></div>

    <h2>📝 Quiz Performance Overview</h2>
    <div class="stat-grid">
        <div class="stat-box info"><span class="number">{{ $quizStats['total_quizzes'] }}</span> <div class="label">Quizzes</div></div>
        <div class="stat-box info"><span class="number">{{ $quizStats['total_submissions'] }}</span> <div class="label">Submissions</div></div>
        <div class="stat-box info"><span class="number">{{ number_format($quizStats['avg_score'], 1) }}%</span> <div class="label">Avg Score</div></div>
        <div class="stat-box info"><span class="number">{{ number_format($quizStats['pass_rate'], 1) }}%</span> <div class="label">Pass Rate</div></div>
    </div>

    <h2>🏅 Top Students by Quiz Performance</h2>
    <table>
        <thead>
            <tr><th>Rank</th><th>Student</th><th>Average Score</th></tr>
        </thead>
        <tbody>
            @forelse($quizStats['top_students'] as $index => $student)
                <tr>
                    <td><span class="rank-badge">{{ $index+1 }}</span></td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->avg_score }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">No data available.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>📊 Quiz Detail Performance</h2>
    <table>
        <thead>
            <tr><th>Quiz</th><th>Submissions</th><th>Avg Score</th></tr>
        </thead>
        <tbody>
            @forelse($quizStats['quiz_performance'] as $quiz)
                <tr>
                    <td>{{ $quiz->title }}</td>
                    <td>{{ $quiz->submissions_count }}</td>
                    <td>{{ $quiz->avg_score }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">No quiz submissions available.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Recent Users and Blacklist --}}
    <div class="page-break"></div>

    <h2>👤 Recent Users (last 20)</h2>
    <table>
        <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr>
        </thead>
        <tbody>
            @forelse($recentUsers as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                    <td>{{ ucfirst($user->status) }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">No recent users.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>🚫 Recent Blacklist Activity</h2>
    <table>
        <thead>
            <tr><th>User</th><th>Reason</th><th>Expires</th><th>Date</th></tr>
        </thead>
        <tbody>
            @forelse($recentBlacklistLogs as $log)
                <tr>
                    <td>{{ $log->user->name ?? 'Unknown' }}</td>
                    <td>{{ $log->reason }}</td>
                    <td>{{ $log->expires_at ? $log->expires_at->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">No recent blacklist entries.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by {{ auth()->user()->name ?? 'Admin' }} – {{ now()->format('F d, Y H:i:s') }}
    </div>

</body>
</html>