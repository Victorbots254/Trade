<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none">
    <!-- Header -->
    <header class="bg-[#181a20] border-b border-[#2b3139] px-4 md:px-8 py-3.5 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center space-x-3">
        <a href="/p2p" class="text-xs text-[#848e9c] hover:text-white transition flex items-center space-x-1">
          <span>← Back to P2P</span>
        </a>
        <span class="text-[#2b3139]">|</span>
        <h1 class="text-sm font-bold text-white flex items-center space-x-2">
          <span>Order #{{ order.order_number }}</span>
          <span :class="statusBadgeClass" class="text-[10px] uppercase font-mono px-2 py-0.5 rounded font-bold">
            {{ formatStatus(order.status) }}
          </span>
        </h1>
      </div>

      <div class="flex items-center space-x-3 text-xs">
        <a href="/p2p/orders" class="text-[#848e9c] hover:text-white">All Orders</a>
      </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- LEFT COLUMN: ORDER TIMELINE & PAYMENT CREDENTIALS (7 cols) -->
      <div class="lg:col-span-7 space-y-6">
        <!-- Live Action Error Banner -->
        <div v-if="actionError || ($page.props.errors && Object.keys($page.props.errors).length > 0)" class="bg-rose-500/10 border border-rose-500/40 text-rose-300 p-4 rounded-2xl text-xs space-y-1 shadow-lg">
          <div class="font-bold text-rose-400 flex items-center space-x-1.5">
            <span>⚠️</span>
            <span>Notice:</span>
          </div>
          <p v-if="actionError">{{ actionError }}</p>
          <div v-for="(err, k) in $page.props.errors" :key="k">
            {{ err }}
          </div>
        </div>

        <!-- Live Success Banner -->
        <div v-if="$page.props.flash?.message || $page.props.flash?.success" class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 p-4 rounded-2xl text-xs font-bold flex items-center space-x-2 shadow-lg">
          <span>✓</span>
          <span>{{ $page.props.flash?.message || $page.props.flash?.success }}</span>
        </div>

        <!-- Progress Stepper Card -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-5 space-y-4 shadow-xl">
          <div class="grid grid-cols-3 gap-2 text-center text-xs">
            <div :class="stepIndex >= 1 ? 'text-[#f0b90b]' : 'text-[#5e6673]'" class="space-y-1">
              <div :class="stepIndex >= 1 ? 'border-[#f0b90b] text-[#f0b90b]' : 'border-[#2b3139] text-[#5e6673]'" class="w-7 h-7 rounded-full border-2 mx-auto flex items-center justify-center font-bold text-xs">1</div>
              <div class="font-bold text-[11px]">Pay Seller</div>
            </div>
            <div :class="stepIndex >= 2 ? 'text-[#f0b90b]' : 'text-[#5e6673]'" class="space-y-1">
              <div :class="stepIndex >= 2 ? 'border-[#f0b90b] text-[#f0b90b]' : 'border-[#2b3139] text-[#5e6673]'" class="w-7 h-7 rounded-full border-2 mx-auto flex items-center justify-center font-bold text-xs">2</div>
              <div class="font-bold text-[11px]">Confirm Receipt</div>
            </div>
            <div :class="stepIndex >= 3 ? 'text-[#0ecb81]' : 'text-[#5e6673]'" class="space-y-1">
              <div :class="stepIndex >= 3 ? 'border-[#0ecb81] text-[#0ecb81]' : 'border-[#2b3139] text-[#5e6673]'" class="w-7 h-7 rounded-full border-2 mx-auto flex items-center justify-center font-bold text-xs">3</div>
              <div class="font-bold text-[11px]">Completed</div>
            </div>
          </div>

          <!-- Countdown / Status Alert -->
          <div v-if="order.status === 'pending_payment'" class="bg-[#0b0e11] border border-[#f0b90b]/30 rounded-xl p-4 flex items-center justify-between text-xs">
            <div class="flex items-center space-x-3">
              <span class="text-xl">⏳</span>
              <div>
                <div class="font-bold text-white">{{ isBuyer ? 'Please pay the seller within:' : 'Waiting for buyer payment within:' }}</div>
                <div class="text-[11px] text-[#848e9c]">Order will automatically cancel if payment is not marked.</div>
              </div>
            </div>
            <div class="text-right">
              <span class="font-mono text-lg font-black text-[#f0b90b]">{{ countdownFormatted }}</span>
            </div>
          </div>

          <div v-else-if="order.status === 'paid'" class="bg-[#0b0e11] border border-[#0ecb81]/30 rounded-xl p-4 flex items-center space-x-3 text-xs">
            <span class="text-xl">💸</span>
            <div>
              <div class="font-bold text-[#0ecb81]">Payment Marked as Sent!</div>
              <div class="text-[11px] text-slate-300">
                {{ isSeller ? 'Please verify your M-Pesa / Bank account before releasing the crypto.' : 'Waiting for seller to confirm payment and release crypto to your wallet.' }}
              </div>
            </div>
          </div>

          <div v-else-if="order.status === 'completed'" class="bg-[#0ecb81]/10 border border-[#0ecb81]/30 text-[#0ecb81] rounded-xl p-4 flex items-center space-x-3 text-xs">
            <span class="text-xl">🎉</span>
            <div>
              <div class="font-bold">Trade Successfully Completed!</div>
              <div class="text-[11px] text-slate-300">Crypto has been released from escrow into the buyer\'s available balance.</div>
            </div>
          </div>

          <div v-else-if="order.status === 'disputed'" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] rounded-xl p-4 flex items-center space-x-3 text-xs">
            <span class="text-xl">⚠️</span>
            <div>
              <div class="font-bold">Order in Dispute / Appeal</div>
              <div class="text-[11px] text-slate-300">Staff is reviewing trade evidence and chat logs to resolve this order.</div>
            </div>
          </div>

          <div v-else-if="order.status === 'cancelled'" class="bg-[#2b3139]/40 border border-[#2b3139] text-[#848e9c] rounded-xl p-4 flex items-center space-x-3 text-xs">
            <span class="text-xl">❌</span>
            <div>
              <div class="font-bold text-white">Order Cancelled</div>
              <div class="text-[11px]">Escrow has been returned to the seller.</div>
            </div>
          </div>
        </div>

        <!-- Order Amounts Breakdown Card -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-5 space-y-4 shadow-xl text-xs">
          <h2 class="text-sm font-bold text-white border-b border-[#2b3139] pb-3">Order Details</h2>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-[11px]">
            <div class="bg-[#0b0e11] p-3 rounded-xl border border-[#2b3139]/60">
              <span class="text-[#848e9c] block text-[10px] uppercase font-sans">Fiat Amount</span>
              <span class="text-base font-black text-white">{{ Number(order.fiat_amount).toLocaleString() }}</span>
              <span class="text-xs text-[#848e9c] ml-1">{{ order.ad?.fiat || 'KES' }}</span>
            </div>

            <div class="bg-[#0b0e11] p-3 rounded-xl border border-[#2b3139]/60">
              <span class="text-[#848e9c] block text-[10px] uppercase font-sans">Locked Escrow</span>
              <span class="text-base font-black text-white">{{ Number(order.crypto_amount).toFixed(4) }}</span>
              <span class="text-xs text-[#848e9c] ml-1">USDT</span>
            </div>

            <div class="bg-[#0b0e11] p-3 rounded-xl border border-[#2b3139]/60">
              <span class="text-[#848e9c] block text-[10px] uppercase font-sans">Escrow Fee</span>
              <span v-if="!order.escrow_fee || Number(order.escrow_fee) === 0" class="text-[11px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/30 inline-block mt-1">FREE ($0.00)</span>
              <div v-else class="text-base font-black text-amber-400">
                {{ Number(order.escrow_fee).toFixed(2) }} <span class="text-xs text-[#848e9c]">USDT</span>
              </div>
            </div>

            <div class="bg-[#0b0e11] p-3 rounded-xl border border-emerald-500/40">
              <span class="text-emerald-400 block text-[10px] uppercase font-sans font-bold">Net to Buyer</span>
              <span class="text-base font-black text-emerald-400">{{ Math.max(0, Number(order.crypto_amount) - Number(order.escrow_fee || 0)).toFixed(4) }}</span>
              <span class="text-xs text-emerald-400/80 ml-1">USDT</span>
            </div>
          </div>
        </div>

        <!-- Seller Payment Account Credentials -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-5 space-y-4 shadow-xl text-xs">
          <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
            <h2 class="text-sm font-bold text-white flex items-center space-x-2">
              <span>💳 Seller Payment Details</span>
              <span class="bg-[#0ecb81]/10 text-[#0ecb81] border border-[#0ecb81]/30 text-[10px] px-2 py-0.5 rounded uppercase font-mono">
                {{ order.payment_method }}
              </span>
            </h2>
            <span class="text-[11px] text-[#848e9c]">Seller: <strong class="text-white">{{ order.seller?.p2p_merchant_name || order.seller?.name }}</strong></span>
          </div>

          <!-- Payment Info Box -->
          <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-4 space-y-3 font-mono text-xs">
            <div class="flex justify-between items-center py-1 border-b border-[#2b3139]/50">
              <span class="text-[#848e9c]">Recipient Name:</span>
              <span class="font-bold text-white select-all">{{ order.seller?.p2p_merchant_name || order.seller?.name }}</span>
            </div>

            <div class="flex justify-between items-center py-1 border-b border-[#2b3139]/50">
              <span class="text-[#848e9c]">Payment Method:</span>
              <span class="font-bold text-[#0ecb81] uppercase">{{ order.payment_method === 'mpesa' ? 'Safaricom M-Pesa' : 'Bank Transfer' }}</span>
            </div>

            <!-- Custom payment instructions or phone -->
            <div v-if="order.seller?.p2p_payment_details" class="py-1 border-b border-[#2b3139]/50 space-y-1">
              <span class="text-[#848e9c] block text-[11px]">Payment Instructions / Account Number:</span>
              <p class="font-bold text-white whitespace-pre-wrap select-all bg-[#181a20] p-2.5 rounded-lg border border-[#2b3139]">
                {{ order.seller.p2p_payment_details }}
              </p>
            </div>

            <div class="flex justify-between items-center py-1">
              <span class="text-[#848e9c]">Payment Reference:</span>
              <span class="font-bold text-[#f0b90b] select-all">{{ order.order_number }}</span>
            </div>
          </div>

          <!-- Warning Notice -->
          <div class="bg-amber-500/10 border border-amber-500/30 text-amber-300 p-3 rounded-xl text-[11px] leading-relaxed">
            ⚠️ <strong>Important Security Reminder:</strong> Do not include crypto-related words (e.g. "USDT", "TradeCo", "Crypto") in your bank or M-Pesa transaction reference. Only use the order number or your real name.
          </div>

          <!-- Action Controls for Buyer / Seller -->
          <div class="pt-2 space-y-3">
            <!-- BUYER ACTIONS -->
            <div v-if="isBuyer && order.status === 'pending_payment'" class="flex items-center space-x-3">
              <button
                @click="showMarkPaidModal = true"
                type="button"
                class="flex-1 bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black py-3 rounded-xl transition text-xs shadow-lg uppercase tracking-wider">
                Transferred, Notify Seller →
              </button>
              <button
                @click="showCancelModal = true"
                type="button"
                class="border border-[#2b3139] hover:bg-[#2b3139] text-[#848e9c] hover:text-white px-4 py-3 rounded-xl font-bold transition text-xs">
                Cancel Trade
              </button>
            </div>

            <!-- SELLER ACTIONS -->
            <div v-if="(isSeller || isAdminOrMod) && order.status !== 'completed' && order.status !== 'cancelled'" class="space-y-2">
              <button
                @click="showReleaseModal = true"
                type="button"
                class="w-full bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black py-3.5 rounded-xl transition text-xs shadow-xl uppercase tracking-wider flex items-center justify-center space-x-2">
                <span>✓ Payment Received & Release Crypto</span>
              </button>
              <p class="text-[10px] text-center text-[#848e9c]">
                {{ order.status === 'pending_payment' ? 'Buyer has not marked paid yet, but you can release immediately if you have confirmed receipt of funds.' : 'Please verify that you have logged into your bank/M-Pesa app and the funds are cleared before releasing.' }}
              </p>
            </div>

            <!-- APPEAL / DISPUTE BUTTON -->
            <div v-if="(order.status === 'paid' || order.status === 'pending_payment') && !order.status.includes('completed') && !order.status.includes('cancelled')" class="flex justify-end pt-1">
              <button
                @click="showDisputeModal = true"
                type="button"
                class="text-[11px] text-[#f6465d] hover:underline flex items-center space-x-1">
                <span>⚠️ Need Help? Open Appeal / Dispute</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: IN-ORDER LIVE CHAT & PROOFS (5 cols) -->
      <div class="lg:col-span-5 bg-[#181a20] border border-[#2b3139] rounded-2xl flex flex-col h-[650px] shadow-xl overflow-hidden text-xs">
        <!-- Chat Header -->
        <div class="p-4 border-b border-[#2b3139] bg-[#14161a] flex justify-between items-center">
          <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#0ecb81] animate-pulse"></span>
            <span class="font-bold text-white">Trade Chat & Receipt Proofs</span>
          </div>
          <span class="text-[10px] text-[#848e9c] font-mono">End-to-End Logged</span>
        </div>

        <!-- Chat Messages Area -->
        <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-[#0b0e11]/60">
          <div v-for="msg in order.messages" :key="msg.id" class="space-y-1">
            <!-- System message -->
            <div v-if="msg.is_system" class="bg-[#1e2329]/90 border border-[#2b3139] text-[#848e9c] p-2.5 rounded-xl text-[11px] text-center my-2 leading-relaxed">
              {{ msg.message }}
            </div>

            <!-- User Chat Message -->
            <div v-else :class="msg.user_id === currentUser.id ? 'items-end' : 'items-start'" class="flex flex-col">
              <div class="flex items-center space-x-1.5 text-[10px] text-[#848e9c] mb-0.5">
                <span class="font-bold" :class="msg.user_id === currentUser.id ? 'text-[#f0b90b]' : 'text-slate-300'">
                  {{ msg.user?.name || 'Trader' }}
                </span>
                <span v-if="msg.user_id === order.seller_id" class="text-[9px] bg-[#f0b90b]/10 text-[#f0b90b] px-1 rounded">Seller</span>
                <span v-else-if="msg.user_id === order.buyer_id" class="text-[9px] bg-[#0ecb81]/10 text-[#0ecb81] px-1 rounded">Buyer</span>
                <span>· {{ formatTime(msg.created_at) }}</span>
              </div>

              <div
                :class="msg.user_id === currentUser.id ? 'bg-[#f0b90b] text-[#1e2329] font-medium' : 'bg-[#1e2329] text-white border border-[#2b3139]'"
                class="max-w-[85%] rounded-2xl px-3.5 py-2 text-xs leading-relaxed space-y-2">
                <p v-if="msg.message" class="whitespace-pre-wrap">{{ msg.message }}</p>

                <!-- Receipt Image Attachment -->
                <div v-if="msg.attachment_path" class="pt-1">
                  <a :href="'/storage/' + msg.attachment_path" target="_blank" class="block rounded-lg overflow-hidden border border-black/20">
                    <img :src="'/storage/' + msg.attachment_path" alt="Payment Proof" class="max-h-48 w-full object-cover hover:opacity-90 transition" />
                  </a>
                  <span class="text-[9px] block text-right mt-1 opacity-70">Click to view full receipt</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Input Form -->
        <form @submit.prevent="sendChatMessage" class="p-3 border-t border-[#2b3139] bg-[#181a20] space-y-2">
          <!-- Attachment preview -->
          <div v-if="chatAttachment" class="flex items-center justify-between bg-[#0b0e11] px-3 py-1.5 rounded-lg text-[11px] border border-[#2b3139]">
            <span class="text-[#0ecb81] truncate">Attached: {{ chatAttachment.name }}</span>
            <button @click="chatAttachment = null" type="button" class="text-[#f6465d] ml-2">✕</button>
          </div>

          <div class="flex items-center space-x-2">
            <!-- Image Attachment Button -->
            <label class="cursor-pointer text-[#848e9c] hover:text-[#f0b90b] p-2 hover:bg-[#2b3139] rounded-xl transition" title="Attach Payment Receipt">
              <input @change="handleChatFile" type="file" accept="image/*" class="hidden" />
              <span>📷</span>
            </label>

            <input
              v-model="chatInput"
              type="text"
              placeholder="Type message or paste M-Pesa code..."
              class="flex-1 bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white placeholder-[#5e6673] focus:outline-none focus:border-[#f0b90b] text-xs font-sans" />

            <button
              type="submit"
              :disabled="sendingChat || (!chatInput && !chatAttachment)"
              class="bg-[#f0b90b] hover:bg-[#d4a30b] disabled:opacity-40 text-[#1e2329] font-bold px-4 py-2.5 rounded-xl transition shadow">
              Send
            </button>
          </div>
        </form>
      </div>
    </main>

    <!-- MODAL 1: MARK AS PAID CONFIRMATION -->
    <div v-if="showMarkPaidModal" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <h3 class="font-bold text-white text-sm">Confirm Payment Sent</h3>
        <p class="text-[#848e9c] leading-relaxed">
          Please confirm that you have sent <strong class="text-white font-mono">{{ Number(order.fiat_amount).toLocaleString() }} {{ order.ad?.fiat }}</strong> to the seller via <strong>{{ order.payment_method }}</strong>.
        </p>

        <div class="bg-amber-500/10 border border-amber-500/20 p-3 rounded-xl text-amber-300 text-[11px]">
          ⚠️ False declarations will lead to immediate account suspension and dispute penalties.
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="showMarkPaidModal = false" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold">
            Cancel
          </button>
          <button @click="confirmMarkPaid" type="button" class="flex-1 bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329] font-black py-2.5 rounded-xl shadow-lg">
            Yes, I Have Paid
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 2: RELEASE CRYPTO CONFIRMATION (FOR SELLER) -->
    <div v-if="showReleaseModal" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <div class="w-12 h-12 bg-[#0ecb81]/10 border border-[#0ecb81]/30 rounded-2xl flex items-center justify-center mx-auto text-xl">
          🔓
        </div>
        <h3 class="font-bold text-white text-sm text-center">Release {{ Number(order.crypto_amount).toFixed(4) }} USDT</h3>
        <p class="text-[#848e9c] leading-relaxed text-center">
          Have you checked your bank or M-Pesa account directly to confirm receipt of <strong class="text-white font-mono">{{ Number(order.fiat_amount).toLocaleString() }} {{ order.ad?.fiat }}</strong>?
        </p>

        <div class="bg-[#f6465d]/10 border border-[#f6465d]/20 p-3 rounded-xl text-[#f6465d] text-[11px]">
          ⚠️ <strong>Irreversible Action:</strong> Once released, escrowed crypto is deposited immediately into the buyer's wallet and cannot be recalled.
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="showReleaseModal = false" :disabled="releasingCrypto" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold disabled:opacity-50">
            Wait / Check Account
          </button>
          <button @click="confirmReleaseCrypto" :disabled="releasingCrypto" type="button" class="flex-1 bg-[#0ecb81] hover:bg-[#0bb371] disabled:opacity-50 text-[#1e2329] font-black py-2.5 rounded-xl shadow-lg flex items-center justify-center space-x-1.5">
            <span v-if="releasingCrypto">Releasing Crypto...</span>
            <span v-else>Confirm & Release</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 3: CANCEL ORDER -->
    <div v-if="showCancelModal" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <h3 class="font-bold text-white text-sm">Cancel P2P Trade</h3>
        <p class="text-[#848e9c]">Are you sure you want to cancel this trade? If you have already paid, DO NOT cancel.</p>
        <div class="flex items-center space-x-3 pt-2">
          <button @click="showCancelModal = false" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold">
            No, Keep Order
          </button>
          <button @click="confirmCancelOrder" type="button" class="flex-1 bg-[#f6465d] hover:bg-[#e03a51] text-white font-bold py-2.5 rounded-xl shadow">
            Confirm Cancel
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL 4: DISPUTE / APPEAL -->
    <div v-if="showDisputeModal" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <h3 class="font-bold text-white text-sm">Open Dispute / Appeal</h3>
        <p class="text-[#848e9c]">Explain the issue clearly. An Admin will review evidence in the trade chat.</p>

        <div>
          <label class="block text-[#848e9c] mb-1 font-semibold">Reason for Appeal</label>
          <textarea
            v-model="disputeReason"
            required
            rows="3"
            placeholder="e.g. I transferred funds via M-Pesa (Code: QWE12345), but seller has not released."
            class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 text-white focus:outline-none focus:border-[#f0b90b] text-xs"></textarea>
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="showDisputeModal = false" type="button" class="flex-1 border border-[#2b3139] py-2.5 rounded-xl text-[#848e9c] hover:text-white font-bold">
            Cancel
          </button>
          <button @click="confirmDispute" :disabled="!disputeReason" type="button" class="flex-1 bg-[#f6465d] hover:bg-[#e03a51] disabled:opacity-50 text-white font-bold py-2.5 rounded-xl shadow">
            Submit Appeal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  order: { type: Object, required: true },
  currentUser: { type: Object, required: true },
  isBuyer: { type: Boolean, default: false },
  isSeller: { type: Boolean, default: false },
  isAdminOrMod: { type: Boolean, default: false },
});

