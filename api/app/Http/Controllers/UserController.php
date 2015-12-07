<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\UserTransformer;
use App\Models\User;
use App\Models\SocialLogin;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use App\Jobs\SendPasswordResetEmail;
use Illuminate\Contracts\Auth\Guard;
use App\Jobs\SendEmailConfirmationEmail;
use Laravel\Lumen\Routing\DispatchesJobs;
use Spira\Auth\Driver\Guard as SpiraGuard;
use Spira\Core\Controllers\EntityController;
use Spira\Core\Responder\Response\ApiResponse;
use Spira\Core\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends EntityController
{
    use DispatchesJobs;

    /**
     * @var SpiraGuard
     */
    protected $auth;

    protected $permissionsEnabled = true;

    protected $defaultRole = false;

    /**
     * Assign dependencies.
     *
     * @param  User $model
     * @param  Guard $auth
     * @param  UserTransformer $transformer
     */
    public function __construct(
        User $model,
        Guard $auth,
        UserTransformer $transformer
    ) {
        parent::__construct($model, $transformer);
        $this->auth = $auth;
    }

    /**
     * Put an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function putOne(Request $request, $id)
    {
        if ($this->getModel()->find($id)) {
            throw new ValidationException(
                new MessageBag(['uuid' => 'Users are not permitted to be replaced.'])
            );
        }

        /** @var User $model */
        $model = $this->getModel()->newInstance();
        $this->validateRequest($request->json()->all(), $this->getValidationRules($id));
        $model->fill($request->json()->all());
        $model->save();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->created()
            ->item($model);
    }

    /**
     * Patch an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function patchOne(Request $request, $id)
    {
        /** @var User $model */
        $model = $this->findOrFailEntity($id);
        $this->authorize(static::class.'@patchOne', ['model' => $model]);
        // Check if the email is being changed, and initialize confirmation
        $email = $request->input('email');
        if ($email && $model->email != $email) {
            $emailConfirmToken = $model->createEmailConfirmToken($email, $model->email);
            $loginToken = $model->makeLoginToken($model->user_id);

            $this->dispatch(new SendEmailConfirmationEmail($model, $email, $emailConfirmToken, $loginToken));
            $request->merge(['email_confirmed' => null]);
        }

        // Change in email has been confirmed, set the new email
        if ($token = $request->headers->get('email-confirm-token')) {
            if (! $email = $model->getEmailFromToken($token)) {
                throw new ValidationException(
                    new MessageBag(['email_confirmed' => 'The email confirmation token is not valid.'])
                );
            } else {
                $model->email = $email;
            }
        }
        $this->checkEntityIdMatchesRoute($request, $id, $this->getModel(), false);
        $this->validateRequest($request->except('email'), $this->getValidationRules($id), null, true);
        $model->fill($request->except('email'));
        $model->save();

        $response = $this->getResponse();

        //If the update is requested by the user, send an updated token
        if ($request->user()->getKey() == $model->getKey()) {
            $response->header('Authorization-Update', $this->auth->generateToken($model));
        }

        return $response->noContent();
    }

    /**
     * Reset user password.
     *
     * @param $email
     * @return Response
     */
    public function resetPassword($email)
    {
        /** @var User $model */
        $model = $this->getModel();

        try {
            $user = $model->findByEmail($email);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Sorry, this email does not exist in our database.', $e);
        }

        $token = $model->makeLoginToken($user->user_id);
        $this->dispatch(new SendPasswordResetEmail($user, $token));

        return $this->getResponse()->noContent(Response::HTTP_ACCEPTED);
    }

    /**
     * Deletes a social login entry from the database.
     *
     * @param Request $request
     * @param $id
     * @param $provider
     * @return ApiResponse
     * @throws \Exception
     */
    public function unlinkSocialLogin(Request $request, $id, $provider)
    {
        if (! $socialLogin = SocialLogin::where('user_id', '=', $id)
            ->where('provider', '=', $provider)
            ->first()) {
            throw new NotFoundHttpException('Sorry, this provider does not exist for this user.');
        }

        $socialLogin->delete();
        /** @var User $user */
        $user = User::find($id);
        $this->auth->login($user, true);

        $response = $this->getResponse();

        //If the update is requested by the user, send an updated token
        if ($request->user()->getKey() == $user->getKey()) {
            $response->header('Authorization-Update', $this->auth->generateToken($user));
        }

        return $response->noContent();
    }
}
