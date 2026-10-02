<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\PublishedContentIndexRequest;
use App\Services\Client\PublishedContentService;

class PublishedContentController extends Controller
{
    protected $service;

    public function __construct(PublishedContentService $service)
    {
        $this->service = $service;
    }

    public function index(PublishedContentIndexRequest $request)
    {
        $data = $this->service->getIndexData($request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('client.published-content-list', $data)->render(),
            ]);
        }

        return view('client.published-content', $data);
    }
}

