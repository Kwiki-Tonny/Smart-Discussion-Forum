<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $groupName }} - TRINA Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">

   @extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-100">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Smart Discussion Forum
                </h1>
                <p class="text-sm text-gray-500">
                    Student Dashboard
                </p>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-gray-600">
                    Welcome,
                    <strong>{{ Auth::user()->name ?? 'Student' }}</strong>
                </span>

                <a href="#"
                   class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                    Logout
                </a>
            </div>

        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">

        <div class="grid grid-cols-12 gap-6">

            <!-- Main Section -->
            <div class="col-span-8">

                <!-- Group Header -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">

                    <h2 class="text-3xl font-bold text-gray-800">
                        Software Engineering Group
                    </h2>

                    <p class="text-gray-500 mt-2">
                        Welcome to your discussion space. Share ideas, ask questions,
                        and collaborate with your classmates.
                    </p>

                </div>

                <!-- Search -->
                <div class="bg-white rounded-xl shadow p-5 mb-6">

                    <input
                        type="text"
                        placeholder="Search discussions..."
                        class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500">

                </div>

                <!-- Create Topic -->
                <div class="mb-6">

                    <button
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">

                        + Create New Topic

                    </button>

                </div>

                <!-- Recent Discussions -->
                <h3 class="text-xl font-bold mb-4 text-gray-700">

                    Recent Discussions

                </h3>

                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow p-5 mb-4">

                    <h4 class="text-lg font-semibold text-gray-800">

                        Laravel Authentication

                    </h4>

                    <p class="text-gray-500 mt-2">

                        Discuss login, registration and middleware.

                    </p>

                    <div class="mt-4 flex justify-between text-sm text-gray-500">

                        <span>12 Replies</span>

                        <span>Today</span>

                    </div>

                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow p-5 mb-4">

                    <h4 class="text-lg font-semibold">

                        Database Normalization

                    </h4>

                    <p class="text-gray-500 mt-2">

                        Understanding 1NF, 2NF and 3NF.

                    </p>

                    <div class="mt-4 flex justify-between text-sm text-gray-500">

                        <span>8 Replies</span>

                        <span>Yesterday</span>

                    </div>

                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-xl shadow p-5">

                    <h4 class="text-lg font-semibold">

                        PHP Routing

                    </h4>

                    <p class="text-gray-500 mt-2">

                        Understanding Laravel routes.

                    </p>

                    <div class="mt-4 flex justify-between text-sm text-gray-500">

                        <span>15 Replies</span>

                        <span>2 Days Ago</span>

                    </div>

                </div>

            </div>

            <!-- Sidebar -->
            <div class="col-span-4">

                <!-- Members -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">

                    <h3 class="font-bold text-lg mb-4">

                        Members Online

                    </h3>

                    <div class="space-y-3">

                        <div class="flex items-center gap-3">

                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>

                            John

                        </div>

                        <div class="flex items-center gap-3">

                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>

                            Sarah

                        </div>

                        <div class="flex items-center gap-3">

                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>

                            David

                        </div>

                    </div>

                </div>

                <!-- Rules -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">

                    <h3 class="font-bold text-lg">

                        Community Rules

                    </h3>

                    <p class="text-green-600 mt-3">

                        ✅ Rules Accepted

                    </p>

                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-xl shadow p-6">

                    <h3 class="font-bold text-lg mb-4">

                        Group Statistics

                    </h3>

                    <div class="space-y-2 text-gray-600">

                        <p>Total Topics: <strong>25</strong></p>

                        <p>Total Posts: <strong>184</strong></p>

                        <p>Members: <strong>67</strong></p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection