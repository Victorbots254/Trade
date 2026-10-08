<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans select-none pb-12">
    <!-- Top Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
      <a href="/" class="flex items-center space-x-2 font-bold text-lg text-emerald-400 tracking-wider">
        <span>TRADE<span class="text-slate-400 font-normal">CO</span></span>
      </a>
      <a href="/terminal" class="text-xs text-slate-400 hover:text-slate-200 transition">← Back to Trading Terminal</a>
    </header>

    <!-- Main Deposit Container -->
    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3">
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-100 flex items-center space-x-2">
            <span>📥 Deposit Funds</span>
          </h1>
          <p class="text-xs text-slate-400 mt-1">Instant M-Pesa mobile money (MegaPay) or manual BEP-20 crypto deposit.</p>
        </div>

        <!-- Live Balances Summary -->
        <div class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl text-xs font-mono">
          <span class="text-slate-400 font-bold">Live Wallet:</span>
          <div v-for="w in activeWallets" :key="w.currency" class="text-emerald-400 font-bold">
            {{ formatBalance(w.available_balance) }} {{ w.currency }}
          </div>
          <div v-if="activeWallets.length === 0" class="text-slate-500">0.00 USDT</div>
        </div>
      </div>

      <!-- Deposit Method Selector Tabs -->
      <div class="grid grid-cols-2 gap-3 p-1.5 bg-slate-900 border border-slate-800 rounded-2xl">
        <!-- M-Pesa Tab -->
        <button @click="activeMethod = 'mpesa'"
                type="button"
                :class="activeMethod === 'mpesa' ? 'bg-emerald-600 text-white font-bold shadow-lg shadow-emerald-950/50' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                class="py-3 px-4 rounded-xl transition flex items-center justify-center space-x-2.5 text-xs sm:text-sm">
          <span class="text-base sm:text-lg">📱</span>
          <div class="text-left">
            <div class="font-bold leading-tight">M-Pesa STK Push</div>
            <div class="text-[10px] opacity-80 font-normal hidden sm:block">Instant MegaPay · KES to USDT</div>
          </div>
          <span v-if="activeMethod === 'mpesa'" class="bg-white/20 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Active</span>
        </button>

        <!-- Crypto BEP20 Tab -->
        <button @click="activeMethod = 'crypto'"
                type="button"
                :class="activeMethod === 'crypto' ? 'bg-amber-500 text-slate-950 font-bold shadow-lg shadow-amber-950/50' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'"
                class="py-3 px-4 rounded-xl transition flex items-center justify-center space-x-2.5 text-xs sm:text-sm">
          <span class="text-base sm:text-lg">🟡</span>
          <div class="text-left">
            <div class="font-bold leading-tight">BEP-20 Crypto</div>
            <div class="text-[10px] opacity-80 font-normal hidden sm:block">BNB Smart Chain (Manual TxHash)</div>
          </div>
          <span v-if="activeMethod === 'crypto'" class="bg-slate-950/20 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Active</span>
        </button>
      </div>

      <!-- METHOD 1: M-PESA INSTANT DEPOSIT (MEGAPAY) -->
      <div v-if="activeMethod === 'mpesa'" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 space-y-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-800 pb-4 gap-2">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-xl text-emerald-400 font-bold">
              🇰🇪
            </div>
            <div>
              <h2 class="font-bold text-slate-100 text-base flex items-center space-x-2">
                <span>M-Pesa Instant Deposit</span>
                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-[10px] px-2 py-0.5 rounded font-mono font-bold">STK PUSH</span>
              </h2>
              <p class="text-xs text-slate-400">Powered by MegaPay API. Automatic credit upon PIN confirmation.</p>
            </div>
          </div>

          <!-- Rate Badge -->
          <div class="bg-slate-950 border border-slate-800 px-3 py-1.5 rounded-xl text-xs font-mono text-slate-300">
            <span class="text-slate-500">Rate: </span>
            <span class="text-emerald-400 font-bold">1 USDT = {{ exchangeRate }} KES</span>
          </div>
        </div>

        <!-- Feedback Alert Messages -->
        <div v-if="mpesaSuccessMessage" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs font-medium flex items-center space-x-2">
          <span class="text-lg">🎉</span>
          <span>{{ mpesaSuccessMessage }}</span>
        </div>

        <div v-if="mpesaErrorMessage" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs font-medium flex items-center space-x-2">
          <span class="text-lg">⚠️</span>
          <span>{{ mpesaErrorMessage }}</span>
        </div>

        <!-- STK PUSH ACTIVE PROMPT OVERLAY / STATUS BANNER -->
        <div v-if="activeStkSession" class="bg-slate-950 border border-emerald-500/40 rounded-2xl p-6 text-center space-y-4 shadow-xl relative overflow-hidden">
          <div class="absolute inset-0 pointer-events-none opacity-5 bg-gradient-to-r from-emerald-500 to-amber-500"></div>

          <div class="w-14 h-14 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center justify-center mx-auto text-2xl animate-pulse">
            📲
          </div>

          <div>
            <h3 class="text-base font-bold text-slate-100">STK Push Sent to Your Phone!</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
              Please check your phone (<strong class="text-emerald-400 font-mono">{{ activeStkSession.phone }}</strong>) and enter your M-Pesa PIN to complete payment of <strong class="text-slate-200 font-mono">KES {{ activeStkSession.kes_amount }}</strong>.
            </p>
          </div>

          <div class="inline-flex items-center space-x-2 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl text-xs font-mono">
            <svg class="animate-spin h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-emerald-400 font-semibold">{{ stkCheckingStatusText }}</span>
          </div>

          <div class="flex items-center justify-center space-x-3 pt-2">
            <button @click="checkStkStatusManual" :disabled="stkPolling"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl text-xs transition shadow flex items-center space-x-1.5">
              <span>I've Entered My PIN / Check Status</span>
            </button>
            <button @click="cancelStkSession"
                    class="border border-slate-800 hover:border-slate-700 text-slate-400 hover:text-slate-200 px-4 py-2 rounded-xl text-xs transition">
              Cancel / New Request
            </button>
          </div>
        </div>

        <!-- M-Pesa Request Form -->
        <form v-else @submit.prevent="submitMpesaDeposit" class="space-y-5 text-xs">
          <!-- Phone Number -->
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-slate-400 font-medium">M-Pesa Safaricom Phone Number</label>
              <span v-if="mpesaForm.phone" class="text-[11px] font-mono text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded">
                Sending to: {{ formattedPreviewPhone }} (No +)
              </span>
            </div>
            <div class="relative">
              <input v-model="mpesaForm.phone"
                     type="text"
                     required
                     placeholder="e.g. 0798637930 or 254798637930"
                     class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-sm" />
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Accepts any format (e.g. 0798637930, 254798637930, or 798637930) — automatically sent as pure 254XXXXXXXXX with no plus.</p>
          </div>

          <!-- Amount In KES & Conversion -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label class="block text-slate-400 font-medium">Deposit Amount (KES)</label>
                <span class="text-[10px] text-emerald-400 font-mono">Min: {{ minKesAmount }} KES ($5)</span>
              </div>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 font-mono font-bold text-xs">KES</span>
                <input v-model="mpesaForm.amount"
                       @input="onKesAmountInput"
                       type="number"
                       step="1"
                       :min="minKesAmount"
                       required
                       :placeholder="'e.g. ' + minKesAmount"
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-14 pr-4 py-3 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-sm font-bold" />
              </div>
              <p class="text-[11px] text-slate-500 mt-1">Minimum deposit: $5.00 USDT (~{{ minKesAmount }} KES)</p>
            </div>

            <div>
              <div class="flex justify-between items-center mb-1.5">
                <label class="block text-slate-400 font-medium">Credited to Wallet (USDT)</label>
                <span class="text-[10px] text-emerald-400 font-mono">Min: 5.00 USDT</span>
              </div>
              <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400 font-mono font-bold text-xs">USDT</span>
                <input :value="computedUsdtAmount"
                       readonly
                       class="w-full bg-slate-950/60 border border-slate-800 rounded-xl pl-16 pr-4 py-3 text-emerald-400 font-mono text-sm font-bold focus:outline-none cursor-default" />
              </div>
            </div>
          </div>

          <!-- Quick Preset Chips -->
          <div>
            <label class="block text-slate-500 text-[11px] mb-2 font-medium">Quick Select Amount:</label>
            <div class="flex flex-wrap gap-2">
              <button v-for="amt in [650, 1300, 2600, 6500, 13000, 26000]" :key="amt"
                      type="button"
                      @click="setQuickAmount(amt)"
                      class="bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-emerald-500/50 text-slate-300 hover:text-emerald-400 px-3 py-1.5 rounded-lg font-mono text-xs transition">
                KES {{ amt.toLocaleString() }} (${{ (amt / exchangeRate).toFixed(0) }})
              </button>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" :disabled="mpesaLoading"
                  class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3.5 rounded-xl transition uppercase tracking-wider text-xs shadow-lg shadow-emerald-950/50 flex items-center justify-center space-x-2">
            <svg v-if="mpesaLoading" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="mpesaLoading">Requesting M-Pesa STK Push...</span>
            <span v-else>Deposit KES {{ Number(mpesaForm.amount || 0).toLocaleString() }} via M-Pesa →</span>
          </button>
        </form>
      </div>

      <!-- METHOD 2: BEP-20 MANUAL CRYPTO DEPOSIT -->
      <div v-else class="bg-slate-900 border border-slate-800 rounded-2xl p-6 md:p-8 grid grid-cols-1 md:grid-cols-2 gap-8 shadow-2xl">
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
              <div class="flex justify-between items-center mb-1">
                <label class="block text-slate-400 font-medium">Expected Deposit Amount</label>
                <span v-if="form.currency === 'USDT'" class="text-[10px] text-amber-400 font-mono">Min: 5.00 USDT</span>
              </div>
              <input v-model="form.amount"
                     type="number"
                     step="0.0001"
                     :min="form.currency === 'USDT' ? 5 : 0.0001"
                     required
                     :placeholder="form.currency === 'USDT' ? 'e.g. 50.00 (min 5.00)' : 'e.g. 0.50'"
                     class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500" />
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-medium">Transaction Hash (TxHash / TxID)</label>
              <input v-model="form.tx_hash" type="text" required placeholder="0x..." class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-[11px]" />
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-medium">Payment Receipt (Optional Screenshot)</label>
              <input @change="handleFileUpload" type="file" accept="image/*" class="w-full text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-emerald-400 hover:file:bg-slate-700" />
            </div>

            <button type="submit" :disabled="loading" class="w-full bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold py-3 rounded-lg transition uppercase tracking-wider text-xs shadow-lg flex items-center justify-center space-x-2">
              <svg v-if="loading" class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
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
                <th class="pb-2">Method</th>
                <th class="pb-2">Amount & Asset</th>
                <th class="pb-2">Reference / TxID</th>
                <th class="pb-2">Status</th>
                <th class="pb-2 text-right">Date Submitted</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="d in depositHistory" :key="d.id" class="hover:bg-slate-800/30 transition">
                <td class="py-2.5 font-bold text-slate-400">#{{ d.id }}</td>
                <td class="py-2.5">
                  <span v-if="d.payment_method === 'mpesa'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px] font-bold">
                    📱 M-PESA
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold">
                    🟡 BEP-20
                  </span>
                </td>
                <td class="py-2.5 font-bold text-emerald-400">
                  {{ formatBalance(d.amount) }} {{ d.currency }}
                  <span v-if="d.kes_amount" class="text-slate-400 font-normal text-[10px] block">
                    (KES {{ Number(d.kes_amount).toLocaleString() }})
                  </span>
                </td>
                <td class="py-2.5">
                  <div v-if="d.mpesa_receipt" class="text-emerald-400 font-bold">
                    Receipt: {{ d.mpesa_receipt }}
                  </div>
                  <div v-else-if="d.payment_method === 'mpesa'" class="text-slate-400 truncate max-w-[140px]">
                    {{ d.reference || d.tx_hash }}
                  </div>
                  <div v-else>
                    <a :href="'https://bscscan.com/tx/' + d.tx_hash" target="_blank" class="text-slate-400 hover:text-emerald-400 underline truncate inline-block max-w-[140px]">
                      {{ d.tx_hash }} ↗
                    </a>
                  </div>
                </td>
                <td class="py-2.5">
                  <span v-if="d.status === 'approved'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    ✓ APPROVED
                  </span>
                  <span v-else-if="d.status === 'rejected'" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]" :title="d.rejection_reason">
                    ✕ REJECTED
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px] animate-pulse">
                    ⏳ PENDING
                  </span>
                </td>
                <td class="py-2.5 text-right text-slate-500 text-[11px]">{{ formatDate(d.created_at) }}</td>
              </tr>
              <tr v-if="depositHistory.length === 0">
                <td colspan="6" class="py-6 text-center text-slate-500">No deposit requests submitted yet.</td>
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

