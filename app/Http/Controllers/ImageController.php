<?php

namespace App\Http\Controllers;

use App\Models\GeneratedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessEmotionJob;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GeneratedImage::query();

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'complete':
                    $query->whereNotNull('sad_image')->whereNotNull('happy_image');
                    break;
                case 'processing':
                    $query->where(function($q) {
                        $q->whereNotNull('sad_image')->whereNull('happy_image')
                          ->orWhere(function($q2) {
                              $q2->whereNull('sad_image')->whereNotNull('happy_image');
                          });
                    });
                    break;
                case 'pending':
                    $query->whereNull('sad_image')->whereNull('happy_image');
                    break;
            }
        }

        // Filter by date
        if ($request->filled('date')) {
            switch ($request->date) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', now()->startOfWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', now()->startOfMonth());
                    break;
            }
        }

        // Search by phone number
        if ($request->filled('search')) {
            $query->where('phone_number', 'like', '%' . $request->search . '%');
        }

        $images = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.images.index', compact('images'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.images.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'original_image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        // Handle image upload
        if ($request->hasFile('original_image')) {
            $image = $request->file('original_image');
            $filename = 'original_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('generated', $filename, 'public');
        }

        $generatedImage = GeneratedImage::create([
            'phone_number' => $request->phone_number,
            'original_image' => $path,
            'emotion_data' => json_encode([
                'sad_processed' => false,
                'happy_processed' => false,
                'campaign_completed' => false,
                'job_status' => 'pending',
                'job_updated_at' => now()->toISOString()
            ])
        ]);

        return redirect()->route('admin.images.index')->with('success', 'Image uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GeneratedImage $image)
    {
        return view('admin.images.show', compact('image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GeneratedImage $image)
    {
        return view('admin.images.edit', compact('image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeneratedImage $image)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'original_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        $data = ['phone_number' => $request->phone_number];

        // Handle image update
        if ($request->hasFile('original_image')) {
            // Delete old image
            if ($image->original_image && Storage::disk('public')->exists($image->original_image)) {
                Storage::disk('public')->delete($image->original_image);
            }

            $newImage = $request->file('original_image');
            $filename = 'original_' . time() . '_' . uniqid() . '.' . $newImage->getClientOriginalExtension();
            $path = $newImage->storeAs('generated', $filename, 'public');
            $data['original_image'] = $path;

            // Reset processing status since image changed
            $emotionData = json_decode($image->emotion_data, true) ?? [];
            $emotionData['sad_processed'] = false;
            $emotionData['happy_processed'] = false;
            $emotionData['campaign_completed'] = false;
            $emotionData['job_status'] = 'pending';
            $emotionData['job_updated_at'] = now()->toISOString();
            $data['emotion_data'] = json_encode($emotionData);
            $data['sad_image'] = null;
            $data['happy_image'] = null;
        }

        $image->update($data);

        return redirect()->route('admin.images.index')->with('success', 'Image updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneratedImage $image)
    {
        // Delete associated images
        if ($image->original_image && Storage::disk('public')->exists($image->original_image)) {
            Storage::disk('public')->delete($image->original_image);
        }
        if ($image->sad_image && Storage::disk('public')->exists($image->sad_image)) {
            Storage::disk('public')->delete($image->sad_image);
        }
        if ($image->happy_image && Storage::disk('public')->exists($image->happy_image)) {
            Storage::disk('public')->delete($image->happy_image);
        }
        if ($image->framed_image && Storage::disk('public')->exists($image->framed_image)) {
            Storage::disk('public')->delete($image->framed_image);
        }

        $image->delete();

        return redirect()->route('admin.images.index')->with('success', 'Image deleted successfully.');
    }

    /**
     * Generate happy image for the specified resource.
     */
    public function generateHappy(GeneratedImage $image)
    {
        if (!$image->original_image || !Storage::disk('public')->exists($image->original_image)) {
            return back()->with('error', 'Original image not found for processing.');
        }

        // Update emotion_data to reflect job status
        $emotionData = json_decode($image->emotion_data, true) ?? [];
        $emotionData['happy_processed'] = false;
        $emotionData['job_status'] = 'queued';
        $emotionData['job_updated_at'] = now()->toISOString();
        $image->emotion_data = json_encode($emotionData);
        $image->happy_image = null;
        $image->save();

        // Dispatch AI processing job for happy emotion
        ProcessEmotionJob::dispatch($image->id, $image->original_image, 'happy', $image->phone_number);

        return back()->with('success', 'Happy photo generation job has been queued for ' . $image->phone_number);
    }

    /**
     * Generate sad image for the specified resource.
     */
    public function generateSad(GeneratedImage $image)
    {
        if (!$image->original_image || !Storage::disk('public')->exists($image->original_image)) {
            return back()->with('error', 'Original image not found for processing.');
        }

        // Update emotion_data to reflect job status
        $emotionData = json_decode($image->emotion_data, true) ?? [];
        $emotionData['sad_processed'] = false;
        $emotionData['job_status'] = 'queued';
        $emotionData['job_updated_at'] = now()->toISOString();
        $image->emotion_data = json_encode($emotionData);
        $image->sad_image = null;
        $image->save();

        // Dispatch AI processing job for sad emotion
        ProcessEmotionJob::dispatch($image->id, $image->original_image, 'sad', $image->phone_number);

        return back()->with('success', 'Sad photo generation job has been queued for ' . $image->phone_number);
    }

    /**
     * Download the specified image.
     */
    public function download(GeneratedImage $image, $type = 'original')
    {
        $imagePath = null;
        $filename = null;

        switch ($type) {
            case 'original':
                $imagePath = $image->original_image;
                $filename = 'original_' . $image->phone_number . '.jpg';
                break;
            case 'sad':
                $imagePath = $image->sad_image;
                $filename = 'sad_' . $image->phone_number . '.jpg';
                break;
            case 'happy':
                $imagePath = $image->happy_image;
                $filename = 'happy_' . $image->phone_number . '.jpg';
                break;
            case 'framed':
                $imagePath = $image->framed_image;
                $filename = 'framed_' . $image->phone_number . '.jpg';
                break;
        }

        if (!$imagePath || !Storage::disk('public')->exists($imagePath)) {
            return back()->with('error', 'Image not found.');
        }

        return Storage::disk('public')->download($imagePath, $filename);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,generate_happy,generate_sad',
            'images' => 'required|array',
            'images.*' => 'exists:generated_images,id'
        ]);

        $images = GeneratedImage::whereIn('id', $request->images)->get();

        switch ($request->action) {
            case 'delete':
                foreach ($images as $image) {
                    $this->destroy($image);
                }
                return back()->with('success', count($images) . ' images deleted successfully.');

            case 'generate_happy':
                foreach ($images as $image) {
                    if ($image->original_image) {
                        $this->generateHappy($image);
                    }
                }
                return back()->with('success', 'Happy photo generation queued for ' . count($images) . ' images.');

            case 'generate_sad':
                foreach ($images as $image) {
                    if ($image->original_image) {
                        $this->generateSad($image);
                    }
                }
                return back()->with('success', 'Sad photo generation queued for ' . count($images) . ' images.');
        }

        return back()->with('error', 'Invalid action.');
    }
}
