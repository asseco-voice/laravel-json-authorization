<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Http\Controllers;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableModel;
use Illuminate\Http\JsonResponse;

class AuthorizableModelController extends Controller
{
    public AuthorizableModel $authorizableModel;

    public function __construct(AuthorizableModel $authorizableModel)
    {
        $this->authorizableModel = $authorizableModel;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json($this->authorizableModel::all());
    }

    /**
     * Display the specified resource.
     *
     * @param  AuthorizableModel  $authorizableModel
     * @return JsonResponse
     */
    public function show(AuthorizableModel $authorizableModel): JsonResponse
    {
        return response()->json($authorizableModel);
    }
}
