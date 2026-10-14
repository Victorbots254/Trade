<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col pb-12">
    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="$page.props.auth?.user"
    />
    <ToastNotification ref="toastRef" />

    <!-- Admin Content Container -->
    <div class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- Title & Sub-navigation bar -->
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-800 pb-4 gap-3">
        <div>
          <h1 class="text-xl font-bold text-slate-100 flex items-center space-x-2">
            <span class="text-cyan-400">💸</span>
            <span>BEP-20 Withdrawal Approvals &amp; Payouts</span>
          </h1>
          <p class="text-slate-400 text-xs mt-1">
            Review outgoing BEP-20 withdrawal requests, verify trader destination addresses, and mark withdrawals as complete with optional BscScan TxHash.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
          <Link href="/admin/users" class="bg-slate-900 text-slate-400 hover:text-slate-200 px-3 py-1.5 rounded-lg border border-slate-800 transition">
            👥 Trader Controls
          </Link>
          <Link href="/admin/deposits" class="bg-slate-900 text-slate-400 hover:text-slate-200 px-3 py-1.5 rounded-lg border border-slate-800 transition">
            💰 Deposit Approvals
          </Link>
          <Link href="/admin/withdrawals" class="bg-cyan-600 text-white font-bold px-3 py-1.5 rounded-lg border border-cyan-500 shadow">
            💸 Withdrawal Approvals
          </Link>
          <Link href="/admin/p2p" class="bg-slate-900 text-[#f0b90b] hover:bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-800 font-bold transition">
            🛡️ P2P Disputes
          </Link>
        </div>
      </div>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3.5 rounded-xl text-xs font-semibold flex items-center justify-between">
        <span>✓ {{ $page.props.flash.success }}</span>
      </div>
      <div v-if="$page.props.flash?.error" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3.5 rounded-xl text-xs font-semibold">
        <span>⚠️ {{ $page.props.flash.error }}</span>
      </div>

      <!-- Metrics Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Pending Approvals -->
        <div class="bg-slate-900 border border-amber-500/30 rounded-xl p-4 shadow-lg relative overflow-hidden">
          <div class="flex items-center justify-between">
            <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Pending Payouts</span>
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
          </div>
          <div class="mt-2 text-2xl font-black text-white font-mono">
            {{ stats.total_pending }}
          </div>
          <div class="mt-1 text-xs text-slate-400 font-mono">
            ${{ formatPrice(stats.pending_amount) }} USDT awaiting release
          </div>
        </div>

        <!-- 2. Approved Payouts -->
        <div class="bg-slate-900 border border-emerald-500/30 rounded-xl p-4 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs text-emerald-400 font-bold uppercase tracking-wider">Completed / Approved</span>
            <span>✓</span>
          </div>
          <div class="mt-2 text-2xl font-black text-emerald-400 font-mono">
            {{ stats.total_approved }}
          </div>
          <div class="mt-1 text-xs text-slate-400 font-mono">
            ${{ formatPrice(stats.approved_amount) }} USDT released
          </div>
        </div>

        <!-- 3. Rejected & Refunded -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs text-rose-400 font-bold uppercase tracking-wider">Rejected &amp; Refunded</span>
            <span>✕</span>
          </div>
          <div class="mt-2 text-2xl font-black text-rose-400 font-mono">
            {{ stats.total_rejected }}
          </div>
          <div class="mt-1 text-xs text-slate-400">
            Returned to trader wallets
          </div>
        </div>

        <!-- 4. Total Lifetime Requests -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-lg">
          <div class="flex items-center justify-between">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Lifetime</span>
            <span>📊</span>
          </div>
          <div class="mt-2 text-2xl font-black text-slate-100 font-mono">
            {{ stats.total_count }}
          </div>
          <div class="mt-1 text-xs text-slate-400">
            All recorded payout requests
          </div>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 text-xs">
        <!-- Status Tabs -->
        <div class="flex items-center space-x-1.5 p-1 bg-slate-950 rounded-lg border border-slate-800">
          <button @click="statusFilter = 'all'"
                  :class="statusFilter === 'all' ? 'bg-slate-800 text-white font-bold shadow' : 'text-slate-400 hover:text-white'"
                  class="px-3 py-1.5 rounded-md transition">
            All ({{ withdrawals.length }})
          </button>
          <button @click="statusFilter = 'pending'"
                  :class="statusFilter === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'text-slate-400 hover:text-white'"
                  class="px-3 py-1.5 rounded-md transition flex items-center space-x-1">
            <span>Pending</span>
            <span v-if="stats.total_pending > 0" class="bg-amber-400 text-slate-950 font-black px-1.5 py-0.2 rounded-full text-[10px]">
              {{ stats.total_pending }}
            </span>
          </button>
          <button @click="statusFilter = 'approved'"
                  :class="statusFilter === 'approved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold' : 'text-slate-400 hover:text-white'"
                  class="px-3 py-1.5 rounded-md transition">
            Approved
          </button>
          <button @click="statusFilter = 'rejected'"
                  :class="statusFilter === 'rejected' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold' : 'text-slate-400 hover:text-white'"
                  class="px-3 py-1.5 rounded-md transition">
            Rejected
          </button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full sm:w-72">
          <input v-model="searchQuery"
                 type="text"
                 placeholder="Search trader, email, or 0x address..."
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-2 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-cyan-500 text-xs font-mono" />
          <span class="absolute left-2.5 top-2.5 text-slate-500">🔍</span>
          <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-white">✕</button>
        </div>
      </div>

      <!-- Main Withdrawals Roster Table -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl text-xs">
        <div class="px-5 py-3.5 border-b border-slate-800 bg-slate-950/40 flex justify-between items-center">
          <h2 class="font-bold text-slate-100 text-sm flex items-center space-x-2">
            <span>Withdrawal Requests Queue</span>
            <span class="text-slate-500 font-mono text-xs font-normal">({{ filteredWithdrawals.length }} shown)</span>
          </h2>
          <span class="text-[11px] text-slate-400 font-mono">Network: BEP-20 (BNB Smart Chain)</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left font-mono">
            <thead>
              <tr class="text-slate-400 border-b border-slate-800 bg-slate-950 text-[11px] font-sans font-semibold">
                <th class="py-3 px-4">ID</th>
                <th class="py-3 px-4">Trader Account</th>
                <th class="py-3 px-4">Amount</th>
                <th class="py-3 px-4">Channel & Destination</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Ref / TxID</th>
                <th class="py-3 px-4">Requested Date</th>
                <th class="py-3 px-4">Date Paid</th>
                <th class="py-3 px-4 text-right">Admin Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr v-for="w in filteredWithdrawals" :key="w.id" class="hover:bg-slate-800/30 transition">
                <td class="py-3.5 px-4 font-bold text-slate-400">#{{ w.id }}</td>
                
                <!-- Trader info -->
                <td class="py-3.5 px-4 font-sans">
                  <div class="font-bold text-white">{{ w.user?.name || 'Unknown' }}</div>
                  <div class="text-[11px] text-slate-400 font-mono">{{ w.user?.email }}</div>
                </td>

                <!-- Amount -->
                <td class="py-3.5 px-4 font-bold text-emerald-400">
                  <div>${{ formatPrice(w.amount) }} {{ w.currency }}</div>
                  <div v-if="w.amount_kes" class="text-[10px] text-slate-400 font-normal">
                    ≈ KES {{ Number(w.amount_kes).toLocaleString() }}
                  </div>
                </td>

                <!-- Channel & Destination -->
                <td class="py-3.5 px-4">
                  <!-- M-Pesa destination -->
                  <div v-if="w.payment_method === 'mpesa'" class="space-y-0.5">
                    <div class="flex items-center space-x-1.5">
                      <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-1.5 py-0.2 rounded font-bold uppercase text-[9px]">
                        🟢 M-Pesa
                      </span>
                      <span class="text-white font-mono font-bold">{{ w.mpesa_phone }}</span>
                      <button @click="copyText(w.mpesa_phone)" class="text-slate-500 hover:text-emerald-400 text-[10px]" title="Copy Phone">
                        📋
                      </button>
                    </div>
                    <div v-if="w.mpesa_name" class="text-[10px] text-slate-400 font-sans">
                      {{ w.mpesa_name }} <span v-if="w.mpesa_type === 'paybill'">({{ w.mpesa_account_number }})</span>
                    </div>
                  </div>

                  <!-- BEP20 destination -->
                  <div v-else class="space-y-0.5">
                    <div class="flex items-center space-x-1.5">
                      <span class="bg-amber-500/15 text-amber-400 border border-amber-500/30 px-1.5 py-0.2 rounded font-bold uppercase text-[9px]">
                        🟡 BEP-20
                      </span>
                      <span class="text-slate-300 font-mono truncate max-w-[130px]" :title="w.bep20_address">
                        {{ w.bep20_address }}
                      </span>
                      <button v-if="w.bep20_address" @click="copyText(w.bep20_address)" class="text-slate-500 hover:text-cyan-400 p-0.5" title="Copy Address">
                        📋
                      </button>
                      <a v-if="w.bep20_address" :href="'https://bscscan.com/address/' + w.bep20_address" target="_blank" class="text-slate-500 hover:text-cyan-400 text-[10px]" title="View on BscScan">
                        ↗
                      </a>
                    </div>
                  </div>
                </td>

                <!-- Status -->
                <td class="py-3.5 px-4">
                  <span v-if="w.status === 'approved'" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]">
                    ✓ APPROVED
                  </span>
                  <span v-else-if="w.status === 'rejected'" class="bg-rose-500/10 text-rose-400 border border-rose-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px]" :title="w.admin_notes">
                    ✕ REJECTED
                  </span>
                  <span v-else class="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded font-bold uppercase text-[10px] animate-pulse">
                    ⏳ PENDING
                  </span>
                </td>

                <!-- TxHash / BscScan -->
                <td class="py-3.5 px-4">
                  <div v-if="w.tx_hash">
                    <a :href="'https://bscscan.com/tx/' + w.tx_hash" target="_blank" class="text-cyan-400 hover:underline truncate inline-block max-w-[130px]" :title="w.tx_hash">
                      {{ w.tx_hash }} ↗
                    </a>
                  </div>
                  <span v-else class="text-slate-600 text-[11px]">—</span>
                </td>

                <!-- Requested Date -->
                <td class="py-3.5 px-4 text-slate-400 text-[11px] whitespace-nowrap">
                  {{ formatDate(w.created_at) }}
                </td>

                <!-- Date Paid -->
                <td class="py-3.5 px-4 whitespace-nowrap">
                  <div v-if="w.paid_at" class="flex items-center space-x-1.5">
                    <span class="text-emerald-400 font-bold text-[11px] flex items-center space-x-1">
                      <span>✓</span>
                      <span>{{ formatDate(w.paid_at) }}</span>
                    </span>
                    <button @click="openEditPaidModal(w)" class="text-slate-500 hover:text-cyan-400 p-0.5 text-xs transition" title="Edit Date Paid">
                      ✏️
                    </button>
                  </div>
                  <div v-else-if="w.status === 'approved'" class="flex items-center space-x-1.5">
                    <span class="text-slate-500 italic text-[11px]">—</span>
                    <button @click="openEditPaidModal(w)" class="text-cyan-400 hover:text-cyan-300 font-sans font-bold text-[11px] underline">
                      + Set Paid Date
                    </button>
                  </div>
                  <div v-else-if="w.status === 'rejected'" class="text-slate-600 italic text-[11px]">
                    Refunded
                  </div>
                  <div v-else class="flex items-center space-x-1 text-slate-500 text-[11px] italic">
                    <span>Pending</span>
                    <button @click="openApproveModal(w)" class="text-emerald-400 hover:underline font-sans not-italic text-[10px] ml-1">
                      (Pay)
                    </button>
                  </div>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-4 text-right">
                  <div v-if="w.status === 'pending'" class="inline-flex items-center space-x-1.5 font-sans font-bold">
                    <button @click="openApproveModal(w)"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs transition shadow flex items-center space-x-1">
                       <span>✓</span>
                      <span>Allow / Complete</span>
                    </button>
                    <button @click="openRejectModal(w)"
                            class="bg-rose-600/80 hover:bg-rose-600 text-white px-2.5 py-1.5 rounded-lg text-xs transition">
                      ✕ Reject
                    </button>
                  </div>
                  <div v-else class="inline-flex items-center space-x-2 text-slate-400 text-[11px] font-sans">
                    <span>Processed</span>
                    <button @click="openEditPaidModal(w)" class="text-cyan-400 hover:text-cyan-300 font-bold underline text-[11px]">
                      Details
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredWithdrawals.length === 0">
                <td colspan="9" class="py-12 text-center text-slate-500 font-sans">
                  No withdrawal requests found matching your current filter.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- APPROVE / COMPLETE MODAL -->
    <div v-if="approvingWithdrawal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="text-sm font-bold text-white flex items-center space-x-2">
            <span class="text-emerald-400">✓</span>
            <span>Approve &amp; Complete Withdrawal #{{ approvingWithdrawal.id }}</span>
          </h3>
          <button @click="approvingWithdrawal = null" class="text-slate-500 hover:text-slate-300 text-base">✕</button>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3.5 space-y-2 font-mono text-[11px]">
          <div class="flex justify-between">
            <span class="text-slate-400">Trader:</span>
            <span class="text-white font-bold">{{ approvingWithdrawal.user?.name }} ({{ approvingWithdrawal.user?.email }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Amount:</span>
            <div class="text-right">
              <span class="text-emerald-400 font-bold">${{ formatPrice(approvingWithdrawal.amount) }} USDT</span>
              <span v-if="approvingWithdrawal.amount_kes" class="text-slate-400 block text-[10px]">
                ≈ KES {{ Number(approvingWithdrawal.amount_kes).toLocaleString() }}
              </span>
            </div>
          </div>
          <div class="flex justify-between items-start">
            <span class="text-slate-400">Send To:</span>
            <div v-if="approvingWithdrawal.payment_method === 'mpesa'" class="text-right font-sans">
              <span class="text-emerald-400 font-bold font-mono">{{ approvingWithdrawal.mpesa_phone }}</span>
              <span class="text-slate-300 block text-[11px]">{{ approvingWithdrawal.mpesa_name }}</span>
              <span v-if="approvingWithdrawal.mpesa_type === 'paybill'" class="text-slate-400 block text-[10px] font-mono">
                Paybill Acc: {{ approvingWithdrawal.mpesa_account_number }}
              </span>
            </div>
            <span v-else class="text-cyan-400 break-all max-w-[240px] text-right">{{ approvingWithdrawal.bep20_address }}</span>
          </div>
        </div>

        <div class="space-y-2">
          <label class="block text-slate-300 font-medium">
            Date &amp; Time Paid <span class="text-emerald-400">*</span>
          </label>
          <input v-model="approvalPaidAt"
                 type="datetime-local"
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-xs" />
          <p class="text-[11px] text-slate-500">
            Recorded in the database and shown under the "Date Paid" column. Defaults to current time.
          </p>
        </div>

        <div class="space-y-2">
          <label class="block text-slate-300 font-medium">
            {{ approvingWithdrawal.payment_method === 'mpesa' ? 'M-Pesa Reference / Confirmation Code — Optional' : 'BscScan Transaction Hash (TxID) — Optional' }}
          </label>
          <input v-model="approvalTxHash"
                 type="text"
                 :placeholder="approvingWithdrawal.payment_method === 'mpesa' ? 'e.g. RK849204J2 (M-Pesa SMS Code)' : '0x... (paste transaction hash after sending on BSC)'"
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 font-mono text-xs" />
          <p class="text-[11px] text-slate-500">
            {{ approvingWithdrawal.payment_method === 'mpesa' ? 'Enter the Safaricom M-Pesa transaction code for trader reference.' : 'Pastes the blockchain transaction hash for the trader to view directly on BscScan.' }}
          </p>
        </div>

        <div class="space-y-2">
          <label class="block text-slate-300 font-medium">Admin Notes (Optional)</label>
          <input v-model="approvalNotes"
                 type="text"
                 placeholder="e.g. Sent via Binance Corporate Hot Wallet"
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500 text-xs" />
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="approvingWithdrawal = null" type="button" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
            Cancel
          </button>
          <button @click="submitApprove" :disabled="submitting" type="button" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition shadow disabled:opacity-50">
            <span v-if="submitting">Completing...</span>
            <span v-else>Confirm &amp; Mark Complete →</span>
          </button>
        </div>
      </div>
    </div>

    <!-- EDIT / SET DATE PAID MODAL -->
    <div v-if="editingPaidWithdrawal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="text-sm font-bold text-white flex items-center space-x-2">
            <span class="text-cyan-400">📅</span>
            <span>Update Payment Details — #{{ editingPaidWithdrawal.id }}</span>
          </h3>
          <button @click="editingPaidWithdrawal = null" class="text-slate-500 hover:text-slate-300 text-base">✕</button>
        </div>

        <div class="bg-slate-950 border border-slate-800 rounded-xl p-3.5 space-y-1.5 font-mono text-[11px]">
          <div class="flex justify-between">
            <span class="text-slate-400">Trader:</span>
            <span class="text-white font-bold">{{ editingPaidWithdrawal.user?.name }} ({{ editingPaidWithdrawal.user?.email }})</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Amount:</span>
            <span class="text-emerald-400 font-bold">${{ formatPrice(editingPaidWithdrawal.amount) }} USDT</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Status:</span>
            <span class="uppercase font-bold" :class="editingPaidWithdrawal.status === 'approved' ? 'text-emerald-400' : 'text-amber-400'">
              {{ editingPaidWithdrawal.status }}
            </span>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="block text-slate-300 font-medium">Date &amp; Time Paid</label>
          <input v-model="editPaidAt"
                 type="datetime-local"
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-cyan-500 font-mono text-xs" />
          <p class="text-[11px] text-slate-500">
            Sets the date and time when payout funds reached the trader.
          </p>
        </div>

        <div class="space-y-1.5">
          <label class="block text-slate-300 font-medium">BscScan TxID (Optional)</label>
          <input v-model="editTxHash"
                 type="text"
                 placeholder="0x..."
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-cyan-500 font-mono text-xs" />
        </div>

        <div class="space-y-1.5">
          <label class="block text-slate-300 font-medium">Admin Notes (Optional)</label>
          <input v-model="editNotes"
                 type="text"
                 placeholder="Payment remarks or reference..."
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-cyan-500 text-xs" />
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="editingPaidWithdrawal = null" type="button" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
            Cancel
          </button>
          <button @click="submitUpdatePaidDate" :disabled="submitting" type="button" class="flex-1 bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-2.5 rounded-xl transition shadow disabled:opacity-50">
            <span v-if="submitting">Saving...</span>
            <span v-else>Save Payment Details →</span>
          </button>
        </div>
      </div>
    </div>

    <!-- REJECT / REFUND MODAL -->
    <div v-if="rejectingWithdrawal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3">
          <h3 class="text-sm font-bold text-white flex items-center space-x-2">
            <span class="text-rose-400">✕</span>
            <span>Reject Withdrawal #{{ rejectingWithdrawal.id }}</span>
          </h3>
          <button @click="rejectingWithdrawal = null" class="text-slate-500 hover:text-slate-300 text-base">✕</button>
        </div>

        <div class="bg-rose-500/10 border border-rose-500/30 rounded-xl p-3 text-rose-300 space-y-1">
          <div class="font-bold flex items-center space-x-1">
            <span>🛡️</span>
            <span>Automatic Wallet Refund:</span>
          </div>
          <p class="text-[11px] leading-relaxed text-slate-300">
            Rejecting this withdrawal will automatically refund <strong class="text-emerald-400 font-mono">${{ formatPrice(rejectingWithdrawal.amount) }} USDT</strong> back into <strong>{{ rejectingWithdrawal.user?.name }}</strong>'s wallet.
          </p>
        </div>

        <div class="space-y-2">
          <label class="block text-slate-300 font-medium">Rejection Reason</label>
          <input v-model="rejectionReason"
                 type="text"
                 placeholder="e.g. Invalid BEP20 address, please relink and re-request."
                 class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-slate-100 placeholder-slate-600 focus:outline-none focus:border-rose-500 text-xs" />
        </div>

        <div class="flex items-center space-x-3 pt-2">
          <button @click="rejectingWithdrawal = null" type="button" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
            Cancel
          </button>
          <button @click="submitReject" :disabled="submitting" type="button" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold py-2.5 rounded-xl transition shadow disabled:opacity-50">
            <span v-if="submitting">Rejecting...</span>
            <span v-else>Reject &amp; Refund Funds</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
  withdrawals: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
  currentStatus: { type: String, default: 'all' },
});

