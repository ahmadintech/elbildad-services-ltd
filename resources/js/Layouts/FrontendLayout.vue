<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-800 selection:bg-blue-200">
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 transition-all duration-300">
      <div style="width:100%;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:72px;max-width:1400px;margin:0 auto;">
        <!-- Logo -->
        <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
          <img src="/images/logo/elbildad-logo.png" alt="Elbildad Services Logo" style="height:44px;width:auto;object-fit:contain;">
          <div style="display:flex;flex-direction:column;line-height:1;">
            <span style="font-size:22px;font-weight:800;color:#0f172a;letter-spacing:-0.3px;">Elbildad</span>
            <span style="font-size:10px;font-weight:600;color:#2563eb;text-transform:uppercase;letter-spacing:3px;">Services</span>
          </div>
        </a>
        <!-- Desktop nav -->
        <nav style="display:none;gap:2rem;align-items:center;" class="frontend-desktop-nav">
          <a href="/" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Home</a>
          <a href="/#services" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Services</a>
          <a href="/rfq" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">RFQ</a>
          <a href="/contact" style="font-size:15px;font-weight:500;color:#374151;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#374151'">Contact</a>
        </nav>
        <!-- CTA Buttons -->
        <div style="display:flex;align-items:center;gap:12px;">
          <a href="/visa-application" style="display:none;background:#7c3aed;color:#fff;font-size:14px;font-weight:700;padding:10px 22px;border-radius:10px;text-decoration:none;transition:all 0.2s;" class="frontend-desktop-nav hover:bg-purple-700">Visa & Study in China</a>
          <a href="/rfq" style="display:none;background:#2563eb;color:#fff;font-size:14px;font-weight:700;padding:10px 22px;border-radius:10px;text-decoration:none;transition:all 0.2s;" class="frontend-desktop-nav hover:bg-blue-700">Request Quote</a>
          <a href="/signin" style="display:none;font-size:14px;font-weight:600;color:#374151;text-decoration:none;transition:color 0.2s;" class="frontend-desktop-nav hover:text-blue-600">Sign In</a>
          <!-- Mobile hamburger -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" style="background:none;border:none;cursor:pointer;padding:8px;border-radius:8px;" class="frontend-mobile-only" aria-label="Toggle menu">
            <svg v-if="!mobileMenuOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round"><line x1="6" y1="18" x2="18" y2="6"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      </div>
      <!-- Mobile drawer -->
      <div v-if="mobileMenuOpen" style="position:fixed;top:0;right:0;width:280px;height:100vh;background:#fff;z-index:9999;box-shadow:-4px 0 32px rgba(0,0,0,0.12);padding:24px 20px;display:flex;flex-direction:column;gap:8px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
          <span style="font-size:17px;font-weight:700;color:#0f172a;">Menu</span>
          <button @click="mobileMenuOpen = false" style="background:none;border:none;cursor:pointer;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#374151" stroke-width="2" stroke-linecap="round"><line x1="6" y1="18" x2="18" y2="6"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <a href="/" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Home</a>
        <a href="/#services" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Services</a>
        <a href="/rfq" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">RFQ</a>
        <a href="/contact" style="display:block;padding:12px 0;font-size:15px;font-weight:600;color:#374151;text-decoration:none;border-bottom:1px solid #f1f5f9;">Contact</a>
        <div style="margin-top:12px;display:flex;flex-direction:column;gap:10px;">
          <a href="/visa-application" style="text-align:center;padding:12px;background:#7c3aed;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;">Visa & Study in China</a>
          <a href="/rfq" style="text-align:center;padding:12px;background:#2563eb;color:#fff;border-radius:10px;font-weight:700;text-decoration:none;">Request Quote</a>
          <a href="/signin" style="text-align:center;padding:12px;border:1.5px solid #2563eb;color:#2563eb;border-radius:10px;font-weight:700;text-decoration:none;">Sign In</a>
        </div>
      </div>
    </header>

    <main class="pt-24 pb-12">
      <slot />
    </main>
    <FrontendFooter />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import FrontendFooter from '../Components/FrontendFooter.vue'
const mobileMenuOpen = ref(false)
</script>

<style scoped>
@media (min-width: 768px) {
  .frontend-desktop-nav { display: flex !important; }
  .frontend-mobile-only { display: none !important; }
}
@media (max-width: 767px) {
  .frontend-mobile-only { display: flex !important; }
}
</style>
