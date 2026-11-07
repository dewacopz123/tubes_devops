<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesSekData;
use App\Models\Karyawan;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    use ValidatesSekData;

    public function index()
    {
        $karyawan = Karyawan::findOrFail(Auth::id());

        return view('Profiles.profile', compact('karyawan'));
    }

    public function update(Request $request)
    {
        $karyawan = Karyawan::findOrFail(Auth::id());

        $data = $request->validate(
            $this->profileRules((int) $karyawan->id),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $karyawan->update([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'telepon' => $data['telepon'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui.',
            ]);
        }

        return redirect()->back()->with('success', 'Profile berhasil diperbarui');
    }

    public function uploadPhoto(Request $request)
    {
        try {
            $request->validate([
                'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            $karyawan = Auth::user();
            $realPath = $request->file('foto')->getRealPath();

            $uploadResponse = Cloudinary::uploadApi()->upload(
                $realPath,
                [
                    'folder' => 'sek/profile',
                ]
            );

            $uploadedFileUrl = $uploadResponse['secure_url'] ?? $uploadResponse['url'] ?? null;

            $karyawan->foto = $uploadedFileUrl;
            $karyawan->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil diupload',
                'foto_url' => $uploadedFileUrl,
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {

            \Log::error('Cloudinary Upload Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
