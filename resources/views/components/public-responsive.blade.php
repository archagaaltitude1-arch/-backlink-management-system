{{-- Sitewide responsive polish for the public customer site. --}}
<style>
    html { scroll-behavior: smooth; }
    body { overflow-x: hidden; }
    img, svg, video { max-width: 100%; height: auto; }
    main { min-width: 0; }
    main a, main button, main input, main select, main textarea {
        max-width: 100%;
    }

    /* Shared containers & type scale */
    .public-shell-pad {
        padding-left: max(16px, env(safe-area-inset-left));
        padding-right: max(16px, env(safe-area-inset-right));
    }

    @media (max-width: 1099px) {
        .nd-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .nd-table { width: 1100px; }
    }

    @media (max-width: 900px) {
        .pub-wrap,
        .bag-wrap,
        .ck-wrap,
        .contact-wrap,
        .site-front-wrap,
        .site-body-wrap,
        .pk-wrap,
        .home-cta-wrap {
            padding-left: 16px !important;
            padding-right: 16px !important;
        }

        .pub-head { flex-direction: column; align-items: stretch; }
        .pub-head h1 { font-size: 28px !important; }
        .pub-recent { width: 100%; }
        .pub-tools { flex-direction: column; align-items: stretch; }
        .pub-tools input,
        .pub-tools select { width: 100% !important; }
        .pub-filters { gap: 8px; }
        .pub-filters select { flex: 1 1 calc(50% - 8px); min-width: 0; }
        .pub-project { flex-direction: column; align-items: stretch; }
        .pub-create { width: 100%; }
        .pub-options { grid-template-columns: 1fr !important; }

        .bag-hero,
        .bag-layout,
        .co-grid { grid-template-columns: 1fr !important; }
        .bag-intro h1 { font-size: 30px !important; }
        .bag-picks { grid-template-columns: 1fr 1fr !important; }
        .bag-features { grid-template-columns: 1fr !important; }
        .bag-line { grid-template-columns: 1fr !important; }
        .co-top { grid-template-columns: 1fr !important; }
        .co-stats { flex-direction: column; align-items: stretch; }

        .ck-grid { grid-template-columns: 1fr !important; }
        .ck-fields { grid-template-columns: 1fr !important; }
        .ck-title { font-size: 28px !important; }

        .contact-panel { grid-template-columns: 1fr !important; }
        .contact-row { grid-template-columns: 1fr !important; }
        .contact-title { font-size: 32px !important; max-width: none !important; }

        .site-front h1 { font-size: 26px !important; }
        .site-front h2 { font-size: 20px !important; }
        .site-also-grid { grid-template-columns: 1fr !important; }

        .pk-grid { grid-template-columns: 1fr !important; }
        .pk-hero { min-height: 280px !important; padding: 24px 20px !important; }
        .pk-hero h2 { margin-top: 28px !important; font-size: 28px !important; }
        .pk-panel h1 { font-size: 26px !important; }
        .pk-price { font-size: 28px !important; }

        .home-hero-inner { gap: 28px !important; padding-top: 28px !important; padding-bottom: 36px !important; }
        .home-cta-card { grid-template-columns: 1fr !important; }
        .home-cta-actions { flex-direction: column; }
        .home-cta-primary,
        .home-cta-secondary { width: 100%; }

        .why-head { grid-template-columns: 1fr !important; }
        .why-title { font-size: 28px !important; }

        .cart-drawer-panel { width: 100vw !important; }
        .cart-drawer-item { grid-template-columns: 64px minmax(0, 1fr) auto; gap: 10px; }
        .cart-drawer-thumb { width: 64px; height: 64px; }
    }

    @media (max-width: 640px) {
        .pub-filters select { flex: 1 1 100%; }
        .bag-picks { grid-template-columns: 1fr !important; }
        .bag-intro,
        .bag-steps,
        .ck-card,
        .ck-side,
        .contact-card,
        .contact-form,
        .site-body-wrap > div,
        .pk-panel { padding: 18px !important; border-radius: 16px !important; }

        .contact-pills { flex-direction: column; align-items: stretch; }
        .contact-pills li { justify-content: flex-start; }

        .home-hero h1 { font-size: 30px !important; }
        .home-hero .mt-6.flex { width: 100%; }
        .home-hero .mt-6.flex a { width: 100%; justify-content: center; }

        .site-footer-main { grid-template-columns: 1fr !important; gap: 24px !important; padding: 36px 16px 20px !important; }
        .site-footer-cats,
        .site-footer-bar-inner { padding-left: 16px !important; padding-right: 16px !important; }
        .site-footer-bar-inner { flex-direction: column; align-items: flex-start; }

        .nd-publish { width: 100%; }
    }

    @media (max-width: 420px) {
        .pub-count,
        .ck-row,
        .cart-drawer-sub { font-size: 13px !important; }
        .cart-drawer-head h2 { font-size: 18px !important; }
        .pk-qty { width: 100%; justify-content: space-between; }
    }

    /* Touch-friendly controls */
    @media (hover: none) and (pointer: coarse) {
        .pub-filters select,
        .pub-tools select,
        .pub-tools input,
        .ck-fields input,
        .ck-fields textarea,
        .contact-fields input,
        .contact-fields textarea,
        .contact-fields select {
            min-height: 48px;
            font-size: 16px; /* avoids iOS zoom */
        }
    }
</style>
