<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizableModel;

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
        $authorizableModel = AuthorizableModel::query()->create($request->all());

        return Response::json($authorizableModel->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableModel $authorizableModel
     * @return JsonResponse
     */
    public function show(AuthorizableModel $authorizableModel): JsonResponse
    {
        return Response::json($authorizableModel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableModel $authorizableModel
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableModel $authorizableModel): JsonResponse
    {
        $authorizableModel->update($request->all());

        return Response::json($authorizableModel->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableModel $authorizableModel
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableModel $authorizableModel): JsonResponse
    {
        $isDeleted = $authorizableModel->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
