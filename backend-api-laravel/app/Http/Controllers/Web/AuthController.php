<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.',
            ])->onlyInput('email');
        }

        // Check if blacklisted
        if ($user->status === 'blacklisted') {
            // Check if ban has expired
            if ($user->blacklist_expires_at && now()->greaterThan($user->blacklist_expires_at)) {
                // Auto-reactivate if expired
                $user->update(['status' => 'active', 'blacklist_expires_at' => null]);
            } else {
                return back()->withErrors([
                    'email' => 'Your account has been blacklisted. Please contact the administrator.',
                ])->onlyInput('email');
            }
        }

        // Check if pending
        if ($user->status === 'pending') {
            return back()->withErrors([
                'email' => 'Your account is pending approval. Please wait for an administrator to approve your registration.',
            ])->onlyInput('email');
        }

        // Attempt login
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last_communicated_at
            Auth::user()->update(['last_communicated_at' => now()]);

            // Redirect based on role
            $role = Auth::user()->role;
            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'lecturer') {
                return redirect()->route('lecturer.dashboard');
            }
            // Default: student dashboard
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'status' => 'pending',
            'last_communicated_at' => null,
        ]);

        // Log the user in
        //Auth::login($user);

        return redirect()->route('login')
            ->with('success', 'Account created successfully! Welcome to the Smart Discussion Forum. Please wait for admin approval before you can log in. You will receive an email notification once your account is approved.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Show the password reset request form.
     */
    public function showResetForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }
}