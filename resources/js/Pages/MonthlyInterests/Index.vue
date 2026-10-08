<template>
  <div class="min-h-screen bg-slate-950 text-slate-300 font-sans">
    <Head title="USDT MMF (18%)" />

    <!-- Standard Trading Navigation Header -->
    <TradingHeader :user="user" :wallets="wallet ? [wallet] : []" />

    <main class="max-w-6xl mx-auto px-4 py-8">
      <!-- Flash / Success / Error Messages -->
      <div v-if="$page.props.flash.message" class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span>✓</span>
          <span>{{ $page.props.flash.message }}</span>
        </div>
        <button @click="$page.props.flash.message = null" class="text-emerald-400/70 hover:text-emerald-300 text-sm">✕</button>
      </div>

      <div v-if="successDepositNotice" class="mb-6 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 p-4 rounded-xl flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-lg">🎉</span>
          <span>{{ successDepositNotice }}</span>
        </div>
        <button @click="successDepositNotice = ''" class="text-emerald-400/70 hover:text-emerald-300 text-sm">✕</button>
      </div>

      <div v-if="$page.props.errors.amount" class="mb-6 bg-rose-500/10 border border-rose-500/50 text-rose-400 p-4 rounded-xl">
        {{ $page.props.errors.amount }}
      </div>

      <!-- Dashboard Cards (4 columns) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Card 1: Available Wallet Balance + Direct Add Funds -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg relative overflow-hidden flex flex-col justify-between">
          <div class="absolute -right-3 -top-3 w-16 h-16 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <span class="text-slate-400 text-xs font-medium">Available USDT</span>
              <span class="text-[10px] bg-slate-800 text-slate-400 border border-slate-700 px-1.5 py-0.5 rounded font-mono">Live</span>
            </div>
            <div class="text-2xl lg:text-3xl font-bold text-slate-100">
              ${{ formatPrice(availableBalance) }}
              <span class="text-xs text-slate-500 font-normal">USDT</span>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between">
            <button @click="showDepositModal = true"
                    type="button"
                    class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-3 py-1.5 rounded-lg transition shadow-md shadow-emerald-950 flex items-center space-x-1.5">
              <span>+</span>
              <span>Add Funds</span>
            </button>
            <span class="text-[11px] text-slate-500 font-mono">Instant STK / Crypto</span>
          </div>
        </div>

        <!-- Card 2: Total Locked Funds -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg flex flex-col justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium mb-1.5">Total Locked Funds</div>
            <div class="text-2xl lg:text-3xl font-bold text-slate-100">
              ${{ formatPrice(totalLocked) }}
              <span class="text-xs text-slate-500 font-normal">USDT</span>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-slate-800/80 text-[11px] text-slate-500">
            {{ activeLocksCount }} active investment{{ activeLocksCount === 1 ? '' : 's' }}
          </div>
        </div>

        <!-- Card 3: Target Yield (APY) -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg relative overflow-hidden flex flex-col justify-between">
          <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-500/15 rounded-full blur-xl pointer-events-none"></div>
          <div>
            <div class="flex justify-between items-center mb-1.5">
              <span class="text-slate-400 text-xs font-medium">Target Yield</span>
              <span class="text-[10px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-1.5 py-0.5 rounded font-bold">GUARANTEED</span>
            </div>
            <div class="text-2xl lg:text-3xl font-bold text-emerald-400">
              18.0%
              <span class="text-xs text-slate-500 font-normal">/ month</span>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-slate-800/80 text-[11px] text-emerald-400/80 font-medium">
            30-day fixed maturity cycle
          </div>
        </div>

        <!-- Card 4: Total Interest Earned -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-lg flex flex-col justify-between">
          <div>
            <div class="text-slate-400 text-xs font-medium mb-1.5">Total Interest Earned</div>
            <div class="text-2xl lg:text-3xl font-bold text-slate-100">
              ${{ formatPrice(totalEarned) }}
              <span class="text-xs text-slate-500 font-normal">USDT</span>
            </div>
          </div>
          <div class="mt-4 pt-3 border-t border-slate-800/80 text-[11px] text-emerald-400 font-mono">
            Automated payouts
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Interactive Actions Panel (Lock vs Add Funds) -->
        <div class="lg:col-span-1">
          <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl">
            <!-- Mode Selector Tabs -->
            <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-950 border border-slate-800 rounded-lg mb-5 text-xs font-bold">
              <button @click="activePanelTab = 'lock'"
                      type="button"
                      :class="activePanelTab === 'lock' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                      class="py-2 px-3 rounded-md transition text-center flex items-center justify-center space-x-1.5">
                <span>🔒</span>
                <span>Lock Funds</span>
              </button>
              <button @click="activePanelTab = 'add'"
                      type="button"
                      :class="activePanelTab === 'add' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                      class="py-2 px-3 rounded-md transition text-center flex items-center justify-center space-x-1.5">
                <span>⚡</span>
                <span>Add Funds</span>
              </button>
            </div>

            <!-- TAB 1: LOCK FUNDS -->
            <div v-if="activePanelTab === 'lock'">
              <div class="flex justify-between items-center mb-3">
                <span class="text-xs text-slate-400">Available to Lock:</span>
                <div class="flex items-center space-x-2">
                  <span class="font-mono text-xs font-bold text-emerald-400">${{ formatPrice(availableBalance) }} USDT</span>
                  <button @click="activePanelTab = 'add'" type="button" class="text-[10px] text-emerald-400 hover:underline font-bold">
                    + Add
                  </button>
                </div>
              </div>

              <!-- Quick Presets -->
              <div class="grid grid-cols-4 gap-1.5 mb-4 text-xs font-mono">
                <button type="button" @click="setAmount(10)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1.5 rounded transition text-center">
                  $10
                </button>
                <button type="button" @click="setAmount(25)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1.5 rounded transition text-center">
                  $25
                </button>
                <button type="button" @click="setAmount(50)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1.5 rounded transition text-center">
                  $50
                </button>
                <button type="button" @click="setMaxAmount" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-emerald-400 py-1.5 rounded transition text-center font-bold">
                  Max
                </button>
              </div>

              <!-- Insufficient Balance Callout -->
              <div v-if="availableBalance < 10" class="mb-4 bg-amber-500/10 border border-amber-500/30 rounded-lg p-3 text-xs text-amber-300 space-y-2">
                <div class="flex items-start space-x-2">
                  <span>💡</span>
                  <div>
                    <div class="font-bold text-amber-200">Balance below $10.00 minimum</div>
                    <div class="text-[11px] text-amber-300/80 mt-0.5">You can add funds directly using instant M-Pesa STK Push or Crypto.</div>
                  </div>
                </div>
                <button @click="activePanelTab = 'add'" type="button" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-1.5 px-3 rounded text-xs transition flex items-center justify-center space-x-1.5">
                  <span>⚡</span>
                  <span>Add Funds Right Here →</span>
                </button>
              </div>

              <form @submit.prevent="submitLock">
                <div class="mb-4">
                  <label class="block text-slate-400 text-xs mb-1.5 font-medium">Amount to Lock (USDT)</label>
                  <div class="relative">
                    <input v-model="form.amount"
                           type="number"
                           step="0.01"
                           min="10"
                           :max="availableBalance"
                           placeholder="Minimum $10.00"
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 font-mono text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition pr-16">
                    <button type="button" @click="setMaxAmount" class="absolute right-2 top-2 text-[11px] bg-slate-800 hover:bg-slate-700 text-emerald-400 font-bold px-2 py-1 rounded">
                      MAX
                    </button>
                  </div>
                </div>

                <div class="mb-5">
                  <label class="block text-slate-400 text-xs mb-1.5 font-medium">Lock Duration</label>
                  <div class="grid grid-cols-1">
                    <div class="bg-emerald-600/10 border border-emerald-500/40 text-emerald-400 py-2.5 px-3 rounded-lg font-bold text-xs flex justify-between items-center">
                      <span>30 Days (Fixed Term)</span>
                      <span class="text-xs font-mono font-bold bg-emerald-500/20 px-2 py-0.5 rounded">18.0% APY</span>
                    </div>
                  </div>
                </div>

                <!-- Yield Calculator Preview -->
                <div class="bg-slate-950 rounded-lg p-3.5 mb-5 border border-slate-800 text-xs text-slate-400 space-y-2">
                  <div class="flex justify-between">
                    <span>Est. 30-Day Interest:</span>
                    <span class="text-emerald-400 font-bold font-mono">+${{ estInterest }} USDT</span>
                  </div>
                  <div class="flex justify-between pt-1 border-t border-slate-800/80">
                    <span class="font-medium text-slate-300">Total Return at Maturity:</span>
                    <span class="text-slate-100 font-bold font-mono">${{ estTotal }} USDT</span>
                  </div>
                </div>

                <button type="submit"
                        :disabled="form.processing || !form.amount || form.amount < 10 || form.amount > availableBalance"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg transition shadow-lg flex justify-center items-center text-xs">
                  <span v-if="form.processing">Locking Funds...</span>
                  <span v-else>Confirm & Lock Funds</span>
                </button>
              </form>
            </div>

            <!-- TAB 2: INSTANT ADD FUNDS (DIRECT M-PESA & CRYPTO) -->
            <div v-else class="space-y-4">
              <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center space-x-2">
                  <span class="text-lg">📱</span>
                  <div>
                    <h4 class="font-bold text-slate-100 text-xs">Instant M-Pesa STK Push</h4>
                    <p class="text-[10px] text-slate-400">Automatic wallet credit upon PIN approval</p>
                  </div>
                </div>
                <div class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-1 rounded font-bold">
                  1 USDT = {{ exchangeRate }} KES
                </div>
              </div>

              <!-- Inline STK Push Active Prompt -->
              <div v-if="inlineMpesaActivePrompt" class="bg-slate-950 border border-emerald-500/40 rounded-xl p-5 text-center space-y-3">
                <div class="text-2xl animate-pulse">📲</div>
                <h5 class="font-bold text-slate-100 text-xs">STK Push Sent to Your Phone!</h5>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                  Please enter your M-Pesa PIN on <strong class="text-emerald-400 font-mono">{{ inlineMpesaActivePrompt.phone }}</strong> for KES {{ inlineMpesaActivePrompt.kes_amount }}.
                </p>
                <div class="text-[11px] text-emerald-400 font-mono flex items-center justify-center space-x-2 py-1">
                  <svg class="animate-spin h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Waiting for PIN confirmation...</span>
                </div>
                <button @click="cancelInlineMpesa" class="text-slate-500 hover:text-slate-300 text-[10px] underline">
                  Cancel / Re-enter Number
                </button>
              </div>

              <!-- Inline M-Pesa Form -->
              <form v-else @submit.prevent="submitInlineMpesa" class="space-y-3.5">
                <div v-if="inlineMpesaError" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-2.5 rounded-lg text-xs">
                  {{ inlineMpesaError }}
                </div>

                <div>
                  <div class="flex justify-between items-center mb-1">
                    <label class="block text-slate-400 text-xs font-medium">M-Pesa Phone Number</label>
                    <span v-if="inlinePhone" class="text-[10px] font-mono text-emerald-400 font-bold bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.5 rounded">
                      Sent as: {{ formattedInlinePhone }}
                    </span>
                  </div>
                  <input v-model="inlinePhone"
                         type="text"
                         required
                         placeholder="0712345678 or 254712345678"
                         class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-100 font-mono text-xs focus:outline-none focus:border-emerald-500" />
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-slate-400 text-[11px] mb-1 font-medium">Amount (KES)</label>
                    <input v-model="inlineKesAmount"
                           type="number"
                           :min="minKesAmount"
                           required
                           :placeholder="minKesAmount.toString()"
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 font-mono text-slate-100 text-xs font-bold focus:outline-none focus:border-emerald-500" />
                  </div>
                  <div>
                    <label class="block text-slate-400 text-[11px] mb-1 font-medium">Credits (USDT)</label>
                    <input :value="calculatedUsdtAmount"
                           readonly
                           class="w-full bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2 font-mono text-emerald-400 text-xs font-bold focus:outline-none cursor-default" />
                  </div>
                </div>

                <!-- Amount Shortcut Presets -->
                <div class="grid grid-cols-4 gap-1 text-[11px] font-mono">
                  <button type="button" @click="setInlineKes(650)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1 rounded text-center">
                    650 (≈$5)
                  </button>
                  <button type="button" @click="setInlineKes(1300)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1 rounded text-center">
                    1.3K (≈$10)
                  </button>
                  <button type="button" @click="setInlineKes(3250)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1 rounded text-center">
                    3.25K (≈$25)
                  </button>
                  <button type="button" @click="setInlineKes(6500)" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 py-1 rounded text-center">
                    6.5K (≈$50)
                  </button>
                </div>

                <button type="submit"
                        :disabled="inlineMpesaLoading"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-lg transition shadow-lg text-xs disabled:opacity-50 flex items-center justify-center space-x-2">
                  <svg v-if="inlineMpesaLoading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span v-if="inlineMpesaLoading">Sending STK Push...</span>
                  <span v-else>Deposit via M-Pesa STK Push →</span>
                </button>

                <!-- Alternate Option: Crypto Modal -->
                <div class="pt-2 text-center">
                  <button type="button" @click="showDepositModal = true" class="text-[11px] text-amber-400 hover:text-amber-300 underline font-medium">
                    🟡 Or deposit via BEP-20 Crypto Address
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Active Locks Table -->
        <div class="lg:col-span-2">
          <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-lg overflow-hidden flex flex-col h-full">
            <div class="px-6 py-4 border-b border-slate-800 bg-slate-900/50 flex justify-between items-center">
              <h3 class="text-lg font-bold text-slate-100 flex items-center space-x-2">
                <span>Your Active Locks</span>
                <span class="text-xs bg-slate-800 text-slate-400 font-mono px-2 py-0.5 rounded-full font-normal">
                  {{ subscriptions.length }}
                </span>
              </h3>
              <button @click="showDepositModal = true" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold flex items-center space-x-1">
                <span>+</span>
                <span>Add More Funds</span>
              </button>
            </div>
            <div class="p-0 overflow-auto flex-1">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/50 text-slate-400 font-semibold border-b border-slate-800 text-xs">
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
                    <td class="px-6 py-4 text-slate-400 text-xs">{{ formatDate(sub.locked_at) }}</td>
                    <td class="px-6 py-4 text-slate-300 font-mono text-xs">{{ formatDate(sub.unlocks_at) }}</td>
                    <td class="px-6 py-4 text-emerald-400 font-bold font-mono">+${{ formatPrice(sub.expected_interest) }}</td>
                    <td class="px-6 py-4">
                      <span v-if="sub.status === 'locked'" class="bg-amber-500/10 text-amber-400 px-2.5 py-1 rounded-full text-xs border border-amber-500/20 font-bold">LOCKED</span>
                      <span v-else class="bg-emerald-500/10 text-emerald-400 px-2.5 py-1 rounded-full text-xs border border-emerald-500/20 font-bold uppercase">{{ sub.status }}</span>
                    </td>
                  </tr>
                  <tr v-if="!subscriptions.length">
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                      <div class="space-y-2">
                        <div class="text-2xl">🌱</div>
                        <div>No active funds locked yet.</div>
                        <div class="text-xs text-slate-400">Lock USDT to earn 18% guaranteed monthly yield.</div>
                        <div class="pt-2">
                          <button @click="activePanelTab = 'add'" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                            + Add Funds & Start Earning
                          </button>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Global Deposit Modal -->
    <DepositModal
      v-if="showDepositModal"
      :custodialAddress="custodialAddress"
      @close="showDepositModal = false"
      @deposit-submitted="handleModalDepositSubmitted"
      @deposit-approved="handleModalDepositApproved"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import TradingHeader from "@/Components/TradingHeader.vue";
