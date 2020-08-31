<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizableSetType;

// Stock Laravel controller class

class AuthorizableSetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response::json(AuthorizableSetType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $authorizationManageType = AuthorizableSetType::create($request->all());

        return Response::json($authorizationManageType);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     */
    public function show(AuthorizableSetType $authorizationManageType): JsonResponse
    {
        return Response::json($authorizationManageType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableSetType $authorizationManageType): JsonResponse
    {
        $isUpdated = $authorizationManageType->update($request->all());

        return Response::json($isUpdated ? 'true' : 'false');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableSetType $authorizationManageType): JsonResponse
    {
        $isDeleted = $authorizationManageType->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
