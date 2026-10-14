<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] font-sans select-none flex flex-col pb-12">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="user"
      :wallets="wallets"
    />

    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <!-- Breadcrumbs & Header -->
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-[#2b3139] pb-4 gap-3">
        <div>
          <div class="flex items-center space-x-2 text-xs text-[#848e9c] mb-1">
            <Link href="/profile" class="hover:text-white transition">Account</Link>
            <span>/</span>
            <span class="text-white">Withdraw</span>
          </div>
          <h1 class="text-xl md:text-2xl font-bold text-white flex items-center space-x-2">
            <span>💸</span>
            <span>Withdraw Funds</span>
          </h1>
          <p class="text-xs md:text-sm text-[#848e9c] mt-1">
            Withdraw your trading profits and USDT balance via Safaricom M-Pesa or Binance BEP-20.
          </p>
        </div>

        <div class="flex items-center space-x-3 self-start md:self-auto">
          <div class="bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2 text-right">
            <span class="text-[10px] text-[#848e9c] block font-semibold uppercase">Available USDT</span>
            <span class="text-sm font-bold text-[#0ecb81] font-mono">${{ availableBalance.toFixed(2) }}</span>
          </div>
          <Link href="/profile" 
             class="bg-[#181a20] hover:bg-[#2b3139] text-[#f0b90b] border border-[#2b3139] px-3.5 py-2.5 rounded-xl transition text-xs font-bold flex items-center space-x-1.5 shadow">
            <span>⚙️ Payout Settings</span>
          </Link>
        </div>
      </div>

      <!-- Success Flash Banner with 24 Hours Delivery Message -->
      <div v-if="$page.props.flash?.message" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl text-xs md:text-sm font-bold flex items-start space-x-3 shadow-lg">
        <span class="text-lg">✅</span>
        <div class="space-y-1">
          <div class="font-black text-white text-sm">Withdrawal Request Received!</div>
          <div class="text-emerald-300 font-normal leading-relaxed">{{ $page.props.flash.message }}</div>
          <div class="text-[11px] text-emerald-400/80 font-mono pt-1">
            ⏱ Guaranteed processing: All payments are disbursed to your preferred method within 24 hours.
          </div>
        </div>
      </div>

      <!-- Global Error Banner -->
      <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-4 rounded-xl text-xs space-y-1">
        <div class="font-bold flex items-center space-x-2">
          <span>⚠️</span>
          <span>Please review the errors below:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-[11px]">
          <li v-for="(err, key) in $page.props.errors" :key="key">{{ err }}</li>
        </ul>
      </div>

      <!-- Main Withdrawal Card -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-6 shadow-2xl space-y-6">
        <!-- Payout Method Selection Tabs -->
        <div>
          <label class="block text-xs font-bold text-[#848e9c] uppercase tracking-wider mb-2.5">
            Select Payout Channel
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- M-Pesa Tab -->
            <button
              type="button"
              @click="setPaymentMethod('mpesa')"
              :class="form.payment_method === 'mpesa' 
                ? 'border-[#0ecb81] bg-[#0ecb81]/10 text-white shadow-lg ring-1 ring-[#0ecb81]' 
                : 'border-[#2b3139] bg-[#0b0e11] text-[#848e9c] hover:border-[#3b414a] hover:text-white'"
              class="p-4 rounded-xl border text-left transition flex items-center justify-between group">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold">
                  🟢
                </div>
                <div>
                  <div class="font-bold text-sm text-white flex items-center space-x-2">
                    <span>Safaricom M-Pesa</span>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.2 rounded font-bold uppercase">Local KES</span>
                  </div>
                  <div class="text-[11px] text-[#848e9c] mt-0.5">Direct to phone or till (1 USDT ≈ {{ marketRateKes.toFixed(2) }} KES)</div>
                </div>
              </div>
              <span v-if="form.payment_method === 'mpesa'" class="text-[#0ecb81] font-bold text-lg">✓</span>
            </button>

            <!-- BEP-20 Tab -->
            <button
              type="button"
              @click="setPaymentMethod('bep20')"
              :class="form.payment_method === 'bep20' 
                ? 'border-[#f0b90b] bg-[#f0b90b]/10 text-white shadow-lg ring-1 ring-[#f0b90b]' 
                : 'border-[#2b3139] bg-[#0b0e11] text-[#848e9c] hover:border-[#3b414a] hover:text-white'"
              class="p-4 rounded-xl border text-left transition flex items-center justify-between group">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl font-bold">
                  🟡
                </div>
                <div>
                  <div class="font-bold text-sm text-white flex items-center space-x-2">
                    <span>Binance BEP-20</span>
                    <span class="text-[10px] bg-amber-500/20 text-amber-400 px-1.5 py-0.2 rounded font-bold uppercase">USDT Crypto</span>
                  </div>
                  <div class="text-[11px] text-[#848e9c] mt-0.5">BNB Smart Chain blockchain payout</div>
                </div>
              </div>
              <span v-if="form.payment_method === 'bep20'" class="text-[#f0b90b] font-bold text-lg">✓</span>
            </button>
          </div>
        </div>

        <form @submit.prevent="submitWithdraw" class="space-y-5">
          <!-- Amount Input Section -->
          <div class="space-y-2">
            <div class="flex justify-between items-center text-xs">
              <label class="font-bold text-slate-200">Amount to Withdraw (USDT) *</label>
              <span class="text-[#848e9c] text-[11px]">
                Available: <strong class="text-white font-mono">${{ availableBalance.toFixed(2) }} USDT</strong>
              </span>
            </div>

            <div class="relative">
              <span class="absolute left-3.5 top-3 text-[#848e9c] font-bold text-sm">$</span>
              <input 
                v-model="form.amount" 
                type="number" 
                step="0.01" 
                min="10" 
                required 
                placeholder="Minimum 10.00" 
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl pl-8 pr-20 py-3 text-white text-sm font-mono font-bold focus:border-[#0ecb81] focus:outline-none transition" />
              <button 
                type="button" 
                @click="setMaxAmount"
                class="absolute right-3 top-2.5 bg-[#1e2329] hover:bg-[#2b3139] text-[#f0b90b] font-bold text-xs px-2.5 py-1 rounded-lg transition border border-[#2b3139]">
                MAX
              </button>
            </div>

            <!-- Quick Amount Presets -->
            <div class="flex items-center space-x-2 pt-1 text-xs">
              <span class="text-[#848e9c] text-[11px]">Quick:</span>
              <button 
                v-for="amt in [20, 50, 100, 250, 500]" 
                :key="amt"
                type="button"
                @click="setAmount(amt)"
                class="px-2.5 py-1 bg-[#0b0e11] hover:bg-[#2b3139] border border-[#2b3139] rounded-lg text-slate-300 font-mono text-[11px] font-semibold transition">
                ${{ amt }}
              </button>
            </div>

            <!-- M-Pesa KES Conversion Display -->
            <div v-if="form.payment_method === 'mpesa' && estimatedKes > 0" class="p-3 bg-[#0b0e11] border border-emerald-500/20 rounded-xl flex items-center justify-between text-xs font-mono">
              <span class="text-[#848e9c]">You will receive in M-Pesa:</span>
              <span class="text-emerald-400 font-bold text-sm">
                KES {{ formatNumber(estimatedKes) }}
                <span class="text-[10px] text-[#848e9c] font-sans font-normal ml-1">(@ {{ marketRateKes.toFixed(2) }} KES/$)</span>
              </span>
            </div>
          </div>

          <!-- M-PESA PAYOUT DETAILS -->
          <div v-if="form.payment_method === 'mpesa'" class="p-4 bg-[#0b0e11] border border-[#2b3139] rounded-xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#2b3139] pb-2.5">
              <span class="font-bold text-white text-xs flex items-center space-x-2">
                <span>🟢</span>
                <span>Safaricom M-Pesa Destination Details</span>
              </span>
              <span class="text-[11px] text-[#848e9c]">Zero withdrawal fees</span>
            </div>

            <!-- M-Pesa Type Toggle -->
            <div class="grid grid-cols-2 gap-2 text-xs">
              <button 
                type="button"
                @click="form.mpesa_type = 'phone'"
                :class="form.mpesa_type === 'phone' ? 'bg-[#0ecb81]/15 border-[#0ecb81] text-[#0ecb81]' : 'bg-[#181a20] border-[#2b3139] text-[#848e9c]'"
                class="py-2 px-3 rounded-lg border font-bold text-center transition">
                Send Money (Mobile Phone)
              </button>
              <button 
                type="button"
                @click="form.mpesa_type = 'paybill'"
                :class="form.mpesa_type === 'paybill' ? 'bg-[#0ecb81]/15 border-[#0ecb81] text-[#0ecb81]' : 'bg-[#181a20] border-[#2b3139] text-[#848e9c]'"
                class="py-2 px-3 rounded-lg border font-bold text-center transition">
                Buy Goods / Paybill
              </button>
            </div>

            <div v-if="form.mpesa_type === 'phone'" class="space-y-3">
              <div>
                <label class="block text-[#848e9c] text-xs font-semibold mb-1">M-Pesa Phone Number *</label>
                <input 
                  v-model="form.mpesa_phone" 
                  type="text" 
                  required
                  placeholder="e.g. 0712345678 or +254712345678"
                  class="w-full bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white font-mono text-xs focus:border-[#0ecb81] focus:outline-none" />
              </div>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[#848e9c] text-xs font-semibold mb-1">Business / Till No. *</label>
                <input 
                  v-model="form.mpesa_phone" 
                  type="text" 
                  required
                  placeholder="e.g. 247247 or Till 123456"
                  class="w-full bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white font-mono text-xs focus:border-[#0ecb81] focus:outline-none" />
              </div>
              <div>
                <label class="block text-[#848e9c] text-xs font-semibold mb-1">Account Number (Optional for Till)</label>
                <input 
                  v-model="form.mpesa_account_number" 
                  type="text" 
                  placeholder="e.g. 0712345678"
                  class="w-full bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white font-mono text-xs focus:border-[#0ecb81] focus:outline-none" />
              </div>
            </div>

            <div>
              <label class="block text-[#848e9c] text-xs font-semibold mb-1">Registered M-Pesa Name *</label>
              <input 
                v-model="form.mpesa_name" 
                type="text" 
                required
                placeholder="Full name as registered on Safaricom M-Pesa"
                class="w-full bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white text-xs focus:border-[#0ecb81] focus:outline-none" />
            </div>
          </div>

          <!-- BEP-20 PAYOUT DETAILS -->
          <div v-else class="p-4 bg-[#0b0e11] border border-[#2b3139] rounded-xl space-y-3">
            <div class="flex items-center justify-between border-b border-[#2b3139] pb-2.5">
              <span class="font-bold text-white text-xs flex items-center space-x-2">
                <span>🟡</span>
                <span>Binance BEP-20 Wallet Destination</span>
              </span>
              <Link href="/profile" class="text-[11px] text-[#f0b90b] hover:underline font-bold">
                Change in Profile ↗
              </Link>
            </div>

            <div>
              <label class="block text-[#848e9c] text-xs font-semibold mb-1">Recipient BEP-20 Address (0x...)</label>
              <input 
                v-model="form.bep20_address" 
                type="text" 
                required
                placeholder="0x71C7656EC7ab88b098defB751B7401B5f6d8976F"
                class="w-full bg-[#181a20] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white font-mono text-xs focus:border-[#f0b90b] focus:outline-none" />
            </div>
          </div>

          <!-- 24-HOUR DELIVERY NOTICE (EXPLICIT USER PROMISE) -->
          <div class="p-4 bg-[#0b0e11] border border-[#2b3139] rounded-xl flex items-start space-x-3 text-xs">
            <span class="text-base">🕒</span>
            <div class="space-y-0.5">
              <span class="font-bold text-white block">24-Hour Settlement Guarantee</span>
              <p class="text-[#848e9c] leading-relaxed text-[11px]">
                Your withdrawal request will be processed and sent to your preferred method within <strong>24 hours</strong>. You will receive an email confirmation once the transfer has been executed.
              </p>
            </div>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="form.processing || !canSubmit"
            class="w-full bg-[#0ecb81] hover:bg-[#0bb573] disabled:opacity-40 disabled:cursor-not-allowed text-[#1e2329] font-black py-3.5 rounded-xl transition uppercase tracking-wider text-xs shadow-xl flex items-center justify-center space-x-2">
            <span v-if="form.processing">Submitting Withdrawal Request...</span>
            <span v-else>Confirm & Request Withdrawal (${{ Number(form.amount || 0).toFixed(2) }} USDT)</span>
          </button>
        </form>
      </div>

      <!-- Live Withdrawals Requests History Table -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-6 space-y-4 shadow-xl text-xs">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <div>
            <h2 class="font-bold text-white text-sm">Withdrawal Requests History</h2>
            <p class="text-[11px] text-[#848e9c] mt-0.5">Track your pending and completed payouts.</p>
          </div>
          <span class="text-slate-500 font-mono text-[11px]">Total: {{ withdrawals.length }}</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] font-sans font-semibold">
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Amount</th>
                <th class="py-3 px-4">Channel</th>
                <th class="py-3 px-4">Payout Destination</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Submitted</th>
                <th class="py-3 px-4 text-right">Date Paid</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr v-for="w in withdrawals" :key="w.id" class="hover:bg-[#1e2329]/50 transition">
                <td class="py-3.5 px-4 font-bold text-[#848e9c]">#{{ w.id }}</td>
                
                <!-- Amount -->
                <td class="py-3.5 px-4">
                  <div class="font-bold text-[#0ecb81]">${{ Number(w.amount).toFixed(2) }} USDT</div>
                  <div v-if="w.amount_kes" class="text-[10px] text-[#848e9c]">
                    ≈ KES {{ Number(w.amount_kes).toLocaleString() }}
                  </div>
                </td>

                <!-- Channel Badge -->
                <td class="py-3.5 px-4 font-sans">
                  <span v-if="w.payment_method === 'mpesa'" class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-flex items-center space-x-1">
                    <span>🟢</span>
                    <span>M-Pesa</span>
                  </span>
                  <span v-else class="bg-amber-500/15 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-flex items-center space-x-1">
                    <span>🟡</span>
                    <span>BEP-20</span>
                  </span>
                </td>

                <!-- Destination -->
                <td class="py-3.5 px-4 font-mono text-[11px]">
                  <div v-if="w.payment_method === 'mpesa'">
                    <span class="text-white font-bold">{{ w.mpesa_phone }}</span>
                    <span v-if="w.mpesa_name" class="text-[#848e9c] block font-sans text-[10px]">{{ w.mpesa_name }}</span>
                  </div>
                  <div v-else>
                    <span class="text-slate-300 truncate max-w-[140px] block" :title="w.bep20_address">
                      {{ w.bep20_address || 'BEP-20' }}
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4 font-sans">
                  <span v-if="w.status === 'approved'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    APPROVED
                  </span>
                  <span v-else-if="w.status === 'rejected'" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    REJECTED
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px] animate-pulse">
                    PENDING (Within 24h)
                  </span>
                </td>

                <!-- Submitted Date -->
                <td class="py-3.5 px-4 text-[#848e9c] text-[11px]">
                  {{ formatDate(w.created_at) }}
                </td>

                <!-- Date Paid -->
                <td class="py-3.5 px-4 text-right font-mono text-[11px]">
                  <div v-if="w.paid_at" class="text-emerald-400 font-bold">
                    {{ formatDate(w.paid_at) }}
                  </div>
                  <div v-else-if="w.status === 'approved'" class="text-emerald-400 font-bold">
                    {{ w.approved_at ? formatDate(w.approved_at) : 'Completed' }}
                  </div>
                  <div v-else class="text-slate-600">
                    Within 24h
                  </div>
                </td>
              </tr>

              <tr v-if="withdrawals.length === 0">
                <td colspan="7" class="py-8 text-center text-[#848e9c]">
                  No withdrawal requests yet. Your payout transactions will appear here.
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
import { computed, ref } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import TradingHeader from "@/Components/TradingHeader.vue";
import ToastNotification from "@/Components/ToastNotification.vue";

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  wallets: { type: Array, default: () => [] },
  withdrawals: { type: Array, default: () => [] },
  marketRateKes: { type: Number, default: 129.50 },
  savedPaymentDetails: { type: Object, default: () => ({}) },
});

