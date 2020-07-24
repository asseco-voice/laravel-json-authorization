<?php

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Voice\JsonAuthorization\App\AuthorizationModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizationModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(AuthorizationModel::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorizationModel = AuthorizationModel::create($request->all());

        return response()->json($authorizationModel);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizationModel $authorizationModel
     * @return JsonResponse
     */
    public function show(AuthorizationModel $authorizationModel)
    {
        return response()->json($authorizationModel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizationModel $authorizationModel
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizationModel $authorizationModel)
    {
        $isUpdated = $authorizationModel->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizationModel $authorizationModel
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizationModel $authorizationModel)
    {
        $isDeleted = $authorizationModel->delete();

        return response()->json($isDeleted);
    }
}
