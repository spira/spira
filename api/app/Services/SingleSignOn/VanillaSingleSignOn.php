<?php

namespace App\Services\SingleSignOn;

use Illuminate\Http\Request;
use App\Services\SingleSignOn\Exceptions\VanillaException;
use App\Services\SingleSignOn\Exceptions\VanillaAccessDeniedException;
use App\Services\SingleSignOn\Exceptions\VanillaInvalidClientException;
use App\Services\SingleSignOn\Exceptions\VanillaInvalidRequestException;

class VanillaSingleSignOn extends SingleSignOnAbstract implements SingleSignOnContract
{
    /**
     * Constant representing the max allowed timestamp difference in seconds.
     *
     * @var int
     */
    const TIMEOUT = 1440;

    /**
     * Client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * Secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Security scheme to use.
     *
     * @var string|bool
     */
    protected $secure = 'sha1';

    /**
     * Assign dependencies.
     *
     * @param  Request  $request
     * @param  mixed    $user
     *
     * @return void
     */
    public function __construct(Request $request, $user)
    {
        $this->clientId = env('VANILLA_JSCONNECT_CLIENT_ID');
        $this->secret = env('VANILLA_JSCONNECT_SECRET');

        parent::__construct($request, $user);
    }

    /**
     * Get the response to the requester.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response($this->formatUser(), $this->secure);
    }

    /**
     * Format the user as expected by Vanilla.
     *
     * @return array
     */
    public function formatUser()
    {
        if (!$this->user) {
            return [];
        } else {
            return [
                'uniqueid' => $this->user->user_id,
                'name' => $this->user->username,
                'email' => $this->user->email,
                'photourl' => $this->user->avatar_img_url,
                'roles' => $this->getMappedRoles()
            ];
        }
    }

    /**
     * Convert spira roles to a string suitable for Vanilla.
     *
     * @return string
     */
    protected function getMappedRoles()
    {
        $roles = $this->user->roles->pluck('name');

        // Map Spira to Vanilla roles
        $roles = $roles->map(function ($role) {
            $mapping = [
                'admin' => 'administrator',
                'guest' => 'member'
            ];

            if (array_key_exists($role, $mapping)) {
                return $mapping[$role];
            }

            return $role;
        });

        return $roles->implode(',');
    }

    /**
     * Generate the response string that Vanilla expects.
     *
     * @param  array  $user
     * @param  mixed  $secure
     *
     * @return string
     */
    protected function response($user, $secure = true)
    {
        $user = array_change_key_case($user);

        // Check if there are any errors in the request, when using signatures.
        if ($secure) {
            try {
                $this->validateRequest();
            } catch (VanillaException $e) {
                $error = [
                    'error' => $e->getType(),
                    'message' => $e->getMessage()
                ];
            }
        }

        if (isset($error)) {
            $result = $error;
        } elseif (!$this->request->has('timestamp') && !$this->request->has('signature')) {
            if (is_array($user) && count($user) > 0) {
                // We are only going to return public information when no signature is sent.
                $result = [
                    'name' => $user['name'],
                    'photourl' => @$user['photourl']
                ];
            } else {
                $result = [
                    'name' => '',
                    'photourl' => ''
                ];
            }
        } elseif (is_array($user) && count($user) > 0) {
            if ($secure === null) {
                $result = $user;
            } else {
                $result = $this->sign($user, $secure, true);
            }
        } else {
            $result = ['name' => '', 'photourl' => ''];
        }

        $json = json_encode($result);

        if ($this->request->has('callback')) {
            return sprintf('%s(%s)', $this->request->get('callback'), $json);
        } else {
            return $json;
        }
    }

    /**
     * Sign the data for the response.
     *
     * @param  array    $data
     * @param  string   $hashType
     * @param  boolean  $returnData
     *
     * @return mixed
     */
    protected function sign(array $data, $hashType, $returnData = false)
    {
        $data = array_change_key_case($data);
        ksort($data);

        foreach ($data as $key => $value) {
            if ($value === null) {
                $data[$key] = '';
            }
        }

        $string = http_build_query($data, null, '&');
        $signature = $this->hash($string.$this->secret, $hashType);

        if ($returnData) {
            $data['client_id'] = $this->clientId;
            $data['signature'] = $signature;

            return $data;
        } else {
            return $signature;
        }
    }