import DepositModal from "@/Components/DepositModal.vue";

const props = defineProps({
  subscriptions: { type: Array, default: () => [] },
  logs: { type: Array, default: () => [] },
  user: { type: Object, default: () => ({}) },
  wallet: { type: Object, default: () => ({ available_balance: 0 }) },
  custodialAddress: { type: String, default: "0x71C7656EC7ab88b098defB751B7401B5f6d8976F" },
});

// Navigation / Tabs
const activePanelTab = ref("lock"); // 'lock' | 'add'
const showDepositModal = ref(false);
const successDepositNotice = ref("");

// Balance state (reactive, updates without full page reloads)
const liveBalance = ref(Number(props.wallet?.available_balance || 0));

const availableBalance = computed(() => {
  return liveBalance.value;
});

// Lock Funds Form
const form = useForm({
  amount: "",
  duration_days: 30,
});

// Inline M-Pesa State
const exchangeRate = ref(130);
const inlinePhone = ref("");
const inlineKesAmount = ref("1300"); // default ~10 USDT
const inlineMpesaLoading = ref(false);
const inlineMpesaError = ref("");
const inlineMpesaActivePrompt = ref(null);
let inlineMpesaPollTimer = null;

const minKesAmount = computed(() => {
  return Math.ceil(5 * exchangeRate.value);
});