const activeMethod = ref('mpesa'); // 'mpesa' | 'crypto'
const exchangeRate = ref(130);

// Crypto Form State
const form = ref({
  currency: 'USDT',
  amount: '',
  tx_hash: '',
  receipt: null,
});
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// M-Pesa Form State
const mpesaForm = ref({
  phone: '',
  amount: '650',
});
const mpesaLoading = ref(false);
const mpesaSuccessMessage = ref('');
const mpesaErrorMessage = ref('');
const activeStkSession = ref(null);
const stkCheckingStatusText = ref('Waiting for M-Pesa PIN confirmation...');
const stkPolling = ref(false);
let stkTimer = null;

// History and Wallets
const depositHistory = ref([]);
const wallets = ref([]);
let pollTimer = null;

const minKesAmount = computed(() => {
  return Math.ceil(5 * exchangeRate.value);
});

const computedUsdtAmount = computed(() => {
  const kes = parseFloat(mpesaForm.value.amount) || 0;
  if (kes <= 0) return '0.00';
  return (kes / exchangeRate.value).toFixed(2);
});

const formattedPreviewPhone = computed(() => {
  let p = (mpesaForm.value.phone || '').replace(/[^0-9]/g, '');
  if (p.startsWith('2540')) p = '254' + p.slice(4);
  else if (p.startsWith('0')) p = '254' + p.slice(1);
  else if (p.startsWith('7') || p.startsWith('1')) p = '254' + p;
  return p;
});

