<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none">
    <!-- Top Navigation Header -->
    <header class="bg-[#181a20] border-b border-[#2b3139] px-4 md:px-8 py-3 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center space-x-6">
        <a href="/" class="flex items-center space-x-2 font-black text-lg text-[#f0b90b] tracking-wider">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#f0b90b] to-[#d4a30b] text-[#1e2329] flex items-center justify-center text-sm font-black shadow-md">T</div>
          <span class="text-white">TRADE<span class="text-[#f0b90b]">CO</span> <span class="text-xs bg-[#f0b90b]/10 text-[#f0b90b] px-2 py-0.5 rounded ml-1 border border-[#f0b90b]/20 font-bold uppercase">P2P</span></span>
        </a>

        <nav class="hidden md:flex items-center space-x-4 text-xs font-semibold">
          <a href="/terminal" class="text-[#848e9c] hover:text-[#f0b90b] transition">Spot Terminal</a>
          <a href="/options" class="text-[#848e9c] hover:text-[#f0b90b] transition">Options</a>
          <a href="/p2p" class="text-[#f0b90b] border-b-2 border-[#f0b90b] pb-1 font-bold">P2P Trading</a>
          <a href="/deposit" class="text-[#848e9c] hover:text-[#f0b90b] transition">Deposit</a>
          <a href="/monthly-interests" class="text-[#848e9c] hover:text-[#f0b90b] transition">Earn up to 15%</a>
        </nav>
      </div>

      <div class="flex items-center space-x-3 text-xs">
        <a href="/p2p/orders" class="hidden sm:inline-flex items-center space-x-1.5 text-[#848e9c] hover:text-white bg-[#2b3139]/40 hover:bg-[#2b3139] px-3 py-1.5 rounded-lg border border-[#2b3139] transition">
          <span>📋</span>
          <span>My P2P Orders</span>
        </a>
        <a href="/p2p/merchant/ads" class="inline-flex items-center space-x-1.5 bg-[#f0b90b]/10 text-[#f0b90b] hover:bg-[#f0b90b]/20 border border-[#f0b90b]/30 px-3 py-1.5 rounded-lg font-bold transition">
          <span>📢</span>
          <span>Merchant Ads</span>
        </a>
        <div v-if="user" class="text-xs text-[#848e9c] bg-[#1e2329] px-3 py-1.5 rounded-lg border border-[#2b3139]">
          <span class="text-[#848e9c]">Balance: </span>
          <span class="text-[#0ecb81] font-mono font-bold">{{ usdtBalance.toFixed(2) }} USDT</span>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- Banner / Announcement -->
      <div class="bg-gradient-to-r from-[#1e2329] via-[#1e2329] to-[#2b3139]/60 border border-[#2b3139] rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl space-y-2">
          <div class="inline-flex items-center space-x-2 bg-[#f0b90b]/10 border border-[#f0b90b]/30 text-[#f0b90b] text-[11px] font-bold px-2.5 py-0.5 rounded-full">
            <span>🛡️ 100% Escrow Protection</span>
          </div>
          <h1 class="text-xl md:text-2xl font-black text-white">TradeCo P2P Crypto Marketplace</h1>
          <p class="text-xs text-[#848e9c] leading-relaxed">
            Buy and sell USDT directly with verified local traders using M-Pesa and Bank Transfers with 0% platform trading fees. Crypto is locked in secure escrow until payment is confirmed.
          </p>
        </div>
      </div>

      <!-- Control Bar: BUY / SELL TABS & FILTERS -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-4 space-y-4 shadow-lg">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <!-- Buy / Sell Toggle Tabs -->
          <div class="flex items-center bg-[#0b0e11] p-1 rounded-xl border border-[#2b3139] w-full sm:w-auto">
            <button
              @click="setTab('sell')"
              type="button"
              :class="activeType === 'sell' ? 'bg-[#0ecb81] text-[#1e2329] font-black shadow' : 'text-[#848e9c] hover:text-white'"
              class="flex-1 sm:flex-initial px-6 py-2 rounded-lg text-xs md:text-sm font-bold transition flex items-center justify-center space-x-2">
              <span>Buy USDT</span>
            </button>
            <button
              @click="setTab('buy')"
              type="button"
              :class="activeType === 'buy' ? 'bg-[#f6465d] text-white font-black shadow' : 'text-[#848e9c] hover:text-white'"
              class="flex-1 sm:flex-initial px-6 py-2 rounded-lg text-xs md:text-sm font-bold transition flex items-center justify-center space-x-2">
              <span>Sell USDT</span>
            </button>
          </div>

          <!-- Filters: Fiat & Payment Method -->
          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto text-xs">
            <div class="flex items-center space-x-2 bg-[#0b0e11] border border-[#2b3139] px-3 py-1.5 rounded-xl">
              <span class="text-[#848e9c] text-[11px]">Fiat:</span>
              <select v-model="selectedFiat" @change="applyFilters" class="bg-transparent text-white font-bold focus:outline-none">
                <option value="KES" class="bg-[#181a20]">KES (Kenyan Shilling)</option>
                <option value="USD" class="bg-[#181a20]">USD (US Dollar)</option>
              </select>
            </div>

            <div class="flex items-center space-x-2 bg-[#0b0e11] border border-[#2b3139] px-3 py-1.5 rounded-xl">
              <span class="text-[#848e9c] text-[11px]">Payment:</span>
              <select v-model="selectedPayment" @change="applyFilters" class="bg-transparent text-white font-bold focus:outline-none">
                <option value="all" class="bg-[#181a20]">All Payment Methods</option>
                <option value="mpesa" class="bg-[#181a20]">📱 M-Pesa</option>
                <option value="bank_transfer" class="bg-[#181a20]">🏦 Bank Transfer</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Ads Table -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] uppercase tracking-wider font-semibold">
                <th class="py-3.5 px-4 md:px-6">Advertiser</th>
                <th class="py-3.5 px-4">Price</th>
                <th class="py-3.5 px-4">Available / Order Limits</th>
                <th class="py-3.5 px-4">Payment Methods</th>
                <th class="py-3.5 px-4 md:px-6 text-right">Trade Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr v-for="ad in ads.data" :key="ad.id" class="hover:bg-[#1e2329]/50 transition">
                <!-- Merchant info -->
                <td class="py-4 px-4 md:px-6">
                  <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#f0b90b]/20 to-[#0ecb81]/20 border border-[#f0b90b]/30 flex items-center justify-center font-bold text-white text-sm">
                      {{ (ad.user?.p2p_merchant_name || ad.user?.name || 'M')[0].toUpperCase() }}
                    </div>
                    <div>
                      <div class="flex items-center space-x-1.5">
                        <span class="font-bold text-white text-sm">{{ ad.user?.p2p_merchant_name || ad.user?.name }}</span>
                        <span class="text-[#0ecb81] text-xs" title="Verified Merchant">✓</span>
                      </div>
                      <div class="text-[11px] text-[#848e9c] flex items-center space-x-2 mt-0.5">
                        <span>{{ ad.user?.p2p_completed_trades || 0 }} orders</span>
                        <span>·</span>
                        <span class="text-[#0ecb81]">{{ Number(ad.user?.p2p_completion_rate || 100).toFixed(1) }}% completion</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Price -->
                <td class="py-4 px-4 font-mono">
                  <div class="text-base font-black text-white">
                    {{ Number(ad.price).toFixed(2) }} <span class="text-xs text-[#848e9c] font-normal">{{ ad.fiat }}</span>
                  </div>
                  <div class="text-[10px] text-[#848e9c]">per 1 USDT</div>
                </td>

                <!-- Available & Limits -->
                <td class="py-4 px-4 font-mono space-y-1">
                  <div class="text-slate-300">
                    <span class="text-[#848e9c] text-[11px]">Available: </span>
                    <strong class="text-white">{{ Number(ad.available_amount).toFixed(2) }}</strong> USDT
                  </div>
                  <div class="text-[11px] text-[#848e9c]">
                    <span>Limit: </span>
                    <span class="text-white">{{ Number(ad.min_limit).toLocaleString() }} - {{ Number(ad.max_limit).toLocaleString() }} {{ ad.fiat }}</span>
                  </div>
                </td>

                <!-- Payment methods -->
                <td class="py-4 px-4">
                  <div class="flex flex-wrap gap-1.5">
                    <span v-for="method in ad.payment_methods" :key="method"
                          :class="method === 'mpesa' ? 'bg-[#0ecb81]/10 text-[#0ecb81] border-[#0ecb81]/30' : 'bg-[#1e88e5]/10 text-[#64b5f6] border-[#1e88e5]/30'"
                          class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase font-mono">
                      {{ method === 'mpesa' ? '📱 M-Pesa' : '🏦 Bank' }}
                    </span>
                  </div>
                </td>

                <!-- Action Button -->
                <td class="py-4 px-4 md:px-6 text-right">
                  <button
                    @click="openTradeModal(ad)"
                    type="button"
                    :class="activeType === 'sell' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e03a51] text-white'"
                    class="font-black px-5 py-2.5 rounded-xl transition text-xs shadow-lg font-sans">
                    {{ activeType === 'sell' ? 'Buy USDT' : 'Sell USDT' }}
                  </button>
                </td>
              </tr>

              <tr v-if="ads.data.length === 0">
                <td colspan="5" class="py-12 text-center text-[#848e9c]">
                  <div class="text-3xl mb-2">🔍</div>
                  <p class="text-sm font-semibold text-slate-300">No active P2P ads matching your filters.</p>
                  <p class="text-xs mt-1">Try switching currency, payment method, or check back shortly.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- TRADE INITIATION MODAL -->
    <div v-if="selectedAd" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl text-xs space-y-4 p-6">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <div class="flex items-center space-x-2">
            <span :class="selectedAd.type === 'sell' ? 'text-[#0ecb81]' : 'text-[#f6465d]'" class="font-bold text-base">
              {{ selectedAd.type === 'sell' ? '🟢 Buy USDT' : '🔴 Sell USDT' }}
            </span>
            <span class="text-[#848e9c]">with {{ selectedAd.user?.p2p_merchant_name || selectedAd.user?.name }}</span>
          </div>
          <button @click="selectedAd = null" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <div v-if="orderError" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] p-3 rounded-xl text-xs">
          {{ orderError }}
        </div>

        <!-- Ad Summary Details -->
        <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 space-y-2 font-mono text-[11px]">
          <div class="flex justify-between">
            <span class="text-[#848e9c]">Unit Price:</span>
            <span class="font-bold text-white">{{ Number(selectedAd.price).toFixed(2) }} {{ selectedAd.fiat }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#848e9c]">Order Limits:</span>
            <span class="text-white">{{ Number(selectedAd.min_limit).toLocaleString() }} - {{ Number(selectedAd.max_limit).toLocaleString() }} {{ selectedAd.fiat }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-[#848e9c]">Payment Window:</span>
            <span class="text-[#f0b90b]">{{ selectedAd.time_limit_minutes || 15 }} Minutes</span>
          </div>
        </div>

        <!-- Inputs: Amount Calculator -->
        <div class="space-y-3">
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">I want to pay ({{ selectedAd.fiat }})</label>
            <div class="relative">
              <input
                v-model="tradeFiatInput"
                @input="calculateFromFiat"
                type="number"
                step="1"
                :min="selectedAd.min_limit"
                :max="selectedAd.max_limit"
                placeholder="0.00"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-4 py-3 text-white font-mono text-sm font-bold focus:outline-none focus:border-[#f0b90b]" />
              <button @click="setMaxAmount" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[#f0b90b] font-bold uppercase hover:underline">Max</button>
            </div>
          </div>

          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">I will receive (USDT)</label>
            <div class="relative">
              <input
                v-model="tradeCryptoInput"
                @input="calculateFromCrypto"
                type="number"
                step="0.0001"
                placeholder="0.00"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-4 py-3 text-[#0ecb81] font-mono text-sm font-bold focus:outline-none focus:border-[#f0b90b]" />
            </div>
          </div>

          <!-- Select Payment Method -->
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">Payment Method</label>
            <select v-model="selectedOrderPaymentMethod" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2.5 text-white font-semibold focus:outline-none focus:border-[#f0b90b]">
              <option v-for="m in selectedAd.payment_methods" :key="m" :value="m">
                {{ m === 'mpesa' ? '📱 Safaricom M-Pesa' : '🏦 Bank Transfer' }}
              </option>
            </select>
          </div>

          <!-- Merchant Terms -->
          <div v-if="selectedAd.terms" class="bg-[#1e2329]/60 border border-[#2b3139] p-2.5 rounded-lg text-[11px] text-[#848e9c] space-y-1">
            <span class="text-white font-semibold">Merchant Terms:</span>
            <p>{{ selectedAd.terms }}</p>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center space-x-3 pt-2">
          <button @click="selectedAd = null" type="button" class="flex-1 border border-[#2b3139] hover:bg-[#2b3139]/40 text-[#848e9c] hover:text-white py-3 rounded-xl font-bold transition">
            Cancel
          </button>
          <button
            @click="submitOrder"
            :disabled="orderLoading || !tradeFiatInput || tradeFiatInput <= 0"
            type="button"
            :class="selectedAd.type === 'sell' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e03a51] text-white'"
            class="flex-1 font-black py-3 rounded-xl transition shadow-lg disabled:opacity-50 flex items-center justify-center space-x-2">
            <span v-if="orderLoading">Creating Escrow...</span>
            <span v-else>{{ selectedAd.type === 'sell' ? 'Buy USDT (Escrow)' : 'Sell USDT' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  ads: { type: Object, required: true },
  filters: { type: Object, default: () => ({ type: 'sell', fiat: 'KES', payment_method: 'all' }) },
  user: { type: Object, default: null },
  usdtBalance: { type: Number, default: 0.00 },
});

const activeType = ref(props.filters.type || 'sell');
const selectedFiat = ref(props.filters.fiat || 'KES');
const selectedPayment = ref(props.filters.payment_method || 'all');

const selectedAd = ref(null);
const tradeFiatInput = ref('');
const tradeCryptoInput = ref('');
const selectedOrderPaymentMethod = ref('mpesa');
const orderLoading = ref(false);
const orderError = ref('');

function setTab(type) {
  activeType.value = type;
  applyFilters();
}

function applyFilters() {
  router.get('/p2p', {
    type: activeType.value,
    fiat: selectedFiat.value,
    payment_method: selectedPayment.value,
  }, { preserveState: true });
}

function openTradeModal(ad) {
  if (!props.user) {
    window.location.href = '/login';
    return;
  }
  selectedAd.value = ad;
  selectedOrderPaymentMethod.value = ad.payment_methods[0] || 'mpesa';
  tradeFiatInput.value = ad.min_limit.toString();
  calculateFromFiat();
  orderError.value = '';
}

function calculateFromFiat() {
  if (!selectedAd.value) return;
  const fiat = parseFloat(tradeFiatInput.value) || 0;
  if (fiat <= 0) {
    tradeCryptoInput.value = '';
    return;
  }
  tradeCryptoInput.value = (fiat / selectedAd.value.price).toFixed(4);
}

function calculateFromCrypto() {
  if (!selectedAd.value) return;
  const crypto = parseFloat(tradeCryptoInput.value) || 0;
  if (crypto <= 0) {
    tradeFiatInput.value = '';
    return;
  }
  tradeFiatInput.value = (crypto * selectedAd.value.price).toFixed(2);
}

function setMaxAmount() {
  if (!selectedAd.value) return;
  const maxFiat = Math.min(selectedAd.value.max_limit, selectedAd.value.available_amount * selectedAd.value.price);
  tradeFiatInput.value = maxFiat.toFixed(2);
  calculateFromFiat();
}

function submitOrder() {
  if (!selectedAd.value) return;
  orderLoading.value = true;
  orderError.value = '';

  router.post('/p2p/orders', {
    ad_id: selectedAd.value.id,
    amount: tradeFiatInput.value,
    amount_type: 'fiat',
    payment_method: selectedOrderPaymentMethod.value,
  }, {
    onError: (errors) => {
      orderError.value = errors.message || Object.values(errors)[0] || 'Could not place P2P order.';
      orderLoading.value = false;
    },
    onFinish: () => {
      orderLoading.value = false;
    },
  });
}
</script>
