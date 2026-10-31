<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\DownloadPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    /**
     * Pre-Flight Validation API Endpoint for Public Downloads.
     * Validates file availability, physical existence, and size metadata before transfer.
     */
    public function check(Request $request, int $id): JsonResponse
    {
        $result = DownloadPolicyService::validateDownload($id, null);

        if (!$result['allowed']) {
            DownloadPolicyService::logDownloadAttempt($result, $request, null);
        }

        return response()->json([
            'allowed'     => $result['allowed'],
            'reason'      => $result['reason'],
            'reason_text' => $result['reason_text'],
            'size_bytes'  => $result['size_bytes'],
            'size_human'  => $result['size_human'],
            'file_name'   => $result['file_name'],
            'file_type'   => $result['file_type'],
        ]);
    }

    /**
     * Memory-Safe Public File Streaming Download.
     */
    public function stream(Request $request, int $id)
    {
        $result = DownloadPolicyService::validateDownload($id, null);

        if (!$result['allowed']) {
            DownloadPolicyService::logDownloadAttempt($result, $request, null);
            return redirect()->route('public.downloads.index')->with('error', $result['reason_text']);
        }

        DownloadPolicyService::logDownloadAttempt($result, $request, null);

        return DownloadPolicyService::createStreamResponse($result);
    }
}
