<?php

namespace Database\Seeders;

use App\Models\CustomerProject;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;

class FulfillmentWorkflowSeeder extends Seeder
{
    /**
     * Sample customer orders covering assignment, publication, and approval.
     */
    public function run(): void
    {
        $admin = $this->account('admin@backlink.test', 'Aisha Rahman', 'Admin', 'admin');
        $customer = $this->account('noah.williams@example.com', 'Noah Williams', 'Customer', 'customer');
        $maya = $this->account('maya.chen@example.com', 'Maya Chen', 'Staff', 'staff');
        $lucas = $this->account('lucas.meyer@example.com', 'Lucas Meyer', 'Staff', 'staff');
        $staff = ['maya' => $maya, 'lucas' => $lucas];
        $websites = Website::query()->where('status', 'active')->orderBy('id')->get();

        foreach ($this->samples() as $index => $sample) {
            $order = Order::query()->updateOrCreate(
                ['order_number' => $sample['number']],
                [
                    'customer_id' => $customer->id,
                    'session_id' => 'fulfillment-demo',
                    'total_amount' => $sample['price'],
                    'discount_amount' => 0,
                    'payment_status' => 'paid',
                    'order_status' => $sample['order_status'],
                ],
            );
            $order->forceFill([
                'created_at' => now()->subDays(count($this->samples()) - $index),
                'updated_at' => now()->subDays(count($this->samples()) - $index),
            ])->save();

            $website = $websites->isEmpty() ? null : $websites[$index % $websites->count()];
            $service = Service::query()->where('service_name', $sample['item'])->first();
            $item = $order->items()->updateOrCreate(
                ['item_name' => $sample['item']],
                [
                    'website_id' => $website?->id,
                    'service_id' => $service?->id,
                    'unit_price' => $sample['price'],
                    'quantity' => 1,
                    'target_url' => $sample['target'],
                    'anchor_text' => $sample['anchor'],
                    'article_topic' => $sample['topic'],
                    'requirements' => 'Keep the link natural and use the submitted anchor text.',
                ],
            );

            $this->syncAssignment($item, $sample['assignment'], $staff, $admin);
            $this->syncApproval($item, $customer, $sample['approval']);

            Payment::query()->updateOrCreate(
                ['transaction_id' => str_replace('BM-', '', $sample['number'])],
                [
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'amount' => $sample['price'],
                    'payment_method' => 'Demo Visa ending 4242',
                    'gateway' => 'demo',
                    'status' => 'successful',
                    'paid_at' => $order->created_at,
                    'metadata' => ['mode' => 'fulfillment_demo'],
                ],
            );
            Invoice::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-'.$sample['number'],
                    'total_amount' => $sample['price'],
                    'issued_at' => $order->created_at,
                ],
            );
        }
    }

    /**
     * @param  array{staff: string, status: string, notes: string}|null  $assignment
     * @param  array<string, User>  $staff
     */
    private function syncAssignment(OrderItem $item, ?array $assignment, array $staff, User $admin): void
    {
        if ($assignment === null) {
            OrderAssignment::query()->where('order_item_id', $item->id)->delete();

            return;
        }

        OrderAssignment::query()->updateOrCreate(
            ['order_item_id' => $item->id],
            [
                'staff_id' => $staff[$assignment['staff']]->id,
                'assigned_by' => $admin->id,
                'status' => $assignment['status'],
                'notes' => $assignment['notes'],
            ],
        );
    }

    /**
     * @param  array{status: string, verification_status: string, published_url: string, report_summary: string|null}|null  $approval
     */
    private function syncApproval(OrderItem $item, User $customer, ?array $approval): void
    {
        if ($approval === null) {
            CustomerProject::query()->where('order_item_id', $item->id)->delete();

            return;
        }

        CustomerProject::query()->updateOrCreate(
            ['order_item_id' => $item->id],
            [
                'customer_id' => $customer->id,
                'order_id' => $item->order_id,
                'name' => $item->item_name,
                'status' => $approval['status'],
                'target_url' => $item->target_url,
                'published_url' => $approval['published_url'],
                'verification_status' => $approval['verification_status'],
                'report_summary' => $approval['report_summary'],
                'completed_at' => $approval['verification_status'] === 'approved' ? now()->subDay() : null,
            ],
        );
    }

    private function account(string $email, string $name, string $roleName, string $accountType): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => $roleName], ['permissions' => []]);

        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'Password123',
                'role_id' => $role->id,
                'status' => 'active',
                'account_type' => $accountType,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * @return list<array{
     *     number: string,
     *     item: string,
     *     price: float,
     *     target: string,
     *     anchor: string,
     *     topic: string,
     *     order_status: string,
     *     assignment: array{staff: string, status: string, notes: string}|null,
     *     approval: array{status: string, verification_status: string, published_url: string, report_summary: string|null}|null
     * }>
     */
    private function samples(): array
    {
        return [
            [
                'number' => 'BM-FLOW-0001',
                'item' => 'Guest Post',
                'price' => 120,
                'target' => 'https://noahwilliams.example/packaging-guide',
                'anchor' => 'sustainable packaging',
                'topic' => 'How brands choose packaging partners',
                'order_status' => 'processing',
                'assignment' => null,
                'approval' => null,
            ],
            [
                'number' => 'BM-FLOW-0002',
                'item' => 'Niche Edits',
                'price' => 90,
                'target' => 'https://noahwilliams.example/content-calendar',
                'anchor' => 'editorial calendar',
                'topic' => 'Planning a quarterly content calendar',
                'order_status' => 'processing',
                'assignment' => [
                    'staff' => 'maya',
                    'status' => 'assigned',
                    'notes' => 'Place the link in the second section.',
                ],
                'approval' => null,
            ],
            [
                'number' => 'BM-FLOW-0003',
                'item' => 'Link Insertion',
                'price' => 80,
                'target' => 'https://noahwilliams.example/supplier-checklist',
                'anchor' => 'supplier checklist',
                'topic' => 'A checklist for new suppliers',
                'order_status' => 'processing',
                'assignment' => [
                    'staff' => 'lucas',
                    'status' => 'in_progress',
                    'notes' => 'The publisher is reviewing the draft.',
                ],
                'approval' => null,
            ],
            [
                'number' => 'BM-FLOW-0004',
                'item' => 'Press Release',
                'price' => 150,
                'target' => 'https://noahwilliams.example/spring-launch',
                'anchor' => 'product launch',
                'topic' => 'Launch announcement for the spring range',
                'order_status' => 'processing',
                'assignment' => [
                    'staff' => 'maya',
                    'status' => 'published',
                    'notes' => 'Waiting for the customer to approve the live URL.',
                ],
                'approval' => [
                    'status' => 'published',
                    'verification_status' => 'pending',
                    'published_url' => 'https://growth-ledger.example/spring-range-launch',
                    'report_summary' => null,
                ],
            ],
            [
                'number' => 'BM-FLOW-0005',
                'item' => 'Guest Post',
                'price' => 120,
                'target' => 'https://noahwilliams.example/warehouse-tour',
                'anchor' => 'warehouse tour',
                'topic' => 'Inside a modern warehouse',
                'order_status' => 'completed',
                'assignment' => [
                    'staff' => 'lucas',
                    'status' => 'completed',
                    'notes' => 'The customer approved this placement.',
                ],
                'approval' => [
                    'status' => 'completed',
                    'verification_status' => 'approved',
                    'published_url' => 'https://founder-path.example/warehouse-tour',
                    'report_summary' => 'The customer approved the published link.',
                ],
            ],
        ];
    }
}
