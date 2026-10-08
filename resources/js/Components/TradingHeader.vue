<template>
  <header class="bg-slate-900 border-b border-slate-800 text-xs select-none shadow-md z-40 relative transition-colors duration-200">
    <ToastNotification ref="toastRef" />

    <div class="max-w-[1600px] mx-auto px-3 sm:px-4 lg:px-6 h-14 flex items-center justify-between">
      <!-- Left Brand Logo & Primary Navigation Links -->
      <div class="flex items-center space-x-4 lg:space-x-6">
        <!-- Logo -->
        <Link href="/terminal" class="flex items-center space-x-2 shrink-0 group">
          <div class="w-7 h-7 bg-[#f0b90b] group-hover:bg-[#d4a30b] rounded flex items-center justify-center font-black text-[#1e2329] text-sm shadow transition">
            T
          </div>
          <span class="font-bold text-slate-100 text-sm tracking-wide">
            TRADE<span class="text-[#f0b90b]">CO</span>
          </span>
        </Link>

        <!-- Navigation Links (Desktop) -->
        <nav class="hidden md:flex items-center space-x-1.5 font-medium text-[13px]">
          <!-- ADMIN & MODERATOR NAVIGATION -->
          <template v-if="user?.is_admin || user?.is_moderator">
            <Link href="/admin/p2p" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 font-bold text-xs"
                  :class="$page.url.startsWith('/admin/p2p') ? 'bg-[#f0b90b] text-[#1e2329] shadow' : 'text-[#f0b90b] bg-[#f0b90b]/10 border border-[#f0b90b]/30 hover:bg-[#f0b90b]/20'">
              <span>🛡️</span>
              <span>P2P Dispute Center</span>
            </Link>

            <Link v-if="user?.is_admin" href="/admin/users" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 font-bold text-xs"
                  :class="$page.url.startsWith('/admin/users') ? 'bg-amber-500 text-slate-950 shadow' : 'text-amber-300 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/60'">
              <span>👥</span>
              <span>Trader Controls</span>
            </Link>

            <Link href="/admin/deposits" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5 font-bold text-xs"
                  :class="$page.url.startsWith('/admin/deposits') ? 'bg-emerald-600 text-white shadow' : 'text-emerald-400 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/60'">
              <span>💰</span>
              <span>Deposit Approvals</span>
            </Link>

            <div class="h-4 w-px bg-slate-700 mx-1"></div>

            <Link href="/terminal" 
                  class="px-2.5 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs text-slate-400 hover:text-white hover:bg-slate-800">
              <span>Terminal ↗</span>
            </Link>
          </template>

          <!-- REGULAR TRADER NAVIGATION -->
          <template v-else>
            <Link href="/terminal" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5"
                  :class="$page.url === '/terminal' ? 'bg-slate-800 text-[#f0b90b] font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'">
              <span>Spot Trade</span>
            </Link>

            <Link href="/trade/options/BTC_USDT" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5"
                  :class="$page.url.includes('/trade/options') || $page.url === '/options' ? 'bg-slate-800 text-[#f0b90b] font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'">
              <span>Quick Options</span>
            </Link>

            <Link href="/p2p" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5"
                  :class="$page.url.startsWith('/p2p') ? 'bg-slate-800 text-[#f0b90b] font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'">
              <span>P2P Trading</span>
            </Link>

            <!-- USDT MMF (15%) Navigation Link -->
            <Link href="/monthly-interests" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1"
                  :class="$page.url === '/monthly-interests' ? 'bg-slate-800 text-[#f0b90b] font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'">
              <span>USDT MMF <span class="text-emerald-400 font-bold">(15%)</span></span>
            </Link>

            <Link href="/trades" 
                  class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5"
                  :class="$page.url === '/trades' ? 'bg-slate-800 text-[#f0b90b] font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60'">
              <span>Past Trades</span>
            </Link>
          </template>
        </nav>
      </div>

      <!-- Right User Controls, Balances, & CTAs -->
      <div class="flex items-center space-x-2 sm:space-x-3">
        <!-- DEMO vs LIVE MODE SWITCHER -->
        <div class="hidden lg:flex items-center p-0.5 bg-slate-950/80 rounded-lg border border-slate-800 text-[11px] font-semibold">
          <button @click="switchAccountMode('demo')" 
                  :class="accountMode === 'demo' ? 'bg-[#f0b90b]/20 text-[#f0b90b] border border-[#f0b90b]/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 border border-transparent'"
                  class="px-2.5 py-1 rounded-md transition flex items-center space-x-1">
            <span>Demo</span>
          </button>
          <button @click="switchAccountMode('live')" 
                  :class="accountMode === 'live' ? 'bg-[#f0b90b] text-[#1e2329] font-bold shadow-sm' : 'text-slate-400 hover:text-slate-200 border border-transparent'"
                  class="px-2.5 py-1 rounded-md transition flex items-center space-x-1">
            <span v-if="accountMode === 'live'" class="w-1.5 h-1.5 rounded-full bg-[#1e2329] animate-pulse"></span>
            <span>Live</span>
          </button>
        </div>

        <!-- AUTHENTICATED USER SECTION -->
        <template v-if="user">
          <!-- DEMO BALANCE DISPLAY -->
          <div v-if="accountMode === 'demo'" class="hidden sm:flex items-center space-x-1.5 bg-[#f0b90b]/10 border border-[#f0b90b]/25 px-2.5 py-1.5 rounded-lg font-mono text-xs">
            <span class="text-[#f0b90b] font-semibold text-[11px]">Demo:</span>
            <span class="text-amber-300 font-bold">${{ formatBalance(demoBalance) }}</span>
            <button @click="resetDemoBalance" :disabled="resetting"
                    title="Reset Demo Account to $10,000.00"
                    class="ml-1 text-[10px] text-[#f0b90b] hover:text-white bg-[#f0b90b]/20 hover:bg-[#f0b90b]/40 border border-[#f0b90b]/30 px-1.5 py-0.5 rounded transition flex items-center space-x-0.5">
              <svg v-if="resetting" class="animate-spin h-2.5 w-2.5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-else>↻</span>
              <span>Reset</span>
            </button>
          </div>

          <!-- LIVE BALANCE DISPLAY -->
          <div v-else class="hidden sm:flex items-center space-x-1.5 bg-emerald-500/10 border border-emerald-500/25 px-2.5 py-1.5 rounded-lg font-mono text-xs">
            <span class="text-emerald-400 font-semibold text-[11px]">Live:</span>
            <span class="text-emerald-300 font-bold">${{ formatBalance(liveBalance) }}</span>
            <span class="text-[10px] text-emerald-500/70 font-normal">USDT</span>
          </div>

          <!-- DEPOSIT & WITHDRAW CTAS -->
          <Link href="/deposit"
                class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] px-3.5 py-1.5 rounded-lg font-bold transition flex items-center space-x-1.5 text-xs shadow-sm hover:shadow-[#f0b90b]/20 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span>Deposit</span>
          </Link>
          
          <Link href="/withdraw"
                class="hidden md:flex bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700/70 px-3 py-1.5 rounded-lg font-semibold transition items-center space-x-1.5 text-xs shrink-0">
            <span>Withdraw</span>
          </Link>

          <!-- Divider -->
          <div class="h-5 w-px bg-slate-800 mx-0.5 hidden sm:block"></div>

          <!-- Theme Toggle -->
          <button @click="toggleTheme" 
                  title="Toggle Theme"
                  class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800 shrink-0">
            <svg v-if="currentTheme === 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
          </button>

          <!-- User Profile Dropdown -->
          <div class="relative" @mouseenter="showProfileDropdown = true" @mouseleave="showProfileDropdown = false">
            <button class="flex items-center space-x-2 text-slate-300 hover:text-white transition py-1 px-1.5 rounded-lg hover:bg-slate-800 cursor-pointer">
              <div class="w-7 h-7 bg-slate-800 border border-slate-700 rounded-full flex items-center justify-center text-xs font-bold text-[#f0b90b]">
                {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
              </div>
              <span class="font-medium hidden xl:block max-w-[100px] truncate text-slate-200">{{ user.name }}</span>
              <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <!-- Dropdown Menu -->
            <div v-if="showProfileDropdown" 
                 class="absolute top-full right-0 mt-1 w-60 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-2 z-50 text-xs font-medium divide-y divide-slate-800/80">
              <div class="px-4 py-2.5">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-slate-100 font-bold truncate">{{ user.name }}</span>
                  <span v-if="user.is_admin" class="bg-[#f0b90b]/15 text-[#f0b90b] text-[10px] px-1.5 py-0.5 rounded font-bold border border-[#f0b90b]/30">Admin</span>
                  <span v-else-if="user.is_moderator" class="bg-blue-500/15 text-blue-400 text-[10px] px-1.5 py-0.5 rounded font-bold border border-blue-500/30">Moderator</span>
                </div>
                <div class="text-slate-400 text-[11px] truncate">{{ user.email }}</div>
              </div>

              <!-- Admin & Mod Links -->
              <div v-if="user.is_admin || user.is_moderator" class="py-1">
                <Link href="/admin/p2p" class="px-4 py-2 hover:bg-slate-800 text-[#f0b90b] flex items-center space-x-2 transition font-semibold">
                  <span>🛡️</span>
                  <span>P2P Center (Admin/Mod)</span>
                </Link>
                <Link v-if="user.is_admin" href="/admin/users" class="px-4 py-2 hover:bg-slate-800 text-amber-300 flex items-center space-x-2 transition">
                  <span>⚙️</span>
                  <span>Admin Users Control</span>
                </Link>
                <Link v-if="user.is_admin" href="/admin/deposits" class="px-4 py-2 hover:bg-slate-800 text-emerald-400 flex items-center space-x-2 transition">
                  <span>💰</span>
                  <span>Admin Deposits</span>
                </Link>
              </div>

              <!-- P2P Quick Access -->
              <div class="py-1">
                <Link href="/p2p/orders" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                  <span>📋</span>
                  <span>My P2P Orders</span>
                </Link>
                <Link href="/p2p/merchant/ads" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                  <span>📢</span>
                  <span>Merchant Ads</span>
                </Link>
              </div>

              <!-- User Standard Links -->
              <div class="py-1">
                <Link href="/profile" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                  <span>👤</span>
                  <span>Profile &amp; Wallet</span>
                </Link>
                <Link href="/payments" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                  <span>💳</span>
                  <span>Payment Settings</span>
                </Link>
                <Link href="/withdraw" class="md:hidden px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
                  <span>💸</span>
                  <span>Withdraw Funds</span>
                </Link>
              </div>

              <!-- Logout -->
              <div class="pt-1">
                <button @click="logout" class="w-full text-left px-4 py-2 hover:bg-slate-800 text-rose-400 hover:text-rose-300 transition flex items-center space-x-2 font-semibold">
                  <span>🚪</span>
                  <span>Logout</span>
                </button>
              </div>
            </div>
          </div>
        </template>

        <!-- GUEST SECTION -->
        <template v-else>
          <Link href="/login" class="text-slate-300 hover:text-white px-3 py-1.5 rounded-lg transition text-xs font-semibold">
            Log In
          </Link>
          <Link href="/register" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] px-3.5 py-1.5 rounded-lg font-bold transition text-xs shadow-sm">
            Register
          </Link>
        </template>

        <!-- MOBILE HAMBURGER MENU BUTTON -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" 
                class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition ml-1"
                aria-label="Toggle Navigation">
          <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- MOBILE DROPDOWN DRAWER -->
    <div v-if="mobileMenuOpen" class="md:hidden bg-slate-900 border-t border-slate-800 px-4 py-3 space-y-3 shadow-2xl">
      <!-- Mobile Account Switcher (Demo / Live) -->
      <div v-if="user" class="flex items-center justify-between pb-2 border-b border-slate-800">
        <div class="flex items-center space-x-1 bg-slate-950 p-1 rounded-lg border border-slate-800 text-xs font-semibold">
          <button @click="switchAccountMode('demo')" 
                  :class="accountMode === 'demo' ? 'bg-[#f0b90b]/20 text-[#f0b90b] font-bold' : 'text-slate-400'"
                  class="px-3 py-1 rounded transition">Demo</button>
          <button @click="switchAccountMode('live')" 
                  :class="accountMode === 'live' ? 'bg-[#f0b90b] text-[#1e2329] font-bold' : 'text-slate-400'"
                  class="px-3 py-1 rounded transition">Live</button>
        </div>
        <div class="text-xs font-mono">
          <span v-if="accountMode === 'demo'" class="text-amber-300 font-bold">${{ formatBalance(demoBalance) }}</span>
          <span v-else class="text-emerald-400 font-bold">${{ formatBalance(liveBalance) }} USDT</span>
        </div>
      </div>

      <!-- Navigation Links -->
      <div class="grid grid-cols-1 gap-1 text-[13px] font-medium">
        <template v-if="user?.is_admin || user?.is_moderator">
          <Link href="/admin/p2p" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg bg-[#f0b90b]/10 text-[#f0b90b] font-bold flex items-center justify-between border border-[#f0b90b]/20">
            <span>🛡️ P2P Dispute Center</span>
            <span>→</span>
          </Link>
          <Link v-if="user?.is_admin" href="/admin/users" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-amber-300 font-bold flex items-center justify-between">
            <span>👥 Trader Controls</span>
            <span>→</span>
          </Link>
          <Link href="/admin/deposits" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-emerald-400 font-bold flex items-center justify-between">
            <span>💰 Deposit Approvals</span>
            <span>→</span>
          </Link>
          <Link href="/terminal" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-400 flex items-center justify-between">
            <span>Spot Terminal ↗</span>
            <span>→</span>
          </Link>
        </template>
        <template v-else>
          <Link href="/terminal" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-200 flex items-center justify-between">
            <span>Spot Trade</span>
            <span class="text-slate-600">→</span>
          </Link>
          <Link href="/trade/options/BTC_USDT" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-200 flex items-center justify-between">
            <span>Quick Options</span>
            <span class="text-slate-600">→</span>
          </Link>
          <Link href="/p2p" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-200 flex items-center justify-between">
            <span>P2P Trading</span>
            <span class="text-slate-600">→</span>
          </Link>
          <Link href="/monthly-interests" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-[#f0b90b] flex items-center justify-between font-semibold">
            <div class="flex items-center space-x-1.5">
              <span>USDT MMF <span class="text-emerald-400 font-bold">(15%)</span></span>
            </div>
            <span class="text-slate-600">→</span>
          </Link>
          <Link href="/trades" @click="mobileMenuOpen = false" class="px-3 py-2 rounded-lg hover:bg-slate-800 text-slate-200 flex items-center justify-between">
            <span>Past Trades</span>
            <span class="text-slate-600">→</span>
          </Link>
        </template>
      </div>

      <!-- Mobile CTAs -->
      <div v-if="user" class="pt-2 border-t border-slate-800 flex items-center space-x-2">
        <Link href="/deposit" @click="mobileMenuOpen = false" class="flex-1 bg-[#f0b90b] text-[#1e2329] py-2 rounded-lg text-center font-bold text-xs">
          Deposit
        </Link>
        <Link href="/withdraw" @click="mobileMenuOpen = false" class="flex-1 bg-slate-800 border border-slate-700 text-slate-200 py-2 rounded-lg text-center font-semibold text-xs">
          Withdraw
        </Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";
import ToastNotification from "@/Components/ToastNotification.vue";

const props = defineProps({
  user: Object,
  markets: Array,
  wallets: Array,
});

const emit = defineEmits(["select-market", "account-mode-changed"]);

const page = usePage();
const toastRef = ref(null);
const resetting = ref(false);
const showProfileDropdown = ref(false);
const mobileMenuOpen = ref(false);
const liveWallets = ref([]);

const accountMode = ref(localStorage.getItem("trade_account_mode") || "demo");
const currentTheme = ref(localStorage.getItem("trade_theme") || "dark");

function applyTheme(theme) {
  currentTheme.value = theme;
  localStorage.setItem("trade_theme", theme);
  if (theme === "light") {
    document.documentElement.classList.remove("dark");
    document.documentElement.classList.add("light");
  } else {
    document.documentElement.classList.remove("light");
    document.documentElement.classList.add("dark");
  }
}

function toggleTheme() {
  const next = currentTheme.value === "dark" ? "light" : "dark";
  applyTheme(next);
  toastRef.value?.show(`Switched to ${next.toUpperCase()} theme mode`, "info");
}

function switchAccountMode(mode) {
  accountMode.value = mode;
  localStorage.setItem("trade_account_mode", mode);
  toastRef.value?.show(mode === "demo" ? "Switched to Demo Practice Dashboard ($10,000 Virtual Funds)" : "Switched to Live Real Trading Dashboard", "info");
  emit("account-mode-changed", mode);
}

const activeWallets = computed(() => {
  if (liveWallets.value.length > 0) return liveWallets.value;
  if (props.wallets && props.wallets.length > 0) return props.wallets;
  return page.props.auth?.wallets || [];
});

const usdtWallet = computed(() => activeWallets.value.find(w => w.currency === "USDT" && !w.is_demo));
const demoWallet = computed(() => activeWallets.value.find(w => w.currency === "USDT" && w.is_demo));

const demoBalance = computed(() => {
  if (demoWallet.value?.available_balance !== undefined) {
    return parseFloat(demoWallet.value.available_balance);
  }
  if (page.props.auth?.user?.demo_balance !== undefined) {
    return parseFloat(page.props.auth.user.demo_balance);
  }
  if (props.user?.demo_balance !== undefined) {
    return parseFloat(props.user.demo_balance);
  }
  return 10000.00;
});

const liveBalance = computed(() => {
  return usdtWallet.value ? usdtWallet.value.available_balance : 0;
});

function formatBalance(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function resetDemoBalance() {
  resetting.value = true;
  try {
    const res = await axios.post("/api/demo/reset");
    toastRef.value?.show(res.data.message, "success");
    window.location.reload();
  } catch (e) {
    toastRef.value?.show("Failed to reset demo balance.", "error");
  } finally {
    resetting.value = false;
  }
}

async function logout() {
  try {
    await axios.post("/api/logout");
  } catch (e) {}
  window.location.href = "/";
}

onMounted(async () => {
  applyTheme(currentTheme.value);
  if (props.user || page.props.auth?.user) {
    try {
      const res = await axios.get("/api/deposits");
      if (res.data?.wallets) {
        liveWallets.value = res.data.wallets;
      }
    } catch (e) {}
  }
});
</script>
