<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Discussion Forum - Web Workspace</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
@extends('layouts.app')

@section('content')
<div style="max-width:600px; margin:60px auto; padding:40px; background:#fff; border:0.5px solid #e5e5e5; border-radius:8px;">

    {{-- HEADER --}}
    <div style="text-align:center; margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:500; color:#1a1a1a; margin:0 0 8px;">
            Community Rules
        </h1>
        <p style="font-size:13px; color:#777; margin:0;">
            Please review and accept our guidelines before participating in the forum.
        </p>
    </div>

    {{-- RULES LIST --}}
    <div style="margin-bottom:32px;">

        <div style="display:flex; gap:16px; margin-bottom:24px;">
            <div style="flex-shrink:0; width:28px; height:28px; background:#f0fdf4; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                <span style="color:#16a34a; font-size:16px; font-weight:700;">✓</span>
            </div>
            <div>
                <p style="font-size:14px; font-weight:500; color:#1a1a1a; margin:0 0 4px;">Be respectful</p>
                <p style="font-size:13px; color:#777; margin:0;">Maintain professional discourse at all times. Personal attacks, harassment, or exclusionary behavior will not be tolerated within our workspace.</p>
            </div>
        </div>

        <div style="display:flex; gap:16px; margin-bottom:24px;">
            <div style="flex-shrink:0; width:28px; height:28px; background:#f0fdf4; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                <span style="color:#16a34a; font-size:16px; font-weight:700;">✓</span>
            </div>
            <div>
                <p style="font-size:14px; font-weight:500; color:#1a1a1a; margin:0 0 4px;">No spam</p>
                <p style="font-size:13px; color:#777; margin:0;">Keep the environment clean. Avoid repetitive posts, unauthorized self-promotion, or irrelevant content that distracts from the forum's utility.</p>
            </div>
        </div>

        <div style="display:flex; gap:16px; margin-bottom:24px;">
            <div style="flex-shrink:0; width:28px; height:28px; background:#f0fdf4; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                <span style="color:#16a34a; font-size:16px; font-weight:700;">✓</span>
            </div>
            <div>
                <p style="font-size:14px; font-weight:500; color:#1a1a1a; margin:0 0 4px;">Stay on topic</p>
                <p style="font-size:13px; color:#777; margin:0;">Ensure your contributions align with the specific group's purpose. High density productivity relies on structured and relevant discussions.</p>
            </div>
        </div>

        <div style="display:flex; gap:16px; margin-bottom:24px;">
            <div style="flex-shrink:0; width:28px; height:28px; background:#f0fdf4; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                <span style="color:#16a34a; font-size:16px; font-weight:700;">✓</span>
            </div>
            <div>
                <p style="font-size:14px; font-weight:500; color:#1a1a1a; margin:0 0 4px;">Protect Privacy</p>
                <p style="font-size:13px; color:#777; margin:0;">Do not share sensitive internal data or personal information belonging to others. Integrity is the foundation of our community.</p>
            </div>
        </div>

    </div>

    {{-- BUTTONS --}}
    <form method="POST" action="{{ route('groups.agree-rules', $group->id ?? 1) }}">
        @csrf
        <button type="submit"
            style="width:100%; padding:12px; background:#1a1a1a; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; margin-bottom:10px;">
            Accept to Continue
        </button>
    </form>

    <form method="POST" action="{{ route('groups.decline-rules', $group->id ?? 1) }}">
        @csrf
        <button type="submit"
            style="width:100%; padding:12px; background:#fff; color:#1a1a1a; border:1px solid #ddd; border-radius:6px; font-size:14px; cursor:pointer;">
            Decline
        </button>
    </form>

</div>
@endsection