const activeWallets = computed(() => {
  return wallets.value.filter(w => parseFloat(w.available_balance) > 0 || parseFloat(w.locked_balance) > 0);
});

function setQuickAmount(amt) {
  mpesaForm.value.amount = amt.toString();
}

function onKesAmountInput() {
  mpesaErrorMessage.value = '';
}

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

async function fetchSettings() {
  try {
    const res = await axios.get('/api/mpesa/settings');
    if (res.data?.exchange_rate) {
      exchangeRate.value = res.data.exchange_rate;
    }
  } catch (e) {}
}

async function fetchDepositData() {
  try {
    const res = await axios.get('/api/deposits');
    depositHistory.value = res.data.deposits || [];
    wallets.value = res.data.wallets || [];
  } catch (e) {}
}

async function submitMpesaDeposit() {
  mpesaLoading.value = true;
  mpesaErrorMessage.value = '';
  mpesaSuccessMessage.value = '';

  try {
    const res = await axios.post('/api/mpesa/initiate', {
      phone: mpesaForm.value.phone,
      amount: mpesaForm.value.amount,
      amount_type: 'kes',
    });

    activeStkSession.value = res.data;
    mpesaSuccessMessage.value = res.data.message || 'STK Push sent to your phone! Please enter your PIN.';

    // Start status polling
    startStkPolling(res.data.deposit_id);
    fetchDepositData();
  } catch (err) {
    mpesaErrorMessage.value = err.response?.data?.message || 'Failed to initiate M-Pesa STK push.';
  } finally {
    mpesaLoading.value = false;
  }
}

