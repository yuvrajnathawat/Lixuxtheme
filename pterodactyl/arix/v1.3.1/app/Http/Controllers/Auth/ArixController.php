<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;

class ArixController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
