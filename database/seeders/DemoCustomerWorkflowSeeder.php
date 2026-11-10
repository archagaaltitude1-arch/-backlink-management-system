<?php

namespace Database\Seeders;

use App\Models\BillingAddress;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CreditPackage;
use App\Models\CreditReservation;
use App\Models\CreditTransaction;
use App\Models\CustomerProject;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\SavedWebsite;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCustomerWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPublisherWebsites();

        $customer = User::updateOrCreate(
            ['email' => 'customer@example.test'],
            [
                'name' => 'Alex Morgan',
                'password' => Hash::make('CustomerDemo123!'),
                'role' => 'customer',
                'status' => 'active',
                'phone' => '+1 555 010 2040',
                'email_verified_at' => now(),
            ]
        );

        $wallet = Wallet::updateOrCreate(
            ['customer_id' => $customer->id],
            ['available_balance' => 435, 'reserved_balance' => 25]
        );

        $packages = collect([
            ['name' => 'Starter Credits', 'description' => 'A small publishing credit bundle.', 'credits' => 100, 'price' => 29.00],
            ['name' => 'Growth Credits', 'description' => 'For regular backlink campaigns.', 'credits' => 300, 'price' => 75.00],
            ['name' => 'Professional Credits', 'description' => 'A larger credit balance for active teams.', 'credits' => 750, 'price' => 159.00],
        ])->map(fn (array $package) => CreditPackage::updateOrCreate(['name' => $package['name']], $package + ['is_active' => true]));

        $coupon = Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order_amount' => 50,
                'usage_limit' => 500,
                'per_customer_limit' => 1,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );
        $sampleCoupon = Coupon::updateOrCreate(
            ['code' => 'SPRING15'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order_amount' => 50,
                'usage_limit' => 500,
                'per_customer_limit' => 2,
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        SavedWebsite::updateOrCreate(['customer_id' => $customer->id, 'domain' => 'growthledger.com'], [
            'website_name' => 'Growth Ledger', 'url' => 'https://growthledger.com', 'domain_rating' => 72,
            'monthly_traffic' => 18400, 'price' => 120,
        ]);
        SavedWebsite::updateOrCreate(['customer_id' => $customer->id, 'domain' => 'founderpath.io'], [
            'website_name' => 'Founder Path', 'url' => 'https://founderpath.io', 'domain_rating' => 64,
            'monthly_traffic' => 12100, 'price' => 80,
        ]);

        BillingAddress::updateOrCreate(['customer_id' => $customer->id, 'label' => 'Work'], [
            'full_name' => $customer->name, 'company' => 'Morgan Digital', 'address_line_1' => '410 Market Street',
            'city' => 'San Francisco', 'state' => 'CA', 'postal_code' => '94105', 'country' => 'US', 'is_default' => true,
        ]);

        PaymentMethod::updateOrCreate(['token_reference' => 'demo_customer_visa_4242'], [
            'customer_id' => $customer->id, 'label' => 'Visa ending in 4242', 'brand' => 'Visa',
            'last_four' => '4242', 'is_default' => true,
        ]);

        $completedOrder = Order::updateOrCreate(['order_number' => 'BM-DEMO-0001'], [
            'customer_id' => $customer->id, 'session_id' => 'demo-customer-session', 'total_amount' => 120,
            'payment_status' => 'paid', 'order_status' => 'completed', 'discount_amount' => 0,
        ]);
        $publishedItem = $completedOrder->items()->updateOrCreate(['item_name' => 'Guest Post Placement'], [
            'unit_price' => 120, 'quantity' => 1, 'credit_cost' => 40,
            'target_url' => 'https://morgandigital.example/seo-services', 'anchor_text' => 'SEO services',
            'article_topic' => 'A practical guide to sustainable search growth',
            'requirements' => 'Use a helpful, editorial tone and include the target link naturally.',
        ]);

        $inProgressOrder = Order::updateOrCreate(['order_number' => 'BM-DEMO-0002'], [
            'customer_id' => $customer->id, 'session_id' => 'demo-customer-session', 'total_amount' => 80,
            'payment_status' => 'paid', 'order_status' => 'processing', 'discount_amount' => 0,
        ]);
        $reservedItem = $inProgressOrder->items()->updateOrCreate(['item_name' => 'Niche Edit'], [
            'unit_price' => 80, 'quantity' => 1, 'credit_cost' => 25,
            'target_url' => 'https://morgandigital.example/marketing', 'anchor_text' => 'digital marketing team',
            'article_topic' => 'Small business marketing workflows', 'requirements' => 'Please confirm the placement before publication.',
        ]);

        $pendingOrder = Order::updateOrCreate(['order_number' => 'BM-DEMO-0003'], [
            'customer_id' => $customer->id, 'session_id' => 'demo-customer-session', 'total_amount' => 60,
            'payment_status' => 'pending', 'order_status' => 'pending', 'discount_amount' => 6, 'coupon_code' => $sampleCoupon->code,
        ]);
        $pendingOrder->items()->updateOrCreate(['item_name' => 'Link Insertion'], [
            'unit_price' => 60, 'quantity' => 1, 'credit_cost' => 20,
            'target_url' => 'https://morgandigital.example/resources', 'anchor_text' => 'marketing resources',
            'article_topic' => 'Curated resources for growth teams', 'requirements' => 'Keep surrounding copy intact.',
        ]);

        $completedPayment = Payment::updateOrCreate(['transaction_id' => 'DEMO-ORDER-0001'], [
            'customer_id' => $customer->id, 'order_id' => $completedOrder->id, 'amount' => 120,
            'payment_method' => 'Demo Visa ending 4242', 'gateway' => 'demo', 'status' => 'successful', 'paid_at' => now()->subDays(8),
            'metadata' => ['mode' => 'seeded_demo'],
        ]);
        Payment::updateOrCreate(['transaction_id' => 'DEMO-ORDER-0002'], [
            'customer_id' => $customer->id, 'order_id' => $inProgressOrder->id, 'amount' => 80,
            'payment_method' => 'Demo Visa ending 4242', 'gateway' => 'demo', 'status' => 'successful', 'paid_at' => now()->subDays(2),
            'metadata' => ['mode' => 'seeded_demo'],
        ]);

        Invoice::updateOrCreate(['order_id' => $completedOrder->id], [
            'customer_id' => $customer->id, 'invoice_number' => 'INV-DEMO-0001', 'total_amount' => 120,
            'issued_at' => now()->subDays(8),
        ]);

        CreditTransaction::updateOrCreate(['wallet_id' => $wallet->id, 'description' => 'Growth Credits package purchased'], [
            'payment_id' => $completedPayment->id, 'transaction_type' => 'purchase', 'amount' => 500,
        ]);
        CreditTransaction::updateOrCreate(['wallet_id' => $wallet->id, 'description' => 'Verified backlink published'], [
            'order_item_id' => $publishedItem->id, 'transaction_type' => 'deduction', 'amount' => 40,
        ]);
        CreditTransaction::updateOrCreate(['wallet_id' => $wallet->id, 'description' => 'Credits reserved for Niche Edit'], [
            'order_item_id' => $reservedItem->id, 'transaction_type' => 'reserve', 'amount' => 25,
        ]);

        CreditReservation::updateOrCreate(['wallet_id' => $wallet->id, 'order_item_id' => $reservedItem->id], [
            'reserved_credits' => 25, 'status' => 'reserved', 'resolved_at' => null,
        ]);

        CouponUsage::updateOrCreate(['coupon_id' => $sampleCoupon->id, 'customer_id' => $customer->id, 'order_id' => $pendingOrder->id], [
            'discount_amount' => 6,
        ]);

        CustomerProject::updateOrCreate(['order_id' => $completedOrder->id], [
            'customer_id' => $customer->id, 'name' => 'Spring search visibility', 'status' => 'completed',
            'target_url' => $publishedItem->target_url, 'published_url' => 'https://growthledger.com/growth-playbook',
            'verification_status' => 'verified',
            'report_summary' => 'The published page links to the requested target using the submitted anchor text. The link was checked and verified.',
            'completed_at' => now()->subDays(7),
        ]);
        CustomerProject::updateOrCreate(['order_id' => $inProgressOrder->id], [
            'customer_id' => $customer->id, 'name' => 'Marketing placements', 'status' => 'processing',
            'target_url' => $reservedItem->target_url, 'verification_status' => 'pending',
        ]);

        $customer->notifications()->updateOrCreate(['id' => '00000000-0000-4000-8000-000000000001'], [
            'type' => 'demo.order.update',
            'data' => ['title' => 'Backlink verified', 'message' => 'Your order BM-DEMO-0001 has been published and verified.', 'order_number' => $completedOrder->order_number],
            'read_at' => null,
        ]);

        $customer->notifications()->updateOrCreate(['id' => '00000000-0000-4000-8000-000000000002'], [
            'type' => 'demo.order.update',
            'data' => ['title' => 'Order in progress', 'message' => 'Your Niche Edit order is being processed.', 'order_number' => $inProgressOrder->order_number],
            'read_at' => null,
        ]);
    }

    private function seedPublisherWebsites(): void
    {
        $publishers = [
            'Clear Byte', 'Metric Mint', 'Launch Loom', 'Founder Field', 'Data Harbor',
            'Circuit Atlas', 'Cloud Craft', 'Query Quarter', 'Pixel Pioneer', 'Secure Stack',
            'Growth Ledger', 'Market Mosaic', 'Design Current', 'Product Signal', 'Startup Almanac',
            'Code Orchard', 'Finance Lantern', 'Health Commons', 'Travel Notebook', 'Green Circuit',
            'Retail Rhythm', 'People Platform', 'Research Relay', 'Creator Compass', 'Home Habitat',
            'Food Foundry', 'Learning Loop', 'Legal Briefcase', 'Science Window', 'Media Current',
            'Wellness Journal', 'Commerce Pilot', 'Operations Review', 'Mobile Foundry', 'Energy Digest',
            'Culture Index', 'Security Notebook', 'Analytics Workshop', 'Workplace Atlas', 'Future Archive',
        ];
        $niches = ['Technology', 'Business', 'Finance', 'Health', 'Travel', 'Education', 'Lifestyle', 'Marketing'];

        foreach ($publishers as $index => $publisher) {
            $domain = str($publisher)->slug()->append('.example')->toString();
            $domainRating = 42 + (($index * 7) % 55);

            Website::updateOrCreate(
                ['domain' => $domain],
                [
                    'website_name' => $publisher,
                    'url' => 'https://'.$domain,
                    'niche' => $niches[$index % count($niches)],
                    'description' => 'A sample '.$niches[$index % count($niches)].' publisher for marketplace demos.',
                    'domain_rating' => $domainRating,
                    'domain_authority' => 38 + (($index * 9) % 58),
                    'trust_flow' => 18 + (($index * 5) % 42),
                    'page_authority' => 30 + (($index * 11) % 66),
                    'monthly_traffic' => 4500 + (($index * 1873) % 245000),
                    'spam_score' => 1 + (($index * 3) % 12),
                    'backlinks' => 12000 + (($index * 5917) % 850000),
                    'referring_domains' => 450 + (($index * 317) % 42000),
                    'price' => 65 + (($index * 17) % 285),
                    'credit_cost' => 20 + (($index * 7) % 85),
                    'status' => 'active',
                ]
            );
        }
    }
}
