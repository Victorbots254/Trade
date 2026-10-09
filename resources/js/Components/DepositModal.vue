<template>
  <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl text-xs">
      <!-- Modal Header -->
      <div class="flex justify-between items-center px-5 py-3.5 border-b border-slate-800 bg-slate-950/40">
        <h3 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
          <span class="text-emerald-400">📥</span>
          <span>Deposit Funds</span>
        </h3>
        <button @click="$emit('close')" class="text-slate-500 hover:text-slate-300 text-base p-1">✕</button>
      </div>

      <!-- Deposit Method Selector Tabs (M-Pesa, Binance, P2P Option) -->
      <div class="grid grid-cols-3 gap-1.5 p-2 bg-slate-950/60 border-b border-slate-800">
        <!-- 1. M-Pesa -->
        <button @click="activeTab = 'mpesa'"
                type="button"
                :class="activeTab === 'mpesa' ? 'bg-emerald-600 text-white font-bold shadow' : 'text-slate-400 hover:text-white bg-slate-900 border border-slate-800'"
                class="py-2.5 px-2 rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1.5 text-xs text-center">
          <span class="text-sm">📱</span>
          <span>M-Pesa</span>
        </button>

        <!-- 2. Binance -->
        <button @click="activeTab = 'crypto'"
                type="button"
                :class="activeTab === 'crypto' ? 'bg-amber-500 text-slate-950 font-bold shadow' : 'text-slate-400 hover:text-white bg-slate-900 border border-slate-800'"
                class="py-2.5 px-2 rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1.5 text-xs text-center">
          <span class="text-sm">🟡</span>
          <span>Binance</span>
        </button>

        <!-- 3. P2P Option -->
        <button @click="activeTab = 'p2p'"
                type="button"
                :class="activeTab === 'p2p' ? 'bg-sky-500 text-white font-bold shadow' : 'text-slate-400 hover:text-white bg-slate-900 border border-slate-800'"
                class="py-2.5 px-2 rounded-xl transition flex flex-col sm:flex-row items-center justify-center space-x-0 sm:space-x-1.5 text-xs text-center">
          <span class="text-sm">🤝</span>
          <span>P2P Option</span>
        </button>
      </div>

      <!-- PENDING / SUCCESS SCREEN FOR CRYPTO/MPESA -->
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
            <span class="text-slate-400 font-medium">Instant Safaricom M-Pesa</span>
            <span class="text-emerald-400 font-mono text-[11px] font-bold">1 USDT = {{ exchangeRate }} KES</span>
          </div>

          <div v-if="mpesaError" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-2.5 rounded-lg text-xs">
            {{ mpesaError }}
          </div>

          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="block text-slate-400 text-[11px] font-medium">M-Pesa Mobile Number</label>
              <span v-if="mpesaForm.phone" class="text-[10px] font-mono text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/30 px-1.5 py-0.5 rounded">
                Sending to: {{ formattedModalPhone }}
              </span>
            </div>
            <div class="relative">
              <input v-model="mpesaForm.phone"
                     type="text"
                     required
                     placeholder="e.g. 0798637930 or 254798637930"
                     class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 font-mono text-xs focus:outline-none focus:border-emerald-500" />
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Accepts any format — sent as pure 254XXXXXXXXX automatically.</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="block text-slate-400 text-[11px] font-medium">Amount (KES)</label>
                <span class="text-[10px] text-emerald-400 font-mono">Min: {{ minKesAmount }} KES</span>
              </div>
              <input v-model="mpesaForm.amount"
                     type="number"
                     :min="minKesAmount"
                     required
                     :placeholder="minKesAmount.toString()"
                     class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 font-mono text-slate-100 text-xs font-bold focus:outline-none focus:border-emerald-500" />
            </div>
            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="block text-slate-400 text-[11px] font-medium">Credits in USDT</label>
                <span class="text-[10px] text-emerald-400 font-mono">Min: 5.00 USDT</span>
              </div>
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

      <!-- TAB 2: BINANCE (BEP-20 CRYPTO) -->
      <form v-else-if="activeTab === 'crypto'" @submit.prevent="submitDeposit" class="p-5 space-y-4">
        <!-- Asset Selector -->
        <div>
          <label class="block text-slate-400 text-[11px] mb-1 font-medium">Select Deposit Currency</label>
          <select v-model="form.currency" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-200 font-mono focus:outline-none focus:border-amber-500 text-xs">
            <option value="USDT">USDT - BEP20 (Binance Smart Chain)</option>
            <option value="BNB">BNB - BEP20 (Binance Smart Chain)</option>
            <option value="BTC">BTC - BEP20 (Wrapped BTC)</option>
            <option value="ETH">ETH - BEP20 (Wrapped ETH)</option>
          </select>
        </div>

        <!-- Custodial Address -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 space-y-2">
          <div class="flex items-center justify-between text-slate-400 text-[11px]">
            <span class="font-medium">Binance BEP-20 Custodial Address:</span>
            <span class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold">BINANCE BEP-20</span>
          </div>

          <div class="flex items-center space-x-2 bg-slate-900 border border-slate-800 rounded p-2 font-mono">
            <input readonly :value="custodialAddress" class="bg-transparent text-slate-200 w-full focus:outline-none text-[11px] truncate" />
            <button type="button" @click="copyAddress" class="bg-slate-800 hover:bg-slate-700 text-amber-400 px-2.5 py-1 rounded text-[10px] font-semibold transition shrink-0">
              {{ copied ? 'Copied!' : 'Copy' }}
            </button>
          </div>
          <p class="text-[10px] text-slate-500">Send USDT via BNB Smart Chain (BEP20) to this custodial address.</p>
        </div>

        <!-- User Input Fields -->
        <div class="space-y-3">
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="block text-slate-400 text-[11px]">Expected Amount</label>
              <span v-if="form.currency === 'USDT'" class="text-[10px] text-amber-400 font-mono">Min: 5.00 USDT</span>
            </div>
            <input v-model="form.amount" type="number" step="0.0001" :min="form.currency === 'USDT' ? 5 : 0.0001" required :placeholder="form.currency === 'USDT' ? 'e.g. 50.00 (min 5.00)' : 'e.g. 0.50'"
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
          <span v-else>Submit Binance Proof →</span>
        </button>
      </form>

      <!-- TAB 3: P2P OPTION (BUY USDT VIA M-PESA / BANK) -->
      <div v-else-if="activeTab === 'p2p'" class="p-5 space-y-4">
        <div class="bg-gradient-to-r from-sky-500/10 via-sky-500/5 to-transparent border border-sky-500/30 rounded-xl p-4 space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-sky-400 font-bold text-xs flex items-center space-x-1.5">
              <span>🤝</span>
              <span>P2P Express Trading</span>
            </span>
            <span class="bg-sky-500/20 text-sky-400 border border-sky-500/40 px-2 py-0.5 rounded text-[10px] font-bold">
              0% ESCROW FEE
            </span>
          </div>
          <p class="text-xs text-slate-300 leading-relaxed">
            Buy USDT directly from verified Kenyan merchants using local <strong>Safaricom M-Pesa</strong> (Send Money, Paybill, Till) or <strong>Bank Transfer</strong>.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-2.5 text-xs">
          <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 space-y-1">
            <div class="text-[#0ecb81] font-bold flex items-center space-x-1">
              <span>📱</span>
              <span>Safaricom M-Pesa</span>
            </div>
            <p class="text-[11px] text-slate-400 leading-snug">
              Send Money, Lipa Na M-Pesa Paybill, and Till number options.
            </p>
          </div>

          <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 space-y-1">
            <div class="text-[#38bdf8] font-bold flex items-center space-x-1">
              <span>🏦</span>
              <span>Local Bank Transfer</span>
            </div>
            <p class="text-[11px] text-slate-400 leading-snug">
              Instant transfer via Equity, KCB, NCBA, Co-op, and more.
            </p>
          </div>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 space-y-2 text-[11px]">
          <div class="flex justify-between items-center">
            <span class="text-slate-400">Current Market Rate:</span>
            <span class="text-emerald-400 font-bold font-mono">1 USDT ≈ {{ exchangeRate }} KES</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-400">Escrow Protection:</span>
            <span class="text-sky-400 font-bold">TradeCo Smart Escrow</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-slate-400">Escrow Fee:</span>
            <span class="text-emerald-400 font-bold">Free up to $50 (0.5 USDT thereafter)</span>
          </div>
        </div>

        <div class="space-y-2 pt-1">
          <a href="/p2p"
             class="w-full bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold py-2.5 px-4 rounded-xl transition shadow-lg text-xs flex items-center justify-center space-x-2">
            <span>Browse P2P Buy Offers →</span>
          </a>

          <div class="text-center">
            <a href="/p2p/orders" class="text-[11px] text-slate-400 hover:text-white underline">
              View My P2P Orders ↗
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  custodialAddress: { type: String, default: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' },
});

