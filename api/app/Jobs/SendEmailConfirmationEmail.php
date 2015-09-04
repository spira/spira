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
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;

class SendEmailConfirmationEmail extends Job implements SelfHandling, ShouldQueue
{
    /**
     * User to email.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Email address to confirm.
     *
     * @var string
     */
    protected $email;

    /**
     * Token for email confirmation.
     *
     * @var string
     */
    protected $emailConfirmToken;

    /**
     * Token for login.
     *
     * @var string
     */
    protected $loginToken;

    /**
     * Create a new job instance.
     *
     * @param  User    $user
     * @param  string  $email
     * @param  string  $token
     * @return void
     */
    public function __construct(User $user, $email, $emailConfirmToken, $loginToken)
    {
        $this->user = $user;
        $this->email = $email;
        $this->emailConfirmToken = $emailConfirmToken;
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
        $mailer->send('emails.emailConfirmation', [
            'user' => $this->user,
            'email' => $this->email,
            'emailConfirmationRedirectionUrl' => Config::get('hosts.app').'/profile?emailConfirmationToken='.$this->emailConfirmToken.'&loginToken='.$this->loginToken,
        ], function ($m) {

            $m->to($this->email, $this->user->full_name)
                ->subject('Confirm Your Email');
        });
    }
}
