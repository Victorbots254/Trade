<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none">
    <!-- Top Navigation Header -->
    <TradingHeader :user="user || $page.props.auth?.user" />

    <!-- Sub Header Breadcrumb Bar -->
    <div class="bg-[#181a20] border-b border-[#2b3139] px-4 md:px-8 py-2.5 flex items-center justify-between sticky top-0 z-30 text-xs">
      <div class="flex items-center space-x-3">
        <Link href="/p2p" class="text-xs text-[#848e9c] hover:text-white transition flex items-center space-x-1 font-semibold">
          <span>← Back to P2P Marketplace</span>
        </Link>
        <span class="text-[#2b3139]">|</span>
        <h1 class="text-xs font-bold text-white flex items-center space-x-1.5">
          <span>📋</span>
          <span>My P2P Orders</span>
        </h1>
      </div>
      <Link href="/p2p/merchant/ads" class="inline-flex items-center space-x-1 text-xs text-[#f0b90b] hover:text-yellow-400 font-bold transition">
        <span>Merchant Ads</span>
        <span>→</span>
      </Link>
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl text-xs">
        <div class="p-4 border-b border-[#2b3139] flex justify-between items-center">
          <h2 class="font-bold text-white text-sm">All P2P Trades ({{ orders.total || orders.data.length }})</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] font-sans font-semibold">
                <th class="py-3 px-4">Order #</th>
                <th class="py-3 px-4">Role / Type</th>
                <th class="py-3 px-4">Counterparty</th>
                <th class="py-3 px-4">Crypto Amount</th>
                <th class="py-3 px-4">Fiat Total</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4 text-right">Created</th>
                <th class="py-3 px-4 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr v-for="o in orders.data" :key="o.id" class="hover:bg-[#1e2329]/50 transition">
                <td class="py-3.5 px-4 font-bold text-white">
                  {{ o.order_number }}
                </td>

                <td class="py-3.5 px-4 font-sans font-bold">
                  <span v-if="o.buyer_id === user.id" class="text-[#0ecb81]">
                    BUYING USDT
                  </span>
                  <span v-else class="text-[#f6465d]">
                    SELLING USDT
                  </span>
                </td>

                <td class="py-3.5 px-4 font-sans">
                  {{ o.buyer_id === user.id ? (o.seller?.p2p_merchant_name || o.seller?.name) : (o.buyer?.p2p_merchant_name || o.buyer?.name) }}
                </td>

                <td class="py-3.5 px-4 font-bold text-[#0ecb81]">
                  {{ Number(o.crypto_amount).toFixed(4) }} USDT
                </td>

                <td class="py-3.5 px-4 text-white">
                  {{ Number(o.fiat_amount).toLocaleString() }} {{ o.ad?.fiat || 'KES' }}
                </td>

                <td class="py-3.5 px-4 font-sans">
                  <span :class="getStatusBadge(o.status)" class="text-[10px] px-2 py-0.5 rounded font-bold uppercase">
                    {{ formatStatus(o.status) }}
                  </span>
                </td>

                <td class="py-3.5 px-4 text-right text-[#848e9c] text-[11px]">
                  {{ new Date(o.created_at).toLocaleDateString() }}
                </td>

                <td class="py-3.5 px-4 text-right font-sans">
                  <a :href="'/p2p/orders/' + o.id" class="bg-[#2b3139] hover:bg-[#f0b90b] hover:text-[#1e2329] text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                    View Trade Room →
                  </a>
                </td>
              </tr>

              <tr v-if="orders.data.length === 0">
                <td colspan="8" class="py-12 text-center text-[#848e9c] font-sans">
                  No P2P trades found. Visit the <a href="/p2p" class="text-[#f0b90b] underline font-bold">P2P Marketplace</a> to start a trade.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';

defineProps({
  orders: { type: Object, required: true },
  user: { type: Object, required: true },
});

function formatStatus(st) {
  if (st === 'pending_payment') return 'Pending';
  if (st === 'paid') return 'Paid';
  if (st === 'disputed') return 'Disputed';
  return st;
}

function getStatusBadge(st) {
  switch (st) {
    case 'completed': return 'bg-[#0ecb81]/10 text-[#0ecb81] border border-[#0ecb81]/30';
    case 'paid': return 'bg-[#f0b90b]/10 text-[#f0b90b] border border-[#f0b90b]/30';
    case 'disputed': return 'bg-[#f6465d]/10 text-[#f6465d] border border-[#f6465d]/30';
    case 'cancelled': return 'bg-[#2b3139] text-[#848e9c]';
    default: return 'bg-[#1e88e5]/10 text-[#64b5f6] border border-[#1e88e5]/30';
  }
}
</script>
