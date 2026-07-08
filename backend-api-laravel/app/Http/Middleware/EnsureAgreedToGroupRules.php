<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgreedToGroupRules
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Grab the group dynamic parameter from the URL route
        $group = $request->route('group'); 
        $groupId = is_object($group) ? $group->id : $group;

        if ($groupId) { 
            // 2. If they are submitting the Accept form, save it to their session
            if ($request->routeIs('groups.rules.agree')) {
                session(["group_rules_accepted_{$groupId}" => true]);
                
                // Force a clean browser redirect straight to the dashboard to bypass JSON output
                return redirect()->to("/groups/{$groupId}");
            }

            // 3. If they haven't accepted yet, redirect them to the rules gate
            if (!session("group_rules_accepted_{$groupId}") && 
                !$request->routeIs('groups.rules.gate') && 
                !$request->routeIs('groups.rules.agree') && 
                !$request->routeIs('groups.rules.decline')) {
                
                return redirect()->route('groups.rules.gate', ['group' => $groupId]);
            }
        }

        return $next($request);
    }
}