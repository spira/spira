<?php namespace App\Http\Controllers;

use App;
use App\Extensions\Lock\Manager;
use App\Http\Transformers\EloquentModelTransformer;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Models\UserCredential;
use Illuminate\Support\MessageBag;
use App\Jobs\SendPasswordResetEmail;
use App\Exceptions\ValidationException;
use App\Jobs\SendEmailConfirmationEmail;
use Laravel\Lumen\Routing\DispatchesJobs;
use App\Extensions\Lock\Manager as Lock;
use App\Repositories\UserRepository as Repository;
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
     * @param  Repository $repository
     * @param  Lock $lock
     * @param  JWTAuth $jwtAuth
     * @param  Request $request
     * @param  EloquentModelTransformer $transformer
     * @param  Cache $cache
     */
    public function __construct(
        Repository $repository,
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
        parent::__construct($repository,$transformer);
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

        // Set new users to guest
        $request->merge(['user_type' =>'guest']);

        $this->validateId($id, $this->getKeyName(),$this->validateRequestRule);
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

        if ($request->get('email_confirmed')) {
            $token = $request->headers->get('email-confirm-token');
            if (!$email = $this->cache->pull('email_confirmation_'.$token)) {
                throw new ValidationException(
                    new MessageBag(['email_confirmed' => 'The email confirmation token is not valid.'])
                );
            }
        }

        // Check if the email is being changed, and initialize confirmation
        $email = $request->get('email');
        if ($email && $model->email != $email) {
            $token = $model->makeConfirmationToken($email, $this->cache);
            $this->dispatch(new SendEmailConfirmationEmail($model, $email, $token));
            $request->merge(['email_confirmed' => null]);
        }

        $model->fill($request->all());
        $this->repository->save($model);

        return $this->getResponse()->noContent();
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
}
