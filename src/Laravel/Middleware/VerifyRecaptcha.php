<?php

namespace WebMavens\AutoGoogleRecaptcha\Laravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use WebMavens\AutoGoogleRecaptcha\Laravel\Facades\AutoReCaptcha;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next)
    {
        if (AutoReCaptcha::isEnabled()) {
            // Verify Recaptcha for non-admin routes and form submissions for this methods
            $allowedMethod = config('auto-google-recaptcha.options.allowed_methods');
            $excludeRoutes = config('auto-google-recaptcha.options.excluded_routes');
            
            if (in_array($request->method(), $allowedMethod) && 
            !Str::is($excludeRoutes, $request->route()->getName())) 
            {
                $validator = Validator::make($request->all(), [
                    'g-recaptcha-response' => 'required|recaptcha'
                ]);

                if($validator->fails()) {
                    Log::error('Recaptcha failed', [
                        'method' => $request->method(),
                        'route' => $request->route()->getName(),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'data' => $request->all()
                    ]);
                    return abort(403, 'Please verify that you are not a robot.');
                }
            }
        }

        return $next($request);
    }
}