    /**
     * Return the hash of a string.
     *
     * @param  string       $string
     * @param  string|bool  $secure
     *
     * @return string
     */
    protected function hash($string, $secure = true)
    {
        // If no specific hash method is specificed, but requested secure
        // default to md5, as that is the default Vanilla method.
        if ($secure === true) {
            $secure = 'md5';
        }

        switch ($secure) {
            case 'sha1':
                return sha1($string);
                break;
            case 'md5':
            case false:
                return md5($string);
            default:
                return hash($secure, $string);
        }
    }

    /**
     * Generate an SSO string suitible for passing in the url for embedded SSO.
     *
     * @param  array   $user
     *
     * @return string
     */
    protected function ssoString(array $user)
    {
        if (!isset($user['client_id'])) {
            $user['client_id'] = $this->clientId;
        }

        $string = base64_encode(json_encode($user));
        $timestamp = time();
        $hash = hash_hmac('sha1', "$string $timestamp", $this->secret);

        return "$string $hash $timestamp hmacsha1";
    }

    /**
     * Set the security to use.
     *
     * @param  string|bool  $secure
     *
     * @return  void
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * Run the request through the validation rules.
     *
     * @return void
     */
    protected function validateRequest()
    {
        $validators = [
            'hasClientId' => null,
            'knownClientId' => null,
            'timestamp' => 'signed',
            'hasSignature' => 'signed',
            'notExpiredTimestamp' => 'signed',
            'signature' => 'signed',
        ];

        foreach ($validators as $validator => $condition) {
            if ($condition) {
                $method = camel_case('validCondition_'.$condition);

                if (!$this->{$method}()) {
                    continue;
                }
            }

            $method = camel_case('valid_'.$validator);
            $this->{$method}();
        }
    }

    /**
     * Condition to ensure that it is a signed request.
     *
     * Vanilla makes requests that isn't signed when retrieving public info.
     *
     * @return bool
     */
    protected function validConditionSigned()
    {
        return ($this->request->has('timestamp') || $this->request->has('signature'));
    }

    /**
     * Validate that the request has a client id.
     *
     * @throws VanillaInvalidRequestException
     *
     * @return void
     */
    protected function validHasClientId()
    {
        if (!$this->request->has('client_id')) {
            throw new VanillaInvalidRequestException('The client_id parameter is missing.');
        }
    }

    /**
     * Validate that the request has a known client id.
     *
     * @throws VanillaInvalidRequestException
     *
     * @return void
     */
    protected function validKnownClientId()
    {
        if ($this->request->get('client_id') != $this->clientId) {
            throw new VanillaInvalidClientException("Unknown client {$this->request->get('client_id')}.");
        }
    }

    /**
     * Validate that the timestamp exists and is in correct format.
     *
     * @throws VanillaInvalidRequestException
     *
     * @return void
     */
    protected function validTimestamp()
    {
        if (!$this->request->has('timestamp') || !is_numeric($this->request->get('timestamp'))) {
            throw new VanillaInvalidRequestException('The timestamp parameter is missing or invalid.');
        }
    }

    /**
     * Validate that signature exists.
     *
     * @throws VanillaInvalidRequestException
     *
     * @return void
     */
    protected function validHasSignature()
    {
        if (!$this->request->has('signature')) {
            throw new VanillaInvalidRequestException('Missing signature parameter.');
        }
    }

    /**
     * Validate that timestamp is not expired.
     *
     * @throws VanillaInvalidRequestException
     *
     * @return void
     */
    protected function validNotExpiredTimestamp()
    {
        if ((abs($this->request->get('timestamp') - time())) > self::TIMEOUT) {
            throw new VanillaInvalidRequestException('The timestamp is invalid.');
        }
    }

    /**
     * Validate signature.
     *
     * @throws VanillaAccessDeniedException
     *
     * @return void
     */
    protected function validSignature()
    {
        $signature = $this->hash($this->request->get('timestamp').$this->secret, $this->secure);

        if ($signature != $this->request->get('signature')) {
            throw new VanillaAccessDeniedException('Signature invalid.');
        }
    }
}
