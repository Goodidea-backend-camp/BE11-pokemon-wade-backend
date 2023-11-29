<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $exception)
    {
        
        if ($exception instanceof ModelNotFoundException) {
            return response()->json(['error' => config('error_messages.general.RESOURCE_NOT_FOUND')], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof AuthorizationException) {
            
            return response()->json(['error' => config('error_messages.general.UNAUTHORIZED')], Response::HTTP_FORBIDDEN);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException && $request->expectsJson()) {
            return response()->json($exception->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['error' => config('error_messages.general.METHOD_NOT_ALLOWED')], Response::HTTP_METHOD_NOT_ALLOWED);
        }


        return parent::render($request, $exception);



        
    
       
        
    }

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
{
    $this->renderable(function (NotFoundHttpException $e, $request) {
        // 匹配 /api/{model_name}/{model_id} 這種路徑的異常
    
        if (preg_match('/^\/api\/(\w+)\/\w+/', $request->getPathInfo(), $matches)) {
            $modelName = $matches[1]; // 
            return response()->json(["message" => config('error_messages.general.DATA_NOT_FOUND')], Response::HTTP_NOT_FOUND);
        }
        

        // 其他路徑的異常
        return response()->json(["message" => "error_messages.general.THE_PAGE_NOT_FOUND"], Response::HTTP_NOT_FOUND);
    });
}



    
}
