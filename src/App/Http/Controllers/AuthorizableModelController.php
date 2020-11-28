<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Asseco\JsonAuthorization\App\AuthorizableModel;

class AuthorizableModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json(AuthorizableModel::all());
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableModel $authorizableModel
     * @return JsonResponse
     */
    public function show(AuthorizableModel $authorizableModel): JsonResponse
    {
        return response()->json($authorizableModel);
    }
}
