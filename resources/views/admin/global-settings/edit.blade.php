@extends('layouts.admin')

@section('title', 'Global Settings')

@section('content')
    @php
        $input = 'mt-2 w-full rounded-xl border border-navy/15 bg-white px-4 py-3 text-sm text-navy outline-none placeholder:text-navy/40 focus:border-brand focus:ring-2 focus:ring-brand/30';
        $state = [
            'fields' => [
                'company_name' => old('company_name', $settings->company_name),
                'website_name' => old('website_name', $settings->website_name),
                'website_url' => old('website_url', $settings->website_url),
                'company_email' => old('company_email', $settings->company_email),
                'support_email' => old('support_email', $settings->support_email),
                'phone_number' => old('phone_number', $settings->phone_number),
                'company_address' => old('company_address', $settings->company_address),
                'city' => old('city', $settings->city),
                'state' => old('state', $settings->state),
                'postal_code' => old('postal_code', $settings->postal_code),
                'country' => old('country', $settings->country),
                'currency' => old('currency', $settings->currency),
                'timezone' => old('timezone', $settings->timezone),
                'date_format' => old('date_format', $settings->date_format),
                'default_language' => old('default_language', $settings->default_language),
                'copyright_text' => old('copyright_text', $settings->copyright_text),
                'wallpaper_position' => old('wallpaper_position', $settings->wallpaper_position),
                'footer_copyright' => old('footer_copyright', $settings->footer_copyright),
                'footer_description' => old('footer_description', $settings->footer_description),
                'footer_html' => old('footer_html', $settings->footer_html),
                'primary_colour' => old('primary_colour', $settings->primary_colour),
                'secondary_colour' => old('secondary_colour', $settings->secondary_colour),
                'accent_colour' => old('accent_colour', $settings->accent_colour),
                'background_colour' => old('background_colour', $settings->background_colour),
                'text_colour' => old('text_colour', $settings->text_colour),
                'button_colour' => old('button_colour', $settings->button_colour),
            ],
            'footer_links' => old('footer_links', $preview['footerLinks']),
            'images' => [
                'logo' => $preview['logoUrl'],
                'wallpaper' => $preview['wallpaperUrl'],
                'favicon' => $preview['faviconUrl'],
            ],
        ];
    @endphp

    <div class="w-full space-y-6">
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold tracking-wider text-brand uppercase">Settings</p>
                <h1 class="mt-2 text-2xl font-semibold">Global Settings</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-navy/70">Company details, branding, footer content, and the colours used on the public website.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" id="settings-reset" class="btn-outline">Reset</button>
                <button type="submit" form="global-settings-form" id="settings-save" class="btn-primary">Save changes</button>
            </div>
        </section>

        @include('admin.niches.partials.alerts')
        <div id="settings-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert"></div>
        <div id="settings-toast" class="hidden rounded-xl border px-4 py-3 text-sm" role="status"></div>

        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Global settings sections">
            @foreach (['general' => 'General', 'company' => 'Company Information', 'branding' => 'Branding', 'footer' => 'Footer', 'theme' => 'Theme'] as $tab => $label)
                <button
                    type="button"
                    class="settings-tab rounded-xl px-4 py-2 text-sm font-medium"
                    role="tab"
                    id="tab-{{ $tab }}"
                    data-tab="{{ $tab }}"
                    aria-controls="panel-{{ $tab }}"
                    aria-selected="{{ $tab === 'general' ? 'true' : 'false' }}"
                >{{ $label }}</button>
            @endforeach
        </div>

        <form id="global-settings-form" method="POST" action="{{ route('admin.global-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section id="panel-general" class="panel space-y-5 p-6 sm:p-8" role="tabpanel" aria-labelledby="tab-general">
                <div>
                    <h2 class="text-lg font-semibold">General</h2>
                    <p class="mt-1 text-sm text-navy/60">Website identity, locale, and the copyright line stored for the public site.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="website_name" class="text-sm font-medium">Website name</label>
                        <input id="website_name" name="website_name" type="text" value="{{ $state['fields']['website_name'] }}" required maxlength="255" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="website_url" class="text-sm font-medium">Website URL</label>
                        <input id="website_url" name="website_url" type="url" value="{{ $state['fields']['website_url'] }}" required maxlength="255" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="currency" class="text-sm font-medium">Currency</label>
                        <select id="currency" name="currency" class="{{ $input }}">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency }}" @selected($state['fields']['currency'] === $currency)>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="timezone" class="text-sm font-medium">Timezone</label>
                        <select id="timezone" name="timezone" class="{{ $input }}">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected($state['fields']['timezone'] === $timezone)>{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_format" class="text-sm font-medium">Date format</label>
                        <select id="date_format" name="date_format" class="{{ $input }}">
                            @foreach ($dateFormats as $format)
                                <option value="{{ $format }}" @selected($state['fields']['date_format'] === $format)>{{ $format }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="default_language" class="text-sm font-medium">Default language</label>
                        <select id="default_language" name="default_language" class="{{ $input }}">
                            @foreach ($languages as $language)
                                <option value="{{ $language }}" @selected($state['fields']['default_language'] === $language)>{{ $language }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="copyright_text" class="text-sm font-medium">Copyright text</label>
                    <input id="copyright_text" name="copyright_text" type="text" value="{{ $state['fields']['copyright_text'] }}" required maxlength="500" class="{{ $input }}">
                </div>
            </section>

            <section id="panel-company" class="panel hidden space-y-5 p-6 sm:p-8" role="tabpanel" aria-labelledby="tab-company" hidden>
                <div>
                    <h2 class="text-lg font-semibold">Company information</h2>
                    <p class="mt-1 text-sm text-navy/60">These details appear in the public footer and contact area.</p>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="company_name" class="text-sm font-medium">Company name</label>
                        <input id="company_name" name="company_name" type="text" value="{{ $state['fields']['company_name'] }}" required maxlength="255" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="company_email" class="text-sm font-medium">Company email</label>
                        <input id="company_email" name="company_email" type="email" value="{{ $state['fields']['company_email'] }}" required maxlength="255" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="support_email" class="text-sm font-medium">Support email</label>
                        <input id="support_email" name="support_email" type="email" value="{{ $state['fields']['support_email'] }}" required maxlength="255" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="phone_number" class="text-sm font-medium">Phone number</label>
                        <input id="phone_number" name="phone_number" type="text" value="{{ $state['fields']['phone_number'] }}" required maxlength="40" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="country" class="text-sm font-medium">Country</label>
                        <input id="country" name="country" type="text" value="{{ $state['fields']['country'] }}" required maxlength="120" class="{{ $input }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="company_address" class="text-sm font-medium">Company address</label>
                        <textarea id="company_address" name="company_address" required maxlength="1000" rows="3" class="{{ $input }}">{{ $state['fields']['company_address'] }}</textarea>
                    </div>
                    <div>
                        <label for="city" class="text-sm font-medium">City</label>
                        <input id="city" name="city" type="text" value="{{ $state['fields']['city'] }}" required maxlength="120" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="state" class="text-sm font-medium">State / province</label>
                        <input id="state" name="state" type="text" value="{{ $state['fields']['state'] }}" required maxlength="120" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="postal_code" class="text-sm font-medium">Postal code</label>
                        <input id="postal_code" name="postal_code" type="text" value="{{ $state['fields']['postal_code'] }}" required maxlength="20" class="{{ $input }}">
                    </div>
                </div>
            </section>

            <section id="panel-branding" class="panel hidden space-y-6 p-6 sm:p-8" role="tabpanel" aria-labelledby="tab-branding" hidden>
                <div>
                    <h2 class="text-lg font-semibold">Branding</h2>
                    <p class="mt-1 text-sm text-navy/60">Uploads save immediately, without reloading this page. Images are resized on the server before they are stored.</p>
                </div>

                <article class="rounded-xl border border-navy/10 p-4 sm:p-5" data-asset="logo">
                    <h3 class="font-semibold">Company logo</h3>
                    <p class="mt-1 text-sm text-navy/60">PNG, JPG, JPEG, or WebP. Fitted inside 250 × 80 pixels without stretching. Maximum 4 MB.</p>
                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="grid h-24 w-full max-w-[280px] place-items-center rounded-xl bg-canvas px-3">
                            <img data-preview alt="Current company logo" class="max-h-20 max-w-[250px] object-contain {{ $preview['logoUrl'] ? '' : 'hidden' }}" @if ($preview['logoUrl']) src="{{ $preview['logoUrl'] }}" @endif>
                            <span data-empty class="text-sm text-navy/50 {{ $preview['logoUrl'] ? 'hidden' : '' }}">No logo uploaded</span>
                        </div>
                        <div class="min-w-0 flex-1 space-y-3">
                            <input data-file type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm">
                            <div data-progress class="hidden h-1.5 overflow-hidden rounded-full bg-navy/10"><div data-bar class="h-full bg-brand" style="width: 0%"></div></div>
                            <p data-status class="hidden text-sm text-navy/70"></p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-upload class="btn-primary" disabled>Upload logo</button>
                                <button type="button" data-remove class="btn-outline" @disabled(! $preview['logoUrl'])>Remove logo</button>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-xl border border-navy/10 p-4 sm:p-5" data-asset="wallpaper">
                    <h3 class="font-semibold">Website wallpaper</h3>
                    <p class="mt-1 text-sm text-navy/60">JPG, PNG, or WebP. Cropped to 1920 × 1080. Maximum 8 MB.</p>
                    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,20rem)_1fr]">
                        <div class="overflow-hidden rounded-xl bg-canvas">
                            <img data-preview alt="Current website wallpaper" class="aspect-video w-full object-cover {{ $preview['wallpaperUrl'] ? '' : 'hidden' }}" @if ($preview['wallpaperUrl']) src="{{ $preview['wallpaperUrl'] }}" @endif>
                            <div data-empty class="grid aspect-video place-items-center text-sm text-navy/50 {{ $preview['wallpaperUrl'] ? 'hidden' : '' }}">No wallpaper uploaded</div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label for="wallpaper_position" class="text-sm font-medium">Background position</label>
                                <select id="wallpaper_position" name="wallpaper_position" class="{{ $input }}">
                                    <option value="center" @selected($state['fields']['wallpaper_position'] === 'center')>Center</option>
                                    <option value="top" @selected($state['fields']['wallpaper_position'] === 'top')>Top</option>
                                    <option value="cover" @selected($state['fields']['wallpaper_position'] === 'cover')>Cover</option>
                                </select>
                            </div>
                            <input data-file type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm">
                            <div data-progress class="hidden h-1.5 overflow-hidden rounded-full bg-navy/10"><div data-bar class="h-full bg-brand" style="width: 0%"></div></div>
                            <p data-status class="hidden text-sm text-navy/70"></p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-upload class="btn-primary" disabled>Upload wallpaper</button>
                                <button type="button" data-remove class="btn-outline" @disabled(! $preview['wallpaperUrl'])>Remove wallpaper</button>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-xl border border-navy/10 p-4 sm:p-5" data-asset="favicon">
                    <h3 class="font-semibold">Favicon</h3>
                    <p class="mt-1 text-sm text-navy/60">PNG, JPG, JPEG, or ICO. Converted to a 32 × 32 favicon.ico. Maximum 2 MB.</p>
                    <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="grid h-20 w-20 place-items-center rounded-xl bg-canvas">
                            <img data-preview alt="Current favicon" class="h-8 w-8 object-contain {{ $preview['faviconUrl'] ? '' : 'hidden' }}" @if ($preview['faviconUrl']) src="{{ $preview['faviconUrl'] }}" @endif>
                            <span data-empty class="text-xs text-navy/50 {{ $preview['faviconUrl'] ? 'hidden' : '' }}">None</span>
                        </div>
                        <div class="min-w-0 flex-1 space-y-3">
                            <input data-file type="file" accept="image/png,image/jpeg,image/x-icon,image/vnd.microsoft.icon,.ico" class="block w-full text-sm">
                            <div data-progress class="hidden h-1.5 overflow-hidden rounded-full bg-navy/10"><div data-bar class="h-full bg-brand" style="width: 0%"></div></div>
                            <p data-status class="hidden text-sm text-navy/70"></p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-upload class="btn-primary" disabled>Upload favicon</button>
                                <button type="button" data-remove class="btn-outline" @disabled(! $preview['faviconUrl'])>Remove favicon</button>
                            </div>
                        </div>
                    </div>
                </article>
            </section>

            <section id="panel-footer" class="panel hidden space-y-5 p-6 sm:p-8" role="tabpanel" aria-labelledby="tab-footer" hidden>
                <div>
                    <h2 class="text-lg font-semibold">Footer</h2>
                    <p class="mt-1 text-sm text-navy/60">Copyright, description, rich text, and the policy links shown on the public website. HTML is sanitized before it is stored.</p>
                </div>
                <div>
                    <label for="footer_copyright" class="text-sm font-medium">Footer copyright text</label>
                    <input id="footer_copyright" name="footer_copyright" type="text" value="{{ $state['fields']['footer_copyright'] }}" required maxlength="500" class="{{ $input }}">
                </div>
                <div>
                    <label for="footer_description" class="text-sm font-medium">Footer description</label>
                    <textarea id="footer_description" name="footer_description" maxlength="1000" rows="3" class="{{ $input }}">{{ $state['fields']['footer_description'] }}</textarea>
                </div>
                <div>
                    <p class="text-sm font-medium">Footer HTML content</p>
                    <div class="mt-2 flex flex-wrap gap-1 rounded-t-xl border border-b-0 border-navy/15 bg-canvas p-2" role="toolbar" aria-label="Footer formatting">
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="bold"><strong>B</strong></button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="italic"><em>I</em></button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="underline"><span class="underline">U</span></button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="justifyLeft">Left</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="justifyCenter">Center</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="justifyRight">Right</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="insertUnorderedList">Bullets</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="insertOrderedList">Numbered</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="link">Link</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="unlink">Unlink</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="source">HTML</button>
                        <button type="button" class="btn-outline px-3 py-1.5" data-command="preview">Preview</button>
                    </div>
                    <div id="link-dialog" class="hidden border border-navy/15 bg-white px-3 py-3">
                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_10rem_auto] sm:items-end">
                            <div>
                                <label for="editor-link-url" class="text-sm font-medium">Link URL</label>
                                <input id="editor-link-url" type="text" placeholder="/contact or https://example.com" class="{{ $input }}">
                            </div>
                            <div>
                                <label for="editor-link-target" class="text-sm font-medium">Open in</label>
                                <select id="editor-link-target" class="{{ $input }}">
                                    <option value="_self">Same tab</option>
                                    <option value="_blank">New tab</option>
                                </select>
                            </div>
                            <button type="button" id="editor-link-apply" class="btn-primary">Apply link</button>
                        </div>
                    </div>
                    <div id="footer-editor" class="min-h-40 rounded-b-xl border border-navy/15 bg-white px-4 py-3 text-sm leading-6 outline-none focus:border-brand" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Footer HTML content"></div>
                    <textarea id="footer-html" name="footer_html" class="{{ $input }} hidden font-mono" rows="8" hidden></textarea>
                    <div id="footer-preview" class="mt-3 hidden rounded-xl border border-navy/10 bg-canvas px-4 py-3 text-sm leading-6"></div>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-medium">Footer links</h3>
                        <button type="button" id="add-footer-link" class="btn-outline">Add link</button>
                    </div>
                    <div id="footer-links" class="mt-3 space-y-3"></div>
                </div>
            </section>

            <section id="panel-theme" class="panel hidden space-y-5 p-6 sm:p-8" role="tabpanel" aria-labelledby="tab-theme" hidden>
                <div>
                    <h2 class="text-lg font-semibold">Theme</h2>
                    <p class="mt-1 text-sm text-navy/60">Saved colours are applied to the public website. The admin workspace keeps its own orange and navy theme.</p>
                </div>
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'primary_colour' => 'Primary colour',
                            'secondary_colour' => 'Secondary colour',
                            'accent_colour' => 'Accent colour',
                            'background_colour' => 'Background colour',
                            'text_colour' => 'Text colour',
                            'button_colour' => 'Button colour',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="text-sm font-medium">{{ $label }}</label>
                                <div class="mt-2 flex items-center gap-3">
                                    <input id="{{ $name }}-picker" type="color" value="{{ $state['fields'][$name] }}" data-color-picker="{{ $name }}" class="h-11 w-14 cursor-pointer rounded-lg border border-navy/15 bg-white p-1" aria-label="{{ $label }} picker">
                                    <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ $state['fields'][$name] }}" required maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" data-color-text="{{ $name }}" class="{{ $input }} mt-0 uppercase">
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <aside id="theme-preview" class="rounded-xl border border-navy/10 p-5">
                        <p class="text-xs font-semibold tracking-wider uppercase" data-preview-accent>Preview</p>
                        <h3 class="mt-3 text-xl font-semibold" data-preview-heading>Linkora</h3>
                        <p class="mt-2 text-sm" data-preview-text>Public pages use these colours for text, background, and buttons.</p>
                        <button type="button" class="mt-4 rounded-lg px-4 py-2.5 text-sm font-medium text-white" data-preview-button>Sample button</button>
                    </aside>
                </div>
            </section>
        </form>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="global-settings-state">@json($state)</script>
    <script>
        (() => {
            const fieldClass = @json($input);
            const form = document.getElementById('global-settings-form');
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const toast = document.getElementById('settings-toast');
            const errors = document.getElementById('settings-errors');
            const saveButton = document.getElementById('settings-save');
            const stateNode = document.getElementById('global-settings-state');
            let saved = JSON.parse(stateNode.textContent || '{}');
            const editor = document.getElementById('footer-editor');
            const source = document.getElementById('footer-html');
            const preview = document.getElementById('footer-preview');
            const linkDialog = document.getElementById('link-dialog');
            let sourceMode = false;

            function notify(message, tone) {
                toast.textContent = message;
                toast.className = tone === 'error'
                    ? 'rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700'
                    : 'rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
            }

            function showErrors(list) {
                const messages = Object.values(list || {}).flat();
                if (messages.length === 0) {
                    errors.classList.add('hidden');
                    errors.textContent = '';
                    return;
                }
                errors.innerHTML = messages.map((message) => `<p>${escapeHtml(String(message))}</p>`).join('');
                errors.classList.remove('hidden');
            }

            function escapeHtml(value) {
                return value.replace(/[&<>"']/g, (character) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                })[character]);
            }

            function safeUrl(value) {
                const url = value.trim();
                if (url === '' || /[\u0000-\u001F\u007F]/.test(url) || url.startsWith('//')) {
                    return false;
                }
                if (url.startsWith('/')) {
                    return true;
                }
                try {
                    const parsed = new URL(url);
                    return parsed.protocol === 'http:' || parsed.protocol === 'https:' || parsed.protocol === 'mailto:';
                } catch {
                    return false;
                }
            }

            document.querySelectorAll('.settings-tab').forEach((tab) => {
                tab.addEventListener('click', () => activateTab(tab.dataset.tab));
            });

            function activateTab(name) {
                document.querySelectorAll('.settings-tab').forEach((tab) => {
                    const active = tab.dataset.tab === name;
                    tab.setAttribute('aria-selected', active ? 'true' : 'false');
                    tab.classList.toggle('bg-brand', active);
                    tab.classList.toggle('text-white', active);
                    tab.classList.toggle('bg-white', !active);
                    tab.classList.toggle('text-navy', !active);
                    tab.classList.toggle('ring-1', !active);
                    tab.classList.toggle('ring-navy/10', !active);
                });
                document.querySelectorAll('[role="tabpanel"]').forEach((panel) => {
                    const show = panel.id === `panel-${name}`;
                    panel.classList.toggle('hidden', !show);
                    panel.hidden = !show;
                });
            }

            activateTab('general');

            function syncEditorToField() {
                if (sourceMode) {
                    editor.innerHTML = source.value;
                } else {
                    source.value = editor.innerHTML;
                }
            }

            editor.innerHTML = saved.fields?.footer_html || '';
            source.value = editor.innerHTML;
            document.execCommand('styleWithCSS', false, true);

            document.querySelectorAll('[data-command]').forEach((button) => {
                button.addEventListener('click', () => {
                    const command = button.dataset.command;
                    if (command === 'source') {
                        if (sourceMode) {
                            editor.innerHTML = source.value;
                            editor.classList.remove('hidden');
                            editor.hidden = false;
                            source.classList.add('hidden');
                            source.hidden = true;
                        } else {
                            source.value = editor.innerHTML;
                            editor.classList.add('hidden');
                            editor.hidden = true;
                            source.classList.remove('hidden');
                            source.hidden = false;
                        }
                        sourceMode = !sourceMode;
                        preview.classList.add('hidden');
                        return;
                    }
                    if (command === 'preview') {
                        syncEditorToField();
                        preview.innerHTML = sourceMode ? source.value : editor.innerHTML;
                        preview.classList.toggle('hidden');
                        return;
                    }
                    if (command === 'link') {
                        linkDialog.classList.toggle('hidden');
                        return;
                    }
                    editor.focus();
                    document.execCommand(command, false, null);
                });
            });

            document.getElementById('editor-link-apply').addEventListener('click', () => {
                const url = document.getElementById('editor-link-url').value.trim();
                const target = document.getElementById('editor-link-target').value;
                if (!safeUrl(url)) {
                    notify('Enter a site path or an http(s) URL.', 'error');
                    return;
                }
                editor.focus();
                document.execCommand('createLink', false, url);
                const anchor = editor.querySelector(`a[href="${CSS.escape(url)}"]`);
                if (anchor) {
                    if (target === '_blank') {
                        anchor.target = '_blank';
                        anchor.rel = 'noopener noreferrer';
                    } else {
                        anchor.removeAttribute('target');
                        anchor.removeAttribute('rel');
                    }
                }
                linkDialog.classList.add('hidden');
            });

            const linksRoot = document.getElementById('footer-links');

            function linkRow(link = { label: '', url: '', target: '_self' }) {
                const row = document.createElement('div');
                row.className = 'grid gap-3 rounded-xl border border-navy/10 p-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_9rem_auto]';
                row.innerHTML = `
                    <input data-label type="text" maxlength="80" placeholder="Label" class="${fieldClass}" value="">
                    <input data-url type="text" maxlength="255" placeholder="/privacy-policy" class="${fieldClass}" value="">
                    <select data-target class="${fieldClass}">
                        <option value="_self">Same tab</option>
                        <option value="_blank">New tab</option>
                    </select>
                    <button type="button" data-remove-link class="btn-outline">Remove</button>
                `;
                row.querySelector('[data-label]').value = link.label || '';
                row.querySelector('[data-url]').value = link.url || '';
                row.querySelector('[data-target]').value = link.target === '_blank' ? '_blank' : '_self';
                row.querySelector('[data-remove-link]').addEventListener('click', () => row.remove());
                return row;
            }

            function renderLinks(links) {
                linksRoot.replaceChildren();
                (links || []).forEach((link) => linksRoot.append(linkRow(link)));
            }

            renderLinks(saved.footer_links || []);
            document.getElementById('add-footer-link').addEventListener('click', () => linksRoot.append(linkRow()));

            function collectLinks() {
                return [...linksRoot.children].map((row) => ({
                    label: row.querySelector('[data-label]').value.trim(),
                    url: row.querySelector('[data-url]').value.trim(),
                    target: row.querySelector('[data-target]').value,
                })).filter((link) => link.label !== '' || link.url !== '');
            }

            function fieldNames() {
                return Object.keys(saved.fields || {});
            }

            function readFields() {
                const fields = {};
                fieldNames().forEach((name) => {
                    if (name === 'footer_html') {
                        return;
                    }
                    const control = form.elements.namedItem(name);
                    if (control) {
                        fields[name] = control.value;
                    }
                });
                syncEditorToField();
                fields.footer_html = source.value;
                return fields;
            }

            function writeFields(fields) {
                Object.entries(fields || {}).forEach(([name, value]) => {
                    if (name === 'footer_html') {
                        editor.innerHTML = value || '';
                        source.value = editor.innerHTML;
                        return;
                    }
                    const control = form.elements.namedItem(name);
                    if (control) {
                        control.value = value ?? '';
                    }
                });
                document.querySelectorAll('[data-color-text]').forEach((input) => {
                    const picker = document.querySelector(`[data-color-picker="${input.dataset.colorText}"]`);
                    if (picker && /^#[0-9A-Fa-f]{6}$/.test(input.value)) {
                        picker.value = input.value;
                    }
                });
                paintTheme();
            }

            document.getElementById('settings-reset').addEventListener('click', () => {
                writeFields(saved.fields);
                renderLinks(saved.footer_links || []);
                sourceMode = false;
                editor.classList.remove('hidden');
                editor.hidden = false;
                source.classList.add('hidden');
                source.hidden = true;
                preview.classList.add('hidden');
                showErrors({});
                notify('Unsaved changes were reset.', 'ok');
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                syncEditorToField();
                const payload = readFields();
                payload.footer_links = collectLinks();
                saveButton.disabled = true;
                const original = saveButton.textContent;
                saveButton.textContent = 'Saving…';
                showErrors({});
                try {
                    const response = await fetch(form.action, {
                        method: 'PUT',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });
                    const body = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        showErrors(body.errors || { form: [body.message || 'The settings could not be saved.'] });
                        notify(body.message || 'The settings could not be saved.', 'error');
                        return;
                    }
                    saved.fields = payload;
                    saved.footer_links = payload.footer_links;
                    notify(body.message || 'Global settings saved.', 'ok');
                } catch (error) {
                    notify('The settings could not be saved.', 'error');
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = original;
                }
            });

            function paintTheme() {
                const card = document.getElementById('theme-preview');
                const background = form.background_colour.value;
                const text = form.text_colour.value;
                const button = form.button_colour.value;
                const accent = form.accent_colour.value;
                const primary = form.primary_colour.value;
                if (!/^#[0-9A-Fa-f]{6}$/.test(background)) {
                    return;
                }
                card.style.backgroundColor = background;
                card.style.color = text;
                card.querySelector('[data-preview-heading]').style.color = primary;
                card.querySelector('[data-preview-accent]').style.color = accent;
                card.querySelector('[data-preview-text]').style.color = text;
                const sample = card.querySelector('[data-preview-button]');
                sample.style.backgroundColor = button;
            }

            document.querySelectorAll('[data-color-picker]').forEach((picker) => {
                picker.addEventListener('input', () => {
                    const text = document.querySelector(`[data-color-text="${picker.dataset.colorPicker}"]`);
                    text.value = picker.value.toUpperCase();
                    paintTheme();
                });
            });
            document.querySelectorAll('[data-color-text]').forEach((input) => {
                input.addEventListener('input', () => {
                    if (/^#[0-9A-Fa-f]{6}$/.test(input.value)) {
                        document.querySelector(`[data-color-picker="${input.dataset.colorText}"]`).value = input.value;
                        paintTheme();
                    }
                });
            });
            paintTheme();

            const limits = { logo: 4 * 1024 * 1024, wallpaper: 8 * 1024 * 1024, favicon: 2 * 1024 * 1024 };
            const types = {
                logo: ['image/png', 'image/jpeg', 'image/webp'],
                wallpaper: ['image/png', 'image/jpeg', 'image/webp'],
                favicon: ['image/png', 'image/jpeg', 'image/x-icon', 'image/vnd.microsoft.icon'],
            };

            document.querySelectorAll('[data-asset]').forEach((card) => {
                const asset = card.dataset.asset;
                const fileInput = card.querySelector('[data-file]');
                const upload = card.querySelector('[data-upload]');
                const remove = card.querySelector('[data-remove]');
                const image = card.querySelector('[data-preview]');
                const empty = card.querySelector('[data-empty]');
                const progress = card.querySelector('[data-progress]');
                const bar = card.querySelector('[data-bar]');
                const status = card.querySelector('[data-status]');

                function showImage(url) {
                    if (url) {
                        image.src = url;
                        image.classList.remove('hidden');
                        empty.classList.add('hidden');
                        remove.disabled = false;
                    } else {
                        image.removeAttribute('src');
                        image.classList.add('hidden');
                        empty.classList.remove('hidden');
                        remove.disabled = true;
                    }
                }

                fileInput.addEventListener('change', () => {
                    const file = fileInput.files?.[0];
                    upload.disabled = !file;
                    if (!file) {
                        return;
                    }
                    const allowed = types[asset];
                    const ico = asset === 'favicon' && file.name.toLowerCase().endsWith('.ico');
                    if (!ico && file.type && !allowed.includes(file.type)) {
                        notify('That file type is not accepted for this image.', 'error');
                        fileInput.value = '';
                        upload.disabled = true;
                        return;
                    }
                    if (file.size > limits[asset]) {
                        notify('The selected file is larger than the allowed size.', 'error');
                        fileInput.value = '';
                        upload.disabled = true;
                        return;
                    }
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = () => showImage(reader.result);
                        reader.readAsDataURL(file);
                    }
                });

                upload.addEventListener('click', () => {
                    const file = fileInput.files?.[0];
                    if (!file) {
                        return;
                    }
                    const body = new FormData();
                    body.append('image', file);
                    if (asset === 'wallpaper') {
                        body.append('wallpaper_position', form.wallpaper_position.value);
                    }
                    const request = new XMLHttpRequest();
                    request.open('POST', @json(url('/admin/global-settings/branding')).replace(/\/$/, '') + '/' + asset);
                    request.setRequestHeader('Accept', 'application/json');
                    request.setRequestHeader('X-CSRF-TOKEN', token);
                    request.upload.addEventListener('progress', (event) => {
                        if (!event.lengthComputable) {
                            return;
                        }
                        progress.classList.remove('hidden');
                        bar.style.width = `${Math.round((event.loaded / event.total) * 100)}%`;
                    });
                    upload.disabled = true;
                    status.classList.remove('hidden');
                    status.textContent = 'Uploading…';
                    request.addEventListener('load', () => {
                        progress.classList.add('hidden');
                        bar.style.width = '0%';
                        const body = JSON.parse(request.responseText || '{}');
                        if (request.status >= 200 && request.status < 300) {
                            showImage(body.url);
                            fileInput.value = '';
                            upload.disabled = true;
                            status.textContent = body.message || 'Uploaded.';
                            if (asset === 'favicon' && body.url) {
                                let icon = document.querySelector('link[rel="icon"]');
                                if (!icon) {
                                    icon = document.createElement('link');
                                    icon.rel = 'icon';
                                    document.head.appendChild(icon);
                                }
                                icon.href = body.url;
                            }
                            notify(body.message || 'Image uploaded.', 'ok');
                            return;
                        }
                        status.textContent = '';
                        notify(body.message || 'The image could not be uploaded.', 'error');
                        upload.disabled = false;
                    });
                    request.addEventListener('error', () => {
                        progress.classList.add('hidden');
                        notify('The image could not be uploaded.', 'error');
                        upload.disabled = false;
                    });
                    request.send(body);
                });

                remove.addEventListener('click', async () => {
                    if (!window.confirm('Remove this image?')) {
                        return;
                    }
                    remove.disabled = true;
                    status.classList.remove('hidden');
                    status.textContent = 'Removing…';
                    const response = await fetch(@json(url('/admin/global-settings/branding')) + '/' + asset, {
                        method: 'DELETE',
                        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    });
                    const body = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        notify(body.message || 'The image could not be removed.', 'error');
                        remove.disabled = false;
                        return;
                    }
                    showImage('');
                    status.textContent = body.message || 'Image removed.';
                    notify(body.message || 'Image removed.', 'ok');
                });
            });
        })();
    </script>
@endpush
