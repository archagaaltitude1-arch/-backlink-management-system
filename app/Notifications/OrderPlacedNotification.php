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
            ->subject('Order '.$this->order->order_number.($this->order->order_status === 'awaiting_details' ? ' payment receipt' : ($successful ? ' confirmation' : ' payment update')))
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Payment status: '.ucfirst($this->payment->status).'.')
            ->line('Order total: $'.number_format((float) $this->payment->amount, 2).'.');

        if ($successful) {
            $message->line($this->order->order_status === 'awaiting_details'
                ? 'Your payment is confirmed. Add your publishing details to submit the order for admin review.'
                : 'Your payment is confirmed. Your order is awaiting admin approval.')
                ->line('Payment method: '.$this->payment->payment_method.'.')
                ->line('Payment reference: '.$this->payment->transaction_id.'.');

            if ($invoice = $this->order->invoice) {
                $invoice->loadMissing('customer', 'order.items');
                $message->attachData(
                    view('orders.invoice', ['invoice' => $invoice, 'payment' => $this->payment])->render(),
                    $invoice->invoice_number.'.html',
                    ['mime' => 'text/html'],
                );
            }

            if ($coupon = $this->order->rewardCoupon) {
                $message->line('Use '.$coupon->code.' for 10% off your next order. This one-use code expires on '.$coupon->expires_at->format('M j, Y').'.');
            }
        }

        return $message->action($successful ? 'View order and invoice' : 'View order', route('orders.show', $this->order));
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
