<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class UploadImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $image = $request->file('image');
        $imagePath = Storage::disk('public')->putFile('images', $image);
        $tesseract = new TesseractOCR();
        $tesseract->image(public_path('storage/'.$imagePath));
        $text = $tesseract->run();
        return response()->json([
            'text' => $text
        ], 200);
    }
}