const toastRef = ref(null);

const usdtWallet = computed(() => {
  return (props.wallets || []).find(w => w.currency === 'USDT' && !w.is_demo);
});

const availableBalance = computed(() => {
  return usdtWallet.value ? parseFloat(usdtWallet.value.available_balance) : 0;
});

// Pre-fill from user's saved P2P payment details or profile
const defaultMpesa = props.savedPaymentDetails?.mpesa || {};
const initialPhone = defaultMpesa.phone || "";
const initialName = defaultMpesa.account_name || props.user?.name || "";
const initialType = defaultMpesa.type || "phone";
const initialAcc = defaultMpesa.account_number || "";

const form = useForm({
  amount: "",
  payment_method: "mpesa", // Default to M-Pesa as requested
  bep20_address: props.user?.bep20_address || "",
  mpesa_phone: initialPhone,
  mpesa_name: initialName,
  mpesa_type: initialType,
  mpesa_account_number: initialAcc,
});

function setPaymentMethod(method) {
  form.payment_method = method;
}

function setAmount(val) {
  form.amount = val.toString();
}

function setMaxAmount() {
  form.amount = availableBalance.value > 0 ? availableBalance.value.toFixed(2) : "0.00";
}

const estimatedKes = computed(() => {
  const num = parseFloat(form.amount || 0);
  if (isNaN(num) || num <= 0) return 0;
  return num * (props.marketRateKes || 129.50);
});

const canSubmit = computed(() => {
  const amt = parseFloat(form.amount);
  if (isNaN(amt) || amt < 10 || amt > availableBalance.value) return false;

  if (form.payment_method === 'mpesa') {
    return !!form.mpesa_phone && form.mpesa_phone.trim().length >= 8;
  } else {
    return !!form.bep20_address && form.bep20_address.trim().length >= 20;
  }
});

function formatNumber(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return "N/A";
  return new Date(dateStr).toLocaleString(undefined, {
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  });
}

function submitWithdraw() {
  form.post("/withdraw", {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show("Withdrawal request submitted! Your payment will be sent within 24 hours.", "success");
      form.amount = "";
    },
    onError: () => {
      toastRef.value?.show("Failed to submit withdrawal request. Please check inputs.", "error");
    }
  });
}
</script>