// Modals
const showMarkPaidModal = ref(false);
const showReleaseModal = ref(false);
const showCancelModal = ref(false);
const showDisputeModal = ref(false);
const disputeReason = ref('');
const releasingCrypto = ref(false);
const actionError = ref('');

// Chat
const chatInput = ref('');
const chatAttachment = ref(null);
const sendingChat = ref(false);
const chatContainer = ref(null);

// Countdown Timer
const remainingSeconds = ref(0);
let countdownTimer = null;
let pollTimer = null;

const stepIndex = computed(() => {
  if (props.order.status === 'completed') return 3;
  if (props.order.status === 'paid' || props.order.status === 'disputed') return 2;
  return 1;
});

const countdownFormatted = computed(() => {
  if (remainingSeconds.value <= 0) return '00:00';
  const m = Math.floor(remainingSeconds.value / 60);
  const s = remainingSeconds.value % 60;
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
});

const statusBadgeClass = computed(() => {
  switch (props.order.status) {
    case 'completed': return 'bg-[#0ecb81]/10 text-[#0ecb81] border border-[#0ecb81]/30';
    case 'paid': return 'bg-[#f0b90b]/10 text-[#f0b90b] border border-[#f0b90b]/30';
    case 'disputed': return 'bg-[#f6465d]/10 text-[#f6465d] border border-[#f6465d]/30';
    case 'cancelled': return 'bg-[#2b3139] text-[#848e9c] border border-[#2b3139]';
    default: return 'bg-[#1e88e5]/10 text-[#64b5f6] border border-[#1e88e5]/30';
  }
});