const calculatedUsdtAmount = computed(() => {
  const kes = parseFloat(inlineKesAmount.value) || 0;
  return (kes / exchangeRate.value).toFixed(2);
});

const formattedInlinePhone = computed(() => {
  let p = (inlinePhone.value || "").replace(/[^0-9]/g, "");
  if (p.startsWith("2540")) p = "254" + p.slice(4);
  else if (p.startsWith("0")) p = "254" + p.slice(1);
  else if (p.startsWith("7") || p.startsWith("1")) p = "254" + p;
  return p;
});

// Dashboard Computations
const totalLocked = computed(() => {
  return props.subscriptions
    .filter(s => s.status === "locked")
    .reduce((acc, s) => acc + Number(s.amount || 0), 0);
});

const activeLocksCount = computed(() => {
  return props.subscriptions.filter(s => s.status === "locked").length;
});

const totalEarned = computed(() => {
  return props.logs.reduce((acc, log) => acc + Number(log.amount || 0), 0);
});

const estInterest = computed(() => {
  const amt = parseFloat(form.amount) || 0;
  return (amt * 0.18).toFixed(2);
});

const estTotal = computed(() => {
  const amt = parseFloat(form.amount) || 0;
  return (amt + amt * 0.18).toFixed(2);
});

// Form Helpers
function setAmount(val) {
  form.amount = val;
}

