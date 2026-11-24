<?php

namespace App\Http\Controllers;

use App\Services\PdfMergeService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MergeController extends Controller
{
    public function index()
    {
        return view('merge');
    }

    /** Download hasil gabungan. */
    public function store(Request $request, PdfMergeService $service)
    {
        $items = $this->validatedItems($request);

        try {
            $binary = $service->merge($items);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['merge' => $e->getMessage()]);
        }

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="merged.pdf"',
        ]);
    }

    /** Preview inline hasil gabungan (dipanggil via fetch). */
    public function preview(Request $request, PdfMergeService $service)
    {
        $items = $this->validatedItems($request);

        try {
            $binary = $service->merge($items);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
        ]);
    }

    private function validatedItems(Request $request): array
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimes:pdf', 'max:51200'],
            'ranges' => ['array'],
            'ranges.*' => ['nullable', 'string', 'max:255'],
        ]);

        $items = [];
        foreach ($request->file('files') as $i => $file) {
            $items[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getClientOriginalName(),
                'ranges' => $request->input("ranges.$i"),
            ];
        }

        return $items;
    }
}
