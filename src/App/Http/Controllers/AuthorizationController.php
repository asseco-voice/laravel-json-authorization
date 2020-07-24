<?php

namespace Voice\JsonAuthorization\App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Voice\JsonAuthorization\App\Authorization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Stock Laravel controller class

class AuthorizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Authorization::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $authorization = Authorization::create($request->all());

        return response()->json($authorization);
    }

    /**
     * Display the specified resource.
     *
     * @param Authorization $authorization
     * @return JsonResponse
     */
    public function show(Authorization $authorization)
    {
        return response()->json($authorization);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Authorization $authorization
     * @return JsonResponse
     */
    public function update(Request $request, Authorization $authorization)
    {
        $isUpdated = $authorization->update($request->all());

        return response()->json($isUpdated);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Authorization $authorization
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Authorization $authorization)
    {
        $isDeleted = $authorization->delete();

        return response()->json($isDeleted);
    }
}
