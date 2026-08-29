<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="user"
      :wallets="wallets"
    />

    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-800 pb-4 gap-3">
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-100 flex items-center space-x-2">
            <span>Withdraw Funds</span>
          </h1>
          <p class="text-xs md:text-sm text-slate-400 mt-1">Withdraw USDT to your linked Binance BEP20 wallet address.</p>
        </div>
        <Link href="/payments" 
           class="bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-800 px-4 py-2 rounded-xl transition text-xs font-bold flex items-center space-x-1.5 self-start md:self-auto shadow-md">
          <span>Manage BEP20 Address</span>
        </Link>
      </div>

      <div v-if="!user.bep20_address" class="bg-rose-500/10 border border-rose-500/50 p-4 rounded-xl mb-4 text-rose-400 text-sm flex justify-between items-center">
        <span>You must link a Binance BEP20 address before you can withdraw.</span>
        <Link href="/payments" class="bg-rose-600 text-white font-bold px-4 py-2 rounded-lg hover:bg-rose-500 transition text-xs">Link Address</Link>
      </div>

      <div v-if="$page.props.flash?.message" class="mb-4 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-lg text-sm">
        {{ $page.props.flash.message }}
      </div>
      <div v-if="$page.props.errors?.amount" class="mb-4 bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-lg text-sm">
        {{ $page.props.errors.amount }}
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row gap-8">
        <div class="flex-1">
          <form @submit.prevent="submitWithdraw" class="space-y-4">
            <div>
              <label class="block text-slate-400 mb-1 font-medium text-sm">Amount to Withdraw (USDT)</label>
              <input v-model="form.amount" type="number" step="0.01" min="10" required placeholder="Min 10.00" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500" />
            </div>

            <div class="bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm">
              <label class="block text-slate-500 mb-1 font-medium text-xs">Destination Address</label>
              <div class="text-slate-300 font-mono break-all">{{ user.bep20_address || "Not linked yet" }}</div>
            </div>

            <button type="submit" :disabled="form.processing || !user.bep20_address" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3 rounded-lg transition uppercase tracking-wider text-xs shadow-lg">
              <span v-if="form.processing">Processing...</span>
              <span v-else>Request Withdrawal</span>
            </button>
          </form>
        </div>
      </div>

      <!-- Live Withdrawals Requests History Table -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h2 class="font-bold text-slate-200 text-sm">Withdrawal History</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800 text-[11px]">
                <th class="pb-2">ID</th>
                <th class="pb-2">Amount</th>
                <th class="pb-2">Address</th>
                <th class="pb-2">Status</th>
                <th class="pb-2 text-right">Date Submitted</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="w in withdrawals" :key="w.id" class="hover:bg-slate-800/30 transition">
                <td class="py-2.5 font-bold text-slate-400">#{{ w.id }}</td>
                <td class="py-2.5 font-bold text-emerald-400">${{ Number(w.amount).toFixed(2) }} USDT</td>
                <td class="py-2.5 text-slate-400 truncate max-w-[120px]">{{ w.bep20_address }}</td>
                <td class="py-2.5">
                  <span v-if="w.status === `approved`" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    APPROVED
                  </span>
                  <span v-else-if="w.status === `rejected`" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    REJECTED
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px] animate-pulse">
                    PENDING
                  </span>
                </td>
                <td class="py-2.5 text-right text-slate-500 text-[11px]">{{ new Date(w.created_at).toLocaleString() }}</td>
              </tr>
              <tr v-if="withdrawals.length === 0">
                <td colspan="5" class="py-6 text-center text-slate-500">No withdrawal requests found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import TradingHeader from "@/Components/TradingHeader.vue";

const props = defineProps({
  user: Object,
  wallets: Array,
  withdrawals: Array,
});

const form = useForm({
  amount: "",
});

function submitWithdraw() {
  form.post("/withdraw", {
    onSuccess: () => form.reset(),
  });
}
</script>
