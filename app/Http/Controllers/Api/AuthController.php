<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Registered successfully',
        ], 200);
    }

    public function login(Request $request)
    {

        $input = [
            'email' => $request->email,
            'password' => $request->password
        ];

        $user = User::where("email", $input['email'])->first();

        $role = $user->role;
        $name = $user->name;

        if (Auth::attempt($input)) {
            $token = $user->createToken("token")->plainTextToken;

            return response()->json([
                "status" => "success",
                "message" => "Login Successfully",
                "name" => $name,
                "role" => $role,
                "token" => $token
            ], 200);
        } else {
            return response()->json([
                "status" => "error",
                "message" => "Incorrect username or password"
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        // Pastikan user terautentikasi
        if (!$request->user()) {
            return response()->json([
                "status" => "error",
                "message" => "User is not authenticated",
            ], 401);
        }

        // Hapus token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "status" => "success",
            "message" => "You has logout",
        ], 200);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $otp = rand(100000, 999999); // Generate 6 digit OTP

        // Hapus OTP lama jika ada
        DB::table('password_reset_otps')->where('email', $email)->delete();

        // Simpan OTP ke database
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'created_at' => now()
        ]);

        // Kirim email OTP
        Mail::to($email)->send(new PasswordResetOtpMail($otp));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP telah dikirim ke email Anda'
        ], 200);
    }

    public function verifyOtp(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6'
        ]);

        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord) {
            Log::error('OTP tidak ditemukan untuk email: ' . $request->email . ' dan OTP: ' . $request->otp);
            return response()->json([
                'status' => 'error',
                'message' => 'OTP tidak valid'
            ], 422);
        }

        // OTP valid selama 15 menit
        if (now()->diffInMinutes($otpRecord->created_at) > 15) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP telah kadaluarsa'
            ], 422);
        }

        Log::info('User email:', ['email' => $request->email]);
        Log::info('OTP input:', ['otp' => $request->otp]);


        // Tandai bahwa user sudah verifikasi OTP (misal: flag di DB atau session)
        DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->update(['otp_verified' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'OTP valid, silakan reset password'
        ], 200);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp_verified', true)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP belum diverifikasi untuk email ini'
            ], 403);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus OTP record agar tidak bisa dipakai ulang
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan atau belum login'
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diubah'
        ], 200);
    }



}