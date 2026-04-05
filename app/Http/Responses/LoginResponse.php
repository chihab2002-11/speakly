<?php

namespace App\Http\Responses;

use App\Support\DashboardRedirector;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // If user is not approved, redirect to pending-approval
        if (is_null($user->approved_at)) {
            return redirect()->route('pending-approval');
        }

        return redirect()->route(
            DashboardRedirector::routeNameFor($user),
            DashboardRedirector::routeParametersFor($user)
        );
    }
}
