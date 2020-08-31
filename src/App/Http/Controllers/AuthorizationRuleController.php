<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Voice\JsonAuthorization\App\AuthorizationRule;

// Stock Laravel controller class

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
        $authorization = AuthorizationRule::create($request->all());

        return Response::json($authorization);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     */
    public function show(AuthorizationRule $authorization): JsonResponse
    {
        return Response::json($authorization);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizationRule $authorization): JsonResponse
    {
        $isUpdated = $authorization->update($request->all());

        return Response::json($isUpdated ? 'true' : 'false');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizationRule $authorization): JsonResponse
    {
        $isDeleted = $authorization->delete();

        return Response::json($isDeleted ? 'true' : 'false');
    }
}
