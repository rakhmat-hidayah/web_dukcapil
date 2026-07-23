<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of client API keys.
     */
    public function index(): InertiaResponse
    {
        $keys = ApiKey::withCount('logs')
            ->orderBy('client_name')
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'client_name' => $key->client_name,
                    'api_key' => $key->api_key,
                    'rate_limit_per_hour' => $key->rate_limit_per_hour,
                    'is_active' => $key->is_active,
                    'expires_at' => $key->expires_at ? $key->expires_at->format('Y-m-d H:i') : 'Saves Forever',
                    'permissions' => $key->permissions,
                    'total_requests' => $key->logs_count,
                    'created_at' => $key->created_at->format('d M Y'),
                ];
            });

        return Inertia::render('Admin/ApiKeys/Index', [
            'apiKeys' => $keys,
        ]);
    }

    /**
     * Store a newly created API key.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'client_name' => 'required|string|max:100',
            'rate_limit_per_hour' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ]);

        // Generate secure 40-character API key
        $generatedKey = 'dkp_' . bin2hex(random_bytes(18));

        $apiKey = ApiKey::create([
            'client_name' => $request->client_name,
            'api_key' => $generatedKey,
            'rate_limit_per_hour' => $request->rate_limit_per_hour,
            'expires_at' => $request->expires_at,
            'permissions' => $request->permissions,
            'is_active' => true,
        ]);

        ActivityLogger::log("Membuat API Key baru untuk client: {$apiKey->client_name}", $apiKey, 'create_api_key');

        return redirect()->back()->with('success', 'API Key berhasil dibuat.');
    }

    /**
     * Regenerate the API key token.
     */
    public function regenerate(ApiKey $apiKey): RedirectResponse
    {
        $generatedKey = 'dkp_' . bin2hex(random_bytes(18));
        
        $apiKey->update([
            'api_key' => $generatedKey,
        ]);

        ActivityLogger::log("Meregenerasi token API Key untuk client: {$apiKey->client_name}", $apiKey, 'regenerate_api_key');

        return redirect()->back()->with('success', 'API Key berhasil diregenerasi.');
    }

    /**
     * Toggle the active status of the API key.
     */
    public function toggleStatus(ApiKey $apiKey): RedirectResponse
    {
        $apiKey->is_active = !$apiKey->is_active;
        $apiKey->save();

        $statusStr = $apiKey->is_active ? 'mengaktifkan' : 'menonaktifkan';
        ActivityLogger::log("Status API Key client {$apiKey->client_name} diubah menjadi " . strtoupper($statusStr), $apiKey, 'toggle_api_key');

        return redirect()->back()->with('success', "API Key berhasil di" . $statusStr);
    }

    /**
     * Remove the specified key (soft delete).
     */
    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $clientName = $apiKey->client_name;
        $apiKey->delete();

        ActivityLogger::log("Menghapus API Key client: {$clientName}", null, 'delete_api_key');

        return redirect()->back()->with('success', 'API Key berhasil dihapus.');
    }
}