function formatStatus(st) {
  if (st === 'pending_payment') return 'Pending Payment';
  if (st === 'paid') return 'Paid / Pending Release';
  if (st === 'disputed') return 'In Appeal';
  return st;
}

function formatTime(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function initCountdown() {
  if (props.order.status !== 'pending_payment' || !props.order.expires_at) return;
  const expiry = new Date(props.order.expires_at).getTime();
  const update = () => {
    const diff = Math.floor((expiry - Date.now()) / 1000);
    remainingSeconds.value = Math.max(0, diff);
    if (diff <= 0 && countdownTimer) {
      clearInterval(countdownTimer);
      router.reload();
    }
  };
  update();
  countdownTimer = setInterval(update, 1000);
}

function handleChatFile(e) {
  chatAttachment.value = e.target.files[0];
}

function sendChatMessage() {
  if (!chatInput.value && !chatAttachment.value) return;
  sendingChat.value = true;

  const data = new FormData();
  if (chatInput.value) data.append('message', chatInput.value);
  if (chatAttachment.value) data.append('attachment', chatAttachment.value);

  router.post(`/p2p/orders/${props.order.id}/message`, data, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      chatInput.value = '';
      chatAttachment.value = null;
      scrollToBottom();
    },
    onFinish: () => {
      sendingChat.value = false;
    },
  });
}

