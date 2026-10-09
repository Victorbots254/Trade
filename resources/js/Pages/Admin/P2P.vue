<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none pb-12">
    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="currentUser || $page.props.auth?.user"
    />

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- Admin Sub-Navigation -->
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-[#2b3139] pb-4 gap-3">
        <div>
          <h1 class="text-xl font-bold text-white flex items-center space-x-2">
            <span class="text-[#f0b90b]">🛡️</span>
            <span>P2P Administration & Dispute Center</span>
          </h1>
          <p class="text-xs text-[#848e9c] mt-1">Manage verified merchants, active P2P ads, and arbitrate trade escrow disputes.</p>
        </div>

        <div class="flex items-center space-x-2 text-xs">
          <a href="/admin/users" class="bg-[#181a20] text-[#848e9c] hover:text-white px-3.5 py-2 rounded-lg border border-[#2b3139] transition">
            👥 Trader Controls
          </a>
          <a href="/admin/deposits" class="bg-[#181a20] text-[#848e9c] hover:text-white px-3.5 py-2 rounded-lg border border-[#2b3139] transition">
            💰 Deposit Approvals
          </a>
          <a href="/admin/p2p" class="bg-[#f0b90b] text-[#1e2329] px-3.5 py-2 rounded-lg border border-[#f0b90b]/40 font-bold shadow">
            🛡️ P2P Center & Disputes
          </a>
        </div>
      </div>
      <!-- Flash Alert -->
      <div v-if="$page.props.flash?.message" class="bg-[#0ecb81]/10 border border-[#0ecb81]/30 text-[#0ecb81] p-3 rounded-xl text-xs font-semibold flex items-center space-x-2">
        <span>✓</span>
        <span>{{ $page.props.flash.message }}</span>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3.5 text-xs font-mono">
        <div class="bg-[#181a20] border border-[#2b3139] rounded-xl p-3.5 space-y-1">
          <span class="text-[#848e9c] text-[10px] uppercase font-sans">Verified Merchants</span>
          <div class="text-lg font-bold text-[#0ecb81]">{{ stats.merchants_count }}</div>
        </div>

        <div class="bg-[#181a20] border border-[#2b3139] rounded-xl p-3.5 space-y-1">
          <span class="text-[#848e9c] text-[10px] uppercase font-sans">Active P2P Ads</span>
          <div class="text-lg font-bold text-white">{{ stats.active_ads_count }}</div>
        </div>

        <div class="bg-[#181a20] border border-[#2b3139] rounded-xl p-3.5 space-y-1">
          <span class="text-[#848e9c] text-[10px] uppercase font-sans">Pending Disputes</span>
          <div :class="stats.disputed_count > 0 ? 'text-[#f6465d] animate-pulse' : 'text-[#848e9c]'" class="text-lg font-bold">
            {{ stats.disputed_count }}
          </div>
        </div>

        <div class="bg-[#181a20] border border-[#2b3139] rounded-xl p-3.5 space-y-1">
          <span class="text-[#848e9c] text-[10px] uppercase font-sans">Completed Trades</span>
          <div class="text-lg font-bold text-[#f0b90b]">{{ stats.completed_orders_count }}</div>
        </div>

        <div class="bg-[#181a20] border border-[#2b3139] rounded-xl p-3.5 space-y-1">
          <span class="text-[#848e9c] text-[10px] uppercase font-sans">Active Escrow</span>
          <div class="text-lg font-bold text-white">{{ Number(stats.active_escrow_volume).toFixed(2) }} <span class="text-[10px] font-normal text-[#848e9c]">USDT</span></div>
        </div>
      </div>

      <!-- Nav Tabs -->
      <div class="flex items-center space-x-2 border-b border-[#2b3139] pb-3 text-xs font-semibold">
        <button
          @click="activeTab = 'merchants'"
          type="button"
          :class="activeTab === 'merchants' ? 'bg-[#f0b90b] text-[#1e2329] font-black' : 'text-[#848e9c] hover:text-white bg-[#181a20] border border-[#2b3139]'"
          class="px-4 py-2 rounded-xl transition">
          👥 P2P Merchants & Roles ({{ users.length }})
        </button>

        <button
          @click="activeTab = 'disputes'"
          type="button"
          :class="activeTab === 'disputes' ? 'bg-[#f6465d] text-white font-black' : 'text-[#848e9c] hover:text-white bg-[#181a20] border border-[#2b3139]'"
          class="px-4 py-2 rounded-xl transition flex items-center space-x-1.5">
          <span>⚖️ Live Disputes / Appeals</span>
          <span v-if="stats.disputed_count > 0" class="bg-white/20 text-white text-[10px] px-1.5 rounded-full font-bold">
            {{ stats.disputed_count }}
          </span>
        </button>

        <button
          @click="activeTab = 'ads'"
          type="button"
          :class="activeTab === 'ads' ? 'bg-[#0ecb81] text-[#1e2329] font-black' : 'text-[#848e9c] hover:text-white bg-[#181a20] border border-[#2b3139]'"
          class="px-4 py-2 rounded-xl transition">
          📢 Marketplace Ads ({{ ads.length }})
        </button>
      </div>

      <!-- TAB 1: P2P MERCHANTS & USERS -->
      <div v-if="activeTab === 'merchants'" class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl text-xs space-y-4 p-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
          <div>
            <h2 class="text-sm font-bold text-white">Merchant Seller Approvals</h2>
            <p class="text-[11px] text-[#848e9c]">Authorize or revoke traders permitted to publish P2P Buy/Sell advertisements.</p>
          </div>
          <input
            v-model="userSearch"
            type="text"
            placeholder="Search trader by name or email..."
            class="bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2 text-white placeholder-[#5e6673] focus:outline-none focus:border-[#f0b90b] text-xs w-full sm:w-64" />
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] font-sans font-semibold">
                <th class="py-3 px-3">ID</th>
                <th class="py-3 px-3">Trader Name</th>
                <th class="py-3 px-3">Email Address</th>
                <th class="py-3 px-3">Role Status</th>
                <th class="py-3 px-3">P2P Merchant</th>
                <th class="py-3 px-3">Trades / Completion</th>
                <th class="py-3 px-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr v-for="u in filteredUsers" :key="u.id" class="hover:bg-[#1e2329]/50 transition">
                <td class="py-3 px-3 text-[#848e9c]">#{{ u.id }}</td>
                <td class="py-3 px-3 font-sans font-bold text-white">
                  {{ u.p2p_merchant_name || u.name }}
                </td>
                <td class="py-3 px-3 text-[#848e9c]">{{ u.email }}</td>
                <td class="py-3 px-3 font-sans">
                  <span v-if="u.is_admin" class="bg-[#f0b90b]/10 text-[#f0b90b] border border-[#f0b90b]/30 text-[10px] px-2 py-0.5 rounded font-bold">Admin</span>
                  <span v-else class="text-[#848e9c] text-[11px]">User</span>
                </td>
                <td class="py-3 px-3 font-sans">
                  <span v-if="u.is_p2p_merchant" class="text-[#0ecb81] font-bold flex items-center space-x-1">
                    <span>✓</span>
                    <span>Approved Seller</span>
                  </span>
                  <span v-else class="text-[#848e9c]">Not Merchant</span>
                </td>
                <td class="py-3 px-3">
                  <span class="text-white">{{ u.p2p_completed_trades || 0 }}</span>
                  <span class="text-[#848e9c] text-[10px]"> ({{ Number(u.p2p_completion_rate || 100).toFixed(1) }}%)</span>
                </td>
                <td class="py-3 px-3 text-right font-sans">
                  <!-- Toggle Merchant Button -->
                  <button
                    @click="toggleMerchantStatus(u)"
                    type="button"
                    :class="u.is_p2p_merchant ? 'bg-[#f6465d]/10 hover:bg-[#f6465d]/20 text-[#f6465d] border border-[#f6465d]/30' : 'bg-[#0ecb81]/10 hover:bg-[#0ecb81]/20 text-[#0ecb81] border border-[#0ecb81]/30'"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition">
                    {{ u.is_p2p_merchant ? 'Revoke Merchant' : 'Approve Merchant' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: LIVE DISPUTES & APPEALS -->
      <div v-else-if="activeTab === 'disputes'" class="space-y-4">
        <div v-for="d in disputes" :key="d.id" class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-5 shadow-xl space-y-4 text-xs">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-[#2b3139] pb-3 gap-2">
            <div class="flex items-center space-x-2">
              <span class="text-lg">⚖️</span>
              <h3 class="font-bold text-white text-sm">Order #{{ d.order_number }}</h3>
              <span :class="d.status === 'disputed' ? 'bg-[#f6465d]/10 text-[#f6465d] border border-[#f6465d]/30' : 'bg-[#f0b90b]/10 text-[#f0b90b] border border-[#f0b90b]/30'" class="text-[10px] px-2 py-0.5 rounded uppercase font-bold font-mono">
                {{ d.status }}
              </span>
            </div>

            <div class="font-mono text-xs">
              <span class="text-[#848e9c]">Escrow: </span>
              <strong class="text-[#0ecb81]">{{ Number(d.crypto_amount).toFixed(4) }} USDT</strong>
              <span class="text-[#848e9c] ml-2">Fiat: </span>
              <strong class="text-white">{{ Number(d.fiat_amount).toLocaleString() }} KES</strong>
            </div>
          </div>

          <!-- Parties & Dispute Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3.5 space-y-2 font-mono text-[11px]">
              <div class="flex justify-between">
                <span class="text-[#848e9c]">Buyer:</span>
                <span class="text-white font-bold">{{ d.buyer?.name }} ({{ d.buyer?.email }})</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#848e9c]">Seller:</span>
                <span class="text-white font-bold">{{ d.seller?.name }} ({{ d.seller?.email }})</span>
              </div>
              <div class="flex justify-between">
                <span class="text-[#848e9c]">Payment Method:</span>
                <span class="text-[#0ecb81] font-bold uppercase">{{ d.payment_method }}</span>
              </div>
            </div>

            <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3.5 space-y-2 text-[11px]">
              <div class="flex justify-between font-mono">
                <span class="text-[#848e9c]">Dispute Initiator:</span>
                <span class="text-[#f6465d] font-bold">{{ d.disputer?.name || 'Trader' }}</span>
              </div>
              <div>
                <span class="text-[#848e9c] block text-[10px]">Reported Reason:</span>
                <p class="text-white bg-[#181a20] p-2 rounded border border-[#2b3139] mt-1">{{ d.dispute_reason || 'No statement provided' }}</p>
              </div>
            </div>
          </div>

          <!-- In-Trade Chat Transcript -->
          <div class="space-y-2">
            <div class="flex justify-between items-center text-[11px]">
              <span class="font-bold text-slate-300">In-Order Chat & Evidence Transcript ({{ d.messages?.length || 0 }} messages)</span>
              <a :href="'/p2p/orders/' + d.id" target="_blank" class="text-[#f0b90b] hover:underline">Open Live Trade Room ↗</a>
            </div>

            <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 max-h-40 overflow-y-auto space-y-2 text-[11px]">
              <div v-for="m in d.messages" :key="m.id" class="border-b border-[#2b3139]/40 pb-1.5 last:border-0">
                <div class="flex justify-between text-[10px] text-[#848e9c]">
                  <strong>{{ m.user?.name || (m.is_system ? 'System' : 'Trader') }}</strong>
                  <span>{{ new Date(m.created_at).toLocaleTimeString() }}</span>
                </div>
                <p class="text-slate-200 mt-0.5">{{ m.message }}</p>
                <div v-if="m.attachment_path" class="mt-1">
                  <a :href="'/storage/' + m.attachment_path" target="_blank" class="text-[#f0b90b] underline text-[10px]">
                    📎 View Attached Receipt Screenshot
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Dispute Actions -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2 border-t border-[#2b3139]">
            <button
              @click="openResolutionModal(d, 'release_to_buyer')"
              type="button"
              class="bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black px-4 py-2.5 rounded-xl transition shadow">
              ✓ Force Release to Buyer
            </button>
            <button
              @click="openResolutionModal(d, 'refund_seller')"
              type="button"
              class="bg-[#f6465d] hover:bg-[#e03a51] text-white font-black px-4 py-2.5 rounded-xl transition shadow">
              ✕ Cancel & Refund Seller
            </button>
          </div>
        </div>

        <div v-if="disputes.length === 0" class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-12 text-center text-[#848e9c] text-xs">
          🎉 No open disputes or contested orders at this time.
        </div>
      </div>

      <!-- TAB 3: ALL P2P ADS -->
      <div v-else-if="activeTab === 'ads'" class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl text-xs">
        <div class="p-4 border-b border-[#2b3139]">
          <h2 class="font-bold text-white text-sm">Active & Paused Ads ({{ ads.length }})</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] font-sans font-semibold">
                <th class="py-3 px-4">Ad ID</th>
                <th class="py-3 px-4">Merchant</th>
                <th class="py-3 px-4">Type</th>
                <th class="py-3 px-4">Price</th>
                <th class="py-3 px-4">Remaining</th>
                <th class="py-3 px-4">Limits</th>
                <th class="py-3 px-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr v-for="a in ads" :key="a.id" class="hover:bg-[#1e2329]/50 transition">
                <td class="py-3 px-4 text-[#848e9c]">#{{ a.id }}</td>
                <td class="py-3 px-4 font-sans font-bold text-white">{{ a.user?.p2p_merchant_name || a.user?.name }}</td>
                <td class="py-3 px-4 uppercase font-sans font-bold" :class="a.type === 'sell' ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
                  {{ a.type === 'sell' ? 'Sell USDT' : 'Buy USDT' }}
                </td>
                <td class="py-3 px-4 font-bold text-white">{{ Number(a.price).toFixed(2) }} {{ a.fiat }}</td>
                <td class="py-3 px-4 text-[#0ecb81]">{{ Number(a.available_amount).toFixed(2) }} USDT</td>
                <td class="py-3 px-4 text-[#848e9c]">{{ Number(a.min_limit).toLocaleString() }} - {{ Number(a.max_limit).toLocaleString() }} {{ a.fiat }}</td>
                <td class="py-3 px-4 font-sans font-bold uppercase text-[10px]" :class="a.status === 'active' ? 'text-[#0ecb81]' : 'text-[#848e9c]'">
                  {{ a.status }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- RESOLUTION MODAL -->
    <div v-if="selectedDisputeForResolution" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <h3 class="font-bold text-white text-sm">
          Resolve Order #{{ selectedDisputeForResolution.order_number }}
        </h3>
        <p class="text-[#848e9c]">
          Action:
          <strong :class="resolutionAction === 'release_to_buyer' ? 'text-[#0ecb81]' : 'text-[#f6465d]'">
            {{ resolutionAction === 'release_to_buyer' ? 'Force Release to Buyer' : 'Cancel & Refund Seller' }}
          </strong>
        </p>

        <div>
          <label class="block text-[#848e9c] mb-1 font-semibold">Staff Resolution Note (Logged to audit trail)</label>
          <textarea
            v-model="resolutionNotes"
            required
            rows="3"
            placeholder="e.g. Verified M-Pesa receipt against Safaricom statement; funds cleared."
            class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 text-white focus:outline-none focus:border-[#f0b90b] text-xs"></textarea>
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="selectedDisputeForResolution = null" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold">
            Cancel
          </button>
          <button
            @click="submitDisputeResolution"
            :disabled="!resolutionNotes"
            type="button"
            :class="resolutionAction === 'release_to_buyer' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e03a51] text-white'"
            class="flex-1 font-black py-2.5 rounded-xl shadow-lg disabled:opacity-50">
            Confirm Resolution
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';

const props = defineProps({
  users: { type: Array, required: true },
  ads: { type: Array, required: true },
  disputes: { type: Array, required: true },
  stats: { type: Object, required: true },
  currentUser: { type: Object, required: true },
});

const activeTab = ref('merchants');
const userSearch = ref('');

const selectedDisputeForResolution = ref(null);
const resolutionAction = ref('');
const resolutionNotes = ref('');

const filteredUsers = computed(() => {
  if (!userSearch.value) return props.users;
  const s = userSearch.value.toLowerCase();
  return props.users.filter(u =>
    (u.name && u.name.toLowerCase().includes(s)) ||
    (u.email && u.email.toLowerCase().includes(s)) ||
    (u.p2p_merchant_name && u.p2p_merchant_name.toLowerCase().includes(s))
  );
});

function toggleMerchantStatus(user) {
  const willBeMerchant = !user.is_p2p_merchant;
  const verb = willBeMerchant ? 'approve' : 'revoke';
  if (confirm(`Are you sure you want to ${verb} P2P merchant privileges for ${user.name}?`)) {
    router.post(`/admin/p2p/users/${user.id}/merchant`, {
      is_p2p_merchant: willBeMerchant,
      p2p_merchant_name: user.p2p_merchant_name || user.name,
    });
  }
}

function openResolutionModal(dispute, action) {
  selectedDisputeForResolution.value = dispute;
  resolutionAction.value = action;
  resolutionNotes.value = '';
}

function submitDisputeResolution() {
  if (!selectedDisputeForResolution.value || !resolutionNotes.value) return;

  router.post(`/admin/p2p/orders/${selectedDisputeForResolution.value.id}/resolve`, {
    resolution: resolutionAction.value,
    admin_notes: resolutionNotes.value,
  }, {
    onSuccess: () => {
      selectedDisputeForResolution.value = null;
    },
  });
}
</script>
