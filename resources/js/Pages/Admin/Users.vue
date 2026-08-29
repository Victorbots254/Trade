<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="$page.props.auth.user"
    />

    <!-- Main Admin Workspace -->
    <div class="flex-1 max-w-7xl w-full mx-auto p-6 space-y-6">
      <div class="flex justify-between items-center border-b border-slate-800 pb-4">
        <div>
          <h1 class="text-xl font-bold text-slate-100 flex items-center space-x-2">
            <span>👥 Administrator Control Center</span>
          </h1>
          <p class="text-xs text-slate-400">Manage registered trader balances, outcome controls, and deposit approvals.</p>
        </div>

        <div class="flex items-center space-x-2 text-xs">
          <a href="/admin/users" class="bg-slate-800 text-slate-100 font-bold px-3.5 py-2 rounded-lg border border-slate-700">
            👥 Trader Roster & Controls
          </a>
          <a href="/admin/deposits" class="bg-slate-900 text-slate-400 hover:text-slate-200 px-3.5 py-2 rounded-lg border border-slate-800">
            💰 Deposit Approvals
          </a>
        </div>
      </div>

      <!-- MASTER GLOBAL TRADING ENGINE CONTROLS -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-2">
          <div>
            <h2 class="text-base font-bold text-slate-100 flex items-center space-x-2">
              <span>🎛️ Master Global Trading Outcome Engine</span>
            </h2>
            <p class="text-xs text-slate-400">Apply global execution overrides to all active traders on the platform.</p>
          </div>

          <!-- Master Action Buttons -->
          <div class="flex items-center space-x-2">
            <button @click="bulkSetMode('fair_market')" :disabled="processing"
                    class="bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow">
              <svg v-if="processingMode === 'bulk_fair_market'" class="animate-spin h-3.5 w-3.5 text-cyan-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>🟢 All Traders: Fair Market</span>
            </button>

            <button @click="bulkSetMode('force_win')" :disabled="processing"
                    class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow">
              <svg v-if="processingMode === 'bulk_force_win'" class="animate-spin h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>📈 Everyone to Earn (WIN)</span>
            </button>

            <button @click="bulkSetMode('force_loss')" :disabled="processing"
                    class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow">
              <svg v-if="processingMode === 'bulk_force_loss'" class="animate-spin h-3.5 w-3.5 text-rose-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>📉 Everyone to Loss (LOSS)</span>
            </button>
          </div>
        </div>
      </div>

      <!-- REGISTERED TRADERS & INDIVIDUAL CONTROLS TABLE -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-4 text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="font-bold text-slate-100 text-sm">Registered Traders Account Roster ({{ users.length }})</h3>
          <span class="text-slate-500 font-mono">Real Balance Reflects Exact User Funds</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800 pb-2">
                <th class="pb-2">ID</th>
                <th class="pb-2">Trader Name</th>
                <th class="pb-2">Email Address</th>
                <th class="pb-2">USDT Funds</th>
                <th class="pb-2">Locked Funds</th>
                <th class="pb-2">Current Mode</th>
                <th class="pb-2 text-right">Individual Control Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="u in users" :key="u.id" class="hover:bg-slate-800/30 transition">
                <td class="py-3 font-bold text-slate-400">#{{ u.id }}</td>
                <td class="py-3 font-bold text-slate-100">
                  <span>{{ u.name }}</span>
                  <span v-if="u.is_admin" class="ml-1 text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/30 px-1.5 py-0.5 rounded">ADMIN</span>
                </td>
                <td class="py-3 text-slate-300">{{ u.email }}</td>
                <td class="py-3 text-emerald-400 font-bold">${{ formatPrice(u.usdt_balance) }}</td>
                <td class="py-3 text-slate-400">${{ formatPrice(u.locked_balance) }}</td>
                <td class="py-3">
                  <span v-if="u.trading_outcome_mode === 'force_win'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold">
                    📈 FORCE WIN (EARN)
                  </span>
                  <span v-else-if="u.trading_outcome_mode === 'force_loss'" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold">
                    📉 FORCE LOSS
                  </span>
                  <span v-else class="bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 px-2 py-0.5 rounded font-bold">
                    🟢 FAIR MARKET
                  </span>
                </td>

                <td class="py-3 text-right">
                  <div class="inline-flex space-x-1">
                    <button @click="setUserMode(u.id, 'fair_market')" :disabled="processing"
                            :class="u.trading_outcome_mode === 'fair_market' ? 'bg-cyan-600 text-white font-bold' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border-slate-800'"
                            class="px-2 py-1 rounded border text-[10px] transition">
                      Fair Market
                    </button>
                    <button @click="setUserMode(u.id, 'force_win')" :disabled="processing"
                            :class="u.trading_outcome_mode === 'force_win' ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border-slate-800'"
                            class="px-2 py-1 rounded border text-[10px] transition">
                      Force Win
                    </button>
                    <button @click="setUserMode(u.id, 'force_loss')" :disabled="processing"
                            :class="u.trading_outcome_mode === 'force_loss' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border-slate-800'"
                            class="px-2 py-1 rounded border text-[10px] transition">
                      Force Loss
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
  users: Array,
});

const toastRef = ref(null);
const processing = ref(false);
const processingMode = ref(null);

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function setUserMode(userId, mode) {
  processing.value = true;
  router.post(`/admin/users/${userId}/mode`, { trading_outcome_mode: mode }, {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show(`Updated user trading outcome mode to ${mode.toUpperCase()}`, 'success');
      processing.value = false;
    },
    onError: () => {
      toastRef.value?.show('Failed to update user trading outcome mode', 'error');
      processing.value = false;
    }
  });
}

function bulkSetMode(mode) {
  if (!confirm(`Are you sure you want to set ALL traders to ${mode.toUpperCase()}?`)) return;

  processing.value = true;
  processingMode.value = `bulk_${mode}`;

  router.post('/admin/users/bulk-mode', { trading_outcome_mode: mode }, {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show(`Bulk updated all traders to ${mode.toUpperCase()}`, 'success');
      processing.value = false;
      processingMode.value = null;
    },
    onError: () => {
      toastRef.value?.show('Failed to bulk update traders', 'error');
      processing.value = false;
      processingMode.value = null;
    }
  });
}
</script>
