<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;

    protected $otp;

    // هنا نستقبل الكود الذي ولدناه
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    // نخبر لارافيل أننا نريد الإرسال عبر الإيميل
    public function via($notifiable)
    {
        return ['mail'];
    }

    // هنا نكتب محتوى رسالة الإيميل
   public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('كود التحقق الخاص بك')
            ->greeting('مرحباً بك في نظام المعارض')
            ->line('كود التحقق الخاص بك هو:')
            ->line($this->otp) // وضعنا الكود هنا
            ->line('هذا الكود صالح لمدة 10 دقائق فقط.')
            ->line('إذا لم تطلب هذا الكود، يرجى تجاهل الرسالة.'); // هذا بديل الـ footer
    }
}