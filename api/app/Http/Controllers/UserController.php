<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SocialLogin;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Models\UserCredential;
use Illuminate\Support\MessageBag;
use App\Jobs\SendPasswordResetEmail;
use Illuminate\Contracts\Auth\Guard;
use App\Jobs\SendEmailConfirmationEmail;
use Laravel\Lumen\Routing\DispatchesJobs;
use Spira\Auth\Driver\Guard as SpiraGuard;
use Symfony\Component\HttpFoundation\Response;
use Spira\Model\Validation\ValidationException;
use App\Http\Transformers\EloquentModelTransformer;
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
     * @param  Request $request
     * @param  EloquentModelTransformer $transformer
     */
    public function __construct(
        User $model,
        Guard $auth,
        Request $request,
        EloquentModelTransformer $transformer
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
        $this->validateRequest($request->json()->all(), $this->getValidationRules());
        $model->fill($request->json()->all());
        $model->save();

        // Finally create the credentials
        if ($credential = $request->input('_user_credential', null)) {
            $this->validateRequest($credential, UserCredential::getValidationRules());
            $model->setCredential(new UserCredential($credential));
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdItem($model);
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
        $this->validateRequest($request->except('email'), $this->getValidationRules(), true);
        $model->fill($request->except('email'));
        $model->save();

        // Extract the profile and update if necessary
        $profileUpdateDetails = $request->input('_user_profile', []);
        if (! empty($profileUpdateDetails)) {
            /** @var UserProfile $profile */
            $profile = UserProfile::findOrNew($id); // The user profile may not exist for the user
            $this->validateRequest($profileUpdateDetails, UserProfile::getValidationRules(), $profile->exists);
            $profile->fill($profileUpdateDetails);
            $model->setProfile($profile);
        }

        // Extract the credentials and update if necessary
        $credentialUpdateDetails = $request->input('_user_credential');
        if ($credentialUpdateDetails) {
            // Invalidate token for the user when user changes their password
            if ($request->user()->user_id == $model->user_id) {
                $this->auth->logout();
            }

            /* @var UserCredential $credentials */
            $credentials = UserCredential::findOrNew($id)->fill($credentialUpdateDetails);
            $model->setCredential($credentials);
        }

        return $this->getResponse()->header('Authorization-Update', $this->auth->generateToken($model))->noContent();
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
     * @param $id
     * @param $provider
     * @return \Spira\Responder\Response\ApiResponse
     */
    public function unlinkSocialLogin($id, $provider)
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

        return $this->getResponse()
            ->header('Authorization-Update', $this->auth->generateToken($user))
            ->noContent();
    }
}