const emit = defineEmits(['close', 'deposit-submitted', 'deposit-approved']);

const activeTab = ref('mpesa'); // 'mpesa' | 'crypto' | 'p2p'
const exchangeRate = ref(130);

// M-Pesa State
const mpesaForm = ref({ phone: '', amount: '650' });
const mpesaLoading = ref(false);
const mpesaError = ref('');
const mpesaActivePrompt = ref(null);
let mpesaTimer = null;

const minKesAmount = computed(() => {
  return Math.ceil(5 * exchangeRate.value);
});

const formattedModalPhone = computed(() => {
  let p = (mpesaForm.value.phone || '').replace(/[^0-9]/g, '');
  if (p.startsWith('2540')) p = '254' + p.slice(4);
  else if (p.startsWith('0')) p = '254' + p.slice(1);
  else if (p.startsWith('7') || p.startsWith('1')) p = '254' + p;
  return p;
});

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
  if (navigator.clipboard && props.custodialAddress) {
    navigator.clipboard.writeText(props.custodialAddress);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
  }
}

async function submitMpesa() {
  mpesaLoading.value = true;
  mpesaError.value = '';

  try {
    const res = await axios.post('/api/deposits', {
      method: 'mpesa',
      phone: formattedModalPhone.value,
      amount: mpesaForm.value.amount,
    });

    mpesaActivePrompt.value = {
      phone: formattedModalPhone.value,
      kes_amount: mpesaForm.value.amount,
      deposit_id: res.data.deposit_id,
    };

    pollMpesaStatus(res.data.deposit_id);
  } catch (e) {
    mpesaError.value = e.response?.data?.message || 'Failed to initiate M-Pesa STK Push.';
  } finally {
    mpesaLoading.value = false;
  }
}

