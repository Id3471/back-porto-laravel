<?php

namespace App\Http\Controllers;

use App\Models\PortfolioContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicPortfolioController extends Controller
{
    public function content(): JsonResponse
    {
        return response()->json([
            'data' => PortfolioContent::query()->whereKey('portfolio')->first()?->data,
        ]);
    }

    public function downloadCv(): StreamedResponse
    {
        $data = PortfolioContent::query()
            ->whereKey('portfolio')
            ->first()?->data;
        $cvUrl = $data['profile']['cvUrl'] ?? null;

        $cvPath = is_string($cvUrl) ? parse_url($cvUrl, PHP_URL_PATH) : null;
        if (
            ! is_string($cvPath)
            || ! preg_match('#^/storage/(cv/[A-Za-z0-9-]+\.pdf)$#i', $cvPath, $matches)
        ) {
            abort(404, 'Aucun CV n’est disponible pour le moment.');
        }

        $storagePath = $matches[1];
        abort_unless(Storage::disk('public')->exists($storagePath), 404, 'Aucun CV n’est disponible pour le moment.');

        return Storage::disk('public')->download(
            $storagePath,
            'CV-'.Str::slug($data['profile']['fullName'] ?? 'Portfolio').'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }
}
