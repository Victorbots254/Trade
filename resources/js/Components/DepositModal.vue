<template>
  <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl text-xs">
      <!-- Modal Header -->
      <div class="flex justify-between items-center px-5 py-3.5 border-b border-slate-800 bg-slate-950/40">
        <h3 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
          <span class="text-emerald-400">📥</span>
          <span>Deposit Funds</span>
        </h3>
        <button @click="$emit('close')" class="text-slate-500 hover:text-slate-300 text-base p-1">✕</button>
      </div>

      <!-- Deposit Method Selector Tabs -->
      <div class="grid grid-cols-2 gap-2 p-3 bg-slate-950/60 border-b border-slate-800">
        <button @click="activeTab = 'mpesa'"
                type="button"
                :class="activeTab === 'mpesa' ? 'bg-emerald-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white bg-slate-900 border border-slate-800'"
                class="py-2.5 px-3 rounded-xl transition flex items-center justify-center space-x-2 text-xs">
          <span>📱</span>
          <span>M-Pesa STK Push</span>
        </button>

        <button @click="activeTab = 'crypto'"
                type="button"
                :class="activeTab === 'crypto' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-400 hover:text-white bg-slate-900 border border-slate-800'"
                class="py-2.5 px-3 rounded-xl transition flex items-center justify-center space-x-2 text-xs">
          <span>🟡</span>
          <span>BEP-20 Crypto</span>
        </button>
      </div>

      <!-- PENDING / SUCCESS SCREEN -->
      <div v-if="submittedDeposit" class="p-6 text-center space-y-4">
        <div class="w-12 h-12 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center justify-center mx-auto text-emerald-400 text-xl animate-pulse">
          ✓
        </div>
        <h4 class="font-bold text-slate-100 text-base">Deposit Submitted Successfully</h4>
        <p class="text-slate-400 leading-relaxed max-w-xs mx-auto text-xs">
          Your deposit request of <strong class="text-emerald-400">{{ submittedDeposit.amount }} USDT</strong> has been registered.
        </p>

        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 text-left font-mono text-[11px] space-y-1.5">
          <div class="flex justify-between text-slate-400">
            <span>Reference:</span>
            <span class="text-emerald-400 truncate max-w-[200px]">{{ submittedDeposit.reference || submittedDeposit.tx_hash }}</span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span>Status:</span>
            <span :class="submittedDeposit.status === 'approved' ? 'text-emerald-400' : 'text-amber-400'" class="font-bold uppercase">
              {{ submittedDeposit.status }}
            </span>
          </div>
        </div>

        <button @click="resetModal" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-xl font-medium transition">
          Deposit More Funds
        </button>
      </div>

      <!-- TAB 1: M-PESA STK PUSH -->
      <div v-else-if="activeTab === 'mpesa'" class="p-5 space-y-4">
        <div v-if="mpesaActivePrompt" class="bg-slate-950 border border-emerald-500/30 rounded-xl p-5 text-center space-y-3">
          <div class="text-2xl animate-pulse">📲</div>
          <h4 class="font-bold text-slate-100 text-sm">STK Push Sent!</h4>
          <p class="text-xs text-slate-400">
            Please enter your M-Pesa PIN on <strong class="text-emerald-400 font-mono">{{ mpesaActivePrompt.phone }}</strong> to approve KES {{ mpesaActivePrompt.kes_amount }}.
          </p>
          <div class="text-[11px] text-emerald-400 font-mono flex items-center justify-center space-x-1.5 py-1">
            <svg class="animate-spin h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Waiting for PIN confirmation...</span>
          </div>
          <button @click="mpesaActivePrompt = null" class="text-slate-500 hover:text-slate-300 text-[11px] underline">
            Cancel / Back
          </button>
        </div>

        <form v-else @submit.prevent="submitMpesa" class="space-y-4">
          <div class="flex justify-between items-center text-xs">
            <span class="text-slate-400">Instant Safaricom M-Pesa</span>
            <span class="text-emerald-400 font-mono text-[11px] font-bold">1 USDT = {{ exchangeRate }} KES</span>
          </div>

          <div v-if="mpesaError" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-2.5 rounded-lg text-xs">
            {{ mpesaError }}
          </div>

          <div>
            <label class="block text-slate-400 text-[11px] mb-1 font-medium">M-Pesa Phone Number</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-mono text-xs">🇰🇪 +254</span>
              <input v-model="mpesaForm.phone"
                     type="text"
                     required
                     placeholder="712 345 678"
                     class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-16 pr-3 py-2 text-slate-100 font-mono text-xs focus:outline-none focus:border-emerald-500" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-slate-400 text-[11px] mb-1 font-medium">Amount (KES)</label>
              <input v-model="mpesaForm.amount"
                     type="number"
                     min="10"
                     required
                     placeholder="1300"
                     class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 font-mono text-slate-100 text-xs font-bold focus:outline-none focus:border-emerald-500" />
            </div>
            <div>
              <label class="block text-slate-400 text-[11px] mb-1 font-medium">Credits in USDT</label>
              <input :value="((mpesaForm.amount || 0) / exchangeRate).toFixed(2)"
                     readonly
                     class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2 font-mono text-emerald-400 text-xs font-bold focus:outline-none cursor-default" />
            </div>
          </div>

          <button type="submit" :disabled="mpesaLoading"
                  class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition shadow-lg text-xs disabled:opacity-50 flex items-center justify-center space-x-2">
            <svg v-if="mpesaLoading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="mpesaLoading">Sending STK Push...</span>
            <span v-else>Deposit via M-Pesa STK Push →</span>
          </button>
        </form>
      </div>

      <!-- TAB 2: CRYPTO BEP-20 MANUAL -->
      <form v-else @submit.prevent="submitDeposit" class="p-5 space-y-4">
        <!-- Asset Selector -->
        <div>
          <label class="block text-slate-400 text-[11px] mb-1 font-medium">Select Deposit Currency</label>
          <select v-model="form.currency" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 font-mono focus:outline-none focus:border-amber-500 text-xs">
            <option value="USDT">USDT - BEP20 (BNB Smart Chain)</option>
            <option value="BNB">BNB - BEP20 (BNB Smart Chain)</option>
            <option value="BTC">BTC - BEP20 (Wrapped BTC)</option>
            <option value="ETH">ETH - BEP20 (Wrapped ETH)</option>
          </select>
        </div>

        <!-- Custodial Address -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 space-y-2">
          <div class="flex items-center justify-between text-slate-400 text-[11px]">
            <span class="font-medium">Custodial Address:</span>
            <span class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold">BEP-20 ONLY</span>
          </div>

          <div class="flex items-center space-x-2 bg-slate-900 border border-slate-800 rounded p-2 font-mono">
            <input readonly :value="custodialAddress" class="bg-transparent text-slate-200 w-full focus:outline-none text-[11px] truncate" />
            <button type="button" @click="copyAddress" class="bg-slate-800 hover:bg-slate-700 text-amber-400 px-2.5 py-1 rounded text-[10px] font-semibold transition">
              {{ copied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
        </div>

        <!-- User Input Fields -->
        <div class="space-y-3">
          <div>
            <label class="block text-slate-400 text-[11px] mb-1">Expected Amount</label>
            <input v-model="form.amount" type="number" step="0.0001" min="0.0001" required placeholder="e.g. 500.00"
                   class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 font-mono text-slate-100 focus:outline-none focus:border-amber-500 text-xs" />
          </div>

          <div>
            <label class="block text-slate-400 text-[11px] mb-1">Transaction Hash (TxHash / TxID) *</label>
            <input v-model="form.tx_hash" type="text" required placeholder="0x..."
                   class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 font-mono text-slate-100 focus:outline-none focus:border-amber-500 text-xs" />
          </div>
        </div>

        <button type="submit" :disabled="loading"
                class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 py-2.5 rounded-xl font-bold transition shadow-lg text-xs disabled:opacity-50">
          <span v-if="loading">Submitting...</span>
          <span v-else>Submit BEP-20 Proof</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  custodialAddress: { type: String, default: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' },
});

const emit = defineEmits(['close', 'deposit-submitted']);

const activeTab = ref('mpesa'); // 'mpesa' | 'crypto'
const exchangeRate = ref(130);

// M-Pesa State
const mpesaForm = ref({ phone: '', amount: '1300' });
const mpesaLoading = ref(false);
const mpesaError = ref('');
const mpesaActivePrompt = ref(null);
let mpesaTimer = null;

// Crypto State
const form = ref({
  currency: 'USDT',
  amount: '',
  tx_hash: '',
  receipt: null,
});
const copied = ref(false);
const loading = ref(false);
const submittedDeposit = ref(null);

function copyAddress() {
  navigator.clipboard.writeText(props.custodialAddress);
  copied.value = true;
  setTimeout(() => copied.value = false, 2000);
}

function resetModal() {
  submittedDeposit.value = null;
  mpesaActivePrompt.value = null;
}

async function submitMpesa() {
  mpesaLoading.value = true;
  mpesaError.value = '';

  try {
    const res = await axios.post('/api/mpesa/initiate', {
      phone: mpesaForm.value.phone,
      amount: mpesaForm.value.amount,
      amount_type: 'kes',
    });

    mpesaActivePrompt.value = res.data;
    pollMpesaStatus(res.data.deposit_id);
    emit('deposit-submitted');
  } catch (err) {
    mpesaError.value = err.response?.data?.message || 'Failed to initiate M-Pesa payment.';
  } finally {
    mpesaLoading.value = false;
  }
}

function pollMpesaStatus(depositId) {
  if (mpesaTimer) clearInterval(mpesaTimer);
  let count = 0;
  mpesaTimer = setInterval(async () => {
    count++;
    if (count > 25) {
      clearInterval(mpesaTimer);
      return;
    }
    try {
      const res = await axios.get(`/api/mpesa/status/${depositId}`);
      if (res.data.status === 'approved') {
        clearInterval(mpesaTimer);
        submittedDeposit.value = res.data.deposit;
        mpesaActivePrompt.value = null;
      } else if (res.data.status === 'rejected') {
        clearInterval(mpesaTimer);
        mpesaError.value = res.data.message || 'Payment cancelled by user.';
        mpesaActivePrompt.value = null;
      }
    } catch (e) {}
  }, 4000);
}

async function submitDeposit() {
  loading.value = true;
  try {
    const formData = new FormData();
    formData.append('currency', form.value.currency);
    formData.append('amount', form.value.amount);
    formData.append('tx_hash', form.value.tx_hash);
    if (form.value.receipt) {
      formData.append('receipt', form.value.receipt);
    }

    const res = await axios.post('/api/deposits', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    submittedDeposit.value = res.data.deposit;
    emit('deposit-submitted');
  } catch (err) {
    alert(err.response?.data?.message || 'Error submitting deposit. Ensure TxHash is unique.');
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    const res = await axios.get('/api/mpesa/settings');
    if (res.data?.exchange_rate) {
      exchangeRate.value = res.data.exchange_rate;
    }
  } catch (e) {}
});
</script>
