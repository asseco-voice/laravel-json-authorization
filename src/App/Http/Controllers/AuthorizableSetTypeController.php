<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizableSetType;

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
        $authorizableSetType = AuthorizableSetType::query()->create($request->all());

        return Response::json($authorizableSetType);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableSetType $authorizableSetType
     * @return JsonResponse
     */
    public function show(AuthorizableSetType $authorizableSetType): JsonResponse
    {
        return Response::json($authorizableSetType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableSetType $authorizableSetType
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableSetType $authorizableSetType): JsonResponse
    {
        $isUpdated = $authorizableSetType->update($request->all());

        return Response::json($isUpdated ? 'true' : 'false');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableSetType $authorizableSetType
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableSetType $authorizableSetType): JsonResponse
    {
        $isDeleted = $authorizableSetType->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
