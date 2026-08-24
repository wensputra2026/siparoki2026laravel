<!-- Google Font: Plus Jakarta Sans -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

<style>
    :root {
        --font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
    }

    body {
        font-family: var(--font-family) !important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        letter-spacing: -0.01em;
    }

    /* Custom Modern Scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.4);
        border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.7);
    }
    .dark ::-webkit-scrollbar-thumb {
        background: rgba(75, 85, 99, 0.4);
    }
    .dark ::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.7);
    }

    /* ==========================================================================
       1. Login Page Styling
       ========================================================================== */
    .fi-simple-layout {
        background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.08) 0%, rgba(15, 23, 42, 0.02) 60%), linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%) !important;
        min-height: 100vh;
        position: relative;
    }

    .dark .fi-simple-layout {
        background: radial-gradient(circle at 50% 15%, rgba(245, 158, 11, 0.12) 0%, rgba(15, 23, 42, 0.95) 70%), linear-gradient(135deg, #090d16 0%, #0f172a 50%, #020617 100%) !important;
    }

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

    /* ==========================================================================
       2. Sidebar Enhancement
       ========================================================================== */
    .fi-sidebar {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-right: 1px solid rgba(226, 232, 240, 0.8) !important;
        background: #ffffff !important;
    }
    .dark .fi-sidebar {
        border-right: 1px solid rgba(30, 41, 59, 0.7) !important;
        background: #0f172a !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.08em !important;
        color: #94a3b8 !important;
        padding-top: 0.75rem !important;
        padding-bottom: 0.35rem !important;
    }

    .fi-sidebar-item-btn {
        border-radius: 0.625rem !important;
        font-weight: 500 !important;
        transition: all 0.18s ease !important;
        margin-top: 1px !important;
        margin-bottom: 1px !important;
    }
    .fi-sidebar-item-btn:hover {
        transform: translateX(3px);
        background-color: rgba(241, 245, 249, 0.9) !important;
    }
    .dark .fi-sidebar-item-btn:hover {
        background-color: rgba(30, 41, 59, 0.8) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-btn {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.12) 0%, rgba(245, 158, 11, 0.2) 100%) !important;
        color: #d97706 !important;
        font-weight: 700 !important;
        box-shadow: inset 3px 0 0 0 #d97706 !important;
    }
    .dark .fi-sidebar-item-active .fi-sidebar-item-btn {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.25) 100%) !important;
        color: #fbbf24 !important;
        box-shadow: inset 3px 0 0 0 #fbbf24 !important;
    }

    /* ==========================================================================
       3. Topbar Glassmorphic
       ========================================================================== */
    .fi-topbar {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
    }
    .dark .fi-topbar {
        background: rgba(15, 23, 42, 0.88) !important;
        border-bottom: 1px solid rgba(30, 41, 59, 0.7) !important;
    }

    /* ==========================================================================
       4. Cards, Widgets, and Stats
       ========================================================================== */
    .fi-section,
    .fi-widget {
        border-radius: 1rem !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -2px rgba(0, 0, 0, 0.03) !important;
        transition: box-shadow 0.25s ease, transform 0.25s ease !important;
    }
    .dark .fi-section,
    .dark .fi-widget {
        border: 1px solid rgba(30, 41, 59, 0.8) !important;
        box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.3) !important;
    }

    .fi-wi-stats-overview-stat {
        border-radius: 1rem !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%) !important;
        box-shadow: 0 4px 14px -2px rgba(15, 23, 42, 0.04) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08) !important;
    }
    .dark .fi-wi-stats-overview-stat {
        border: 1px solid rgba(30, 41, 59, 0.8) !important;
        background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%) !important;
        box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.25) !important;
    }
    .dark .fi-wi-stats-overview-stat:hover {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.45) !important;
    }

    /* ==========================================================================
       5. Tables & Datatables
       ========================================================================== */
    .fi-ta-ctn {
        border-radius: 1rem !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.03) !important;
        overflow: hidden !important;
    }
    .dark .fi-ta-ctn {
        border: 1px solid rgba(30, 41, 59, 0.8) !important;
        box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.3) !important;
    }

    .fi-ta-table tbody tr {
        transition: background-color 0.15s ease !important;
    }
    .fi-ta-table tbody tr:hover {
        background-color: rgba(248, 250, 252, 0.9) !important;
    }
    .dark .fi-ta-table tbody tr:hover {
        background-color: rgba(30, 41, 59, 0.6) !important;
    }

    /* ==========================================================================
       6. Buttons & Badges
       ========================================================================== */
    .fi-btn {
        border-radius: 0.625rem !important;
        font-weight: 600 !important;
        letter-spacing: -0.01em !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .fi-btn:hover {
        transform: translateY(-1px);
    }
    .fi-btn:active {
        transform: translateY(0);
    }

    .fi-badge {
        border-radius: 9999px !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em !important;
        padding-left: 0.65rem !important;
        padding-right: 0.65rem !important;
    }

    /* Form Inputs */
    .fi-input,
    .fi-select-input {
        border-radius: 0.625rem !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
    }

    /* Header Titles */
    .fi-header-heading {
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
    }
</style>
