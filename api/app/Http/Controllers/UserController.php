<?php namespace App\Http\Controllers;

use App;
use App\Extensions\JWTAuth\JWTManager;
use App\Extensions\Lock\Manager;
use App\Http\Transformers\EloquentModelTransformer;
use App\Models\SocialLogin;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Model\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Models\UserCredential;
use Illuminate\Support\MessageBag;
use App\Jobs\SendPasswordResetEmail;
use App\Jobs\SendEmailConfirmationEmail;
use Laravel\Lumen\Routing\DispatchesJobs;
use App\Extensions\Lock\Manager as Lock;

class UserController extends EntityController
{
    use DispatchesJobs;

    /**
     * Permission Lock Manager.
     *
     * @var Manager
     */
    protected $lock;

    /**
     * JWT Auth.
     *
     * @var \App\Extensions\JWTAuth\JWTAuth|JWTManager
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  User $model
     * @param  Lock $lock
     * @param  JWTAuth $jwtAuth
     * @param  Request $request
     * @param  EloquentModelTransformer $transformer
     */
    public function __construct(
        User $model,
        Lock $lock,
        JWTAuth $jwtAuth,
        Request $request,
        EloquentModelTransformer $transformer
    ) {
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;
        $this->permissions($request);
        parent::__construct($model, $transformer);
    }

    /**
     * Set permissions to be used in the controller.
     *
     * @param  Request  $request
     * @return void
     */
    public function permissions(Request $request)
    {
        $this->lock->setRole(User::$userTypes);
        $user = $this->jwtAuth->user();
        $owner = [User::class, 'userIsOwner', $user, last($request->segments())];

        $this->lock->role(User::USER_TYPE_ADMIN)->permit(['readAll', 'readOne', 'update', 'delete']);
        $this->lock->role(User::USER_TYPE_GUEST)->permit(['readOne', 'update'], [$owner]);

        $this->middleware('permission:readAll', ['only' => 'getAllPaginated']);
        $this->middleware('permission:readOne', ['only' => 'getOne']);
        $this->middleware('permission:update', ['only' => 'patchOne']);
        $this->middleware('permission:delete', ['only' => 'deleteOne']);
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
        // Extract the credentials
        $credential = $request->input('_user_credential', []);

        // Extract the profile
        $profile = $request->input('_user_profile', []);

        // Set new users to guest
        $request->merge(['user_type' =>'guest']);

        if ($this->getModel()->find($id)) {
            throw new ValidationException(
                new MessageBag(['uuid' => 'Users are not permitted to be replaced.'])
            );
        }

        /** @var User $model */
        $model = $this->getModel()->newInstance();
        $this->validateRequest($request->all(), $this->getValidationRules());
        $model->fill($request->all());
        $model->save();

        // Finally create the credentials
        $this->validateRequest($credential, UserCredential::getValidationRules());
        $model->setCredential(new UserCredential($credential));

        // Finally create the profile if it exists
        if (!empty($profile)) {
            $this->validateRequest($profile, UserProfile::getValidationRules());
            $model->setProfile(new UserProfile($profile));
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
            if (!$email = $model->getEmailFromToken($token)) {
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
        if (!empty($profileUpdateDetails)) {
            /** @var UserProfile $profile */
            $profile = UserProfile::findOrNew($id); // The user profile may not exist for the user
            $this->validateRequest($profileUpdateDetails, UserProfile::getValidationRules(), $profile->exists);
            $profile->fill($profileUpdateDetails);
            $model->setProfile($profile);
        }

        /** @var \Tymon\JWTAuth\JWTAuth $jwtAuth */
        // Extract the credentials and update if necessary
        $credentialUpdateDetails = $request->input('_user_credential', []);
        if (!empty($credentialUpdateDetails)) {
            // Invalidate token for the user when user changes their password
            if ($this->jwtAuth->user()->user_id == $model->user_id) {
                $token = $this->jwtAuth->getTokenFromRequest();
                $this->jwtAuth->invalidate($token);
            }

            $credentials = UserCredential::findOrNew($id);
            /** @var UserCredential $credentials */
            $credentials->fill($credentialUpdateDetails);
            $model->setCredential($credentials);
        }

        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        $token = $jwtAuth->fromUser($model);

        return $this->getResponse()->header('Authorization-Update', $token)->noContent();
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
     * Deletes a social login entry from the database
     *
     * @param $id
     * @param $provider
     * @return \Spira\Responder\Response\ApiResponse
     */
    public function unlinkSocialLogin($id, $provider)
    {
        if (!$socialLogin = SocialLogin::where('user_id', '=', $id)
            ->where('provider', '=', $provider)
            ->first()) {
            throw new NotFoundHttpException('Sorry, this provider does not exist for this user.');
        }

        $socialLogin->delete();
        /** @var \Tymon\JWTAuth\JWTAuth $jwtAuth */
        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        $token = $jwtAuth->fromUser(User::find($id));

        return $this->getResponse()->header('Authorization-Update', $token)->noContent();
    }

    /**
     * Get full user details
     *
     * @param Request $request
     * @param string $id
     * @return \Spira\Responder\Response\ApiResponse
     */
    public function getOne(Request $request, $id)
    {
        /** @var User $user */
        $user = User::find($id);

        $userData = $this->transformer->transformItem($user);

        if (is_null($user->userCredential)) {
            $userData['_user_credential'] = false;
        } else {
            $userData['_user_credential'] = $user->userCredential->toArray();
        }

        if (is_null($user->socialLogins)) {
            $userData['_social_logins'] = false;
        } else {
            $userData['_social_logins'] = $user->socialLogins->toArray();
        }

        $userProfile = null;

        if (is_null($user->userProfile)) {
            $userProfile = new UserProfile;
            $userProfile->user_id = $id;
            $user->setProfile($userProfile);
        } else {
            $userProfile = $user->userProfile;
        }

        $userData['_user_profile'] = $this->transformer->transformItem($userProfile);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($userData);
    }
}
