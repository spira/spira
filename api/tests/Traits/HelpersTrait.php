<?php
trait HelpersTrait
{
    protected function tokenFromUser($user)
    {
        $cfg = $this->app->config->get('jwt');
        $validator = new Tymon\JWTAuth\Validators\PayloadValidator;
        $request = new Illuminate\Http\Request;
        $claimFactory = new Tymon\JWTAuth\Claims\Factory;

        $adapter = new App\Extensions\JWTAuth\NamshiAdapter($cfg['secret'], $cfg['algo']);
        $payloadFactory = new App\Extensions\JWTAuth\PayloadFactory($claimFactory, $request, $validator);

        $claims = ['sub' => $user->user_id];
        $payload = $payloadFactory->make($claims);

        $token = $adapter->encode($payload->get());

        return $token;
    }
}
