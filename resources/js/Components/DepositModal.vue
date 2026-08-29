<template>
  <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl text-xs">
      <!-- Modal Header -->
      <div class="flex justify-between items-center px-4 py-3 border-b border-slate-800">
        <h3 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
          <span class="text-emerald-400">📥</span>
          <span>Deposit BEP-20 Crypto Asset</span>
        </h3>
        <button @click="$emit('close')" class="text-slate-500 hover:text-slate-300 text-base">✕</button>
      </div>

      <!-- Pending Verification Screen if submitted -->
      <div v-if="submittedDeposit" class="p-6 text-center space-y-4">
        <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/30 rounded-full flex items-center justify-center mx-auto text-amber-400 text-xl animate-pulse">
          ⏳
        </div>
        <h4 class="font-bold text-slate-100 text-base">Deposit Pending Verification</h4>
        <p class="text-slate-400 leading-relaxed max-w-xs mx-auto text-xs">
          Your deposit request of <strong class="text-slate-200">{{ submittedDeposit.amount }} {{ submittedDeposit.currency }}</strong> has been submitted to support staff for verification.
        </p>

        <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 text-left font-mono text-[11px] space-y-1.5">
          <div class="flex justify-between text-slate-400">
            <span>TxHash:</span>
            <span class="text-emerald-400 truncate max-w-[200px]">{{ submittedDeposit.tx_hash }}</span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span>Status:</span>
            <span class="text-amber-400 font-bold uppercase">{{ submittedDeposit.status }}</span>
          </div>
          <div class="flex justify-between text-slate-400">
            <span>Explorer Link:</span>
            <a :href="'https://bscscan.com/tx/' + submittedDeposit.tx_hash" target="_blank" class="text-blue-400 hover:underline">
              BscScan ↗
            </a>
          </div>
        </div>

        <button @click="submittedDeposit = null" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded font-medium transition">
          Submit Another Deposit
        </button>
      </div>

      <!-- Main Deposit Form -->
      <form v-else @submit.prevent="submitDeposit" class="p-5 space-y-4">
        <!-- Asset Selector -->
        <div>
          <label class="block text-slate-400 text-[11px] mb-1 font-medium">Select Deposit Currency</label>
          <select v-model="form.currency" class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-slate-200 font-mono focus:outline-none focus:border-emerald-500">
            <option value="USDT">USDT - BEP20 (BNB Smart Chain)</option>
            <option value="BNB">BNB - BEP20 (BNB Smart Chain)</option>
            <option value="BTC">BTC - BEP20 (Wrapped BTC)</option>
            <option value="ETH">ETH - BEP20 (Wrapped ETH)</option>
          </select>
        </div>

        <!-- Custodial Payment Address & QR Code -->
        <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 space-y-3">
          <div class="flex items-center justify-between text-slate-400">
            <span class="font-medium">Binance Custodial Deposit Address:</span>
            <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px] font-bold">BEP-20 ONLY</span>
          </div>

          <!-- QR Code Display -->
          <div class="flex justify-center p-2 bg-white rounded-lg w-32 h-32 mx-auto">
            <qrcode-vue :value="custodialAddress" :size="112" level="H" />
          </div>

          <!-- Address Box & Copy CTA -->
          <div class="flex items-center space-x-2 bg-slate-900 border border-slate-800 rounded p-2 font-mono">
            <input readonly :value="custodialAddress" class="bg-transparent text-slate-200 w-full focus:outline-none text-[11px] truncate" />
            <button type="button" @click="copyAddress" class="bg-slate-800 hover:bg-slate-700 text-emerald-400 px-2.5 py-1 rounded text-[10px] font-semibold transition">
              {{ copied ? 'Copied!' : 'Copy' }}
            </button>
          </div>

          <!-- Network Risk Warning -->
          <div class="bg-rose-500/10 border border-rose-500/30 rounded p-2 text-rose-300/90 text-[11px] leading-relaxed flex items-start space-x-2">
            <span class="text-rose-400 text-sm">⚠️</span>
            <span>
              <strong>Crucial Network Warning:</strong> Send only <strong>BEP-20 (BNB Smart Chain)</strong> tokens to this address. Sending funds via any other network will result in permanent loss.
            </span>
          </div>
        </div>

        <!-- User Input Fields -->
        <div class="space-y-3">
          <div>
            <label class="block text-slate-400 text-[11px] mb-1">Expected Amount Sent</label>
            <input v-model="form.amount" type="number" step="0.0001" min="0.0001" required placeholder="e.g. 500.00"
                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 font-mono text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <div>
            <label class="block text-slate-400 text-[11px] mb-1">Transaction Hash (TxHash / TxID) *</label>
            <input v-model="form.tx_hash" type="text" required placeholder="0x..."
                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 font-mono text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <div>
            <label class="block text-slate-400 text-[11px] mb-1">Payment Receipt Screenshot (Optional)</label>
            <input type="file" @change="handleFileUpload" accept="image/*"
                   class="w-full text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700" />
          </div>
        </div>

        <!-- Action Submit Button -->
        <button type="submit" :disabled="loading"
                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 rounded font-bold transition shadow-lg shadow-emerald-900/30 disabled:opacity-50">
          <span v-if="loading">Submitting Deposit...</span>
          <span v-else>Submit Deposit for Verification</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import QrcodeVue from 'qrcode.vue';
import axios from 'axios';

const props = defineProps({
  custodialAddress: { type: String, default: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' },
});

const emit = defineEmits(['close', 'deposit-submitted']);

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

function handleFileUpload(e) {
  form.value.receipt = e.target.files[0] || null;
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
</script>
