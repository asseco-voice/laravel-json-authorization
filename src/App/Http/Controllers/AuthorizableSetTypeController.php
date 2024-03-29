<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Http\Controllers;

use Asseco\JsonAuthorization\App\Contracts\AuthorizableSetType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorizableSetTypeController extends Controller
{
    public AuthorizableSetType $authorizableSetType;

    public function __construct(AuthorizableSetType $authorizableSetType)
    {
        $this->authorizableSetType = $authorizableSetType;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json($this->authorizableSetType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $authorizableSetType = $this->authorizableSetType::query()->create($request->all());

        return response()->json($authorizableSetType->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param  AuthorizableSetType  $authorizableSetType
     * @return JsonResponse
     */
    public function show(AuthorizableSetType $authorizableSetType): JsonResponse
    {
        return response()->json($authorizableSetType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  AuthorizableSetType  $authorizableSetType
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableSetType $authorizableSetType): JsonResponse
    {
        $authorizableSetType->update($request->all());

        return response()->json($authorizableSetType->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AuthorizableSetType  $authorizableSetType
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function destroy(AuthorizableSetType $authorizableSetType): JsonResponse
    {
        $isDeleted = $authorizableSetType->delete();

        return response()->json($isDeleted ? 'true' : 'false');
    }
}
