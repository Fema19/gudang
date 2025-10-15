<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperatorAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('operator.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'operator'
        ])) {

            // 🔐 WAJIB: Regenerate session agar login tidak langsung logout setelah redirect
            $request->session()->regenerate();

            return redirect()->route('barang.index');
        }

        return back()->withErrors(['email' => 'Email atau password salah']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Hapus session dengan benar
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('operator.login');
    }
}