function pollMpesaStatus(depositId) {
  if (mpesaTimer) clearInterval(mpesaTimer);

  let attempts = 0;
  mpesaTimer = setInterval(async () => {
    attempts++;
    if (attempts > 30) {
      clearInterval(mpesaTimer);
      mpesaActivePrompt.value = null;
      mpesaError.value = 'M-Pesa transaction timed out. If you received an SMS receipt, your account will be credited automatically.';
      return;
    }

    try {
      const res = await axios.get(`/api/mpesa/status/${depositId}`);
      if (res.data.status === 'approved') {
        clearInterval(mpesaTimer);
        mpesaActivePrompt.value = null;
        submittedDeposit.value = {
          amount: res.data.amount,
          reference: res.data.reference,
          status: 'approved',
        };
        emit('deposit-approved');
      } else if (res.data.status === 'rejected') {
        clearInterval(mpesaTimer);
        mpesaActivePrompt.value = null;
        mpesaError.value = 'Transaction was cancelled or rejected by user PIN.';
      }
    } catch (e) {}
  }, 2500);
}

async function submitDeposit() {
  loading.value = true;
  try {
    const res = await axios.post('/api/deposits', {
      method: 'crypto',
      currency: form.value.currency,
      amount: form.value.amount,
      tx_hash: form.value.tx_hash,
    });

    submittedDeposit.value = {
      amount: form.value.amount,
      tx_hash: form.value.tx_hash,
      status: 'pending',
    };
    emit('deposit-submitted');
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to submit proof.');
  } finally {
    loading.value = false;
  }
}

function resetModal() {
  submittedDeposit.value = null;
  form.value.amount = '';
  form.value.tx_hash = '';
}

onUnmounted(() => {
  if (mpesaTimer) clearInterval(mpesaTimer);
});
</script>
