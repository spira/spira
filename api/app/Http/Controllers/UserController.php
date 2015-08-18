<?php namespace App\Http\Controllers;

use App;
use App\Extensions\Lock\Manager;
use App\Http\Transformers\EloquentModelTransformer;
use App\Models\SocialLogin;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Repository\Validation\ValidationException;
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
use App\Repositories\UserRepository as Repository;

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
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  Repository $repository
     * @param  Lock $lock
     * @param  JWTAuth $jwtAuth
     * @param  Request $request
     * @param  EloquentModelTransformer $transformer
     */
    public function __construct(
        Repository $repository,
        Lock $lock,
        JWTAuth $jwtAuth,
        Request $request,
        EloquentModelTransformer $transformer
    ) {
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;
        $this->permissions($request);
        parent::__construct($repository, $transformer);
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

        $this->middleware('permission:readAll', ['only' => 'getAll']);
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
    public function putOne($id, Request $request)
    {
        // Extract the credentials
        $credential = $request->get('_user_credential', []);

        // Extract the profile
        $profile = $request->get('_user_profile', []);

        // Set new users to guest
        $request->merge(['user_type' =>'guest']);

        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);
        if ($this->repository->exists($id)) {
            throw new ValidationException(
                new MessageBag(['uuid' => 'Users are not permitted to be replaced.'])
            );
        }

        $model = $this->repository->getNewModel();
        $model->fill($request->all());
        $this->repository->save($model);

        // Finally create the credentials
        $model->setCredential(new UserCredential($credential));

        // Finally create the profile if it exists
        if (!empty($profile)) {
            $model->setProfile(new UserProfile($profile));
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($model, $this->transformer);
    }

    /**
     * Patch an entity.
     *
     * @param  string   $id
     * @param  Request  $request
     * @return Response
     */
    public function patchOne($id, Request $request)
    {
        $this->validateId($id, $this->getKeyName(), $this->validateRequestRule);
        $model = $this->repository->find($id);

        // Check if the email is being changed, and initialize confirmation
        $email = $request->get('email');
        if ($email && $model->email != $email) {

            $emailConfirmToken = $model->createEmailConfirmToken($email, $model->email);
            $loginToken = $this->repository->makeLoginToken($model->user_id);

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

        $model->fill($request->except('email'));
        $this->repository->save($model);

        // Extract the profile and update if necessary
        $profileUpdateDetails = $request->get('_user_profile', []);
        if (!empty($profileUpdateDetails)) {
            $profile = UserProfile::findOrNew($id); // The user profile may not exist for the user
            $profile->fill($profileUpdateDetails);
            $model->setProfile($profile);
        }

        // Extract the credentials and update if necessary
        $credentialUpdateDetails = $request->get('_user_credential', []);
        if (!empty($credentialUpdateDetails)) {
            $credentials = UserCredential::findOrNew($id);
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
        try {
            $user = $this->repository->findByEmail($email);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('Sorry, this email does not exist in our database.', $e);
        }

        $loginToken = $this->repository->makeLoginToken($user->user_id);
        $this->dispatch(new SendPasswordResetEmail($user, $loginToken));

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

        $jwtAuth = App::make('Tymon\JWTAuth\JWTAuth');

        $token = $jwtAuth->fromUser($this->repository->find($id));

        return $this->getResponse()->header('Authorization-Update', $token)->noContent();
    }

    /**
     * Get full user details
     *
     * @param string $id
     * @return \Spira\Responder\Response\ApiResponse
     */
    public function getOne($id)
    {
        $this->validateId($id, $this->getKeyName());

        $user = User::find($id);

        $userData = $user->toArray();

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

        if (is_null($user->userProfile)) {
            $newProfile = new UserProfile;
            $newProfile->user_id = $id;
            $user->setProfile($newProfile);
            $userData['_user_profile'] = $newProfile->toArray();
        } else {
            $userData['_user_profile'] = $user->userProfile->toArray();
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($userData);
    }
}
