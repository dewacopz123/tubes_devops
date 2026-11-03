<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class LoginController extends Controller
{
    public function index()
    {
        return view('login.index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Karyawan::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Email incorrect'
            ])->withInput()->with('active', 'login');
        }

        if ($user->status !== 'Aktif') {
            return back()->withErrors([
                'email' => 'Your account is not active'
            ])->withInput()->with('active', 'login');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password incorrect'
            ])->withInput()->with('active', 'login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required','string','max:255','regex:/^[A-Za-z\s]+$/'],
            'email' => 'required|email|unique:karyawans,email',
            'password' => 'required|min:6|max:8',
        ],[
            'nama.regex' => 'Name cannot using numbers or special characters',
            'password.max' => 'Password cannot be more than 8 characters',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active', 'register');
        }

        $karyawan = Karyawan::create([
            'kode_karyawan' => 'KRY-' . strtoupper(Str::random(6)),
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'karyawan',
            'status' => 'Aktif',
        ]);

        // AUTO LOGIN setelah register
        Auth::login($karyawan);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}

