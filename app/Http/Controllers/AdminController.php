<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GeneratedImage;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        
        // Simple admin authentication (you can enhance this later)
        if ($credentials['email'] === 'admin@snickers.com' && $credentials['password'] === 'admin123') {
            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout()
    {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $images = GeneratedImage::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.dashboard', compact('images'));
    }

    public function show($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        return view('admin.show', compact('image'));
    }

    public function edit($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        return view('admin.edit', compact('image'));
    }

    public function update(Request $request, $id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        $image = GeneratedImage::findOrFail($id);
        $image->update([
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Image updated successfully!');
    }

    public function destroy($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        
        // Delete files from storage
        if (Storage::disk('public')->exists($image->original_image)) {
            Storage::disk('public')->delete($image->original_image);
        }
        if (Storage::disk('public')->exists($image->sad_image)) {
            Storage::disk('public')->delete($image->sad_image);
        }
        if (Storage::disk('public')->exists($image->happy_image)) {
            Storage::disk('public')->delete($image->happy_image);
        }

        $image->delete();

        return redirect()->route('admin.dashboard')->with('success', 'Image deleted successfully!');
    }

    public function download($id, $type)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);
        
        $filePath = match($type) {
            'original' => $image->original_image,
            'sad' => $image->sad_image,
            'happy' => $image->happy_image,
            default => null
        };

        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        return back()->with('error', 'File not found');
    }
}
