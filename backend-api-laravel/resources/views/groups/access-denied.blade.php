@extends('layouts.app')

@section('content')
<div style="max-width:500px; margin:80px auto; padding:40px; background:#fff; border:0.5px solid #e5e5e5; border-radius:8px; text-align:center;">

    {{-- ICON --}}
    <div style="font-size:48px; margin-bottom:16px;">🚫</div>

    {{-- HEADER --}}
    <h1 style="font-size:22px; font-weight:500; color:#1a1a1a; margin:0 0 8px;">
        Access Denied
    </h1>
    <p style="font-size:13px; color:#777; margin:0 0 32px;">
        You declined the group rules. You cannot participate in this group.
    </p>

    {{-- GO BACK BUTTON --}}
    <a href="/" style="display:inline-block; padding:12px 32px; background:#1a1a1a; color:#fff; border-radius:6px; font-size:14px; text-decoration:none;">
        Go Back Home
    </a>

</div>
@endsection