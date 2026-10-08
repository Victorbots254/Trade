<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] font-sans select-none flex flex-col transition-colors duration-200 pb-16">
    <ToastNotification ref="toastRef" />

    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="user"
      :markets="markets"
      :wallets="wallets"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <!-- ========================================================================= -->
      <!-- 1. HERO TRADER IDENTITY BANNER                                            -->
      <!-- ========================================================================= -->
      <div class="bg-gradient-to-r from-[#181a20] via-[#1e2329] to-[#181a20] border border-[#2b3139] rounded-2xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        <!-- Background Ambient Accent Glow -->
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-[#f0b90b]/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-56 h-56 bg-[#0ecb81]/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <!-- Left: Avatar + Details -->
          <div class="flex items-start sm:items-center space-x-4 sm:space-x-5">
            <!-- Avatar Circle -->
            <div class="relative shrink-0">
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[#f0b90b] to-[#d4a30b] p-0.5 shadow-xl">
                <div class="w-full h-full bg-[#181a20] rounded-[14px] flex items-center justify-center">
                  <span class="text-xl sm:text-2xl font-black text-[#f0b90b] tracking-wider">{{ userInitials }}</span>
                </div>
              </div>
              <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#0ecb81] border-2 border-[#181a20] rounded-full flex items-center justify-center text-[10px] text-black font-black" title="Verified Trader">
                ✓
              </div>
            </div>

            <!-- Name, Email, Tags -->
            <div class="space-y-1.5">
              <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">{{ user?.name }}</h1>
                <span class="bg-[#0ecb81]/15 text-[#0ecb81] border border-[#0ecb81]/30 text-[10px] font-bold px-2 py-0.5 rounded-full flex items-center space-x-1">
                  <span>✓</span>
                  <span>Verified Account</span>
                </span>
                <span v-if="user?.is_admin" class="bg-[#f0b90b]/20 text-[#f0b90b] border border-[#f0b90b]/40 text-[10px] font-bold px-2 py-0.5 rounded-full">
                  🛡️ Administrator
                </span>
                <span v-else-if="user?.is_p2p_merchant" class="bg-[#38bdf8]/15 text-[#38bdf8] border border-[#38bdf8]/30 text-[10px] font-bold px-2 py-0.5 rounded-full">
                  ⚡ P2P Merchant
                </span>
                <span v-else class="bg-[#848e9c]/15 text-[#848e9c] border border-[#848e9c]/30 text-[10px] font-bold px-2 py-0.5 rounded-full">
                  VIP Tier 1
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#848e9c]">
                <div class="flex items-center space-x-1.5">
                  <span>✉️</span>
                  <span class="text-slate-300 font-mono">{{ user?.email }}</span>
                </div>

                <div class="flex items-center space-x-1.5">
                  <span>🆔</span>
                  <span class="font-mono text-slate-300">UID: <span class="text-[#f0b90b] font-bold">#{{ user?.id }}</span></span>
                  <button @click="copyText(String(user?.id))" class="text-[10px] text-[#f0b90b] hover:underline px-1 py-0.5 rounded bg-[#f0b90b]/10 ml-0.5">
                    {{ copiedVal === String(user?.id) ? 'Copied!' : 'Copy' }}
                  </button>
                </div>

                <div class="flex items-center space-x-1">
                  <span>📅</span>
                  <span>Joined {{ formatDateShort(user?.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Quick Actions -->
          <div class="flex flex-wrap items-center gap-2.5 pt-2 lg:pt-0">
            <button 
              @click="showDepositModal = true"
              class="bg-[#0ecb81] hover:bg-[#0bb573] text-[#1e2329] font-black text-xs px-4 py-2.5 rounded-xl transition shadow-lg flex items-center space-x-1.5">
              <span>+</span>
              <span>Deposit Funds</span>
            </button>

            <Link 
              href="/withdraw"
              class="bg-[#181a20] hover:bg-[#2b3139] border border-[#2b3139] text-white font-bold text-xs px-4 py-2.5 rounded-xl transition flex items-center space-x-1.5">
              <span>Withdraw ↗</span>
            </Link>

            <button 
              @click="openEditProfileModal"
              class="bg-[#181a20] hover:bg-[#2b3139] border border-[#2b3139] text-[#848e9c] hover:text-white font-bold text-xs px-3.5 py-2.5 rounded-xl transition flex items-center space-x-1.5">
              <span>⚙️ Settings</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- 2. STREAMLINED BALANCE OVERVIEW CARDS                                     -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- CARD 1: REAL LIVE AVAILABLE USDT BALANCE -->
        <div class="bg-[#181a20] border border-[#2b3139] hover:border-[#0ecb81]/50 rounded-2xl p-5 sm:p-6 shadow-xl transition relative overflow-hidden group flex flex-col justify-between space-y-4">
          <div class="absolute -right-4 -top-4 w-20 h-20 bg-[#0ecb81]/10 rounded-full blur-xl pointer-events-none group-hover:bg-[#0ecb81]/20 transition"></div>
          
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-[#848e9c] text-xs font-semibold uppercase tracking-wider">Available USDT Balance</span>
              <span class="bg-[#0ecb81]/15 text-[#0ecb81] border border-[#0ecb81]/30 px-2 py-0.5 rounded text-[10px] font-bold">
                🟢 REAL AVAILABLE
              </span>
            </div>

            <div class="flex items-baseline space-x-2">
              <span class="text-3xl sm:text-4xl font-black text-[#0ecb81] font-mono tracking-tight">
                ${{ formatPrice(usdtBalance) }}
              </span>
              <span class="text-xs text-[#848e9c] font-bold font-mono">USDT</span>
            </div>

            <p class="text-xs text-[#848e9c] leading-relaxed">
              Real deposited balance ready for Spot trading, Binary Options, and P2P exchange.
            </p>
          </div>

          <div class="pt-3 border-t border-[#2b3139] flex items-center justify-between gap-2">
            <button 
              @click="showDepositModal = true"
              class="text-xs font-bold text-[#0ecb81] hover:text-white bg-[#0ecb81]/10 hover:bg-[#0ecb81]/20 px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
              <span>+ Add Funds</span>
            </button>
            <Link 
              href="/terminal"
              class="text-xs font-bold text-[#848e9c] hover:text-[#f0b90b] transition flex items-center space-x-1">
              <span>Trade Spot →</span>
            </Link>
          </div>
        </div>

        <!-- CARD 2: MMF LOCKED FUNDS (ONLY REFLECTS FUNDS LOCKED FOR MMF) -->
        <div class="bg-[#181a20] border border-[#2b3139] hover:border-[#f0b90b]/50 rounded-2xl p-5 sm:p-6 shadow-xl transition relative overflow-hidden group flex flex-col justify-between space-y-4">
          <div class="absolute -right-4 -top-4 w-20 h-20 bg-[#f0b90b]/10 rounded-full blur-xl pointer-events-none group-hover:bg-[#f0b90b]/20 transition"></div>

          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-[#848e9c] text-xs font-semibold uppercase tracking-wider">MMF Locked Funds</span>
              <span class="bg-[#f0b90b]/15 text-[#f0b90b] border border-[#f0b90b]/30 px-2 py-0.5 rounded text-[10px] font-bold flex items-center space-x-1">
                <span>🔒</span>
                <span>18% MONTHLY YIELD</span>
              </span>
            </div>

            <div class="flex items-baseline space-x-2">
              <span class="text-3xl sm:text-4xl font-black text-[#f0b90b] font-mono tracking-tight">
                ${{ formatPrice(mmfLockedBalance) }}
              </span>
              <span class="text-xs text-[#848e9c] font-bold font-mono">USDT</span>
            </div>

            <p class="text-xs text-[#848e9c] leading-relaxed">
              <span v-if="mmfLockedBalance > 0" class="text-slate-200">
                <strong>{{ mmf_active_count }}</strong> active 30-day fixed investment{{ mmf_active_count === 1 ? '' : 's' }} earning 18% guaranteed yield.
              </span>
              <span v-else>
                Capital locked strictly in the Money Market Fund (MMF) pool earning 18% monthly.
              </span>
            </p>
          </div>

          <div class="pt-3 border-t border-[#2b3139] flex items-center justify-between gap-2">
            <div v-if="mmf_interest_earned > 0" class="text-[11px] text-[#0ecb81] font-mono font-bold">
              +${{ formatPrice(mmf_interest_earned) }} Earned
            </div>
            <div v-else class="text-[11px] text-[#848e9c] font-mono">
              30-Day Cycle
            </div>

            <Link 
              href="/monthly-interests"
              class="text-xs font-bold text-[#f0b90b] hover:text-white bg-[#f0b90b]/10 hover:bg-[#f0b90b]/20 px-3 py-1.5 rounded-lg transition flex items-center space-x-1">
              <span>{{ mmfLockedBalance > 0 ? 'Manage MMF ↗' : 'Earn 18% Yield ↗' }}</span>
            </Link>
          </div>
        </div>

        <!-- CARD 3: DEMO PRACTICE ACCOUNT BALANCE -->
        <div class="bg-[#181a20] border border-[#2b3139] hover:border-[#38bdf8]/50 rounded-2xl p-5 sm:p-6 shadow-xl transition relative overflow-hidden group flex flex-col justify-between space-y-4">
          <div class="absolute -right-4 -top-4 w-20 h-20 bg-[#38bdf8]/10 rounded-full blur-xl pointer-events-none group-hover:bg-[#38bdf8]/20 transition"></div>

          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-[#848e9c] text-xs font-semibold uppercase tracking-wider">Demo Practice Account</span>
              <span class="bg-[#38bdf8]/15 text-[#38bdf8] border border-[#38bdf8]/30 px-2 py-0.5 rounded text-[10px] font-bold">
                🎮 VIRTUAL SANDBOX
              </span>
            </div>

            <div class="flex items-baseline space-x-2">
              <span class="text-3xl sm:text-4xl font-black text-[#38bdf8] font-mono tracking-tight">
                ${{ formatPrice(demoBalance) }}
              </span>
              <span class="text-xs text-[#848e9c] font-bold font-mono">USDT</span>
            </div>

            <p class="text-xs text-[#848e9c] leading-relaxed">
              Virtual sandbox funds for testing options strategies and spot trading with zero financial risk.
            </p>
          </div>

          <div class="pt-3 border-t border-[#2b3139] flex items-center justify-between gap-2">
            <button 
              @click="resetDemoBalance" 
              :disabled="resetting"
              class="text-xs font-bold text-[#38bdf8] hover:text-white bg-[#38bdf8]/10 hover:bg-[#38bdf8]/20 px-3 py-1.5 rounded-lg transition flex items-center space-x-1.5">
              <svg v-if="resetting" class="animate-spin h-3 w-3 text-[#38bdf8]" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>↻ Reset to $10k</span>
            </button>

            <Link 
              href="/trade/options/BTC_USDT"
              class="text-xs font-bold text-[#848e9c] hover:text-[#38bdf8] transition flex items-center space-x-1">
              <span>Practice Options →</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- 3. COMBINED PORTFOLIO STRIP                                               -->
      <!-- ========================================================================= -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-xl px-5 py-3.5 flex flex-wrap items-center justify-between gap-4 text-xs">
        <div class="flex items-center space-x-3">
          <span class="text-[#848e9c] font-medium">Total Live Portfolio (Available + MMF):</span>
          <span class="text-white font-mono font-bold text-sm">${{ formatPrice(totalPortfolioValue) }} USDT</span>
        </div>

        <div class="flex items-center space-x-6 text-[#848e9c]">
          <div class="flex items-center space-x-1.5">
            <span>📈 MMF Yield:</span>
            <span class="text-[#0ecb81] font-bold font-mono">18.0% / month</span>
          </div>

          <div class="flex items-center space-x-1.5">
            <span>🛡️ Wallet Custody:</span>
            <span class="text-slate-300 font-bold">BEP-20 Smart Contract</span>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- 4. ACCOUNT INFORMATION & CRYPTO SECURITY PANELS                          -->
      <!-- ========================================================================= -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- PANEL A: IDENTITY & ACCOUNT SETTINGS -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-6 shadow-xl space-y-5">
          <div class="flex items-center justify-between border-b border-[#2b3139] pb-3.5">
            <div class="flex items-center space-x-2">
              <span class="text-[#f0b90b] text-base">👤</span>
              <h2 class="font-bold text-white text-sm">Account Identity & Information</h2>
            </div>
            <button 
              @click="openEditProfileModal"
              class="text-xs text-[#f0b90b] hover:underline font-bold flex items-center space-x-1">
              <span>Edit Details ✏️</span>
            </button>
          </div>

          <div class="space-y-3.5">
            <!-- Full Name Row -->
            <div class="flex items-center justify-between p-3 bg-[#0b0e11] border border-[#2b3139] rounded-xl text-xs">
              <div>
                <span class="text-[#848e9c] block text-[11px]">Trader Full Name</span>
                <span class="font-bold text-white text-sm">{{ user?.name }}</span>
              </div>
              <span class="text-[10px] text-[#0ecb81] bg-[#0ecb81]/10 px-2 py-0.5 rounded font-bold">Active</span>
            </div>

            <!-- Email Address Row -->
            <div class="flex items-center justify-between p-3 bg-[#0b0e11] border border-[#2b3139] rounded-xl text-xs">
              <div>
                <span class="text-[#848e9c] block text-[11px]">Primary Email Address</span>
                <span class="font-mono text-white text-xs">{{ user?.email }}</span>
              </div>
              <span class="text-[10px] text-[#0ecb81] bg-[#0ecb81]/10 px-2 py-0.5 rounded font-bold">Verified ✓</span>
            </div>

            <!-- Account UID Row -->
            <div class="flex items-center justify-between p-3 bg-[#0b0e11] border border-[#2b3139] rounded-xl text-xs">
              <div>
                <span class="text-[#848e9c] block text-[11px]">Trader UID (Account ID)</span>
                <span class="font-mono font-bold text-[#f0b90b] text-xs">#{{ user?.id }}</span>
              </div>
              <button @click="copyText(String(user?.id))" class="text-xs text-[#848e9c] hover:text-white px-2 py-1 rounded bg-[#181a20] border border-[#2b3139]">
                {{ copiedVal === String(user?.id) ? 'Copied!' : 'Copy UID' }}
              </button>
            </div>

            <!-- Registration Date Row -->
            <div class="flex items-center justify-between p-3 bg-[#0b0e11] border border-[#2b3139] rounded-xl text-xs">
              <div>
                <span class="text-[#848e9c] block text-[11px]">Registration Date & Time</span>
                <span class="font-mono text-slate-300 text-xs">{{ formatDate(user?.created_at) }}</span>
              </div>
              <span class="text-[11px] text-[#848e9c]">Member</span>
            </div>

            <!-- Terms & Compliance -->
            <div class="flex items-center justify-between p-3 bg-[#0b0e11] border border-[#2b3139] rounded-xl text-xs">
              <div>
                <span class="text-[#848e9c] block text-[11px]">Platform Agreement</span>
                <span class="text-slate-300 text-xs">Terms of Service & Risk Disclosure</span>
              </div>
              <span class="text-[10px] text-[#0ecb81] bg-[#0ecb81]/10 px-2 py-0.5 rounded font-bold">Agreed</span>
            </div>
          </div>
        </div>

        <!-- PANEL B: PAYOUT CHANNELS & CRYPTO SECURITY -->
        <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-6 shadow-xl space-y-5">
          <div class="flex items-center justify-between border-b border-[#2b3139] pb-3.5">
            <div class="flex items-center space-x-2">
              <span class="text-[#0ecb81] text-base">💳</span>
              <h2 class="font-bold text-white text-sm">Payout & Crypto Security Hub</h2>
            </div>
            <span class="text-[11px] text-[#848e9c]">BEP-20 Verified</span>
          </div>

          <div class="space-y-4">
            <!-- Binance BEP-20 Wallet Address -->
            <div class="p-4 bg-[#0b0e11] border rounded-xl space-y-2.5 transition"
                 :class="user?.bep20_address ? 'border-[#2b3139]' : 'border-[#f0b90b]/40 bg-[#f0b90b]/5'">
              <div class="flex items-center justify-between text-xs">
                <span class="text-[#848e9c] font-semibold flex items-center space-x-1.5">
                  <span>🟡</span>
                  <span>Binance Smart Chain (BEP-20) Withdrawal Address</span>
                </span>
                <span v-if="user?.bep20_address" class="text-[10px] text-[#0ecb81] bg-[#0ecb81]/10 px-2 py-0.5 rounded font-bold">
                  Linked ✓
                </span>
                <span v-else class="text-[10px] text-[#f0b90b] bg-[#f0b90b]/10 px-2 py-0.5 rounded font-bold">
                  Action Required
                </span>
              </div>

              <!-- Address Display or Notice -->
              <div v-if="user?.bep20_address" class="flex items-center justify-between gap-2 bg-[#181a20] border border-[#2b3139] rounded-lg p-2.5">
                <span class="font-mono text-xs text-white truncate select-all">{{ user.bep20_address }}</span>
                <div class="flex items-center space-x-1.5 shrink-0">
                  <button @click="copyText(user.bep20_address)" class="text-xs text-[#f0b90b] hover:text-white bg-[#f0b90b]/10 px-2 py-1 rounded font-bold">
                    {{ copiedVal === user.bep20_address ? 'Copied!' : 'Copy' }}
                  </button>
                  <button @click="openBep20Modal" class="text-xs text-[#848e9c] hover:text-white bg-[#2b3139] px-2 py-1 rounded font-bold">
                    Change
                  </button>
                </div>
              </div>

              <div v-else class="space-y-2">
                <p class="text-xs text-[#848e9c] leading-relaxed">
                  Link your personal BEP-20 wallet address (e.g. from Binance, Trust Wallet, MetaMask) to enable automated USDT crypto withdrawals.
                </p>
                <button 
                  @click="openBep20Modal"
                  class="w-full bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-black py-2 px-3 rounded-lg text-xs transition flex items-center justify-center space-x-1.5 shadow">
                  <span>+ Link BEP-20 Withdrawal Address</span>
                </button>
              </div>
            </div>

            <!-- P2P Trading Profile Status -->
            <div class="p-4 bg-[#0b0e11] border border-[#2b3139] rounded-xl space-y-3">
              <div class="flex items-center justify-between text-xs">
                <span class="text-[#848e9c] font-semibold flex items-center space-x-1.5">
                  <span>🤝</span>
                  <span>P2P Trading Status</span>
                </span>
                <span v-if="user?.is_p2p_merchant" class="text-[10px] text-[#38bdf8] bg-[#38bdf8]/10 px-2 py-0.5 rounded font-bold">
                  Verified Merchant
                </span>
                <span v-else class="text-[10px] text-[#848e9c] bg-[#848e9c]/10 px-2 py-0.5 rounded font-bold">
                  Standard Trader
                </span>
              </div>

              <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="bg-[#181a20] p-2.5 rounded-lg border border-[#2b3139]">
                  <span class="text-[#848e9c] text-[10px] block">Merchant Name</span>
                  <span class="font-bold text-white text-xs">{{ user?.p2p_merchant_name || user?.name }}</span>
                </div>
                <div class="bg-[#181a20] p-2.5 rounded-lg border border-[#2b3139]">
                  <span class="text-[#848e9c] text-[10px] block">Completed Trades</span>
                  <span class="font-bold text-[#0ecb81] text-xs font-mono">{{ user?.p2p_completed_trades || 0 }} Orders</span>
                </div>
              </div>

              <div class="flex items-center justify-between pt-1 text-xs">
                <Link href="/p2p" class="text-[#f0b90b] hover:underline font-bold">
                  Go to P2P Marketplace →
                </Link>
                <Link v-if="user?.is_p2p_merchant || user?.is_admin" href="/p2p/ads" class="text-slate-400 hover:text-white font-bold">
                  My P2P Ads ↗
                </Link>
              </div>
            </div>

            <!-- Account Security Password -->
            <div class="p-4 bg-[#0b0e11] border border-[#2b3139] rounded-xl flex items-center justify-between text-xs">
              <div>
                <span class="text-white font-bold block">Account Password</span>
                <span class="text-[#848e9c] text-[11px]">Last updated upon registration</span>
              </div>
              <button 
                @click="openEditProfileModal" 
                class="bg-[#181a20] hover:bg-[#2b3139] border border-[#2b3139] text-white px-3 py-1.5 rounded-lg font-bold transition">
                Change Password
              </button>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Bottom Market Ticker Bar -->
    <BottomMarketTicker :markets="markets" />

    <!-- ========================================================================= -->
    <!-- MODAL 1: GLOBAL DEPOSIT MODAL                                             -->
    <!-- ========================================================================= -->
    <DepositModal
      v-if="showDepositModal"
      :custodialAddress="custodialAddress"
      @close="showDepositModal = false"
      @deposit-submitted="handleModalDepositSubmitted"
    />

    <!-- ========================================================================= -->
    <!-- MODAL 2: LINK / UPDATE BEP-20 WALLET ADDRESS                              -->
    <!-- ========================================================================= -->
    <div v-if="showBep20Modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md overflow-hidden shadow-2xl text-xs space-y-4 p-6">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-lg">🟡</span>
            <h3 class="font-bold text-white text-sm">Link BEP-20 Withdrawal Address</h3>
          </div>
          <button @click="showBep20Modal = false" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <p class="text-xs text-[#848e9c] leading-relaxed">
          Enter your Binance Smart Chain (BEP-20) wallet address. Automated crypto withdrawals will be credited directly to this address.
        </p>

        <form @submit.prevent="submitBep20Address" class="space-y-4">
          <div>
            <label class="block text-[#848e9c] font-semibold text-xs mb-1.5">BEP-20 Wallet Address (0x...)</label>
            <input 
              v-model="bep20Input"
              type="text"
              required
              placeholder="0x71C7656EC7ab88b098defB751B7401B5f6d8976F"
              class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white font-mono text-xs focus:border-[#f0b90b] focus:outline-none" />
            <span v-if="bep20Error" class="text-rose-400 text-[11px] block mt-1">{{ bep20Error }}</span>
          </div>

          <div class="flex items-center justify-end space-x-2.5 pt-2 border-t border-[#2b3139]">
            <button 
              type="button" 
              @click="showBep20Modal = false" 
              class="px-4 py-2 rounded-xl text-[#848e9c] hover:text-white hover:bg-[#2b3139] font-bold transition">
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="savingBep20"
              class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-black px-4 py-2 rounded-xl transition flex items-center space-x-1.5">
              <span v-if="savingBep20">Saving...</span>
              <span v-else>Save Wallet Address</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: EDIT PROFILE / CHANGE PASSWORD                                   -->
    <!-- ========================================================================= -->
    <div v-if="showEditProfileModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md overflow-hidden shadow-2xl text-xs space-y-4 p-6">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <div class="flex items-center space-x-2">
            <span class="text-lg">⚙️</span>
            <h3 class="font-bold text-white text-sm">Update Trader Profile & Security</h3>
          </div>
          <button @click="showEditProfileModal = false" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <form @submit.prevent="submitProfileUpdate" class="space-y-4">
          <div>
            <label class="block text-[#848e9c] font-semibold text-xs mb-1.5">Full Name *</label>
            <input 
              v-model="profileForm.name"
              type="text"
              required
              class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2.5 text-white text-xs focus:border-[#0ecb81] focus:outline-none" />
          </div>

          <div class="border-t border-[#2b3139] pt-3 space-y-3">
            <h4 class="font-bold text-slate-200 text-xs">Change Password (Optional)</h4>

            <div>
              <label class="block text-[#848e9c] font-semibold text-[11px] mb-1">Current Password</label>
              <input 
                v-model="profileForm.current_password"
                type="password"
                placeholder="Leave blank if not changing"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2 text-white text-xs focus:border-[#0ecb81] focus:outline-none" />
            </div>

            <div>
              <label class="block text-[#848e9c] font-semibold text-[11px] mb-1">New Password</label>
              <input 
                v-model="profileForm.new_password"
                type="password"
                placeholder="Minimum 8 characters"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2 text-white text-xs focus:border-[#0ecb81] focus:outline-none" />
            </div>

            <div>
              <label class="block text-[#848e9c] font-semibold text-[11px] mb-1">Confirm New Password</label>
              <input 
                v-model="profileForm.new_password_confirmation"
                type="password"
                placeholder="Repeat new password"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3.5 py-2 text-white text-xs focus:border-[#0ecb81] focus:outline-none" />
            </div>
          </div>

          <span v-if="profileError" class="text-rose-400 text-[11px] block">{{ profileError }}</span>

          <div class="flex items-center justify-end space-x-2.5 pt-2 border-t border-[#2b3139]">
            <button 
              type="button" 
              @click="showEditProfileModal = false" 
              class="px-4 py-2 rounded-xl text-[#848e9c] hover:text-white hover:bg-[#2b3139] font-bold transition">
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="savingProfile"
              class="bg-[#0ecb81] hover:bg-[#0bb573] text-[#1e2329] font-black px-4 py-2 rounded-xl transition flex items-center space-x-1.5">
              <span v-if="savingProfile">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import TradingHeader from '@/Components/TradingHeader.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';
import DepositModal from '@/Components/DepositModal.vue';

const props = defineProps({
  user: { type: Object, default: () => ({}) },
  wallets: { type: Array, default: () => [] },
  markets: { type: Array, default: () => [] },
  mmf_locked: { type: Number, default: 0 },
  mmf_active_count: { type: Number, default: 0 },
  mmf_interest_earned: { type: Number, default: 0 },
  custodialAddress: { type: String, default: '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' },
});

const toastRef = ref(null);
const resetting = ref(false);
const showDepositModal = ref(false);

// Wallets and Balances
const usdtWallet = computed(() => (props.wallets || []).find(w => w.currency === 'USDT' && !w.is_demo));
const demoWallet = computed(() => (props.wallets || []).find(w => w.currency === 'USDT' && w.is_demo));

const usdtBalance = computed(() => usdtWallet.value ? parseFloat(usdtWallet.value.available_balance) : 0.00);

// Only reflects funds locked for MMF
const mmfLockedBalance = computed(() => {
  return props.mmf_locked !== undefined ? parseFloat(props.mmf_locked) : 0.00;
});

const demoBalance = computed(() => {
  return demoWallet.value ? parseFloat(demoWallet.value.available_balance) : (props.user?.demo_balance !== undefined ? parseFloat(props.user.demo_balance) : 10000.00);
});

const totalPortfolioValue = computed(() => {
  return (usdtBalance.value + mmfLockedBalance.value).toFixed(2);
});

// User Initials
const userInitials = computed(() => {
  const name = props.user?.name || 'Trader';
  const parts = name.trim().split(' ').filter(Boolean);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.slice(0, 2).toUpperCase();
});

// Copying helper
const copiedVal = ref('');
function copyText(val) {
  if (!val) return;
  try {
    navigator.clipboard.writeText(val);
    copiedVal.value = val;
    setTimeout(() => {
      if (copiedVal.value === val) copiedVal.value = '';
    }, 2000);
  } catch (e) {}
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  return new Date(dateStr).toLocaleString();
}

function formatDateShort(dateStr) {
  if (!dateStr) return 'Recently';
  const d = new Date(dateStr);
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

async function resetDemoBalance() {
  resetting.value = true;
  try {
    const res = await axios.post('/api/demo/reset');
    toastRef.value?.show(res.data.message || 'Demo balance reset to $10,000!', 'success');
    window.location.reload();
  } catch (e) {
    toastRef.value?.show('Failed to reset demo balance.', 'error');
  } finally {
    resetting.value = false;
  }
}

function handleModalDepositSubmitted() {
  toastRef.value?.show('Deposit request submitted successfully!', 'success');
  showDepositModal.value = false;
}

// BEP-20 Wallet Address Modal & Update
const showBep20Modal = ref(false);
const bep20Input = ref('');
const bep20Error = ref('');
const savingBep20 = ref(false);

function openBep20Modal() {
  bep20Input.value = props.user?.bep20_address || '';
  bep20Error.value = '';
  showBep20Modal.value = true;
}

async function submitBep20Address() {
  bep20Error.value = '';
  if (!bep20Input.value.startsWith('0x') || bep20Input.value.length !== 42) {
    bep20Error.value = 'Please enter a valid 42-character Binance BEP-20 wallet address starting with 0x.';
    return;
  }

  savingBep20.value = true;
  try {
    const res = await axios.post('/api/payments/bep20', {
      bep20_address: bep20Input.value.trim(),
    });
    toastRef.value?.show(res.data.message || 'BEP-20 Address saved!', 'success');
    showBep20Modal.value = false;
    window.location.reload();
  } catch (e) {
    bep20Error.value = e.response?.data?.message || 'Failed to save address. Please check formatting.';
  } finally {
    savingBep20.value = false;
  }
}

// Profile & Password Update Modal
const showEditProfileModal = ref(false);
const profileForm = ref({
  name: '',
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});
const profileError = ref('');
const savingProfile = ref(false);

function openEditProfileModal() {
  profileForm.value = {
    name: props.user?.name || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  };
  profileError.value = '';
  showEditProfileModal.value = true;
}

async function submitProfileUpdate() {
  profileError.value = '';

  if (profileForm.value.new_password) {
    if (!profileForm.value.current_password) {
      profileError.value = 'Please provide your current password to set a new password.';
      return;
    }
    if (profileForm.value.new_password.length < 8) {
      profileError.value = 'New password must be at least 8 characters long.';
      return;
    }
    if (profileForm.value.new_password !== profileForm.value.new_password_confirmation) {
      profileError.value = 'New password confirmation does not match.';
      return;
    }
  }

  savingProfile.value = true;
  try {
    const res = await axios.post('/api/profile/update', profileForm.value);
    toastRef.value?.show(res.data.message || 'Profile updated successfully!', 'success');
    showEditProfileModal.value = false;
    window.location.reload();
  } catch (e) {
    profileError.value = e.response?.data?.message || 'Failed to update profile. Please verify your information.';
  } finally {
    savingProfile.value = false;
  }
}
</script>
