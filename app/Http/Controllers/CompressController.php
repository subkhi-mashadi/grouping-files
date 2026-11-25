<?php

namespace App\Http\Controllers;

use App\Services\PdfCompressService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class CompressController extends Controller
{
    public function index()
    {
        return view('compress');
    }

    public function store(Request $request, PdfCompressService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'level' => ['required', 'in:low,medium,high'],
        ]);

        try {
            $result = $service->compress($request->file('file')->getRealPath(), $request->input('level'));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            report($e);

            return response()->json(['message' => 'Layanan kompres sedang tidak tersedia.'], 503);
        }

        return response($result['binary'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="compressed.pdf"',
            'X-Original-Size' => $result['original'],
            'X-Compressed-Size' => $result['compressed'],
        ]);
    }
}
