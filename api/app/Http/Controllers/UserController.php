<?php namespace App\Http\Controllers;

use App;
use App\Extensions\Lock\Manager;
use App\Http\Transformers\EloquentModelTransformer;
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
use Illuminate\Contracts\Cache\Repository as Cache;

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
     * Cache repository.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Assign dependencies.
     *
     * @param  User $model
     * @param  Lock $lock
     * @param  JWTAuth $jwtAuth
     * @param  Request $request
     * @param  EloquentModelTransformer $transformer
     * @param  Cache $cache
     */
    public function __construct(
        User $model,
        Lock $lock,
        JWTAuth $jwtAuth,
        Request $request,
        EloquentModelTransformer $transformer,
        Cache $cache
    ) {
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;
        $this->cache = $cache;
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

        $this->validateId($id, $this->getModel()->getKeyName(), $this->validateIdRule);
        if ($this->getModel()->find($id)) {
            throw new ValidationException(
                new MessageBag(['uuid' => 'Users are not permitted to be replaced.'])
            );
        }

        /** @var User $model */
        $model = $this->getModel()->newInstance();
        $model->fill($request->all());
        $model->save();

        // Finally create the credentials
        $model->setCredential(new UserCredential($credential));

        // Finally create the profile if it exists
        if (!empty($profile)) {
            $model->setProfile(new UserProfile($profile));
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->createdItem($model);
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
        /** @var User $model */
        $model = $this->findOrFailEntity($id);

        // Check if the email is being changed, and initialize confirmation
        $email = $request->get('email');
        if ($email && $model->email != $email) {
            $token = $model->makeConfirmationToken($email, $this->cache);
            $this->dispatch(new SendEmailConfirmationEmail($model, $email, $token));
            $request->merge(['email_confirmed' => null]);
        }

        // Change in email has been confirmed, set the new email
        if ($token = $request->headers->get('email-confirm-token')) {
            if (!$email = $this->cache->pull('email_confirmation_'.$token)) {
                throw new ValidationException(
                    new MessageBag(['email_confirmed' => 'The email confirmation token is not valid.'])
                );
            } else {
                $model->email = $email;
            }
        }

        $model->fill($request->except('email'));
        $model->save();

        // Extract the profile and update if necessary
        $profileUpdateDetails = $request->get('_user_profile', []);
        if (!empty($profileUpdateDetails)) {
            /** @var UserProfile $profile */
            $profile = UserProfile::findOrNew($id); // The user profile may not exist for the user
            $profile->fill($profileUpdateDetails);
            $model->setProfile($profile);
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
     * Get the user's profile.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function getProfile($id)
    {
        $this->validateId($id, $this->getModel()->getKeyName());

        $userProfile = UserProfile::find($id);

        if (is_null($userProfile)) {
            return $this->getResponse()->noContent();
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($userProfile);
    }
}
