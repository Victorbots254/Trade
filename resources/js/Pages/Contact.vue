<template>
  <div class="min-h-screen font-sans pb-9" :class="isDark?'bg-[#0b0e11] text-white':'bg-white text-[#1e2329]'">
    <header class="sticky top-0 z-50 border-b px-6 h-14 flex items-center justify-between" :class="isDark?'bg-[#0b0e11] border-[#1e2329]':'bg-white border-gray-200'">
      <a href="/" class="flex items-center space-x-2">
        <div class="w-7 h-7 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-sm">T</div>
        <span class="font-bold text-lg" :class="isDark?'text-[#f0b90b]':'text-[#1e2329]'">TRADE<span class="text-[#f0b90b]">PRO</span></span>
      </a>
      <div class="flex items-center space-x-4 text-[13px]">
        <button @click="isDark=!isDark" class="p-2 rounded-lg" :class="isDark?'text-[#b7bdc6]':'text-[#474d57]'"><span v-if="isDark">☀️</span><span v-else>🌙</span></button>
        <a href="/" class="hover:text-[#f0b90b] transition" :class="isDark?'text-[#b7bdc6]':'text-[#474d57]'">← Back to Home</a>
      </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-16 space-y-12">
      <div class="text-center space-y-3">
        <div class="text-4xl">💬</div>
        <h1 class="text-4xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Contact Support</h1>
        <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">We're here 24/7. Average first response time: under 2 hours.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Contact Channels -->
        <div class="space-y-4">
          <h2 class="text-lg font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Get in Touch</h2>
          <div v-for="channel in channels" :key="channel.title" class="rounded-xl border p-4 flex items-center space-x-4" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
            <div class="text-2xl">{{ channel.icon }}</div>
            <div>
              <div class="font-bold text-[14px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ channel.title }}</div>
              <div class="text-[13px] text-[#f0b90b]">{{ channel.value }}</div>
              <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ channel.note }}</div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="rounded-2xl border p-6 space-y-4" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
          <h2 class="text-lg font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Send a Message</h2>
          <div v-if="sent" class="rounded-xl bg-[#0ecb81]/10 border border-[#0ecb81]/30 p-4 text-[#0ecb81] text-[13px] font-semibold">
            ✅ Message sent! We'll respond within 2 hours.
          </div>
          <template v-else>
            <div>
              <label class="block text-[12px] font-semibold mb-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Your Name</label>
              <input v-model="form.name" type="text" placeholder="John Trader" class="w-full rounded-lg px-3 py-2 text-[13px] border outline-none focus:border-[#f0b90b] transition" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-white placeholder-[#636e80]':'bg-white border-gray-200 text-[#1e2329]'" />
            </div>
            <div>
              <label class="block text-[12px] font-semibold mb-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Email Address</label>
              <input v-model="form.email" type="email" placeholder="you@example.com" class="w-full rounded-lg px-3 py-2 text-[13px] border outline-none focus:border-[#f0b90b] transition" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-white placeholder-[#636e80]':'bg-white border-gray-200 text-[#1e2329]'" />
            </div>
            <div>
              <label class="block text-[12px] font-semibold mb-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Topic</label>
              <select v-model="form.topic" class="w-full rounded-lg px-3 py-2 text-[13px] border outline-none focus:border-[#f0b90b] transition" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-white':'bg-white border-gray-200 text-[#1e2329]'">
                <option value="">Select a topic...</option>
                <option>Deposit Issue</option>
                <option>Withdrawal Issue</option>
                <option>Account / Login</option>
                <option>Trading Question</option>
                <option>Technical Problem</option>
                <option>Other</option>
              </select>
            </div>
            <div>
              <label class="block text-[12px] font-semibold mb-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Message</label>
              <textarea v-model="form.message" rows="4" placeholder="Describe your issue in detail..." class="w-full rounded-lg px-3 py-2 text-[13px] border outline-none focus:border-[#f0b90b] transition resize-none" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-white placeholder-[#636e80]':'bg-white border-gray-200 text-[#1e2329]'"></textarea>
            </div>
            <button @click="submitForm" class="w-full bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold py-2.5 rounded-xl text-sm transition">Send Message →</button>
          </template>
        </div>
      </div>
    </div>

    <div class="border-t py-6 text-center text-[12px]" :class="isDark?'border-[#1e2329] text-[#848e9c]':'border-gray-200 text-[#707a8a]'">
      © 2026 TradePro Inc. · <a href="/terms" class="hover:text-[#f0b90b]">Terms</a> · <a href="/privacy" class="hover:text-[#f0b90b]">Privacy</a>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue';
const isDark = ref(true);
const sent = ref(false);
const form = ref({ name:'', email:'', topic:'', message:'' });
function submitForm() {
  if (!form.value.name || !form.value.email || !form.value.message) return;
  sent.value = true;
}
const channels = [
  { icon:'📧', title:'Email Support', value:'support@tradepro.io', note:'Response within 2 hours, 24/7' },
  { icon:'💬', title:'Live Chat', value:'Available in-app', note:'For logged-in users on the trading terminal' },
  { icon:'🐦', title:'Twitter / X', value:'@TradePro_io', note:'For announcements and quick queries' },
  { icon:'📢', title:'Telegram', value:'t.me/TradePro', note:'Community announcements and support' },
];
</script>