<?php namespace App\Jobs;

use App\Models\User;
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
        $mailer->send('emails.resetPassword', [
            'user' => $this->user,
            'passwordResetRedirectionUrl' => Config::get('hosts.app') . '/profile?loginToken='.$this->loginToken
        ], function ($m) {
            $m->to($this->user->email, $this->user->full_name)
                ->subject('Password Reset');
        });
    }
}
