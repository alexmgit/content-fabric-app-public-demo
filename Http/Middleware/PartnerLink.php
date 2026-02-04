<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PartnerLink
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $partnerId = $request->query('p');
        if ($partnerId) {
            cookie()->forever('partner_id', $partnerId);
            $request->cookies->set('partner_id', $partnerId);
            $request->session()->put('partner_id', $partnerId);
        }
        return $next($request);
    }
}
