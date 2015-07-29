<?php namespace App\Jobs;

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
     * Token for confirmation.
     *
     * @var string
     */
    protected $token;

    /**
     * Create a new job instance.
     *
     * @param  User    $user
     * @param  string  $email
     * @param  string  $token
     * @return void
     */
    public function __construct(User $user, $email, $token)
    {
        $this->user = $user;
        $this->email = $email;
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
        $mailer->send('emails.emailConfirmation', [
            'user' => $this->user,
            'email' => $this->email,
            'emailConfirmationRedirectionUrl' => Config::get('hosts.app') . '/profile?emailConfirmationToken='.$this->token
        ], function ($m) {

            $m->to($this->user->email, $this->user->full_name)
                ->subject('Confirm Your Email');
        });
    }
}
