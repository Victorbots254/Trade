<template>
  <div class="min-h-screen bg-slate-950 text-slate-300 font-sans">
    <Head title="Monthly Interests" />

    <!-- Navigation Header -->
    <header class="bg-slate-900 border-b border-slate-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
        <div class="flex items-center space-x-4">
          <Link href="/trade/BTCUSDT" class="text-emerald-400 font-bold text-xl hover:text-emerald-300 transition">
            TRADING<span class="text-white">CORE</span>
          </Link>
          <span class="text-slate-600">|</span>
          <h1 class="text-lg font-semibold text-slate-100">Monthly Interests</h1>
        </div>
        <div class="flex items-center space-x-4 text-sm">
          <Link href="/profile" class="text-slate-400 hover:text-slate-200 transition">Back to Profile</Link>
        </div>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
      <div v-if="$page.props.flash.message" class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-lg">
        {{ $page.props.flash.message }}
      </div>
      <div v-if="$page.props.errors.amount" class="mb-6 bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-lg">
        {{ $page.props.errors.amount }}
      </div>

      <!-- Dashboard Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg">
          <div class="text-slate-400 text-sm mb-2">Total Locked Funds</div>
          <div class="text-3xl font-bold text-slate-100">${{ formatPrice(totalLocked) }} <span class="text-sm text-slate-500 font-normal">USDT</span></div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg relative overflow-hidden">
          <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl"></div>
          <div class="text-slate-400 text-sm mb-2">Target Yield (APY)</div>
          <div class="text-3xl font-bold text-emerald-400">5.0% <span class="text-sm text-slate-500 font-normal">/ month</span></div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg">
          <div class="text-slate-400 text-sm mb-2">Total Interest Earned</div>
          <div class="text-3xl font-bold text-slate-100">${{ formatPrice(totalEarned) }} <span class="text-sm text-slate-500 font-normal">USDT</span></div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Transfer Form -->
        <div class="lg:col-span-1">
          <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg">
            <h3 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3 mb-4">Lock Funds</h3>
            
            <form @submit.prevent="submitLock">
              <div class="mb-4">
                <label class="block text-slate-400 text-sm mb-2">Amount to Lock (USDT)</label>
                <input v-model="form.amount" type="number" step="0.01" min="10" placeholder="Minimum $10.00" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-200 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
              </div>

              <div class="mb-6">
                <label class="block text-slate-400 text-sm mb-2">Lock Duration</label>
                <div class="grid grid-cols-1 gap-2">
                  <button type="button" class="bg-emerald-600/20 border border-emerald-500 text-emerald-400 py-2 rounded-lg font-bold">30 Days</button>
                </div>
              </div>

              <div class="bg-slate-950 rounded-lg p-4 mb-6 border border-slate-800 text-sm text-slate-400">
                <div class="flex justify-between mb-2">
                  <span>Est. Monthly Interest:</span>
                  <span class="text-emerald-400 font-bold">+${{ estInterest }} USDT</span>
                </div>
                <div class="flex justify-between">
                  <span>Maturity Return:</span>
                  <span class="text-slate-200 font-bold">${{ estTotal }} USDT</span>
                </div>
              </div>

              <button type="submit" :disabled="form.processing || !form.amount || form.amount < 10" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition shadow-lg flex justify-center items-center">
                <span v-if="form.processing">Processing...</span>
                <span v-else>Confirm & Lock Funds</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Active Locks Table -->
        <div class="lg:col-span-2">
          <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg overflow-hidden flex flex-col h-full">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/50">
              <h3 class="text-lg font-bold text-slate-100">Your Active Locks</h3>
            </div>
            <div class="p-0 overflow-auto flex-1">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/50 text-slate-400 font-semibold border-b border-slate-800">
                  <tr>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Locked Date</th>
                    <th class="px-6 py-3">Unlocks At</th>
                    <th class="px-6 py-3">Est. Interest</th>
                    <th class="px-6 py-3">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                  <tr v-for="sub in subscriptions" :key="sub.id" class="hover:bg-slate-800/20 transition">
                    <td class="px-6 py-4 font-bold text-slate-200">${{ formatPrice(sub.amount) }}</td>
                    <td class="px-6 py-4 text-slate-400">{{ formatDate(sub.locked_at) }}</td>
                    <td class="px-6 py-4 text-slate-300 font-mono">{{ formatDate(sub.unlocks_at) }}</td>
                    <td class="px-6 py-4 text-emerald-400 font-bold">+${{ formatPrice(sub.expected_interest) }}</td>
                    <td class="px-6 py-4">
                      <span v-if="sub.status === `locked`" class="bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-full text-xs border border-amber-500/20 font-bold">LOCKED</span>
                      <span v-else class="bg-emerald-500/10 text-emerald-400 px-2.5 py-1 rounded-full text-xs border border-emerald-500/20 font-bold uppercase">{{ sub.status }}</span>
                    </td>
                  </tr>
                  <tr v-if="!subscriptions.length">
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                      No active funds locked. Start earning by locking some USDT.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
  subscriptions: Array,
  logs: Array,
  user: Object,
});

const form = useForm({
  amount: "",
  duration_days: 30,
});

const totalLocked = computed(() => {
  return props.subscriptions
    .filter(s => s.status === "locked")
    .reduce((acc, s) => acc + s.amount, 0);
});

const totalEarned = computed(() => {
  return props.logs.reduce((acc, log) => acc + log.amount, 0);
});

const estInterest = computed(() => {
  const amt = parseFloat(form.amount) || 0;
  return (amt * 0.05).toFixed(2);
});

const estTotal = computed(() => {
  const amt = parseFloat(form.amount) || 0;
  return (amt + amt * 0.05).toFixed(2);
});

function submitLock() {
  form.post("/monthly-interests/lock", {
    onSuccess: () => form.reset(),
  });
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}
</script>
