<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;
use \App\Models\ConfCorreo;
class TwoFactorController extends Controller
{
    public function index()
    {
        if (!session('two_factor')) {
            return redirect()->route('login');
        }

        return view('auth.twofactor');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|array|min:6|max:6',
            'code.*' => 'numeric',
        ]);

        $code = implode('', $request->input('code'));

        $user = User::find(session('two_factor'));

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->two_factor_code !== $code || $user->two_factor_expires_at->lt(now())) {
            return back()->withErrors(['code' => 'El código es incorrecto o ha expirado.']);
        }

        $user->resetTwoFactorCode();
        Auth::login($user);

        session()->forget('two_factor');

        return redirect()->intended('/home');
    }
    public function resend()
    {

        $correo = env('CONF_CORREO_ID');
        $conf = ConfCorreo::find($correo);
        if (!$conf) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        config([
            'mail.mailers.smtp.host' => $conf->conf_smtp_host,
            'mail.mailers.smtp.port' => $conf->conf_smtp_port,
            'mail.mailers.smtp.username' => $conf->conf_smtp_user,
            'mail.mailers.smtp.password' => $conf->conf_smtp_pass,
            'mail.mailers.smtp.encryption' => $conf->conf_protocol,
            'mail.default' => 'smtp',
        ]);

        $user = User::find(session('two_factor'));

        if (!$user) {
            return redirect()->route('login');
        }

        $user->generateTwoFactorCode();
        Mail::to($user->email)->send(new TwoFactorCodeMail($user));

        return back()->with('message', 'Se ha reenviado un nuevo código a tu correo.');
    }


}