function setMaxAmount() {
  if (availableBalance.value >= 10) {
    form.amount = Math.floor(availableBalance.value * 100) / 100;
  }
}

function setInlineKes(amount) {
  inlineKesAmount.value = amount.toString();
}

// Lock Submission
function submitLock() {
  form.post("/monthly-interests/lock", {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      refreshWalletBalance();
    },
  });
}

// Inline M-Pesa STK Push
async function submitInlineMpesa() {
  inlineMpesaLoading.value = true;
  inlineMpesaError.value = "";

  try {
    const res = await axios.post("/api/mpesa/initiate", {
      phone: inlinePhone.value,
      amount: inlineKesAmount.value,
      amount_type: "kes",
    });

    inlineMpesaActivePrompt.value = res.data;
    pollInlineMpesa(res.data.deposit_id);
  } catch (err) {
    inlineMpesaError.value = err.response?.data?.message || "Failed to initiate M-Pesa STK push. Please check your phone number.";
  } finally {
    inlineMpesaLoading.value = false;
  }
}

function pollInlineMpesa(depositId) {
  if (inlineMpesaPollTimer) clearInterval(inlineMpesaPollTimer);
  let pollCount = 0;

  inlineMpesaPollTimer = setInterval(async () => {
    pollCount++;
    if (pollCount > 30) {
      clearInterval(inlineMpesaPollTimer);
      return;
    }

    try {
      const res = await axios.get(`/api/mpesa/status/${depositId}`);
      if (res.data.status === "approved") {
        clearInterval(inlineMpesaPollTimer);
        const creditedUsdt = Number(res.data.deposit?.amount || calculatedUsdtAmount.value);
        inlineMpesaActivePrompt.value = null;
        
        // Update balance live
        liveBalance.value = liveBalance.value + creditedUsdt;
        successDepositNotice.value = `Payment approved! $${creditedUsdt.toFixed(2)} USDT has been automatically added to your wallet.`;
        
        // Auto-fill lock form with deposited amount and switch to Lock tab
        form.amount = creditedUsdt >= 10 ? creditedUsdt : 10;
        activePanelTab.value = "lock";

        // Refresh Inertia props in background
        refreshWalletBalance();
      } else if (res.data.status === "rejected") {
        clearInterval(inlineMpesaPollTimer);
        inlineMpesaError.value = res.data.message || "M-Pesa payment was cancelled or failed.";
        inlineMpesaActivePrompt.value = null;
      }
    } catch (e) {}
  }, 3500);
}

