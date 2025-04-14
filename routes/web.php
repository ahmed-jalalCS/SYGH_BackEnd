<?php

use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Route;

Route::get('/test-pdf-cover', function () {
    $pdfPath = storage_path('app/public/sample.pdf');

    try {
        $pdf = new Pdf($pdfPath);
        $coverPath = storage_path('app/public/projects/covers/test.jpg');
        $pdf->saveImage($coverPath);
        return "✅ تم توليد الغلاف بنجاح";
    } catch (\Exception $e) {
        return "❌ فشل: " . $e->getMessage(); // ✅ نطبع الخطأ بالتفصيل
    }
});

