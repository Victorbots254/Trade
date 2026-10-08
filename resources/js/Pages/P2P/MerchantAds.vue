<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none">
    <!-- Header -->
    <header class="bg-[#181a20] border-b border-[#2b3139] px-4 md:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center space-x-4">
        <a href="/p2p" class="text-xs text-[#848e9c] hover:text-white transition flex items-center space-x-1">
          <span>← Back to P2P Marketplace</span>
        </a>
        <span class="text-[#2b3139]">|</span>
        <h1 class="text-sm font-bold text-white flex items-center space-x-2">
          <span>Merchant Ads Center</span>
          <span v-if="isMerchant" class="bg-[#0ecb81]/10 text-[#0ecb81] border border-[#0ecb81]/30 text-[10px] px-2 py-0.5 rounded uppercase font-bold">
            Verified Merchant
          </span>
        </h1>
      </div>

      <div class="flex items-center space-x-3 text-xs">
        <div class="text-[#848e9c] bg-[#1e2329] px-3 py-1.5 rounded-lg border border-[#2b3139]">
          <span>Balance: </span>
          <span class="text-[#0ecb81] font-mono font-bold">{{ usdtBalance.toFixed(2) }} USDT</span>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- SCREEN A: USER IS NOT AN APPROVED MERCHANT -->
      <div v-if="!isMerchant" class="max-w-2xl mx-auto my-12 bg-[#181a20] border border-[#2b3139] rounded-2xl p-8 text-center space-y-6 shadow-2xl">
        <div class="w-16 h-16 rounded-2xl bg-[#f0b90b]/10 border border-[#f0b90b]/30 flex items-center justify-center text-3xl mx-auto">
          🛡️
        </div>

        <div class="space-y-2">
          <h2 class="text-xl font-bold text-white">P2P Merchant Authorization Required</h2>
          <p class="text-xs text-[#848e9c] leading-relaxed max-w-md mx-auto">
            To prevent fraud and maintain the highest safety standards (like Binance P2P), only traders vetted and authorized by an <strong>Admin</strong> can publish P2P advertisements.
          </p>
        </div>

        <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-4 text-left text-xs space-y-3 font-mono">
          <div class="flex items-start space-x-2.5">
            <span class="text-[#0ecb81]">✓</span>
            <span class="text-slate-300">Browse and buy USDT from existing verified merchants directly on the marketplace.</span>
          </div>
          <div class="flex items-start space-x-2.5">
            <span class="text-[#0ecb81]">✓</span>
            <span class="text-slate-300">Escrow security applies to all trades automatically.</span>
          </div>
          <div class="flex items-start space-x-2.5">
            <span class="text-[#f0b90b]">!</span>
            <span class="text-slate-300">Want to become a certified seller? Contact system administrators or support staff to have your account enabled.</span>
          </div>
        </div>

        <div class="flex justify-center space-x-3 pt-2">
          <a href="/p2p" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-6 py-3 rounded-xl text-xs transition shadow-lg">
            Explore P2P Marketplace →
          </a>
        </div>
      </div>

      <!-- SCREEN B: USER IS AN APPROVED MERCHANT -->
      <div v-else class="space-y-6">
        <!-- Flash Message Banner -->
        <div v-if="$page.props.flash?.message" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span>✓</span>
            <span>{{ $page.props.flash.message }}</span>
          </div>
          <button @click="$page.props.flash.message = null" class="text-emerald-400/70 hover:text-emerald-300">✕</button>
        </div>

        <!-- Global Error Banner -->
        <div v-if="$page.props.errors?.message" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs font-bold flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span>⚠️</span>
            <span>{{ $page.props.errors.message }}</span>
          </div>
        </div>

        <!-- Merchant Stats Banner -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-[#f0b90b]/20 to-[#0ecb81]/20 border border-[#f0b90b]/30 flex items-center justify-center font-black text-xl text-white">
              {{ merchantName[0].toUpperCase() }}
            </div>
            <div>
              <div class="flex items-center space-x-2">
                <h2 class="text-base font-bold text-white">{{ merchantName }}</h2>
                <span class="text-[#0ecb81] text-xs font-bold bg-[#0ecb81]/10 px-2 py-0.5 rounded border border-[#0ecb81]/30">Verified Seller</span>
              </div>
              <div class="text-xs text-[#848e9c] mt-1 space-x-3">
                <span>Completed Trades: <strong class="text-white">{{ completedTrades }}</strong></span>
                <span>·</span>
                <span>Completion Rate: <strong class="text-[#0ecb81]">{{ completionRate.toFixed(1) }}%</strong></span>
              </div>
            </div>
          </div>

          <button
            @click="showCreateModal = true"
            type="button"
            class="bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black px-6 py-3 rounded-xl text-xs shadow-lg flex items-center space-x-2 uppercase tracking-wider">
            <span>+ Post New P2P Ad</span>
          </button>
        </div>

        <!-- My Ads Table -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl text-xs">
          <div class="p-4 border-b border-[#2b3139] flex justify-between items-center">
            <h3 class="font-bold text-white text-sm">Your Posted Advertisements ({{ ads.length }})</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left font-mono">
              <thead>
                <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] font-sans font-semibold">
                  <th class="py-3 px-4">Ad ID</th>
                  <th class="py-3 px-4">Type</th>
                  <th class="py-3 px-4">Unit Price</th>
                  <th class="py-3 px-4">Available / Total</th>
                  <th class="py-3 px-4">Order Limits</th>
                  <th class="py-3 px-4">Status</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#2b3139]/60">
                <tr v-for="ad in ads" :key="ad.id" class="hover:bg-[#1e2329]/50 transition">
                  <td class="py-3.5 px-4 text-[#848e9c]">#{{ ad.id }}</td>
                  <td class="py-3.5 px-4">
                    <span :class="ad.type === 'sell' ? 'bg-[#0ecb81]/10 text-[#0ecb81] border-[#0ecb81]/30' : 'bg-[#f6465d]/10 text-[#f6465d] border-[#f6465d]/30'" class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase">
                      {{ ad.type === 'sell' ? 'Sell USDT' : 'Buy USDT' }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 font-bold text-white">
                    {{ Number(ad.price).toFixed(2) }} {{ ad.fiat }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="text-[#0ecb81]">{{ Number(ad.available_amount).toFixed(2) }}</span> / {{ Number(ad.total_amount).toFixed(2) }} USDT
                  </td>
                  <td class="py-3.5 px-4 text-[#848e9c]">
                    {{ Number(ad.min_limit).toLocaleString() }} - {{ Number(ad.max_limit).toLocaleString() }} {{ ad.fiat }}
                  </td>
                  <td class="py-3.5 px-4 font-sans">
                    <span :class="ad.status === 'active' ? 'text-[#0ecb81]' : (ad.status === 'paused' ? 'text-[#f0b90b]' : 'text-[#848e9c]')" class="font-bold uppercase text-[10px]">
                      {{ ad.status }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-right font-sans space-x-2">
                    <button
                      v-if="ad.status !== 'closed'"
                      @click="toggleAdStatus(ad)"
                      type="button"
                      class="border border-[#2b3139] hover:bg-[#2b3139] text-[#848e9c] hover:text-white px-3 py-1 rounded-lg text-xs transition">
                      {{ ad.status === 'active' ? 'Pause' : 'Activate' }}
                    </button>
                    <button
                      v-if="ad.status !== 'closed'"
                      @click="closeAd(ad)"
                      type="button"
                      class="border border-[#f6465d]/30 hover:bg-[#f6465d]/20 text-[#f6465d] px-3 py-1 rounded-lg text-xs transition">
                      Close
                    </button>
                  </td>
                </tr>

                <tr v-if="ads.length === 0">
                  <td colspan="7" class="py-8 text-center text-[#848e9c] font-sans">
                    You have not published any P2P advertisements yet. Click "+ Post New P2P Ad" above.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- CREATE P2P AD MODAL -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-xl overflow-hidden shadow-2xl text-xs space-y-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <h3 class="font-bold text-white text-sm">Create P2P Advertisement</h3>
          <button @click="showCreateModal = false" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <!-- Validation Errors Box in Modal -->
        <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="bg-rose-500/10 border border-rose-500/40 text-rose-300 p-3.5 rounded-xl text-xs space-y-1.5">
          <div class="font-bold flex items-center space-x-1.5 text-rose-400">
            <span>⚠️</span>
            <span>Unable to publish advertisement:</span>
          </div>
          <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-300">
            <li v-for="(err, key) in $page.props.errors" :key="key">{{ err }}</li>
          </ul>
        </div>

        <form @submit.prevent="submitCreateAd" class="space-y-4">
          <!-- Type: Sell or Buy -->
          <div class="grid grid-cols-2 gap-3">
            <button
              type="button"
              @click="adForm.type = 'sell'"
              :class="adForm.type === 'sell' ? 'bg-[#0ecb81] text-[#1e2329] font-black' : 'border border-[#2b3139] text-[#848e9c]'"
              class="py-2.5 rounded-xl font-bold transition text-xs">
              I Want to Sell USDT
            </button>
            <button
              type="button"
              @click="adForm.type = 'buy'"
              :class="adForm.type === 'buy' ? 'bg-[#f6465d] text-white font-black' : 'border border-[#2b3139] text-[#848e9c]'"
              class="py-2.5 rounded-xl font-bold transition text-xs">
              I Want to Buy USDT
            </button>
          </div>

          <!-- Currency & Price -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[#848e9c] mb-1 font-semibold">Fiat Currency</label>
              <select v-model="adForm.fiat" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2 text-white">
                <option value="KES">KES (Kenyan Shilling)</option>
                <option value="USD">USD (US Dollar)</option>
              </select>
            </div>

            <div>
              <label class="block text-[#848e9c] mb-1 font-semibold">Price per USDT ({{ adForm.fiat }})</label>
              <input v-model="adForm.price" type="number" step="0.01" required placeholder="e.g. 132.50" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2 text-white font-mono" />
            </div>
          </div>

          <!-- Total Crypto Volume -->
          <div>
            <div class="flex justify-between items-center mb-1">
              <label class="block text-[#848e9c] font-semibold">Total USDT to List</label>
              <span class="text-[10px]" :class="usdtBalance < parseFloat(adForm.total_amount || 0) && adForm.type === 'sell' ? 'text-amber-400 font-bold' : 'text-[#848e9c]'">
                Available: {{ usdtBalance.toFixed(2) }} USDT
              </span>
            </div>
            <input v-model="adForm.total_amount" type="number" step="1" min="5" required placeholder="e.g. 50" class="w-full bg-[#0b0e11] border rounded-xl px-3 py-2 text-white font-mono" :class="$page.props.errors?.total_amount ? 'border-rose-500' : 'border-[#2b3139]'" />
            <p v-if="$page.props.errors?.total_amount" class="text-rose-400 text-[11px] mt-1 font-semibold">{{ $page.props.errors.total_amount }}</p>
          </div>

          <!-- Min / Max Order Limits -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[#848e9c] mb-1 font-semibold">Min Limit ({{ adForm.fiat }})</label>
              <input v-model="adForm.min_limit" type="number" step="1" required placeholder="e.g. 650" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2 text-white font-mono" />
            </div>
            <div>
              <label class="block text-[#848e9c] mb-1 font-semibold">Max Limit ({{ adForm.fiat }})</label>
              <input v-model="adForm.max_limit" type="number" step="1" required placeholder="e.g. 50000" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2 text-white font-mono" />
            </div>
          </div>

          <!-- Payment Methods Checkboxes -->
          <div>
            <label class="block text-[#848e9c] mb-1.5 font-semibold">Accepted Payment Methods</label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center space-x-2 text-white cursor-pointer">
                <input type="checkbox" value="mpesa" v-model="adForm.payment_methods" class="rounded bg-[#0b0e11] border-[#2b3139]" />
                <span>📱 Safaricom M-Pesa</span>
              </label>
              <label class="flex items-center space-x-2 text-white cursor-pointer">
                <input type="checkbox" value="bank_transfer" v-model="adForm.payment_methods" class="rounded bg-[#0b0e11] border-[#2b3139]" />
                <span>🏦 Bank Transfer</span>
              </label>
            </div>
          </div>

          <!-- Auto-Reply Message -->
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">Auto-Reply Message (Sent upon order start)</label>
            <input v-model="adForm.auto_reply" type="text" placeholder="e.g. Hello, send payment to Till: 123456 and type the code here." class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2 text-white text-xs" />
          </div>

          <!-- Merchant Terms -->
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">Merchant Trade Terms</label>
            <textarea v-model="adForm.terms" rows="2" placeholder="e.g. Third-party M-Pesa payments not accepted. Only send from your registered phone." class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 text-white text-xs"></textarea>
          </div>

          <div class="flex items-center space-x-3 pt-2">
            <button @click="showCreateModal = false" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold">
              Cancel
            </button>
            <button :disabled="creatingAd" type="submit" class="flex-1 bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black py-2.5 rounded-xl shadow-lg">
              {{ creatingAd ? 'Publishing...' : 'Publish Advertisement' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  isMerchant: { type: Boolean, default: false },
  merchantName: { type: String, default: '' },
  completionRate: { type: Number, default: 100.0 },
  completedTrades: { type: Number, default: 0 },
  usdtBalance: { type: Number, default: 0.00 },
  ads: { type: Array, default: () => [] },
});

const showCreateModal = ref(false);
const creatingAd = ref(false);

const adForm = ref({
  type: 'sell',
  asset: 'USDT',
  fiat: 'KES',
  price: '132.50',
  total_amount: '50',
  min_limit: '650',
  max_limit: '25000',
  payment_methods: ['mpesa'],
  auto_reply: 'Hello, please send payment via M-Pesa and paste your transaction receipt here.',
  terms: 'No third-party payments. The Safaricom M-Pesa name must match your TradeCo account.',
  time_limit_minutes: 15,
});

function submitCreateAd() {
  creatingAd.value = true;
  router.post('/p2p/merchant/ads', adForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
    },
    onFinish: () => {
      creatingAd.value = false;
    },
  });
}

function toggleAdStatus(ad) {
  router.post(`/p2p/merchant/ads/${ad.id}/toggle`);
}

function closeAd(ad) {
  if (confirm('Are you sure you want to close this advertisement?')) {
    router.post(`/p2p/merchant/ads/${ad.id}/close`);
  }
}
</script>
