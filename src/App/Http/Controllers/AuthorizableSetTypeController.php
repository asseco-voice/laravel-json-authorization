<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Voice\JsonAuthorization\App\AuthorizableSetType;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizableSetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(AuthorizableSetType::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorizationManageType = AuthorizableSetType::create($request->all());

        return response()->json($authorizationManageType);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     */
    public function show(AuthorizableSetType $authorizationManageType)
    {
        return response()->json($authorizationManageType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableSetType $authorizationManageType)
    {
        $isUpdated = $authorizationManageType->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableSetType $authorizationManageType
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableSetType $authorizationManageType)
    {
        $isDeleted = $authorizationManageType->delete();

        return response()->json($isDeleted);
    }
}
