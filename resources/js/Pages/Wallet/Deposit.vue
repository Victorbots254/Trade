<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans select-none">
    <!-- Top Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
      <a href="/" class="flex items-center space-x-2 font-bold text-lg text-emerald-400 tracking-wider">
        <span>TRADE<span class="text-slate-400 font-normal">PRO</span></span>
      </a>
      <a href="/" class="text-xs text-slate-400 hover:text-slate-200 transition">← Back to Trading Terminal</a>
    </header>

    <!-- Main Deposit Container -->
    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="border-b border-slate-800 pb-4 flex justify-between items-end">
        <div>
          <h1 class="text-xl font-bold text-slate-100">BEP-20 Manual Deposit</h1>
          <p class="text-xs text-slate-400 mt-1">Scan the custodial QR code or copy the address to submit your deposit proof.</p>
        </div>

        <!-- Live Balances Summary -->
        <div class="hidden sm:flex items-center space-x-3 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl text-xs font-mono">
          <span class="text-slate-400 font-bold">Wallet Balances:</span>
          <div v-for="w in activeWallets" :key="w.currency" class="text-emerald-400 font-bold">
            {{ formatBalance(w.available_balance) }} {{ w.currency }}
          </div>
          <div v-if="activeWallets.length === 0" class="text-slate-500">0.00 USDT</div>
        </div>
      </div>

      <!-- Deposit Form / QR Card -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 shadow-2xl">
        <!-- Left: QR Code & Custodial Address -->
        <div class="flex flex-col items-center justify-center space-y-4 border-b md:border-b-0 md:border-r border-slate-800 pb-6 md:pb-0 md:pr-8 text-center">
          <div class="bg-white p-3 rounded-xl shadow-lg border border-slate-700">
            <qrcode-vue :value="custodialAddress" :size="160" level="H" />
          </div>
          
          <div class="w-full space-y-1">
            <span class="text-[11px] text-slate-400 uppercase font-mono tracking-wider font-semibold">Binance Custodial Address (BEP-20)</span>
            <div class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-[11px] font-mono text-emerald-400 break-all select-all flex justify-between items-center">
              <span>{{ custodialAddress }}</span>
            </div>
          </div>

          <div class="bg-amber-500/10 border border-amber-500/20 text-amber-300/90 text-[11px] p-3 rounded-lg text-left leading-relaxed">
            ⚠️ <strong>Network Risk Notice:</strong> Send only BEP-20 (BNB Smart Chain) tokens to this address. Sending via other networks will result in permanent loss.
          </div>
        </div>

        <!-- Right: Deposit Form -->
        <div class="space-y-4 text-xs">
          <div v-if="successMessage" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded-lg font-medium">
            {{ successMessage }}
          </div>

          <div v-if="errorMessage" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3 rounded-lg font-medium">
            {{ errorMessage }}
          </div>

          <form @submit.prevent="submitDeposit" class="space-y-4">
            <div>
              <label class="block text-slate-400 mb-1 font-medium">Select Currency</label>
              <select v-model="form.currency" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:border-emerald-500">
                <option value="USDT">USDT (Tether BEP-20)</option>
                <option value="BNB">BNB (Binance Coin)</option>
                <option value="BTC">BTC (Bitcoin BEP-20)</option>
                <option value="ETH">ETH (Ethereum BEP-20)</option>
              </select>
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-medium">Expected Deposit Amount</label>
              <input v-model="form.amount" type="number" step="0.0001" required placeholder="e.g. 500.00" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500" />
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-medium">Transaction Hash (TxHash / TxID)</label>
              <input v-model="form.tx_hash" type="text" required placeholder="0x..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-[11px]" />
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-medium">Payment Receipt (Optional Screenshot)</label>
              <input @change="handleFileUpload" type="file" accept="image/*" class="w-full text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700" />
            </div>

            <button type="submit" :disabled="loading" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3 rounded-lg transition uppercase tracking-wider text-xs shadow-lg flex items-center justify-center space-x-2">
              <svg v-if="loading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="loading">Submitting Deposit...</span>
              <span v-else>Submit Deposit Proof</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Live Deposit Requests History Table -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h2 class="font-bold text-slate-200 text-sm">Your Submitted Deposit Requests</h2>
          <span class="flex items-center space-x-1.5 text-[11px] text-emerald-400 font-bold font-mono bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded">
            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-ping"></span>
            <span>AUTO-SYNCING</span>
          </span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800 text-[11px]">
                <th class="pb-2">ID</th>
                <th class="pb-2">Amount & Asset</th>
                <th class="pb-2">TxHash</th>
                <th class="pb-2">Status</th>
                <th class="pb-2 text-right">Date Submitted</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="d in depositHistory" :key="d.id" class="hover:bg-slate-800/30 transition">
                <td class="py-2.5 font-bold text-slate-400">#{{ d.id }}</td>
                <td class="py-2.5 font-bold text-emerald-400">{{ formatBalance(d.amount) }} {{ d.currency }}</td>
                <td class="py-2.5">
                  <a :href="'https://bscscan.com/tx/' + d.tx_hash" target="_blank" class="text-slate-400 hover:text-emerald-400 underline truncate inline-block max-w-[140px]">
                    {{ d.tx_hash }} ↗
                  </a>
                </td>
                <td class="py-2.5">
                  <span v-if="d.status === 'approved'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    ✓ APPROVED
                  </span>
                  <span v-else-if="d.status === 'rejected'" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    ✕ REJECTED
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px] animate-pulse">
                    ⏳ PENDING VERIFICATION
                  </span>
                </td>
                <td class="py-2.5 text-right text-slate-500 text-[11px]">{{ formatDate(d.created_at) }}</td>
              </tr>
              <tr v-if="depositHistory.length === 0">
                <td colspan="5" class="py-6 text-center text-slate-500">No deposit requests submitted yet.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import QrcodeVue from 'qrcode.vue';
import axios from 'axios';

const props = defineProps({
  custodialAddress: {
    type: String,
    default: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F',
  },
});

const form = ref({
  currency: 'USDT',
  amount: '',
  tx_hash: '',
  receipt: null,
});

const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const depositHistory = ref([]);
const wallets = ref([]);

let pollTimer = null;

const activeWallets = computed(() => {
  return wallets.value.filter(w => parseFloat(w.available_balance) > 0 || parseFloat(w.locked_balance) > 0);
});

function handleFileUpload(e) {
  form.value.receipt = e.target.files[0];
}

function formatBalance(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

function formatDate(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleString();
}

async function fetchDepositData() {
  try {
    const res = await axios.get('/api/deposits');
    depositHistory.value = res.data.deposits || [];
    wallets.value = res.data.wallets || [];
  } catch (e) {}
}

async function submitDeposit() {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('currency', form.value.currency);
  formData.append('amount', form.value.amount);
  formData.append('tx_hash', form.value.tx_hash);
  if (form.value.receipt) {
    formData.append('receipt', form.value.receipt);
  }

  try {
    const res = await axios.post('/api/deposits', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    successMessage.value = res.data.message || 'Deposit submitted for admin approval!';
    form.value.amount = '';
    form.value.tx_hash = '';
    form.value.receipt = null;
    fetchDepositData();
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Failed to submit deposit.';
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchDepositData();
  pollTimer = setInterval(fetchDepositData, 3000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>
