<template>
  <div class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl text-xs space-y-4 p-5">
      <div class="border-b border-slate-800 pb-3 flex justify-between items-center">
        <h3 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
          <span>📜</span>
          <span>Terms of Service & Risk Disclosure</span>
        </h3>
      </div>

      <div class="bg-slate-950 border border-slate-800 rounded p-4 max-h-60 overflow-y-auto text-slate-400 space-y-2 text-[11px] leading-relaxed">
        <p class="font-bold text-slate-200">1. Spot Trading Disclosure</p>
        <p>Trading cryptocurrencies involves high market volatility. You acknowledge that prices fluctuate rapidly and past performance is not indicative of future results.</p>
        
        <p class="font-bold text-slate-200">2. Custodial Deposit Safety</p>
        <p>All manual deposits must be transmitted via the designated BEP-20 (BNB Smart Chain) network address. Deposits made via incorrect network protocols cannot be recovered.</p>

        <p class="font-bold text-slate-200">3. Double-Entry Accounting</p>
        <p>All trades are settled atomically in our ledger. Negative balances or un-backed trade execution are strictly prohibited.</p>
      </div>

      <div class="flex items-center space-x-2">
        <input type="checkbox" v-model="agreed" id="terms-check" class="rounded bg-slate-950 border-slate-800 text-emerald-500 focus:ring-emerald-500" />
        <label for="terms-check" class="text-slate-300">
          I have read and agree to the <a href="/terms" target="_blank" class="text-emerald-400 underline">Terms of Service</a> and <a href="/risk-disclosure" target="_blank" class="text-amber-400 underline">Risk Disclosure Policy</a>.
        </label>
      </div>

      <button :disabled="!agreed" @click="$emit('accept')"
              class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white font-bold py-2 rounded transition">
        Accept & Continue
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const agreed = ref(false);
defineEmits(['accept']);
</script>
