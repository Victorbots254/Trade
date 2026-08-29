<template>
  <header class="bg-slate-900 border-b border-slate-800 px-4 py-2 flex items-center justify-between text-xs select-none shadow-md z-40 transition-colors duration-200">
    <ToastNotification ref="toastRef" />

    <!-- Left Brand Logo & Navigation -->
    <div class="flex items-center space-x-6">
      <Link href="/terminal" class="flex items-center space-x-2 mr-2">
        <div class="w-7 h-7 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-sm shadow">
          T
        </div>
        <span class="font-bold text-slate-100 text-sm tracking-wide">TRADE<span class="text-[#f0b90b]">PRO</span></span>
      </Link>

      <!-- Professional Navigation Links -->
      <nav class="hidden md:flex items-center space-x-1 font-semibold text-[13px]">
        <Link href="/terminal" class="px-3 py-1.5 rounded-lg transition" :class="$page.url === '/terminal' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'">
          Markets
        </Link>

        <!-- TRADE DROPDOWN -->
        <div class="relative" @mouseenter="showTradeDropdown = true" @mouseleave="showTradeDropdown = false">
          <button class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1" :class="$page.url.includes('/trade/') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'">
            <span>Trade</span>
            <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </button>

          <div v-if="showTradeDropdown" @click="showTradeDropdown = false" class="absolute top-full left-0 mt-0 w-44 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-1.5 z-50">
            <Link href="/terminal" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-[#f0b90b] flex items-center space-x-2 transition">
              <span>Spot Trading</span>
            </Link>
            <Link href="/trade/options/BTC_USDT" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-[#f0b90b] flex items-center space-x-2 transition">
              <span>Quick Options</span>
            </Link>
          </div>
        </div>

        <Link href="/monthly-interests" class="px-3 py-1.5 rounded-lg transition" :class="$page.url === '/monthly-interests' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'">
          Earn
        </Link>
        <Link href="/trades" class="px-3 py-1.5 rounded-lg transition" :class="$page.url === '/trades' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'">
          Past Trades
        </Link>
      </nav>
    </div>

    <!-- Right User Controls & Balance CTAs -->
    <div class="flex items-center space-x-3">
      <!-- TWO DEDICATED DASHBOARD SWITCHERS: DEMO DASHBOARD vs LIVE REAL DASHBOARD -->
      <div class="hidden lg:flex items-center space-x-1 bg-slate-950 p-1 rounded-xl border border-slate-800 font-medium mr-2">
        <button @click="switchAccountMode('demo')" 
                :class="accountMode === 'demo' ? 'bg-[#f0b90b]/20 text-[#f0b90b] border border-[#f0b90b]/40 font-bold shadow' : 'text-slate-400 hover:text-slate-200'"
                class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 text-xs">
          <span>Demo</span>
        </button>
        <button @click="switchAccountMode('live')" 
                :class="accountMode === 'live' ? 'bg-[#f0b90b] text-[#1e2329] font-bold shadow shadow-[#f0b90b]/20' : 'text-slate-400 hover:text-slate-200'"
                class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5 text-xs">
          <span class="w-1.5 h-1.5 bg-[#1e2329] rounded-full animate-ping" v-if="accountMode === 'live'"></span>
          <span>Live</span>
        </button>
      </div>

      <div v-if="user" class="flex items-center space-x-3">
        <!-- DEMO DASHBOARD BALANCE VIEW -->
        <div v-if="accountMode === 'demo'" class="flex items-center space-x-2 bg-[#f0b90b]/10 border border-[#f0b90b]/30 px-3 py-1.5 rounded-lg font-mono">
          <span class="text-[#f0b90b] font-bold">Demo Funds:</span>
          <span class="text-[#f0b90b] font-bold text-sm">${{ formatBalance(demoBalance) }}</span>
          <button @click="resetDemoBalance" :disabled="resetting"
                  title="Reset Demo Account to $10,000.00"
                  class="ml-1 text-[10px] text-[#f0b90b] hover:text-white bg-[#f0b90b]/20 hover:bg-[#f0b90b]/40 border border-[#f0b90b]/40 px-2 py-0.5 rounded transition flex items-center space-x-1">
            <svg v-if="resetting" class="animate-spin h-3 w-3 text-[#f0b90b]" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Reset</span>
          </button>
        </div>

        <!-- LIVE TRADING DASHBOARD BALANCE VIEW -->
        <div v-else class="flex items-center space-x-2">
          <div class="hidden sm:flex items-center space-x-2 bg-emerald-500/10 border border-emerald-500/30 px-3 py-1.5 rounded-lg font-mono">
            <span class="text-emerald-400 font-bold">Live Funds:</span>
            <span class="text-emerald-300 font-bold text-sm">${{ formatBalance(liveBalance) }}</span>
          </div>

          <Link href="/deposit"
             class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] px-3.5 py-1.5 rounded-lg font-bold transition flex items-center space-x-1.5 shadow-sm shadow-[#f0b90b]/20">
            <span>Deposit</span>
          </Link>
          
          <Link href="/withdraw"
             class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-3.5 py-1.5 rounded-lg font-bold transition flex items-center space-x-1.5 shadow-sm shadow-slate-900/50">
            <span>Withdraw</span>
          </Link>
        </div>

        <div class="h-6 w-px bg-slate-800 mx-1 hidden sm:block"></div>

        <!-- Theme Toggle -->
        <button @click="toggleTheme" 
                title="Toggle Theme"
                class="text-slate-400 hover:text-white transition p-1.5 rounded-lg hover:bg-slate-800">
          <svg v-if="currentTheme === 'dark'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
        </button>

        <!-- User Profile Dropdown / Area -->
        <div class="relative" @mouseenter="showProfileDropdown = true" @mouseleave="showProfileDropdown = false">
          <Link href="/profile" class="flex items-center space-x-2 text-slate-300 hover:text-white transition py-1.5 px-2 rounded-lg hover:bg-slate-800 cursor-pointer">
            <div class="w-6 h-6 bg-slate-700 rounded-full flex items-center justify-center text-xs font-bold text-slate-300">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="font-medium hidden sm:block">{{ user.name }}</span>
            <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
          </Link>

          <div v-if="showProfileDropdown" @click="showProfileDropdown = false" class="absolute top-full right-0 mt-0 w-56 bg-slate-900 border border-slate-800 rounded-xl shadow-2xl py-2 z-50 text-sm font-medium">
            <div class="px-4 py-2 border-b border-slate-800 mb-1">
              <div class="text-slate-100 font-bold">{{ user.name }}</div>
              <div class="text-slate-500 text-xs truncate">{{ user.email }}</div>
            </div>
            
            <Link v-if="user.is_admin" href="/admin/users" class="px-4 py-2 hover:bg-slate-800 text-amber-400 flex items-center space-x-2 transition">
              <span>Admin Controls</span>
            </Link>
            <Link v-if="user.is_admin" href="/admin/deposits" class="px-4 py-2 hover:bg-slate-800 text-emerald-400 flex items-center space-x-2 transition">
              <span>Admin Deposits</span>
            </Link>

            <Link href="/profile" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
              <span>Profile & Wallet</span>
            </Link>
            <Link href="/payments" class="px-4 py-2 hover:bg-slate-800 text-slate-300 hover:text-white flex items-center space-x-2 transition">
              <span>Payment Settings</span>
            </Link>
            
            <div class="border-t border-slate-800 mt-1 pt-1">
              <button @click="logout" class="w-full text-left px-4 py-2 hover:bg-slate-800 text-rose-400 transition flex items-center space-x-2">
                <span>Logout</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="flex items-center space-x-2">
        <Link href="/login" class="text-slate-300 hover:text-white px-3 py-1.5 rounded transition flex items-center space-x-1.5">
          <span>Log In</span>
        </Link>
        <Link href="/register" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] px-3.5 py-1.5 rounded-md font-bold transition shadow flex items-center space-x-1.5">
          <span>Register</span>
        </Link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import axios from "axios";
import ToastNotification from "@/Components/ToastNotification.vue";

const props = defineProps({
  user: Object,
  markets: Array,
  wallets: Array,
});

const emit = defineEmits(["select-market", "account-mode-changed"]);

const toastRef = ref(null);
const loadingAction = ref(null);
const loggingOut = ref(false);
const resetting = ref(false);
const showTradeDropdown = ref(false);
const showProfileDropdown = ref(false);

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

const usdtWallet = computed(() => (props.wallets || []).find(w => w.currency === "USDT" && !w.is_demo));
const demoWallet = computed(() => (props.wallets || []).find(w => w.currency === "USDT" && w.is_demo));

const demoBalance = computed(() => {
  return demoWallet.value !== undefined ? parseFloat(demoWallet.value.available_balance) : (props.user?.demo_balance !== undefined ? parseFloat(props.user.demo_balance) : 10000.00);
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
  loggingOut.value = true;
  try {
    await axios.post("/api/logout");
  } catch (e) {}
  window.location.href = "/";
}

onMounted(() => {
  applyTheme(currentTheme.value);
});
</script>
