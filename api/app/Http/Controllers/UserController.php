<?php namespace App\Http\Controllers;

use App;
use App\Models\User;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use App\Models\UserCredential;
use Illuminate\Support\MessageBag;
use App\Exceptions\ValidationException;
use App\Jobs\SendEmailConfirmationEmail;
use App\Extensions\Lock\Manager as Lock;
use Laravel\Lumen\Routing\DispatchesJobs;
use App\Repositories\UserRepository as Repository;
use App\Http\Validators\UserValidator as Validator;
use Spira\Responder\Contract\ApiResponderInterface as Responder;

class UserController extends ApiController
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
     * @var Tymon\JWTAuth\JWTAuth
     */
    protected $jwtAuth;

    /**
     * Assign dependencies.
     *
     * @param  Repository  $repository
     * @param  Lock        $lock
     * @param  JWTAuth     $jwtAuth
     * @param  Request     $request
     * @param  Responder   $responder
     * @return void
     */
    public function __construct(Repository $repository, Lock $lock, JWTAuth $jwtAuth, Request $request, Responder $responder)
    {
        $this->repository = $repository;
        $this->lock = $lock;
        $this->jwtAuth = $jwtAuth;
        $this->responder = $responder;

        $this->permissions($request);
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
        $credential = $request->get('_user_credential');

        // Set new users to guest
        $request->merge(['user_type' =>'guest']);

        $this->validateId($id);
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

        return $this->responder->createdItem($model);
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
        $this->validateId($id);
        $model = $this->repository->find($id);

        // Check if the email is being changed, and initialize confirmation
        $email = $request->get('email');
        if ($email and $model->email != $email) {
            $token = $this->repository->makeConfirmationToken($email);
            $this->dispatch(new SendEmailConfirmationEmail($model, $email, $token));
            $request->merge(['email_confirmed' => null]);
        }

        $model->fill($request->all());
        $this->repository->save($model);

        return $this->responder->noContent();
    }
}