const toastRef = ref(null);
const statusFilter = ref(props.currentStatus || 'all');
const searchQuery = ref('');

// Modals
const approvingWithdrawal = ref(null);
const approvalTxHash = ref('');
const approvalNotes = ref('');
const approvalPaidAt = ref('');

// Edit Paid Date modal
const editingPaidWithdrawal = ref(null);
const editPaidAt = ref('');
const editTxHash = ref('');
const editNotes = ref('');

const rejectingWithdrawal = ref(null);
const rejectionReason = ref('');

const submitting = ref(false);

const filteredWithdrawals = computed(() => {
  return props.withdrawals.filter(w => {
    // Status filter
    if (statusFilter.value !== 'all' && w.status !== statusFilter.value) {
      return false;
    }
    // Search query filter
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const name = (w.user?.name || '').toLowerCase();
      const email = (w.user?.email || '').toLowerCase();
      const addr = (w.bep20_address || '').toLowerCase();
      const tx = (w.tx_hash || '').toLowerCase();
      const id = String(w.id);
      return name.includes(q) || email.includes(q) || addr.includes(q) || tx.includes(q) || id.includes(q);
    }
    return true;
  });
});

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleString();
}

function getNowLocalIso() {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  return new Date(now.getTime() - offset).toISOString().slice(0, 16);
}

