<?php

namespace App\Http\Controllers;

use App\Models\Endpoint;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EndpointController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        $endpoint = Endpoint::findForRequest($method, $path);

        if ($endpoint) {
            return $endpoint->toResponse();
        }

        $allowedMethods = Endpoint::allowedMethodsForPath($path);

        if ($allowedMethods->isNotEmpty()) {
            return response('', Response::HTTP_METHOD_NOT_ALLOWED)
                ->header('Allow', $allowedMethods->implode(', '));
        }

        abort(Response::HTTP_NOT_FOUND, "No endpoint registered for {$path}.");
    }
}
