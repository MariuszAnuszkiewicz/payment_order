<?php

namespace App\Http\Controllers;

use App\Services\BlueMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    private $blueMediaService;

    public function __construct(BlueMediaService $blueMediaService)
    {
        $this->blueMediaService = $blueMediaService;
    }

    public function index()
    {
        return view('payments.instant_payment');
    }

    public function getInitUrl(Request $request) : JsonResponse
    {
        if ($request->ajax()) {
            $data = $this->blueMediaService->getInitUrl($request);
            return response()->json([
                'url' => $data[0],
                'orderId' => $data[1],
            ]);
        }
    }

    public function handleItn(Request $request): Response
    {
        return response($this->blueMediaService->handleItn($request->transactions))
            ->header('Content-Type', 'text/xml');
    }
}