function toLocalIso(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const offset = d.getTimezoneOffset() * 60000;
  return new Date(d.getTime() - offset).toISOString().slice(0, 16);
}

function copyText(str) {
  if (!str) return;
  try {
    navigator.clipboard.writeText(str);
    toastRef.value?.show('Address copied to clipboard!', 'info');
  } catch (e) {}
}

function openApproveModal(w) {
  approvingWithdrawal.value = w;
  approvalTxHash.value = '';
  approvalNotes.value = '';
  approvalPaidAt.value = getNowLocalIso();
}

function submitApprove() {
  if (!approvingWithdrawal.value) return;
  submitting.value = true;

  router.post(`/admin/withdrawals/${approvingWithdrawal.value.id}/approve`, {
    tx_hash: approvalTxHash.value,
    admin_notes: approvalNotes.value,
    paid_at: approvalPaidAt.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show('Withdrawal marked as approved & completed!', 'success');
      approvingWithdrawal.value = null;
      submitting.value = false;
    },
    onError: (err) => {
      toastRef.value?.show(err.message || 'Failed to approve withdrawal', 'error');
      submitting.value = false;
    }
  });
}

function openEditPaidModal(w) {
  editingPaidWithdrawal.value = w;
  editPaidAt.value = w.paid_at ? toLocalIso(w.paid_at) : (w.approved_at ? toLocalIso(w.approved_at) : getNowLocalIso());
  editTxHash.value = w.tx_hash || '';
  editNotes.value = w.admin_notes || '';
}

