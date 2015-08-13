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
            $token = $model->createEmailConfirmToken($email, $model->email);

            $this->dispatch(new SendEmailConfirmationEmail($model, $email, $token));
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

        $token = $this->repository->makeLoginToken($user->user_id);
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
        $this->validateId($id, $this->getKeyName());

        $userProfile = UserProfile::find($id);

        if (is_null($userProfile)) {
            return $this->getResponse()->noContent();
        }

        return $this->getResponse()
            ->transformer($this->transformer)
            ->item($userProfile);
    }
}
