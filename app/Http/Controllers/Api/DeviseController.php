<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviseRequest;
use App\Http\Requests\UpdateDeviseRequest;
use App\Http\Resources\DeviseResource;
use App\Models\Devise;
use App\Services\DeviseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeviseController extends Controller
{
    //
     public function __construct(
        private DeviseService $service
    ) {
    }

    /**
     * GET /api/devises
     */
    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 25),
            100
        );

        $search = $request->string('search')->toString();

        $actif = $request->has('actif')
            ? $request->boolean('actif')
            : null;

        $devises = $this->service->paginate(
            $perPage,
            $search ?: null,
            $actif
        );

        return DeviseResource::collection($devises);
    }

    /**
     * POST /api/devises
     */
    //ce code va envoyer 201
    public function store(StoreDeviseRequest $request)
    {
        $devise = $this->service->create(
            $request->validated()
        );

        return (new DeviseResource($devise))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * GET /api/devises/{devise}
     */
    public function show(Devise $devise)
    {
        return new DeviseResource($devise);
    }

    /**
     * PUT/PATCH /api/devises/{devise}
     */
    public function update(
        UpdateDeviseRequest $request,
        Devise $devise
    ) {
        $devise = $this->service->update(
            $devise,
            $request->validated()
        );

        return new DeviseResource($devise);
    }

    /**
     * DELETE /api/devises/{devise}
     */
    //ce code va retourner le code http 204
    public function destroy(Devise $devise)
    {
        $this->service->delete($devise);

        return response()->noContent();
    }
}
