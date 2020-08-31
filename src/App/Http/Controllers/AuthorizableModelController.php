<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizableModel;

// Stock Laravel controller class

class AuthorizableModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response::json(AuthorizableModel::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $authorizationModel = AuthorizableModel::query()->create($request->all());

        return Response::json($authorizationModel);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     */
    public function show(AuthorizableModel $authorizationModel): JsonResponse
    {
        return Response::json($authorizationModel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableModel $authorizationModel): JsonResponse
    {
        $isUpdated = $authorizationModel->update($request->all());

        return Response::json($isUpdated ? 'true' : 'false');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableModel $authorizationModel): JsonResponse
    {
        $isDeleted = $authorizationModel->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
