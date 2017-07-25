<?php

namespace App\Http\Controllers\Auth;

use App\Http\Middleware\Authenticate;
use Dingo\Api\Auth\Provider\Authorization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Exception\HttpResponseException;

class AuthController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt(
                $this->getCredentials($request)
            )) {
                return $this->onUnauthorized();
            }
        } catch (JWTException $e) {
            // Something went wrong whilst attempting to encode the token
            return $this->onJwtGenerationError();
        }

        // All good so return the token
        $user = $this->getUserForLogin($token);

        return $this->onAuthorized($token , $user);

    }



    /**
     * What response should be returned on bad request.
     *
     * @return JsonResponse
     */
    protected function onBadRequest()
    {
        return new JsonResponse([
            'message' => 'invalid_credentials'
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * What response should be returned on invalid credentials.
     *
     * @return JsonResponse
     */
    protected function onUnauthorized()
    {
        return new JsonResponse([
            'message' => 'invalid_credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * What response should be returned on error while generate JWT.
     *
     * @return JsonResponse
     */
    protected function onJwtGenerationError()
    {
        return new JsonResponse([
            'message' => 'could_not_create_token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * What response should be returned on authorized.
     *
     * @return JsonResponse
     */
    protected function onAuthorized($token, $user)
    {
        $person = $user->getPerson()->first();

        return new JsonResponse([
             'message' => 'token_generated',
             'token' => $token,
             'user' => $user,
             'person' => $person

        ]);
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    protected function getCredentials(Request $request)
    {

        $header = $request->header('Authorization');
        $loginarray = explode(':',base64_decode(substr($header,6)));

        $login = [
            'email'=> $loginarray[0],
            'password'=> $loginarray[1]

        ];


        return ($login);
    }

    /**
     * Invalidate a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteInvalidate()
    {
        $token = JWTAuth::parseToken();

        $token->invalidate();

        return new JsonResponse(['message' => 'token_invalidated']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function patchRefresh()
    {
        $token = JWTAuth::parseToken();

        $newToken = $token->refresh();

        return new JsonResponse([

                'token' => $newToken

        ]);
    }

    /**
     * Get authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUser()
    {
        return new JsonResponse([
            'message' => 'authenticated_user',
            'user' => JWTAuth::parseToken()->authenticate()
        ]);
    }

    public function getUserForLogin($token)
    {
        $user = JWTAuth::toUser($token);

        return $user;

    }
}
