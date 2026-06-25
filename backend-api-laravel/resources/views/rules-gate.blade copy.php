@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg w-full">
        
        {{-- HEADER --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-blue-800">
                📋 Group Rules
            </h1>
            <p class="text-gray-500 mt-1">
                You must agree to the rules before participating
            </p>
        </div>

        {{-- RULES --}}
        <div class="bg-gray-50 rounded p-4 mb-6 text-sm text-gray-700 space-y-2">
            <p>✅ Be respectful to all members</p>
            <p>✅ No spamming or flooding messages</p>
            <p>✅ Stay on topic at all times</p>
            <p>✅ No sharing of private information</p>
            <p>✅ Follow your lecturer's instructions</p>
        </div>

        {{-- AGREE BUTTON --}}
        <form method="POST" action="{{ route('groups.agree-rules', $group->id) }}">
            @csrf
            <button type="submit"
                    class="w-full bg-blue-800 text-white py-3 rounded-lg 
                           hover:bg-blue-700 transition font-semibold">
                ✅ I Agree to the Rules
            </button>
        </form>

    </div>
</div>
@endsectionsss