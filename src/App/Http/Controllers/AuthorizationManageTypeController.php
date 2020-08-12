<?php

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Voice\JsonAuthorization\App\AuthorizationManageType;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizationManageTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(AuthorizationManageType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorizationManageType = AuthorizationManageType::create($request->all());

        return response()->json($authorizationManageType);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationManageType $authorizationManageType
     * @return JsonResponse
     */
    public function show(AuthorizationManageType $authorizationManageType)
    {
        return response()->json($authorizationManageType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizationManageType $authorizationManageType
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizationManageType $authorizationManageType)
    {
        $isUpdated = $authorizationManageType->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizationManageType $authorizationManageType
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizationManageType $authorizationManageType)
    {
        $isDeleted = $authorizationManageType->delete();

        return response()->json($isDeleted);
    }
}
