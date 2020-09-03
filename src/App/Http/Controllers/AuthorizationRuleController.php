<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizationRule;

class AuthorizationRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return Response::json(AuthorizationRule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $authorizationRule = AuthorizationRule::query()->create($request->all());

        return Response::json($authorizationRule);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationRule $authorizationRule
     * @return JsonResponse
     */
    public function show(AuthorizationRule $authorizationRule): JsonResponse
    {
        return Response::json($authorizationRule);
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
        $isUpdated = $authorizationRule->update($request->all());

        return Response::json($isUpdated ? 'true' : 'false');
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

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
