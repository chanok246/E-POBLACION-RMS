<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Process the e-Poblacion personnel login.
     *
     * TEMPORARY DEVELOPMENT VERSION.
     *
     * Database authentication will be added later.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => [
                'required',
                'in:captain,secretary,treasurer,staff',
            ],

            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | TEMPORARY DEVELOPMENT LOGIN
        |--------------------------------------------------------------------------
        |
        | This is ONLY so we can test the complete flow:
        |
        | Carousel Login
        |       ↓
        | POST /login
        |       ↓
        | LoginController
        |       ↓
        | Dashboard
        |
        | Later this will be replaced with real database authentication.
        |
        */

        $testAccounts = [
            'captain' => [
                'email' => 'captain@epoblacion.test',
                'password' => 'admin123',
            ],

            'secretary' => [
                'email' => 'secretary@epoblacion.test',
                'password' => 'admin123',
            ],

            'treasurer' => [
                'email' => 'treasurer@epoblacion.test',
                'password' => 'admin123',
            ],

            'staff' => [
                'email' => 'staff@epoblacion.test',
                'password' => 'admin123',
            ],
        ];

        $account = $testAccounts[$validated['role']];

        if (
            $validated['email'] !== $account['email'] ||
            $validated['password'] !== $account['password']
        ) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email, password, or selected role.',
                ])
                ->withInput(
                    $request->only('email', 'role')
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Successful login
        |--------------------------------------------------------------------------
        */

        $request->session()->put('authenticated', true);
        $request->session()->put('user_role', $validated['role']);
        $request->session()->put('user_email', $validated['email']);

        return redirect()->route('dashboard');
    }
}
