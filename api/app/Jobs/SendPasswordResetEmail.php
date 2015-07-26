<?php namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPasswordResetEmail extends Job implements SelfHandling, ShouldQueue
{
    /**
     * User to email.
     *
     * @var \App\Models\User
     */
    protected $user;

    /**
     * Token for confirmation.
     *
     * @var string
     */
    protected $token;

    /**
     * Create a new job instance.
     *
     * @param  User    $user
     * @param  string  $token
     * @return void
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
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
            'token' => $this->token
        ], function ($m) {
            $m->to($this->user->email, $this->user->full_name)
                ->subject('Password Reset');
        });
    }
}
