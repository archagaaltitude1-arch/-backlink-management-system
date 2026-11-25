<?php

namespace App\Notifications;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CheckoutNotification extends Notification
{
    public function __construct(public Coupon|Order $subject, public bool $forAdmin = false) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->subject instanceof Coupon ? ['mail'] : ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->subject instanceof Coupon) {
            return (new MailMessage)
                ->subject('Your checkout coupon')
                ->line('Use '.$this->subject->code.' for 10% off this checkout.')
                ->action('Return to checkout', route('orders.checkout'));
        }

        return (new MailMessage)
            ->subject('Order '.$this->subject->order_number.' confirmation')
            ->line($this->toArray($notifiable)['message'])
            ->action('View order', route($this->forAdmin ? 'admin.orders.show' : 'orders.show', $this->subject));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Order '.$this->subject->order_number.' confirmation',
            'message' => $this->forAdmin
                ? 'A paid order with publishing details is ready for review.'
                : 'Your publishing details were submitted. Your order is awaiting admin approval.',
            'order_number' => $this->subject->order_number,
        ];
    }
}
