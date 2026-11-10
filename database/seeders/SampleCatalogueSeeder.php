<?php

namespace Database\Seeders;

use App\Models\CreditPricing;
use App\Models\Niche;
use App\Models\NicheSpecification;
use App\Models\Package;
use App\Models\Role;
use App\Models\SeoMetric;
use App\Models\Service;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleCatalogueSeeder extends Seeder
{
    /**
     * Seed sample catalogue rows for the existing tables.
     * Existing names, domains, and emails are left in place.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedUsers();
            $this->seedServices();
            $this->seedNichesAndWebsites();
            $this->seedPackages();
            $this->seedCreditPricing();
            $this->seedNicheSpecifications();
        });
    }

    private function seedUsers(): void
    {
        $roles = Role::query()->pluck('id', 'role_name');

        $users = [
            ['name' => 'Aisha Rahman', 'email' => 'admin@backlink.test', 'role' => 'Admin', 'status' => 'active'],
            ['name' => 'Noah Williams', 'email' => 'noah.williams@example.com', 'role' => 'Customer', 'status' => 'active'],
            ['name' => 'Hana Sato', 'email' => 'hana.sato@example.com', 'role' => 'Customer', 'status' => 'active'],
            ['name' => 'Elena Petrova', 'email' => 'elena.petrova@example.com', 'role' => 'Customer', 'status' => 'active'],
            ['name' => 'Omar Haddad', 'email' => 'omar.haddad@example.com', 'role' => 'Customer', 'status' => 'inactive'],
            ['name' => 'Priya Fernando', 'email' => 'priya.fernando@example.com', 'role' => 'Customer', 'status' => 'active'],
            ['name' => 'James Okonkwo', 'email' => 'james.okonkwo@example.com', 'role' => 'Customer', 'status' => 'suspended'],
            ['name' => 'Maya Chen', 'email' => 'maya.chen@example.com', 'role' => 'Staff', 'status' => 'active'],
            ['name' => 'Lucas Meyer', 'email' => 'lucas.meyer@example.com', 'role' => 'Staff', 'status' => 'active'],
            ['name' => 'Sofia Alvarez', 'email' => 'sofia.alvarez@example.com', 'role' => 'Staff', 'status' => 'inactive'],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => 'Password123',
                    'role_id' => $roles[$user['role']] ?? null,
                    'status' => $user['status'],
                ],
            );
        }
    }

    private function seedServices(): void
    {
        $services = [
            ['service_name' => 'Guest Post', 'description' => 'Publish a new article with a backlink on a publisher website.', 'base_price' => 120, 'credit_cost' => 12],
            ['service_name' => 'Link Insertion', 'description' => 'Insert a backlink into an existing article.', 'base_price' => 80, 'credit_cost' => 8],
            ['service_name' => 'Niche Edits', 'description' => 'Add a contextual backlink to a relevant published page.', 'base_price' => 90, 'credit_cost' => 9],
            ['service_name' => 'Press Release', 'description' => 'Distribute a press release that includes a backlink.', 'base_price' => 150, 'credit_cost' => 15],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['service_name' => $service['service_name']],
                $service,
            );
        }
    }

    private function seedNichesAndWebsites(): void
    {
        $niches = [
            'Technology' => 'Devices, software products, and technology news.',
            'Software and IT' => 'Software development, IT services, and infrastructure.',
            'Business' => 'Management, startups, and business operations.',
            'Finance' => 'Banking, investing, and personal finance.',
            'Health' => 'Wellness, medical topics, and healthcare.',
            'Education' => 'Learning, schools, and training.',
            'Travel' => 'Destinations, hospitality, and travel planning.',
            'Fashion and Beauty' => 'Apparel, style, and beauty products.',
            'Lifestyle' => 'Everyday living, culture, and personal interests.',
            'Real Estate' => 'Property, housing, and commercial real estate.',
            'E-commerce' => 'Online retail and digital storefronts.',
            'Automotive' => 'Vehicles, repairs, and the automotive industry.',
            'Sports' => 'Teams, fitness, and sporting events.',
            'Food' => 'Recipes, restaurants, and food products.',
        ];

        $nicheIds = [];

        foreach ($niches as $name => $description) {
            $niche = Niche::query()->updateOrCreate(
                ['niche_name' => $name],
                ['description' => $description],
            );
            $nicheIds[$name] = $niche->id;
        }

        $websites = [
            [
                'domain' => 'techledger.example',
                'website_name' => 'Tech Ledger',
                'url' => 'https://techledger.example',
                'price' => 180,
                'credit_cost' => 18,
                'status' => 'active',
                'niches' => ['Technology', 'Software and IT'],
                'metrics' => ['DR' => 62, 'DA' => 54, 'TF' => 41, 'CF' => 38, 'traffic' => 85000, 'spam_score' => 3, 'backlinks' => 12400],
            ],
            [
                'domain' => 'marketnote.example',
                'website_name' => 'Market Note',
                'url' => 'https://marketnote.example',
                'price' => 140,
                'credit_cost' => 14,
                'status' => 'active',
                'niches' => ['Business', 'Finance'],
                'metrics' => ['DR' => 48, 'DA' => 44, 'TF' => 33, 'CF' => 29, 'traffic' => 42000, 'spam_score' => 6, 'backlinks' => 6800],
            ],
            [
                'domain' => 'wellpath.example',
                'website_name' => 'Well Path',
                'url' => 'https://wellpath.example',
                'price' => 95,
                'credit_cost' => 10,
                'status' => 'pending',
                'niches' => ['Health', 'Lifestyle'],
                'metrics' => ['DR' => 36, 'DA' => 31, 'TF' => 22, 'CF' => 19, 'traffic' => 18000, 'spam_score' => 8, 'backlinks' => 2400],
            ],
            [
                'domain' => 'citystay.example',
                'website_name' => 'City Stay',
                'url' => 'https://citystay.example',
                'price' => 110,
                'credit_cost' => 11,
                'status' => 'active',
                'niches' => ['Travel', 'Real Estate'],
                'metrics' => ['DR' => 41, 'DA' => 39, 'TF' => 27, 'CF' => 24, 'traffic' => 27000, 'spam_score' => 5, 'backlinks' => 4100],
            ],
            [
                'domain' => 'styledesk.example',
                'website_name' => 'Style Desk',
                'url' => 'https://styledesk.example',
                'price' => 75,
                'credit_cost' => 8,
                'status' => 'inactive',
                'niches' => ['Fashion and Beauty', 'Lifestyle'],
                'metrics' => ['DR' => 28, 'DA' => 25, 'TF' => 16, 'CF' => 18, 'traffic' => 9000, 'spam_score' => 12, 'backlinks' => 1500],
            ],
            [
                'domain' => 'tableandco.example',
                'website_name' => 'Table and Co',
                'url' => 'https://tableandco.example',
                'price' => 60,
                'credit_cost' => 6,
                'status' => 'active',
                'niches' => ['Food', 'E-commerce'],
                'metrics' => ['DR' => 33, 'DA' => 29, 'TF' => 20, 'CF' => 21, 'traffic' => 15000, 'spam_score' => 4, 'backlinks' => 2100],
            ],
        ];

        foreach ($websites as $websiteData) {
            $nicheNames = $websiteData['niches'];
            $metrics = $websiteData['metrics'];
            unset($websiteData['niches'], $websiteData['metrics']);

            $website = Website::query()->updateOrCreate(
                ['domain' => $websiteData['domain']],
                $websiteData,
            );

            $website->niches()->sync(
                collect($nicheNames)->map(fn (string $name) => $nicheIds[$name])->all()
            );

            SeoMetric::query()->updateOrCreate(
                ['website_id' => $website->id],
                $metrics,
            );
        }
    }

    private function seedPackages(): void
    {
        $packages = [
            ['package_name' => 'Starter Package', 'price' => 49, 'credit_amount' => 5],
            ['package_name' => 'Growth Package', 'price' => 89, 'credit_amount' => 10],
            ['package_name' => 'Professional Package', 'price' => 199, 'credit_amount' => 25],
            ['package_name' => 'Enterprise Package', 'price' => 0, 'credit_amount' => 0],
        ];

        foreach ($packages as $package) {
            Package::query()->updateOrCreate(
                ['package_name' => $package['package_name']],
                $package,
            );
        }
    }

    private function seedCreditPricing(): void
    {
        $tiers = [
            ['credit_amount' => 10, 'price' => 15],
            ['credit_amount' => 50, 'price' => 60],
            ['credit_amount' => 100, 'price' => 100],
        ];

        foreach ($tiers as $tier) {
            CreditPricing::query()->updateOrCreate(
                ['credit_amount' => $tier['credit_amount']],
                ['price' => $tier['price'], 'status' => 'active'],
            );
        }
    }

    private function seedNicheSpecifications(): void
    {
        $specifications = [
            ['specification_name' => 'Domain Rating', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 100],
            ['specification_name' => 'Domain Authority', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 100],
            ['specification_name' => 'Trust Flow', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 100],
            ['specification_name' => 'Citation Flow', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 100],
            ['specification_name' => 'Monthly Traffic', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 1000000],
            ['specification_name' => 'Spam Score', 'field_type' => 'number', 'min_value' => 0, 'max_value' => 100],
        ];

        foreach ($specifications as $specification) {
            NicheSpecification::query()->updateOrCreate(
                ['specification_name' => $specification['specification_name']],
                [...$specification, 'status' => 'active'],
            );
        }
    }
}
