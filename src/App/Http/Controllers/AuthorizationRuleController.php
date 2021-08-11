<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Http\Controllers;

use Asseco\JsonAuthorization\App\Contracts\AuthorizationRule;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorizationRuleController extends Controller
{
    public AuthorizationRule $authorizationRule;

    public function __construct(AuthorizationRule $authorizationRule)
    {
        $this->authorizationRule = $authorizationRule;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json($this->authorizationRule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $authorizationRule = $this->authorizationRule::query()->create($request->all());

        return response()->json($authorizationRule->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationRule $authorizationRule
     * @return JsonResponse
     */
    public function show(AuthorizationRule $authorizationRule): JsonResponse
    {
        return response()->json($authorizationRule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizationRule $authorizationRule
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizationRule $authorizationRule): JsonResponse
    {
        $authorizationRule->update($request->all());

        return response()->json($authorizationRule->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizationRule $authorizationRule
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizationRule $authorizationRule): JsonResponse
    {
        $isDeleted = $authorizationRule->delete();

        return response()->json($isDeleted ? 'true' : 'false');
    }
}
