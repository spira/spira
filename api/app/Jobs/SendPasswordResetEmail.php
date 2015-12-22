<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Jobs;

use App\Models\User;
use Illuminate\Mail\Message;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendPasswordResetEmail extends Job implements SelfHandling, ShouldQueue
{
    /**
     * User to email.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Token for logging in.
     *
     * @var string
     */
    protected $loginToken;

    /**
     * Create a new job instance.
     *
     * @param  User    $user
     * @param  string  $token
     * @return void
     */
    public function __construct(User $user, $loginToken)
    {
        $this->user = $user;
        $this->loginToken = $loginToken;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        echo "Resetting password for " . $this->user->email . "\n";

        $mailer->send('emails.resetPassword', [
            'user' => $this->user,
            'passwordResetRedirectionUrl' => Config::get('hosts.app').'/profile?loginToken='.$this->loginToken,
        ], function (Message $m) {
            $m->to($this->user->email, $this->user->full_name)
                ->subject('Password Reset');
        });
    }
}
