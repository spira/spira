<?php

namespace App\Services\SingleSignOn;

use Illuminate\Http\Request;

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
     * Assign dependencies.
     *
     * @param  Request  $request
     * @param  mixed    $user
     *
     * @return void
     */
    public function __construct(Request $request, $user)
    {
        $this->clientId = 'client';
        $this->secret = 'secret';

        parent::__construct($request, $user);
    }

    /**
     * Get the response to the requester.
     *
     * @return mixed
     */
    public function getResponse()
    {
        // Use md5 for now.
        // $secure = false;
        $secure = true;

        return $this->response($this->formatUser(), $secure);
    }

    /**
     * Format the user as expected by Vanilla.
     *
     * @return array
     */
    protected function formatUser()
    {
        if (!$this->user) {
            return [];
        } else {
            // Temporaray dummy user
            return [
                'uniqueid' => '1234567890',
                'name' => 'Foo Bar',
                'email' => 'foo@bar.com',
                'photourl' => ''
            ];
        }
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
            if (!$this->request->has('client_id')) {
                $error = [
                    'error' => 'invalid_request',
                    'message' => 'The client_id parameter is missing.'
                ];
            } elseif ($this->request->get('client_id') != $this->clientId) {
                $error = [
                    'error' => 'invalid_client',
                    'message' => "Unknown client {$this->request->get('client_id')}."
                ];
            } elseif (!$this->request->has('timestamp') && !$this->request->has('signature')) {
                if (is_array($user) && count($user) > 0) {
                    // This isn't really an error, but we are only going to
                    // return public information when no signature is sent.
                    $error = [
                        'name' => $user['name'],
                        'photourl' => @$user['photourl']
                    ];
                } else {
                    $error = [
                        'name' => '',
                        'photourl' => ''
                    ];
                }
            } elseif (!$this->request->has('timestamp') || !is_numeric($this->request->get('timestamp'))) {
                $error = [
                    'error' => 'invalid_request',
                    'message' => 'The timestamp parameter is missing or invalid.'
                ];
            } elseif (!$this->request->has('signature')) {
                $error = [
                    'error' => 'invalid_request',
                    'message' => 'Missing  signature parameter.'
                ];
            } elseif (($diff = abs($this->request->get('timestamp') - $this->timestamp())) > self::TIMEOUT) {
                // Make sure the timestamp hasn't timed out.
                $error = [
                    'error' => 'invalid_request',
                    'message' => 'The timestamp is invalid.'
                ];
            } else {
                $signature = $this->hash($this->request->get('timestamp').$this->secret, $secure);
                if ($signature != $this->request->get('signature')) {
                    $error = [
                        'error' => 'access_denied',
                        'message' => 'Signature invalid.'
                    ];
                }
            }
        }

        if (isset($error)) {
            $result = $error;
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
            echo $this->request->get('callback').$json;
        } else {
            echo $json;
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

        foreach ($data as $Key => $Value) {
            if ($Value === null) {
                $data[$Key] = '';
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
     * Get current timestamp.
     *
     * @return int
     */
    protected function timestamp()
    {
        return time();
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
}
