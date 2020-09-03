<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Voice\JsonAuthorization\App\AuthorizationRule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizationRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(AuthorizationRule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorization = AuthorizationRule::create($request->all());

        return response()->json($authorization);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     */
    public function show(AuthorizationRule $authorization)
    {
        return response()->json($authorization);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizationRule $authorization)
    {
        $isUpdated = $authorization->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizationRule $authorization
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizationRule $authorization)
    {
        $isDeleted = $authorization->delete();

        return response()->json($isDeleted);
    }
}
