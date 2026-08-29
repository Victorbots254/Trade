<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="user"
      :markets="markets"
      :wallets="wallets"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 max-w-5xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="border-b border-slate-800 pb-4">
        <h1 class="text-xl md:text-2xl font-bold text-slate-100 flex items-center space-x-2">
          <span>👤 Trader Profile & Account Hub</span>
        </h1>
        <p class="text-xs md:text-sm text-slate-400 mt-1">Overview of your account profile, real live wallet balances, demo practice funds, and payment settings.</p>
      </div>

      <!-- Quick Action Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Live Trading -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between group">
          <div class="space-y-2 mb-6">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-lg">
                📈
              </div>
              <div>
                <h3 class="font-bold text-slate-100 text-base group-hover:text-emerald-400 transition">Live Trading Terminal</h3>
                <p class="text-xs text-slate-400">Access the full trading engine and execution platform.</p>
              </div>
            </div>
          </div>
          
          <Link href="/trade/BTCUSDT" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-lg text-center flex items-center justify-center space-x-1">
            <span>Enter Terminal →</span>
          </Link>
        </div>

        <!-- Card 2: Payments -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between group">
          <div class="space-y-2 mb-6">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-lg">
                💳
              </div>
              <div>
                <h3 class="font-bold text-slate-100 text-base group-hover:text-amber-400 transition">Payments & Settings</h3>
                <p class="text-xs text-slate-400">Link your BEP20 address for automated withdrawals.</p>
              </div>
            </div>
          </div>

          <Link href="/payments" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2.5 rounded-xl transition text-xs shadow-lg text-center flex items-center justify-center space-x-1">
            <span>Manage Payments →</span>
          </Link>
        </div>

        <!-- Card 3: Monthly Interests -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between group relative overflow-hidden">
          <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
          <div class="space-y-2 mb-6 relative z-10">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold text-lg">
                %
              </div>
              <div>
                <h3 class="font-bold text-slate-100 text-base group-hover:text-blue-400 transition">Monthly Interests</h3>
                <p class="text-xs text-slate-400">Lock your idle USDT and earn passive monthly yield.</p>
              </div>
            </div>
          </div>

          <Link href="/monthly-interests" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 rounded-xl transition text-xs shadow-lg text-center flex items-center justify-center space-x-1 relative z-10">
            <span>Earn Interest →</span>
          </Link>
        </div>
      </div>

      <!-- BALANCE OVERVIEW CARDS: REAL LIVE FUNDS VS DEMO PRACTICE FUNDS -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: REAL LIVE AVAILABLE USDT BALANCE -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-2">
          <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400 font-medium">Real Live USDT Balance</span>
            <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px] font-bold">🟢 REAL FUNDS</span>
          </div>
          <div class="text-2xl font-black text-emerald-400 font-mono">${{ formatPrice(usdtBalance) }}</div>
          <div class="text-[11px] text-slate-500 font-mono pt-1">Real deposited balance ready for live trading</div>
        </div>

        <!-- Card 2: REAL LOCKED FUNDS -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-2">
          <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400 font-medium">Real Locked Funds</span>
            <span class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold">🔒 LOCKED REAL</span>
          </div>
          <div class="text-2xl font-black text-amber-400 font-mono">${{ formatPrice(lockedBalance) }}</div>
          <div class="text-[11px] text-slate-500 font-mono pt-1">In active live option contracts / open orders</div>
        </div>

        <!-- Card 3: DEMO PRACTICE ACCOUNT BALANCE -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-2">
          <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400 font-medium">Demo Practice Account</span>
            <span class="bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 px-2 py-0.5 rounded text-[10px] font-bold">🎮 DEMO FUNDS</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-2xl font-black text-cyan-400 font-mono">${{ formatPrice(demoBalance) }}</div>
            <button @click="resetDemoBalance" :disabled="resetting"
                    class="bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center space-x-1">
              <svg v-if="resetting" class="animate-spin h-3 w-3 text-cyan-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>↻ Reset $10k</span>
            </button>
          </div>
          <div class="text-[11px] text-slate-500 font-mono pt-1">Virtual funds for risk-free strategy practice</div>
        </div>
      </div>

      <!-- User Profile Details Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b border-slate-800 pb-4">
          <h2 class="font-bold text-slate-100 text-base">Account Identity & Information</h2>
          <span class="text-xs font-mono text-slate-400">User ID: #{{ user?.id }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
          <div class="space-y-1">
            <label class="block text-slate-400 font-medium">Full Name</label>
            <div class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 font-semibold text-sm">
              {{ user?.name }}
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-slate-400 font-medium">Email Address</label>
            <div class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 font-mono text-sm">
              {{ user?.email }}
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-slate-400 font-medium">Binance BEP20 Withdrawal Address</label>
            <div class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 font-mono text-xs flex justify-between items-center">
              <span class="truncate">{{ user?.bep20_address || 'Not Linked Yet' }}</span>
              <a href="/payments" class="text-amber-400 hover:underline font-bold text-[11px] shrink-0 ml-2">
                {{ user?.bep20_address ? 'Update' : 'Link Address' }}
              </a>
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-slate-400 font-medium">Account Registration Date</label>
            <div class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-100 font-mono text-xs">
              {{ formatDate(user?.created_at) }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Live Right-to-Left Ticker Bar -->
    <BottomMarketTicker :markets="markets" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';

const props = defineProps({
  user: Object,
  wallets: Array,
  markets: Array,
});

const toastRef = ref(null);
const resetting = ref(false);

const usdtWallet = computed(() => (props.wallets || []).find(w => w.currency === 'USDT' && !w.is_demo));
const demoWallet = computed(() => (props.wallets || []).find(w => w.currency === 'USDT' && w.is_demo));

const usdtBalance = computed(() => usdtWallet.value ? parseFloat(usdtWallet.value.available_balance) : 0.00);
const lockedBalance = computed(() => usdtWallet.value ? parseFloat(usdtWallet.value.locked_balance) : 0.00);
const demoBalance = computed(() => demoWallet.value ? parseFloat(demoWallet.value.available_balance) : (props.user?.demo_balance !== undefined ? parseFloat(props.user.demo_balance) : 10000.00));

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString();
}

async function resetDemoBalance() {
  resetting.value = true;
  try {
    const res = await axios.post('/api/demo/reset');
    toastRef.value?.show(res.data.message, 'success');
    window.location.reload();
  } catch (e) {
    toastRef.value?.show('Failed to reset demo balance.', 'error');
  } finally {
    resetting.value = false;
  }
}
</script>