function confirmMarkPaid() {
  showMarkPaidModal.value = false;
  router.post(`/p2p/orders/${props.order.id}/paid`);
}

function confirmReleaseCrypto() {
  releasingCrypto.value = true;
  actionError.value = '';
  router.post(`/p2p/orders/${props.order.id}/release`, {}, {
    onSuccess: () => {
      releasingCrypto.value = false;
      showReleaseModal.value = false;
    },
    onError: (errors) => {
      releasingCrypto.value = false;
      showReleaseModal.value = false;
      actionError.value = errors.message || Object.values(errors)[0] || 'Error releasing crypto.';
      alert('Release Notice: ' + actionError.value);
    },
    onFinish: () => {
      releasingCrypto.value = false;
    }
  });
}

function confirmCancelOrder() {
  showCancelModal.value = false;
  router.post(`/p2p/orders/${props.order.id}/cancel`);
}

function confirmDispute() {
  showDisputeModal.value = false;
  router.post(`/p2p/orders/${props.order.id}/dispute`, { reason: disputeReason.value });
}

function scrollToBottom() {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
}

function playChime() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = 'sine';
    osc.frequency.setValueAtTime(880, now);
    gain.gain.setValueAtTime(0.15, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.start(now);
    osc.stop(now + 0.35);
  } catch (e) {}
}

watch(() => props.order?.messages?.length, (newLen, oldLen) => {
  if (oldLen !== undefined && newLen > oldLen) {
    const lastMsg = props.order.messages[props.order.messages.length - 1];
    if (lastMsg && lastMsg.user_id !== props.currentUser?.id) {
      playChime();
    }
    scrollToBottom();
  }
});

watch(() => props.order?.status, (newStatus, oldStatus) => {
  if (oldStatus && newStatus !== oldStatus) {
    playChime();
  }
});

onMounted(() => {
  initCountdown();
  scrollToBottom();

  // Poll for order and chat updates every 4 seconds
  pollTimer = setInterval(() => {
    router.reload({ only: ['order'] });
  }, 4000);
});

onUnmounted(() => {
  if (countdownTimer) clearInterval(countdownTimer);
  if (pollTimer) clearInterval(pollTimer);
});
</script>
