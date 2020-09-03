<?php

declare(strict_types=1);

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Voice\JsonAuthorization\App\AuthorizableModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizableModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(AuthorizableModel::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorizationModel = AuthorizableModel::create($request->all());

        return response()->json($authorizationModel);
    }

    /**
     * Display the specified resource.
     *
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     */
    public function show(AuthorizableModel $authorizationModel)
    {
        return response()->json($authorizationModel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     */
    public function update(Request $request, AuthorizableModel $authorizationModel)
    {
        $isUpdated = $authorizationModel->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AuthorizableModel $authorizationModel
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(AuthorizableModel $authorizationModel)
    {
        $isDeleted = $authorizationModel->delete();

        return response()->json($isDeleted);
    }
}
