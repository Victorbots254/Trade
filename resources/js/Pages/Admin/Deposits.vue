<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans p-6">
    <!-- Admin Header -->
    <div class="max-w-7xl mx-auto space-y-6">
      <div class="flex justify-between items-center border-b border-slate-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-slate-100 flex items-center space-x-2">
            <span class="text-amber-400">🛡️</span>
            <span>BEP-20 Manual Deposit Approval Dashboard</span>
          </h1>
          <p class="text-slate-400 text-xs mt-1">
            Review incoming BNB Smart Chain deposit transactions, verify BscScan TxHashes, and trigger double-entry ledger credits.
          </p>
        </div>

        <a href="/" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded text-xs font-semibold transition">
          ← Back to Trading Terminal
        </a>
      </div>

      <!-- Real-time Alert Toast Banner -->
      <div v-if="realtimeAlert" class="bg-amber-500/10 border border-amber-500/30 rounded-lg p-4 flex items-center justify-between animate-bounce">
        <div class="flex items-center space-x-3 text-xs text-amber-300">
          <span class="text-lg">🔔</span>
          <div>
            <strong>New Deposit Submitted!</strong> User {{ realtimeAlert.user_email }} requested deposit of {{ realtimeAlert.amount }} {{ realtimeAlert.currency }}.
          </div>
        </div>
        <button @click="realtimeAlert = null" class="text-amber-400 font-bold text-xs">Dismiss</button>
      </div>

      <!-- Deposits Table -->
      <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-xl text-xs">
        <div class="p-4 border-b border-slate-800 font-semibold text-slate-200 flex justify-between items-center">
          <span>Deposit Requests List</span>
          <span class="text-slate-500 font-mono text-[11px]">Custodial Wallet: {{ custodial_address }}</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-950 text-slate-400 border-b border-slate-800 font-mono text-[11px]">
                <th class="p-3">ID</th>
                <th class="p-3">User</th>
                <th class="p-3">Amount</th>
                <th class="p-3">TxHash (BscScan Quick Verifier)</th>
                <th class="p-3">Receipt</th>
                <th class="p-3">Status</th>
                <th class="p-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 font-mono">
              <tr v-for="d in deposits" :key="d.id" class="hover:bg-slate-800/40 transition">
                <td class="p-3 text-slate-400">#{{ d.id }}</td>
                <td class="p-3">
                  <div class="font-semibold text-slate-200">{{ d.user?.name || 'User #' + d.user_id }}</div>
                  <div class="text-[10px] text-slate-500 font-mono">{{ d.user?.email }}</div>
                </td>
                <td class="p-3 font-bold text-emerald-400">
                  {{ d.amount }} {{ d.currency }}
                </td>
                <td class="p-3">
                  <div class="flex items-center space-x-2">
                    <span class="truncate max-w-[180px] text-slate-300">{{ d.tx_hash }}</span>
                    <a :href="d.bscscan_url" target="_blank"
                       class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 px-2 py-0.5 rounded text-[10px] transition flex items-center space-x-1">
                      <span>BscScan</span>
                      <span>↗</span>
                    </a>
                  </div>
                </td>
                <td class="p-3">
                  <a v-if="d.receipt_path" :href="'/storage/' + d.receipt_path" target="_blank" class="text-emerald-400 underline text-[11px]">
                    View Image
                  </a>
                  <span v-else class="text-slate-600">None</span>
                </td>
                <td class="p-3">
                  <span :class="{
                    'bg-amber-500/10 text-amber-400 border-amber-500/30': d.status === 'pending',
                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': d.status === 'approved',
                    'bg-rose-500/10 text-rose-400 border-rose-500/30': d.status === 'rejected',
                  }" class="border px-2 py-0.5 rounded text-[10px] font-bold uppercase">
                    {{ d.status }}
                  </span>
                </td>
                <td class="p-3 text-right">
                  <div v-if="d.status === 'pending'" class="flex items-center justify-end space-x-2">
                    <button @click="approveDeposit(d.id)"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded font-bold transition shadow-sm">
                      Approve & Credit
                    </button>
                    <button @click="openRejectModal(d)"
                            class="bg-rose-600/20 hover:bg-rose-600/30 text-rose-400 border border-rose-600/40 px-3 py-1 rounded font-bold transition">
                      Reject
                    </button>
                  </div>
                  <div v-else class="text-slate-500 text-[10px]">
                    Processed by #{{ d.approved_by || 'System' }}
                  </div>
                </td>
              </tr>

              <tr v-if="deposits.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-500">
                  No deposit requests found in system.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Rejection Reason Modal -->
    <div v-if="rejectingDeposit" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-xl w-full max-w-md p-5 space-y-4 shadow-2xl text-xs">
        <h3 class="font-bold text-slate-100 text-sm">Reject Deposit #{{ rejectingDeposit.id }}</h3>
        <p class="text-slate-400">Please provide a mandatory reason for rejecting this deposit:</p>

        <textarea v-model="rejectReason" rows="3" placeholder="e.g. Invalid TxHash or transaction failed on BscScan."
                  class="w-full bg-slate-950 border border-slate-800 rounded p-2.5 text-slate-100 focus:outline-none focus:border-rose-500"></textarea>

        <div class="flex space-x-2">
          <button @click="confirmReject" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold py-2 rounded transition">
            Confirm Rejection
          </button>
          <button @click="rejectingDeposit = null" class="bg-slate-800 text-slate-300 py-2 px-4 rounded">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  deposits: Array,
  custodial_address: String,
});

const realtimeAlert = ref(null);
const rejectingDeposit = ref(null);
const rejectReason = ref('');

function approveDeposit(id) {
  if (!confirm('Confirm approving deposit #' + id + '? Wallet balance will be credited atomically.')) return;
  router.post(`/admin/deposits/${id}/approve`);
}

function openRejectModal(deposit) {
  rejectingDeposit.value = deposit;
  rejectReason.value = '';
}

function confirmReject() {
  if (!rejectReason.value || rejectReason.value.length < 5) {
    alert('Please enter a valid rejection reason.');
    return;
  }
  router.post(`/admin/deposits/${rejectingDeposit.value.id}/reject`, {
    reason: rejectReason.value,
  });
  rejectingDeposit.value = null;
}

onMounted(() => {
  if (window.Echo) {
    window.Echo.private('admin.deposits')
      .listen('NewDepositPendingApproval', (e) => {
        realtimeAlert.value = e;
        router.reload({ only: ['deposits'] });
      });
  }
});
</script>
