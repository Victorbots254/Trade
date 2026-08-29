<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="currentUser"
      :markets="markets"
      :wallets="wallets"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-800 pb-4 gap-3">
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-100 flex items-center space-x-2">
            <span>💳 Payment & Withdrawal Settings</span>
          </h1>
          <p class="text-xs md:text-sm text-slate-400 mt-1">Manage your Binance wallet withdrawal address and payment destination for automated payouts.</p>
        </div>

        <Link href="/payments/binance-guide" 
           class="bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-800 px-4 py-2 rounded-xl transition text-xs font-bold flex items-center space-x-1.5 self-start md:self-auto shadow-md">
          <span>📖 View Binance BEP20 Setup Guide →</span>
        </Link>
      </div>

      <!-- BEP20 Binance Wallet Address Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 shadow-2xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-800 pb-4 gap-3">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold text-lg">
              🟡
            </div>
            <div>
              <h2 class="font-bold text-slate-100 text-base">Binance Wallet Address (BEP20)</h2>
              <p class="text-xs text-slate-400">BNB Smart Chain (BEP20) Network for USDT & Crypto Withdrawals</p>
            </div>
          </div>

          <span v-if="currentUser?.bep20_address" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-bold font-mono self-start md:self-auto flex items-center space-x-1">
            <span>✓ VERIFIED BEP20 WALLET</span>
          </span>
          <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-3 py-1 rounded-full text-xs font-bold font-mono self-start md:self-auto">
            <span>UNLINKED</span>
          </span>
        </div>

        <form @submit.prevent="saveBep20Address" class="space-y-4">
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-xs font-semibold text-slate-300">
                Your Binance BEP20 Deposit/Withdrawal Address
              </label>
              <Link href="/payments/binance-guide" class="text-amber-400 hover:underline text-[11px] font-semibold">
                How to find your address on Binance?
              </Link>
            </div>
            <div class="relative">
              <input v-model="bep20AddressInput" 
                     type="text" 
                     placeholder="0x... (e.g. 0x71C7656EC7ab88b098defB751B7401B5f6d8976F)"
                     required
                     class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 font-mono text-xs md:text-sm placeholder-slate-600 focus:outline-none focus:border-amber-500/60 transition shadow-inner" />
              <button v-if="bep20AddressInput" type="button" @click="copyAddress" title="Copy Wallet Address"
                      class="absolute right-3 top-2.5 bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-1 rounded-lg text-xs font-medium transition flex items-center space-x-1">
                <span>{{ copied ? 'Copied!' : 'Copy' }}</span>
              </button>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">
              Make sure this is your official BEP20 (BNB Smart Chain) address starting with <code class="text-amber-400 font-bold font-mono">0x</code> from Binance or Trust Wallet.
            </p>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="submit" :disabled="saving"
                    class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-6 py-2.5 rounded-xl transition text-xs md:text-sm shadow-lg flex items-center space-x-2">
              <svg v-if="saving" class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ currentUser?.bep20_address ? 'Update Wallet Address' : 'Save Binance BEP20 Address' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Network Security Guidelines -->
      <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 space-y-3 text-xs">
        <h3 class="font-bold text-slate-200 text-sm flex items-center space-x-2">
          <span class="text-amber-400">🛡️</span>
          <span>Payment & Network Rules</span>
        </h3>
        <ul class="space-y-2 text-slate-400 list-disc list-inside">
          <li>Always verify that your destination address uses the <strong class="text-slate-200">BEP20 (BNB Smart Chain)</strong> network.</li>
          <li>Sending funds to non-BEP20 networks (such as ERC20 or TRC20) may result in delayed credit or asset loss.</li>
          <li>All profits and option payouts are settled in USDT (BEP20) to your linked Binance wallet address.</li>
        </ul>
      </div>
    </div>

    <!-- Bottom Live Right-to-Left Ticker Bar -->
    <BottomMarketTicker :markets="markets" />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import WithdrawalTicker from '@/Components/WithdrawalTicker.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';

const props = defineProps({
  user: Object,
  wallets: Array,
  markets: Array,
});

const toastRef = ref(null);
const currentUser = ref(props.user || {});
const bep20AddressInput = ref(props.user?.bep20_address || '');
const saving = ref(false);
const copied = ref(false);

async function saveBep20Address() {
  saving.value = true;
  try {
    const res = await axios.post('/api/payments/bep20', {
      bep20_address: bep20AddressInput.value,
    });

    currentUser.value = res.data.user;
    toastRef.value?.show(res.data.message, 'success');
  } catch (err) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.bep20_address?.[0] || 'Failed to save BEP20 address.';
    toastRef.value?.show(msg, 'error');
  } finally {
    saving.value = false;
  }
}

function copyAddress() {
  if (!bep20AddressInput.value) return;
  navigator.clipboard.writeText(bep20AddressInput.value);
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2000);
}
</script>
