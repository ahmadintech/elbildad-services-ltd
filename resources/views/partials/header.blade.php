<header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md transition-all duration-300 shadow-sm" id="elbildad-header">
    <div style="width:100%;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:72px;max-width:1400px;margin:0 auto;">
        {{-- Logo --}}
        <a href="{{ url('/') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
            <img src="/images/logo/elbildad-logo.png" alt="Elbildad Services Logo" style="height:44px;width:auto;object-fit:contain;">
            <div style="display:flex;flex-direction:column;line-height:1;">
                <span style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">Elbildad</span>
                <span style="font-size:10px;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:3px;">Services</span>
            </div>
        </a>

        {{-- Desktop nav --}}
        <nav style="display:none;gap:2rem;align-items:center;" class="rfq-desktop-nav">
            <a href="{{ url('/') }}" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Home</a>
            <a href="{{ url('/#services') }}" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Services</a>
            <a href="{{ url('/rfq') }}" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">RFQ</a>
            <a href="{{ url('/contact') }}" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Contact</a>
        </nav>

        {{-- CTA Buttons --}}
        <div style="display:flex;align-items:center;gap:12px;">
            <a href="{{ url('/visa-application') }}" style="display:none;background:#7c3aed;color:#fff;font-size:14px;font-weight:700;padding:10px 22px;border-radius:10px;text-decoration:none;transition:all 0.2s;" class="rfq-desktop-nav hover:bg-purple-700">Visa & Study in China</a>
            
            {{-- Theme Toggle --}}
            <button id="theme-toggle" class="rfq-desktop-nav" style="display:none;background:none;border:none;cursor:pointer;padding:8px;border-radius:50%;color:#374151;transition:all 0.2s;" aria-label="Toggle Dark Mode" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="theme-icon-moon" style="display:block"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="theme-icon-sun" style="display:none"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            </button>
            
            <a href="{{ url('/rfq') }}" style="display:none;background:#2563eb;color:#fff;font-size:14px;font-weight:700;padding:10px 22px;border-radius:10px;text-decoration:none;transition:all 0.2s;" class="rfq-desktop-nav hover:bg-blue-700">Request Quote</a>
            <a href="{{ route('login') }}" style="display:none;font-size:14px;font-weight:600;color:#374151;text-decoration:none;transition:color 0.2s;" class="rfq-desktop-nav hover:text-blue-600">Sign In</a>
            
            {{-- Mobile hamburger --}}
            <button id="rfq-menu-btn" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;" class="rfq-mobile-only" aria-label="Open menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="rfq-mobile-drawer" style="display:none;position:fixed;top:0;right:0;width:280px;height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 32px rgba(0,0,0,0.12);padding:24px 20px;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <span style="font-size:17px;font-weight:700;color:#0f172a;">Menu</span>
            <button id="rfq-menu-close" style="background:none;border:none;cursor:pointer;" aria-label="Close menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round"><line x1="6" y1="18" x2="18" y2="6"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <a href="{{ url('/') }}" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Home</a>
        <a href="{{ url('/#services') }}" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Services</a>
        <a href="{{ url('/rfq') }}" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">RFQ</a>
        <a href="{{ url('/contact') }}" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Contact</a>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px;">
            <a href="{{ url('/visa-application') }}" style="text-align:center;padding:12px;background:#7c3aed;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;">Visa & Study in China</a>
            <a href="{{ url('/rfq') }}" style="text-align:center;padding:12px;background:#2563eb;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;">Request Quote</a>
            <a href="{{ route('login') }}" style="text-align:center;padding:12px;border:1.5px solid #2563eb;color:#2563eb;border-radius:10px;font-weight:700;text-decoration:none;">Sign In</a>
        </div>
    </div>
</header>
<style>
    @media (min-width: 768px) { .rfq-desktop-nav { display: flex !important; } .rfq-mobile-only { display: none !important; } }
    @media (max-width: 767px) { .rfq-mobile-only { display: flex !important; } }
</style>
<script>
    (function() {
        var btn = document.getElementById('rfq-menu-btn');
        var close = document.getElementById('rfq-menu-close');
        var drawer = document.getElementById('rfq-mobile-drawer');
        if (btn) btn.addEventListener('click', function() { drawer.style.display = 'flex'; });
        if (close) close.addEventListener('click', function() { drawer.style.display = 'none'; });

        var themeToggle = document.getElementById('theme-toggle');
        var iconMoon = document.getElementById('theme-icon-moon');
        var iconSun = document.getElementById('theme-icon-sun');
        
        function updateThemeIcon() {
            if (document.documentElement.classList.contains('dark')) {
                if (iconMoon) iconMoon.style.display = 'none';
                if (iconSun) iconSun.style.display = 'block';
            } else {
                if (iconMoon) iconMoon.style.display = 'block';
                if (iconSun) iconSun.style.display = 'none';
            }
        }
        
        if (themeToggle) {
            updateThemeIcon();
            themeToggle.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
                updateThemeIcon();
            });
        }
    })();
</script>
