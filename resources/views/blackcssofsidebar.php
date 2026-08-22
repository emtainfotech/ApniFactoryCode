/* ==========================================================================
   PREMIUM COLORFUL SIDEBAR SYSTEM STYLING
   ========================================================================== */
:root {
    --apni-red: #bd1a1a;
    --transition-speed: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* Sidebar Container Base Setup overrides */
.sidebar-wrapper {
    background: #111111 !important; /* Premium Dark Matte Finish */
    box-shadow: 4px 0 20px rgba(0,0,0,0.2) !important;
    border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-header {
    background: #090909 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06) !important;
    padding: 15px 20px !important;
}

.logo-icon {
    max-height: 42px;
    filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.5));
}

.toggle-icon i {
    color: #ffffff !important;
    font-size: 13pt;
}

/* Metismenu Navigation Anchors Formatting Adjustments */
.metismenu a {
    color: rgba(255, 255, 255, 0.75) !important;
    font-size: 9.5pt !important;
    font-weight: 500 !important;
    padding: 11px 20px !important;
    display: flex !important;
    align-items: center !important;
    border-left: 4px solid transparent;
    transition: var(--transition-speed);
}

/* Submenu Lists Indent Elements Styling */
.metismenu ul {
    background: #161616 !important;
    padding: 5px 0 !important;
}

.metismenu ul a {
    padding: 8px 20px 8px 45px !important;
    font-size: 9pt !important;
    color: rgba(255, 255, 255, 0.6) !important;
}

.metismenu ul a:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.03) !important;
}

/* Dynamic CSS Hover Interactions */
.metismenu a:hover {
    background: rgba(255, 255, 255, 0.04) !important;
    color: #ffffff !important;
    border-left-color: var(--apni-red);
}

/* Parent Icon Contextual Wrappers System */
.parent-icon {
    width: 34px !important;
    height: 34px !important;
    border-radius: 8px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-right: 14px !important;
    font-size: 11pt !important;
    transition: var(--transition-speed);
}

/* Hover scales up icons gracefully */
.metismenu a:hover .parent-icon i {
    transform: scale(1.12);
}

/* Vibrant Individual Contextual Accent Layers Definitions */
.i-dashboard { background: rgba(0, 200, 115, 0.14) !important; color: #00c873 !important; }
.i-brand     { background: rgba(0, 150, 255, 0.14) !important; color: #0096ff !important; }
.i-category  { background: rgba(255, 168, 0, 0.14) !important; color: #ffa800 !important; }
.i-products  { background: rgba(141, 41, 255, 0.14) !important; color: #8d29ff !important; }
.i-orders    { background: rgba(255, 41, 100, 0.14) !important; color: #ff2964 !important; }
.i-coupon    { background: rgba(23, 162, 184, 0.14) !important; color: #17a2b8 !important; }
.i-ad        { background: rgba(2fd, 126, 20, 0.14) !important; color: #fd7e14 !important; }
.i-color     { background: rgba(232, 62, 140, 0.14) !important; color: #e83e8c !important; }
.i-credit    { background: rgba(111, 66, 193, 0.14) !important; color: #6f42c1 !important; }
.i-bank      { background: rgba(40, 167, 69, 0.14)  !important; color: #28a745 !important; }
.i-support   { background: rgba(220, 53, 69, 0.14)  !important; color: #dc3545 !important; }
.i-payment   { background: rgba(255, 193, 7, 0.14)   !important; color: #ffc107 !important; }
.i-restrict  { background: rgba(108, 117, 125, 0.14) !important; color: #adb5bd !important; }
.i-help      { background: rgba(32, 201, 151, 0.14)  !important; color: #20c997 !important; }

/* Active Menu Row Overrides Styling */
.metismenu li.mm-active > a {
    background: linear-gradient(90deg, rgba(189, 26, 26, 0.18) 0%, rgba(189, 26, 26, 0.01) 100%) !important;
    color: #ffffff !important;
    border-left-color: var(--apni-red) !important;
    font-weight: 600 !important;
}
