@extends('layouts.public')

@section('document_title', 'Contact – BacklinkPro')
@section('meta_description', 'Contact the BacklinkPro team about guest posts, niche edits, packages, or a custom link building campaign.')

@section('content')
    @php
        $email = '[YOUR EMAIL]';
        $phone = '[YOUR PHONE]';
        $address = '[YOUR ADDRESS]';
        $channels = [
            ['label' => 'Email', 'value' => $email, 'href' => 'mailto:'.$email, 'icon' => 'mail'],
            ['label' => 'Phone', 'value' => $phone, 'href' => null, 'icon' => 'phone'],
            ['label' => 'Office', 'value' => $address, 'href' => null, 'icon' => 'pin'],
        ];
    @endphp

    <style>
        .contact-page { color: #152029; overflow: hidden; }
        .contact-hero {
            position: relative;
            background:
                radial-gradient(ellipse 80% 70% at 88% 18%, rgba(255, 73, 23, 0.28), transparent 55%),
                radial-gradient(ellipse 60% 50% at 8% 90%, rgba(214, 58, 16, 0.18), transparent 50%),
                linear-gradient(145deg, #152029 0%, #1E303C 48%, #243844 100%);
            color: #fff;
            padding: 28px 20px 72px;
        }
        .contact-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,0.55), transparent 85%);
            pointer-events: none;
        }
        .contact-orb {
            position: absolute;
            right: -40px;
            top: 40px;
            width: min(340px, 48vw);
            height: min(340px, 48vw);
            border-radius: 50%;
            background: #FF4917;
            opacity: 0.9;
            animation: contact-orb 8s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes contact-orb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-18px, 22px) scale(1.05); }
        }
        .contact-wrap { position: relative; z-index: 1; max-width: 1120px; margin: 0 auto; }
        .contact-crumb { margin: 0 0 28px; color: rgba(255,255,255,0.72); font-size: 14px; font-weight: 700; }
        .contact-crumb a { color: inherit; text-decoration: none; }
        .contact-crumb a:hover { color: #fff; }
        .contact-kicker {
            margin: 0 0 12px;
            color: #FFB399;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .contact-title {
            margin: 0;
            max-width: 14ch;
            font-family: Montserrat, sans-serif;
            font-size: clamp(34px, 5vw, 56px);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
        }
        .contact-lead {
            margin: 18px 0 0;
            max-width: 34rem;
            color: rgba(255,255,255,0.82);
            font-size: 17px;
            line-height: 1.65;
        }
        .contact-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 28px 0 0;
            padding: 0;
            list-style: none;
        }
        .contact-pills li {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            padding: 0 14px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            backdrop-filter: blur(6px);
            animation: contact-rise 0.7s ease both;
        }
        .contact-pills li:nth-child(2) { animation-delay: 0.08s; }
        .contact-pills li:nth-child(3) { animation-delay: 0.16s; }
        .contact-pills li span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #FF4917;
            box-shadow: 0 0 0 3px rgba(255, 73, 23, 0.25);
        }
        @keyframes contact-rise {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .contact-main {
            position: relative;
            margin-top: -48px;
            padding: 0 20px 80px;
            background:
                linear-gradient(180deg, transparent 0 48px, #F2F2F2 48px),
                #F2F2F2;
        }
        .contact-panel {
            display: grid;
            gap: 20px;
            max-width: 1120px;
            margin: 0 auto;
            animation: contact-rise 0.75s ease 0.12s both;
        }
        .contact-side, .contact-form {
            border: 1px solid #E3E5E8;
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(21, 32, 41, 0.08);
        }
        .contact-side { padding: 24px; }
        .contact-form { padding: 28px 24px; }
        .contact-side h2, .contact-form h2 {
            margin: 0;
            font-family: Montserrat, sans-serif;
            font-size: 20px;
            font-weight: 800;
        }
        .contact-side > p, .contact-form > p.contact-intro {
            margin: 8px 0 0;
            color: #4A5560;
            font-size: 15px;
            line-height: 1.55;
        }
        .contact-channels { margin: 22px 0 0; padding: 0; list-style: none; display: grid; gap: 12px; }
        .contact-channels a,
        .contact-channels .contact-channel {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            min-height: 72px;
            border: 1px solid #E3E5E8;
            border-radius: 16px;
            background: #F7F8FA;
            padding: 14px 16px;
            color: inherit;
            text-decoration: none;
            transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }
        .contact-channels a:hover {
            border-color: #FF4917;
            background: #FFF1EC;
            transform: translateY(-2px);
        }
        .contact-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            border-radius: 12px;
            background: #FFF1EC;
            color: #D63A10;
        }
        .contact-channels strong {
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4A5560;
        }
        .contact-channels span {
            display: block;
            margin-top: 4px;
            color: #152029;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
            word-break: break-word;
        }
        .contact-hours {
            margin-top: 18px;
            border-radius: 16px;
            background: linear-gradient(135deg, #152029, #243844);
            color: #fff;
            padding: 18px;
        }
        .contact-hours p { margin: 0; font-size: 14px; line-height: 1.55; color: rgba(255,255,255,0.78); }
        .contact-hours strong {
            display: block;
            margin-bottom: 6px;
            color: #FFB399;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .contact-fields { display: grid; gap: 14px; margin-top: 20px; }
        .contact-row { display: grid; gap: 14px; }
        .contact-fields label { display: grid; gap: 7px; font-size: 13px; font-weight: 800; color: #152029; }
        .contact-fields input,
        .contact-fields textarea,
        .contact-fields select {
            min-height: 50px;
            border: 1px solid #E3E5E8;
            border-radius: 14px;
            background: #F7F8FA;
            padding: 12px 14px;
            color: #152029;
            font: inherit;
            font-size: 15px;
            font-weight: 500;
            transition: border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }
        .contact-fields textarea { min-height: 150px; resize: vertical; }
        .contact-fields input:hover,
        .contact-fields textarea:hover,
        .contact-fields select:hover { border-color: #C9CED4; }
        .contact-fields input:focus,
        .contact-fields textarea:focus,
        .contact-fields select:focus {
            outline: none;
            border-color: #FF4917;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(255, 73, 23, 0.12);
        }
        .contact-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            margin-top: 4px;
            border: 0;
            border-radius: 14px;
            background: #D63A10;
            padding: 0 24px;
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .contact-submit:hover { background: #FF4917; transform: translateY(-1px); }
        .contact-submit:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        .contact-note { margin: 14px 0 0; color: #4A5560; font-size: 13px; line-height: 1.5; }
        .contact-status { margin: 12px 0 0; min-height: 1.2em; color: #C0340D; font-size: 14px; font-weight: 700; }
        .contact-topics {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }
        .contact-topics button {
            min-height: 38px;
            border: 1px solid #E3E5E8;
            border-radius: 999px;
            background: #fff;
            padding: 0 14px;
            color: #152029;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
        }
        .contact-topics button:hover,
        .contact-topics button.is-active {
            border-color: #FF4917;
            background: #FFF1EC;
            color: #C0340D;
        }
        .contact-topics button:focus-visible { outline: 2px solid #D63A10; outline-offset: 2px; }
        @media (min-width: 900px) {
            .contact-hero { padding: 36px 28px 96px; }
            .contact-panel { grid-template-columns: 0.92fr 1.08fr; gap: 24px; align-items: start; }
            .contact-side { padding: 28px; }
            .contact-form { padding: 32px; }
            .contact-row { grid-template-columns: 1fr 1fr; }
            .contact-main { margin-top: -56px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .contact-orb, .contact-pills li, .contact-panel { animation: none; }
        }
    </style>

    <div class="contact-page">
        <section class="contact-hero" aria-labelledby="contact-heading">
            <div class="contact-orb" aria-hidden="true"></div>
            <div class="contact-wrap">
                <nav class="contact-crumb" aria-label="Breadcrumb">
                    <a href="{{ url('/') }}">Home</a>
                    <span aria-hidden="true"> / </span>
                    <span>Contact</span>
                </nav>
                <p class="contact-kicker">BacklinkPro support</p>
                <h1 id="contact-heading" class="contact-title">Let’s plan your next backlink campaign</h1>
                <p class="contact-lead">Ask about guest posts, niche edits, packages, or a custom order. Our SEO team replies within [HOURS] hours on business days.</p>
                <ul class="contact-pills">
                    <li><span aria-hidden="true"></span> Real publisher placements</li>
                    <li><span aria-hidden="true"></span> Verified live URLs</li>
                    <li><span aria-hidden="true"></span> Agency-ready reports</li>
                </ul>
            </div>
        </section>

        <section class="contact-main" aria-label="Contact details and form">
            <div class="contact-panel">
                <aside class="contact-side">
                    <h2>Reach us directly</h2>
                    <p>Prefer email or a quick call? Use the details below, or send a message on the right.</p>
                    <ul class="contact-channels">
                        @foreach ($channels as $channel)
                            <li>
                                @if ($channel['href'])
                                    <a href="{{ $channel['href'] }}">
                                        <span class="contact-icon" aria-hidden="true">
                                            @if ($channel['icon'] === 'mail')
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>
                                            @elseif ($channel['icon'] === 'phone')
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
                                            @else
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                            @endif
                                        </span>
                                        <span>
                                            <strong>{{ $channel['label'] }}</strong>
                                            <span>{{ $channel['value'] }}</span>
                                        </span>
                                    </a>
                                @else
                                    <div class="contact-channel">
                                        <span class="contact-icon" aria-hidden="true">
                                            @if ($channel['icon'] === 'phone')
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.1a2 2 0 0 1 2.1-.4c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
                                            @else
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                            @endif
                                        </span>
                                        <span>
                                            <strong>{{ $channel['label'] }}</strong>
                                            <span>{{ $channel['value'] }}</span>
                                        </span>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="contact-hours">
                        <strong>Response time</strong>
                        <p>We reply within [HOURS] hours, Monday–Friday. Urgent campaign questions are prioritized for active customers.</p>
                    </div>
                </aside>

                <div class="contact-form">
                    <h2>Send a message</h2>
                    <p class="contact-intro">Tell us what you need. We’ll follow up with clear next steps.</p>

                    <div class="contact-topics" role="group" aria-label="Quick topics">
                        @foreach (['General question', 'Guest post', 'Niche edit', 'Packages', 'Custom campaign'] as $topic)
                            <button type="button" data-topic="{{ $topic }}" @class(['is-active' => $topic === 'General question'])>{{ $topic }}</button>
                        @endforeach
                    </div>

                    <form id="contact-form" class="contact-fields" novalidate>
                        <input type="hidden" name="topic" id="contact-topic" value="General question">
                        <div class="contact-row">
                            <label>
                                Name
                                <input type="text" name="name" id="contact-name" required maxlength="80" autocomplete="name" placeholder="Your name">
                            </label>
                            <label>
                                Email
                                <input type="email" name="email" id="contact-email" required maxlength="120" autocomplete="email" placeholder="you@company.com">
                            </label>
                        </div>
                        <label>
                            Message
                            <textarea name="message" id="contact-message" required maxlength="2000" placeholder="Share your niche, target URLs, budget range, or timeline"></textarea>
                        </label>
                        <button class="contact-submit" type="submit">
                            Send message
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>
                    <p id="contact-status" class="contact-status" role="status" aria-live="polite"></p>
                    <p class="contact-note">Your message opens in your email app so you can send it straight to our team.</p>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('contact-form');
    var status = document.getElementById('contact-status');
    var topicInput = document.getElementById('contact-topic');
    var topicButtons = document.querySelectorAll('.contact-topics [data-topic]');
    if (!form) return;

    topicButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            topicButtons.forEach(function (item) { item.classList.remove('is-active'); });
            button.classList.add('is-active');
            topicInput.value = button.getAttribute('data-topic') || 'General question';
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        var name = document.getElementById('contact-name').value.trim();
        var email = document.getElementById('contact-email').value.trim();
        var topic = topicInput.value;
        var message = document.getElementById('contact-message').value.trim();

        if (!name || !email || !message) {
            status.textContent = 'Please fill in your name, email, and message.';
            return;
        }

        var subject = encodeURIComponent('BacklinkPro contact: ' + topic);
        var body = encodeURIComponent(
            'Name: ' + name + '\n' +
            'Email: ' + email + '\n' +
            'Topic: ' + topic + '\n\n' +
            message
        );
        status.textContent = 'Opening your email app…';
        window.location.href = 'mailto:{{ $email }}?subject=' + subject + '&body=' + body;
    });
})();
</script>
@endpush
