<?php

namespace Tests\Feature;

use App\Models\GlobalSetting;
use App\Models\Role;
use App\Models\User;
use App\Support\IcoFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GlobalSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_global_settings(): void
    {
        $this->get(route('admin.global-settings'))
            ->assertRedirect(route('login'));

        $this->putJson(route('admin.global-settings.update'), [])
            ->assertUnauthorized();
    }

    public function test_customer_cannot_open_or_change_global_settings(): void
    {
        $this->actingAs($this->customer())
            ->get(route('admin.global-settings'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->customer())
            ->putJson(route('admin.global-settings.update'), $this->payload())
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_save_company_details_and_see_them_after_refresh(): void
    {
        $payload = $this->payload([
            'company_name' => 'Linkora Labs',
            'button_colour' => '#112233',
        ]);

        $this->actingAs($this->admin())
            ->putJson(route('admin.global-settings.update'), $payload)
            ->assertOk()
            ->assertJsonPath('settings.companyName', 'Linkora Labs')
            ->assertJsonPath('settings.theme.button', '#112233');

        $this->assertDatabaseHas('global_settings', [
            'company_name' => 'Linkora Labs',
            'button_colour' => '#112233',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.global-settings'))
            ->assertOk()
            ->assertSee('Global Settings')
            ->assertSee('Linkora Labs')
            ->assertSee('value="Linkora Labs"', false);
    }

    public function test_saved_settings_render_on_the_public_site_and_spa_shell(): void
    {
        $payload = $this->payload([
            'footer_html' => '<p style="text-align: center">Footer note</p><script>alert(1)</script><a href="javascript:alert(1)" onclick="alert(1)">bad</a>',
            'button_colour' => '#112233',
        ]);

        $this->actingAs($this->admin())
            ->putJson(route('admin.global-settings.update'), $payload)
            ->assertOk();

        $html = (string) GlobalSetting::query()->value('footer_html');
        $this->assertStringContainsString('Footer note', $html);
        $this->assertStringContainsString('text-align: center', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onclick', $html);

        $this->get('/')
            ->assertOk()
            ->assertSee('Linkora Digital Solutions (Pvt) Ltd', false)
            ->assertSee('Terms and Conditions', false)
            ->assertSee('href="/terms-and-conditions"', false)
            ->assertSee('Footer note', false)
            ->assertSee('--color-brand: #112233', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('href="javascript:alert(1)"', false)
            ->assertDontSee('onclick="alert(1)"', false);

        $this->get('/', ['X-Backlink-Spa' => '1'])
            ->assertOk()
            ->assertJsonPath('shell.settings.websiteName', 'Linkora')
            ->assertJsonPath('shell.settings.theme.button', '#112233')
            ->assertJsonPath('shell.settings.footerLinks.0.url', '/terms-and-conditions');

        $this->actingAs($this->customer())
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertDontSee('--color-brand: #112233', false)
            ->assertSee('BacklinkPro', false)
            ->assertDontSee('Linkora Digital Solutions (Pvt) Ltd', false);
    }

    public function test_invalid_colour_and_script_link_are_rejected(): void
    {
        $payload = $this->payload([
            'primary_colour' => 'navy',
            'footer_links' => [
                ['label' => 'Bad', 'url' => 'javascript:alert(1)', 'target' => '_self'],
            ],
        ]);

        $this->actingAs($this->admin())
            ->putJson(route('admin.global-settings.update'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['primary_colour', 'footer_links.0.url']);
    }

    public function test_upload_fields_cannot_set_stored_image_paths(): void
    {
        $payload = $this->payload([
            'logo_path' => 'branding/evil.png',
            'favicon_path' => 'branding/evil.ico',
        ]);

        $this->actingAs($this->admin())
            ->putJson(route('admin.global-settings.update'), $payload)
            ->assertOk();

        $settings = GlobalSetting::query()->firstOrFail();
        $this->assertNull($settings->logo_path);
        $this->assertNull($settings->favicon_path);
    }

    public function test_logo_upload_fits_inside_250_by_80_and_can_be_removed_by_an_admin(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.global-settings.branding.store', 'logo'), [
                'image' => $this->raster(500, 100, 'logo.png'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('width', 250)
            ->assertJsonPath('height', 50);

        $path = GlobalSetting::query()->value('logo_path');
        $this->assertIsString($path);
        Storage::disk('public')->assertExists($path);
        $image = imagecreatefromstring(Storage::disk('public')->get($path));
        $this->assertNotFalse($image);
        $this->assertSame(250, imagesx($image));
        $this->assertSame(50, imagesy($image));
        imagedestroy($image);

        $this->actingAs($this->customer())
            ->deleteJson(route('admin.global-settings.branding.destroy', 'logo'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->admin())
            ->deleteJson(route('admin.global-settings.branding.destroy', 'logo'))
            ->assertOk();

        Storage::disk('public')->assertMissing($path);
        $this->assertNull(GlobalSetting::query()->value('logo_path'));
    }

    public function test_text_file_is_rejected_for_logo_upload(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.global-settings.branding.store', 'logo'), [
                'image' => UploadedFile::fake()->create('notes.txt', 20, 'text/plain'),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_wallpaper_is_cropped_to_1920_by_1080_from_the_top(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.global-settings.branding.store', 'wallpaper'), [
                'image' => $this->raster(200, 400, 'wall.png', function ($image): void {
                    $red = imagecolorallocate($image, 255, 0, 0);
                    $blue = imagecolorallocate($image, 0, 0, 255);
                    imagefilledrectangle($image, 0, 0, 199, 39, $red);
                    imagefilledrectangle($image, 0, 40, 199, 399, $blue);
                }),
                'wallpaper_position' => 'top',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('width', 1920)
            ->assertJsonPath('height', 1080);

        $path = GlobalSetting::query()->value('wallpaper_path');
        $this->assertIsString($path);
        $image = imagecreatefromstring(Storage::disk('public')->get($path));
        $this->assertNotFalse($image);
        $this->assertSame(1920, imagesx($image));
        $this->assertSame(1080, imagesy($image));
        $color = imagecolorat($image, 20, 20);
        $this->assertGreaterThan(200, ($color >> 16) & 255);
        $this->assertLessThan(40, $color & 255);
        imagedestroy($image);
    }

    public function test_favicon_upload_writes_a_real_32_pixel_ico(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.global-settings.branding.store', 'favicon'), [
                'image' => $this->raster(64, 40, 'mark.png'),
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('width', 32)
            ->assertJsonPath('height', 32);

        $path = GlobalSetting::query()->value('favicon_path');
        $this->assertSame('branding/favicon.ico', $path);
        $contents = Storage::disk('public')->get($path);
        $this->assertIsString($contents);
        $this->assertSame("\x00\x00\x01\x00", substr($contents, 0, 4));
        $this->assertNotSame("\x89PNG", substr($contents, 0, 4));
        $this->assertSame(32, ord($contents[6]));
        $this->assertSame(32, ord($contents[7]));
        $this->assertSame(32, unpack('v', substr($contents, 12, 2))[1]);

        $decoded = IcoFile::decode($contents);
        $this->assertSame(32, imagesx($decoded));
        $this->assertSame(32, imagesy($decoded));
        imagedestroy($decoded);

        $this->get('/')
            ->assertOk()
            ->assertSee('rel="icon"', false)
            ->assertSee('favicon.ico', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $settings = GlobalSetting::query()->firstOrFail();

        return array_replace([
            'company_name' => $settings->company_name,
            'website_name' => $settings->website_name,
            'website_url' => $settings->website_url,
            'company_email' => $settings->company_email,
            'support_email' => $settings->support_email,
            'phone_number' => $settings->phone_number,
            'company_address' => $settings->company_address,
            'city' => $settings->city,
            'state' => $settings->state,
            'postal_code' => $settings->postal_code,
            'country' => $settings->country,
            'currency' => $settings->currency,
            'timezone' => $settings->timezone,
            'date_format' => $settings->date_format,
            'default_language' => $settings->default_language,
            'copyright_text' => $settings->copyright_text,
            'wallpaper_position' => $settings->wallpaper_position,
            'footer_copyright' => $settings->footer_copyright,
            'footer_description' => $settings->footer_description,
            'footer_html' => $settings->footer_html,
            'footer_links' => $settings->footer_links,
            'primary_colour' => $settings->primary_colour,
            'secondary_colour' => $settings->secondary_colour,
            'accent_colour' => $settings->accent_colour,
            'background_colour' => $settings->background_colour,
            'text_colour' => $settings->text_colour,
            'button_colour' => $settings->button_colour,
        ], $overrides);
    }

    /**
     * @param  callable(\GdImage): void|null  $paint
     */
    private function raster(int $width, int $height, string $name, ?callable $paint = null): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        $paint ??= function ($canvas): void {
            $color = imagecolorallocate($canvas, 249, 115, 22);
            imagefilledrectangle($canvas, 0, 0, imagesx($canvas), imagesy($canvas), $color);
        };
        $paint($image);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);
        $path = tempnam(sys_get_temp_dir(), 'brand');
        file_put_contents((string) $path, $binary);

        return new UploadedFile((string) $path, $name, 'image/png', null, true);
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Admin'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => 'avery@example.test'], [
            'name' => 'Avery Admin',
            'password' => 'password-1',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'admin',
        ]);
    }

    private function customer(): User
    {
        $role = Role::query()->firstOrCreate(['role_name' => 'Customer'], ['permissions' => []]);

        return User::query()->firstOrCreate(['email' => 'casey@example.test'], [
            'name' => 'Casey Customer',
            'password' => 'password-1',
            'role_id' => $role->id,
            'status' => 'active',
            'account_type' => 'customer',
        ]);
    }
}