function submitUpdatePaidDate() {
  if (!editingPaidWithdrawal.value) return;
  submitting.value = true;

  router.post(`/admin/withdrawals/${editingPaidWithdrawal.value.id}/update-paid-date`, {
    paid_at: editPaidAt.value || null,
    tx_hash: editTxHash.value || null,
    admin_notes: editNotes.value || null,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show('Payment date & details updated successfully!', 'success');
      editingPaidWithdrawal.value = null;
      submitting.value = false;
    },
    onError: (err) => {
      toastRef.value?.show(err.message || 'Failed to update payment date', 'error');
      submitting.value = false;
    }
  });
}

function openRejectModal(w) {
  rejectingWithdrawal.value = w;
  rejectionReason.value = '';
}

function submitReject() {
  if (!rejectingWithdrawal.value) return;
  submitting.value = true;

  router.post(`/admin/withdrawals/${rejectingWithdrawal.value.id}/reject`, {
    reason: rejectionReason.value,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastRef.value?.show('Withdrawal rejected and funds refunded to trader.', 'info');
      rejectingWithdrawal.value = null;
      submitting.value = false;
    },
    onError: (err) => {
      toastRef.value?.show(err.message || 'Failed to reject withdrawal', 'error');
      submitting.value = false;
    }
  });
}
</script>
