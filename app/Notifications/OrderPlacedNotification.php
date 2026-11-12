<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public Payment $payment) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $successful = $this->payment->status === 'successful';
        $message = (new MailMessage)
            ->subject('Order '.$this->order->order_number.($successful ? ' confirmation' : ' payment update'))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Payment status: '.ucfirst($this->payment->status).'.')
            ->line('Order total: $'.number_format((float) $this->payment->amount, 2).'.');

        if ($successful && $coupon = $this->order->rewardCoupon) {
            $message->line('Your payment is confirmed. Your order is awaiting admin approval.')
                ->line('Use '.$coupon->code.' for 10% off your next order. This one-use code expires on '.$coupon->expires_at->format('M j, Y').'.');
        }

        return $message->action('View order and invoice', route('orders.show', $this->order));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment '.ucfirst($this->payment->status),
            'message' => 'Order '.$this->order->order_number.' payment is '.$this->payment->status.'.',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'payment_id' => $this->payment->id,
        ];
    }
}
