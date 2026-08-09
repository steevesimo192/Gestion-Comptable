<?php

namespace App\Http\Controllers;

use App\Services\AccountingSchemaCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SchemaExplorerController extends Controller
{
    public function index(AccountingSchemaCatalog $catalog): View
    {
        return view('schema-explorer', ['schema' => $catalog->build()]);
    }

    public function data(AccountingSchemaCatalog $catalog): JsonResponse
    {
        return response()->json($catalog->build());
    }
}
