<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleCustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_firebase_google_login_verifies_the_token_and_starts_a_customer_session(): void
    {
        $token = $this->firebaseToken();

        $this->postJson(route('auth.google.firebase'), ['id_token' => $token])
            ->assertOk()
            ->assertJsonPath('redirect', route('customer.dashboard'));

        $customer = User::query()->where('google_subject', 'firebase-google-subject')->firstOrFail();
        $this->assertAuthenticatedAs($customer);
        $this->assertSame('Customer', $customer->role->role_name);
    }

    public function test_firebase_login_rejects_invalid_signature_and_wrong_project(): void
    {
        $token = $this->firebaseToken();
        [$header, $payload] = explode('.', $token);
        $this->postJson(route('auth.google.firebase'), ['id_token' => "$header.$payload.AAAA"])
            ->assertUnprocessable();

        config(['services.firebase.projectId' => 'different-project']);
        $this->postJson(route('auth.google.firebase'), ['id_token' => $token])
            ->assertUnprocessable();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_firebase_login_does_not_link_an_existing_account_by_email(): void
    {
        User::factory()->create(['email' => 'firebase@example.test']);

        $this->postJson(route('auth.google.firebase'), ['id_token' => $this->firebaseToken()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('google');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    private function firebaseToken(): string
    {
        $opensslConfig = tempnam(sys_get_temp_dir(), 'firebase-test-');
        try {
            $key = openssl_pkey_new([
                'config' => $opensslConfig,
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            $this->assertInstanceOf(\OpenSSLAsymmetricKey::class, $key);
        } finally {
            unlink($opensslConfig);
        }
        $project = config('services.firebase.projectId');
        $encode = fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $header = $encode(json_encode(['alg' => 'RS256', 'kid' => 'test-key']));
        $payload = $encode(json_encode([
            'aud' => $project, 'iss' => 'https://securetoken.google.com/'.$project,
            'sub' => 'firebase-user', 'exp' => now()->addHour()->timestamp,
            'iat' => now()->timestamp, 'auth_time' => now()->timestamp,
            'email' => 'firebase@example.test', 'email_verified' => true, 'name' => 'Firebase Customer',
            'firebase' => [
                'sign_in_provider' => 'google.com',
                'identities' => ['google.com' => ['firebase-google-subject']],
            ],
        ]));
        openssl_sign($header.'.'.$payload, $signature, $key, OPENSSL_ALGO_SHA256);
        Http::preventStrayRequests();
        Http::fake([
            'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com' => Http::response(['test-key' => openssl_pkey_get_details($key)['key']]),
        ]);

        return $header.'.'.$payload.'.'.$encode($signature);
    }

    public function test_verified_google_customer_can_sign_in(): void
    {
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
        Http::preventStrayRequests();
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'customer-subject', 'email' => 'google@example.test',
                'email_verified' => true, 'name' => 'Google Customer',
            ]),
        ]);

        $response = $this->get(route('auth.google'))->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('S256', $query['code_challenge_method']);

        $this->get(route('auth.google.callback', ['state' => $query['state'], 'code' => 'test-code']))
            ->assertRedirect(route('customer.dashboard'));

        $customer = User::query()->where('google_subject', 'customer-subject')->firstOrFail();
        $this->assertAuthenticatedAs($customer);
        $this->assertSame('Customer', $customer->role->role_name);
        $this->get(route('customer.dashboard'))->assertOk();
    }

    public function test_google_callback_rejects_a_mismatched_state(): void
    {
        Http::fake();
        $this->withSession(['google_oauth' => [
            'state' => 'expected-state', 'verifier' => 'test-verifier', 'expires' => now()->addMinute()->timestamp,
        ]])->get(route('auth.google.callback', ['state' => 'wrong-state', 'code' => 'test-code']))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        Http::assertNothingSent();
    }
}
