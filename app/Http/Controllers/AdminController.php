<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\GeneratedImage;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessEmotionJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

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

    public function generateHappy($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        $image = GeneratedImage::findOrFail($id);

        // Update emotion_data to reflect queued job status
        $emotionData = json_decode($image->emotion_data, true) ?? [];
        $emotionData['job_status'] = 'queued';
        $emotionData['job_updated_at'] = now()->toISOString();
        $emotionData['happy_processed'] = $emotionData['happy_processed'] ?? false;
        $image->update(['emotion_data' => json_encode($emotionData)]);

        // Dispatch the happy emotion processing job using the original image
        ProcessEmotionJob::dispatch($image->id, $image->original_image, 'happy', $image->phone_number);

        return redirect()->route('admin.dashboard')->with('success', 'Happy photo generation has been queued for Image ID '.$image->id);
    }

    public function queueJobs()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        // Get jobs from the jobs table
        $jobs = DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get failed jobs
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(20);

        // Get queue statistics
        $stats = [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'total_processed' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
        ];

        return view('admin.queue-jobs', compact('jobs', 'failedJobs', 'stats'));
    }

    public function retryJob($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Retry a failed job
            \Artisan::call('queue:retry', ['id' => $id]);
            return redirect()->route('admin.queue-jobs')->with('success', 'Job retried successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to retry job: ' . $e->getMessage());
        }
    }

    public function deleteJob($id)
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Delete a failed job
            DB::table('failed_jobs')->where('id', $id)->delete();
            return redirect()->route('admin.queue-jobs')->with('success', 'Job deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to delete job: ' . $e->getMessage());
        }
    }

    public function clearQueue()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Clear all pending jobs
            DB::table('jobs')->truncate();
            return redirect()->route('admin.queue-jobs')->with('success', 'Queue cleared successfully');
        } catch (\Exception $e) {
            return redirect()->route('admin.queue-jobs')->with('error', 'Failed to clear queue: ' . $e->getMessage());
        }
    }
}
