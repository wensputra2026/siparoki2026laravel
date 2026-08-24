<style>
    /* Modern Aesthetic for Filament Login Page */
    .fi-simple-layout {
        background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.08) 0%, rgba(15, 23, 42, 0.02) 60%), linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%) !important;
        min-height: 100vh;
        position: relative;
    }

    .dark .fi-simple-layout {
        background: radial-gradient(circle at 50% 15%, rgba(245, 158, 11, 0.12) 0%, rgba(15, 23, 42, 0.95) 70%), linear-gradient(135deg, #090d16 0%, #0f172a 50%, #020617 100%) !important;
    }

    /* Card styling */
    .fi-simple-main {
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        border-radius: 1.25rem !important;
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.03) !important;
        padding: 2.25rem !important;
        transition: all 0.3s ease;
    }

    .dark .fi-simple-main {
        background: rgba(15, 23, 42, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(245, 158, 11, 0.1) !important;
    }

    /* Logo centering & elevation */
    .fi-simple-header {
        margin-bottom: 1.5rem !important;
    }

    .fi-simple-header img {
        filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.07));
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .fi-simple-header img:hover {
        transform: scale(1.04);
    }

    /* Primary button aesthetic */
    .fi-simple-main button[type="submit"],
    .fi-btn-color-primary {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        box-shadow: 0 4px 14px 0 rgba(217, 119, 6, 0.35) !important;
        border: none !important;
        font-weight: 700 !important;
        letter-spacing: 0.025em !important;
        border-radius: 0.625rem !important;
        transition: all 0.25s ease !important;
    }

    .fi-simple-main button[type="submit"]:hover,
    .fi-btn-color-primary:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
        box-shadow: 0 6px 20px 0 rgba(217, 119, 6, 0.45) !important;
        transform: translateY(-1px);
    }

    /* Headings */
    .fi-simple-main h1 {
        font-size: 1.5rem !important;
        font-weight: 800 !important;
        letter-spacing: -0.025em !important;
        color: #0f172a !important;
    }

    .dark .fi-simple-main h1 {
        color: #f8fafc !important;
    }

    .fi-simple-main p.fi-simple-main-subheading {
        font-size: 0.875rem !important;
        color: #64748b !important;
        margin-top: 0.35rem !important;
    }

    .dark .fi-simple-main p.fi-simple-main-subheading {
        color: #94a3b8 !important;
    }
</style>