function startStkPolling(depositId) {
  if (stkTimer) clearInterval(stkTimer);
  stkCheckingStatusText.value = 'Waiting for PIN on phone...';

  let attempts = 0;
  stkTimer = setInterval(async () => {
    attempts++;
    if (attempts > 30) {
      clearInterval(stkTimer);
      stkCheckingStatusText.value = 'Verification timeout. Check status below or retry.';
      return;
    }

    try {
      const res = await axios.get(`/api/mpesa/status/${depositId}`);
      if (res.data.status === 'approved') {
        clearInterval(stkTimer);
        activeStkSession.value = null;
        mpesaSuccessMessage.value = '🎉 Payment confirmed! Your USDT balance has been credited.';
        fetchDepositData();
      } else if (res.data.status === 'rejected') {
        clearInterval(stkTimer);
        activeStkSession.value = null;
        mpesaErrorMessage.value = res.data.message || 'M-Pesa payment was cancelled or failed.';
        fetchDepositData();
      }
    } catch (e) {}
  }, 4000);
}

async function checkStkStatusManual() {
  if (!activeStkSession.value) return;
  stkPolling.value = true;

  try {
    const res = await axios.get(`/api/mpesa/status/${activeStkSession.value.deposit_id}`);
    if (res.data.status === 'approved') {
      if (stkTimer) clearInterval(stkTimer);
      activeStkSession.value = null;
      mpesaSuccessMessage.value = '🎉 Payment confirmed! Your USDT balance has been credited.';
      fetchDepositData();
    } else if (res.data.status === 'rejected') {
      if (stkTimer) clearInterval(stkTimer);
      activeStkSession.value = null;
      mpesaErrorMessage.value = res.data.message || 'M-Pesa payment was cancelled or failed.';
      fetchDepositData();
    } else {
      stkCheckingStatusText.value = 'Still awaiting PIN confirmation...';
    }
  } catch (e) {
  } finally {
    stkPolling.value = false;
  }
}

function cancelStkSession() {
  if (stkTimer) clearInterval(stkTimer);
  activeStkSession.value = null;
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
  fetchSettings();
  fetchDepositData();
  pollTimer = setInterval(fetchDepositData, 4000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
  if (stkTimer) clearInterval(stkTimer);
});
</script>