function cancelInlineMpesa() {
  if (inlineMpesaPollTimer) clearInterval(inlineMpesaPollTimer);
  inlineMpesaActivePrompt.value = null;
}

// Modal Handlers
function handleModalDepositSubmitted() {
  refreshWalletBalance();
}

function handleModalDepositApproved(deposit) {
  const amount = Number(deposit?.amount || 0);
  if (amount > 0) {
    liveBalance.value = liveBalance.value + amount;
    successDepositNotice.value = `Deposit approved! $${amount.toFixed(2)} USDT added to your wallet.`;
    form.amount = amount >= 10 ? amount : 10;
    activePanelTab.value = "lock";
  }
  refreshWalletBalance();
}

async function refreshWalletBalance() {
  try {
    const res = await axios.get("/api/deposits");
    if (res.data?.wallets) {
      const usdtWallet = res.data.wallets.find(w => w.currency === "USDT" && !w.is_demo);
      if (usdtWallet) {
        liveBalance.value = Number(usdtWallet.available_balance || 0);
      }
    }
  } catch (e) {
    router.reload({ only: ["wallet", "subscriptions", "logs"] });
  }
}

// Helpers
function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return "—";
  return new Date(dateStr).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

onMounted(async () => {
  try {
    const res = await axios.get("/api/mpesa/settings");
    if (res.data?.exchange_rate) {
      exchangeRate.value = res.data.exchange_rate;
    }
  } catch (e) {}
});

onUnmounted(() => {
  if (inlineMpesaPollTimer) clearInterval(inlineMpesaPollTimer);
});
</script>
