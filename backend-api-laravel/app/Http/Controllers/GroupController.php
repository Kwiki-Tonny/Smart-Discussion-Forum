<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Show the Rules Gate interface.
     */
    public function showRulesGate($group)
    {
        $groupId = is_object($group) ? $group->id : $group;
        
        return view('groups.rules-gate', compact('groupId'));
    }

    /**
     * Handle rule acceptance.
     */
    public function agreeToRules($group)
    {
        return redirect()->route('groups.dashboard', ['group' => $group]);
    }

    /**
     * Handle rule declination.
     */
    public function declineRules($group)
    {
        return redirect()->to('/');
    }

   public function showDashboard($group)
{
    $groupId = is_object($group) ? $group->id : $group;
    
    // 1. Fetch the real group
    $groupRecord = \App\Models\Group::findOrFail($groupId);
    $groupName = $groupRecord->name;

    // 2. Fetch all real topics belonging to this group, eager-loading your 'creator' relationship
    $topics = \App\Models\Topic::where('group_id', $groupId)
        ->with('creator')
        ->withCount('posts') // Automatically counts the related posts rows for us!
        ->latest()
        ->get();

    return view('groups.dashboard', compact('groupId', 'groupName', 'topics'));
} 

}