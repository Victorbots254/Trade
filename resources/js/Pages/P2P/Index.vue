<template>
  <div class="min-h-screen bg-[#0b0e11] text-[#eaecef] flex flex-col font-sans select-none">
    <!-- Main Top Navigation Header -->
    <TradingHeader :user="user || $page.props.auth?.user" />

    <!-- P2P Sub Navigation Bar -->
    <div class="bg-[#181a20] border-b border-[#2b3139] px-4 md:px-8 py-2.5 flex items-center justify-between sticky top-0 z-30 text-xs">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-white flex items-center space-x-1.5">
          <span>🤝</span>
          <span>P2P Express Marketplace</span>
        </span>
        <span class="bg-[#0ecb81]/10 text-[#0ecb81] border border-[#0ecb81]/30 text-[10px] px-2 py-0.5 rounded font-bold uppercase">0% Escrow</span>
      </div>

      <div class="flex items-center space-x-2.5">
        <Link href="/p2p/orders" class="inline-flex items-center space-x-1.5 text-[#848e9c] hover:text-white bg-[#2b3139]/40 hover:bg-[#2b3139] px-3 py-1.5 rounded-lg border border-[#2b3139] transition">
          <span>📋</span>
          <span>My Orders</span>
        </Link>
        <Link href="/p2p/merchant/ads" class="inline-flex items-center space-x-1.5 bg-[#f0b90b]/10 text-[#f0b90b] hover:bg-[#f0b90b]/20 border border-[#f0b90b]/30 px-3 py-1.5 rounded-lg font-bold transition">
          <span>📢</span>
          <span>Merchant Ads</span>
        </Link>
        <div v-if="user" class="hidden sm:flex text-xs text-[#848e9c] bg-[#1e2329] px-3 py-1.5 rounded-lg border border-[#2b3139] items-center space-x-1">
          <span class="text-[#848e9c]">Balance:</span>
          <span class="text-[#0ecb81] font-mono font-bold">{{ usdtBalance.toFixed(2) }} USDT</span>
        </div>
      </div>
    </div>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">
      <!-- Banner / Announcement -->
      <div class="bg-gradient-to-r from-[#1e2329] via-[#1e2329] to-[#2b3139]/60 border border-[#2b3139] rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl space-y-2">
          <div class="inline-flex items-center space-x-2 bg-[#f0b90b]/10 border border-[#f0b90b]/30 text-[#f0b90b] text-[11px] font-bold px-2.5 py-0.5 rounded-full">
            <span>🛡️ 100% Escrow Protection</span>
          </div>
          <h1 class="text-xl md:text-2xl font-black text-white">TradeCo P2P Crypto Marketplace</h1>
          <p class="text-xs text-[#848e9c] leading-relaxed">
            Buy and sell USDT directly with verified local traders using M-Pesa and Bank Transfers with 0% platform trading fees. Crypto is locked in secure escrow until payment is confirmed.
          </p>
        </div>
      </div>

      <!-- Active P2P Trades in Progress Banner -->
      <div v-if="myActiveOrders && myActiveOrders.length > 0" class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-4 md:p-5 shadow-xl space-y-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <span class="text-amber-400 font-bold text-sm">🔔 Your Active P2P Trade(s) in Progress ({{ myActiveOrders.length }})</span>
            <span class="bg-amber-500/20 text-amber-300 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase animate-pulse">In Progress</span>
          </div>
          <a href="/p2p/orders" class="text-xs text-[#f0b90b] hover:underline font-bold">All Orders ({{ myActiveOrders.length }}) →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
          <a v-for="order in myActiveOrders" :key="order.id" :href="'/p2p/orders/' + order.id"
             class="bg-[#0b0e11] border border-[#2b3139] hover:border-amber-500/50 rounded-xl p-3.5 transition block space-y-1.5 group">
            <div class="flex justify-between items-center text-xs">
              <span class="font-bold text-white group-hover:text-[#f0b90b]">Order #{{ order.order_number }}</span>
              <span :class="order.status === 'disputed' ? 'bg-rose-500/20 text-rose-300' : 'bg-amber-500/20 text-amber-300'" class="font-mono text-[10px] px-2 py-0.5 rounded uppercase font-bold">
                {{ order.status }}
              </span>
            </div>
            <div class="text-[11px] text-[#848e9c] flex justify-between font-mono">
              <span>Crypto: <strong class="text-white">{{ Number(order.crypto_amount).toFixed(2) }} USDT</strong></span>
              <span>Fiat: <strong class="text-white">{{ Number(order.fiat_amount).toLocaleString() }} {{ order.fiat || 'KES' }}</strong></span>
            </div>
            <div class="text-[11px] text-amber-400 font-medium pt-1 flex items-center justify-between">
              <span>Open Trade Chat & Escrow</span>
              <span>→</span>
            </div>
          </a>
        </div>
      </div>

      <!-- Control Bar: BUY / SELL TABS & CURRENCY FILTERS -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl p-4 space-y-4 shadow-lg">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
          <!-- Left: Buy / Sell Toggle Tabs -->
          <div class="flex items-center bg-[#0b0e11] p-1 rounded-xl border border-[#2b3139] w-full sm:w-auto">
            <button
              @click="setTab('sell')"
              type="button"
              :class="activeType === 'sell' ? 'bg-[#0ecb81] text-[#1e2329] font-black shadow' : 'text-[#848e9c] hover:text-white'"
              class="flex-1 sm:flex-initial px-5 py-2 rounded-lg text-xs md:text-sm font-bold transition flex items-center justify-center space-x-1.5">
              <span>Buy USDT</span>
              <span v-if="stats?.sellAdsCount !== undefined" class="text-[10px] opacity-80 bg-black/20 px-1.5 py-0.2 rounded-full">({{ stats.sellAdsCount }})</span>
            </button>
            <button
              @click="setTab('buy')"
              type="button"
              :class="activeType === 'buy' ? 'bg-[#f6465d] text-white font-black shadow' : 'text-[#848e9c] hover:text-white'"
              class="flex-1 sm:flex-initial px-5 py-2 rounded-lg text-xs md:text-sm font-bold transition flex items-center justify-center space-x-1.5">
              <span>Sell USDT</span>
              <span v-if="stats?.buyAdsCount !== undefined" class="text-[10px] opacity-80 bg-black/20 px-1.5 py-0.2 rounded-full">({{ stats.buyAdsCount }})</span>
            </button>
          </div>

          <!-- Right: Currency Switcher (USDT vs KES) & Payment Filter -->
          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto text-xs">
            <!-- Currency Filter Tabs -->
            <div class="flex items-center bg-[#0b0e11] p-1 rounded-xl border border-[#2b3139]">
              <button
                @click="setCurrency('KES')"
                type="button"
                :class="selectedFiat === 'KES' ? 'bg-[#0ecb81] text-[#1e2329] font-black shadow' : 'text-[#848e9c] hover:text-white'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>🇰🇪</span>
                <span>KES</span>
                <span v-if="stats?.kesAdsCount !== undefined" class="text-[10px] opacity-80">({{ stats.kesAdsCount }})</span>
              </button>
              <button
                @click="setCurrency('USDT')"
                type="button"
                :class="selectedFiat === 'USDT' || selectedFiat === 'USD' ? 'bg-[#f0b90b] text-[#1e2329] font-black shadow' : 'text-[#848e9c] hover:text-white'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center space-x-1">
                <span>🪙</span>
                <span>USD</span>
                <span v-if="stats?.usdtAdsCount !== undefined" class="text-[10px] opacity-80">({{ stats.usdtAdsCount }})</span>
              </button>
              <button
                @click="setCurrency('all')"
                type="button"
                :class="selectedFiat === 'all' ? 'bg-[#2b3139] text-white font-bold' : 'text-[#848e9c] hover:text-white'"
                class="px-3 py-1.5 rounded-lg text-xs transition">
                All
              </button>
            </div>

            <!-- Payment Method Filter -->
            <div class="flex items-center space-x-2 bg-[#0b0e11] border border-[#2b3139] px-3 py-1.5 rounded-xl">
              <span class="text-[#848e9c] text-[11px]">Payment:</span>
              <select v-model="selectedPayment" @change="applyFilters" class="bg-transparent text-white font-bold focus:outline-none">
                <option value="all" class="bg-[#181a20]">All Methods</option>
                <option value="mpesa" class="bg-[#181a20]">📱 M-Pesa</option>
                <option value="bank_transfer" class="bg-[#181a20]">🏦 Bank Transfer</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Ads Table -->
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-[#848e9c] border-b border-[#2b3139] bg-[#14161a] text-[11px] uppercase tracking-wider font-semibold">
                <th class="py-3.5 px-4 md:px-6">Advertiser</th>
                <th class="py-3.5 px-4">Price</th>
                <th class="py-3.5 px-4">Available / Order Limits</th>
                <th class="py-3.5 px-4">Payment Methods</th>
                <th class="py-3.5 px-4 md:px-6 text-right">Trade Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#2b3139]/60">
              <tr
                v-for="ad in ads.data"
                :key="ad.id"
                :class="[ad.is_top_trader ? 'bg-gradient-to-r from-[#f0b90b]/10 via-[#181a20] to-[#181a20] border-l-2 border-l-[#f0b90b]' : 'hover:bg-[#1e2329]/50']"
                class="transition">
                <!-- Merchant info -->
                <td class="py-4 px-4 md:px-6">
                  <div class="flex items-center space-x-3">
                    <div
                      :class="[ad.is_top_trader ? 'from-[#f0b90b]/40 to-[#f0b90b]/10 border-[#f0b90b] text-[#f0b90b]' : 'from-[#f0b90b]/20 to-[#0ecb81]/20 border-[#f0b90b]/30 text-white']"
                      class="w-9 h-9 rounded-xl bg-gradient-to-tr border flex items-center justify-center font-bold text-sm">
                      {{ (ad.user?.username || ad.user?.p2p_merchant_name || ad.user?.name || 'M')[0].toUpperCase() }}
                    </div>
                    <div>
                      <div class="flex items-center space-x-1.5 flex-wrap gap-1">
                        <span class="font-bold text-white text-sm font-mono">{{ ad.user?.username || ad.user?.p2p_merchant_name || ad.user?.name }}</span>
                        <!-- Highlight ONLY top traders (Top 3 rated with 100% and 5/5) -->
                        <template v-if="ad.is_top_trader">
                          <span class="bg-[#f0b90b]/20 text-[#f0b90b] border border-[#f0b90b]/40 text-[9px] px-1.5 py-0.2 rounded font-black tracking-wide uppercase">★ TOP TRADER</span>
                          <span class="text-amber-400 font-bold text-[10px] ml-0.5">★ 5.0 (5/5)</span>
                        </template>
                        <template v-else>
                          <span class="text-[#0ecb81] text-xs" title="Verified Merchant">✓</span>
                          <span class="text-[#848e9c] text-[10px]">★ {{ Number(ad.user?.p2p_rating || 4.9).toFixed(1) }}/5</span>
                        </template>
                      </div>
                      <div class="text-[11px] text-[#848e9c] flex items-center space-x-2 mt-0.5">
                        <span>{{ Number(ad.user?.p2p_completed_trades || 0).toLocaleString() }} orders</span>
                        <span>·</span>
                        <span :class="ad.is_top_trader ? 'text-amber-300 font-bold' : 'text-[#0ecb81]'">
                          {{ Number(ad.user?.p2p_completion_rate || 100).toFixed(1) }}% completion
                        </span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Price -->
                <td class="py-4 px-4 font-mono">
                  <div class="text-base font-black text-white">
                    <span v-if="ad.fiat === 'KES'" class="text-[#0ecb81]">KSh {{ Number(ad.price).toFixed(2) }}</span>
                    <span v-else-if="ad.fiat === 'USDT' || ad.fiat === 'USD'" class="text-[#f0b90b]">$ {{ Number(ad.price).toFixed(2) }}</span>
                    <span v-else>{{ Number(ad.price).toFixed(2) }}</span>
                    <span class="text-xs text-[#848e9c] font-normal ml-1">{{ ad.fiat }}</span>
                  </div>
                  <div class="text-[10px] text-[#848e9c]">per 1 USDT</div>
                </td>

                <!-- Available & Limits -->
                <td class="py-4 px-4 font-mono space-y-1">
                  <div class="text-slate-300">
                    <span class="text-[#848e9c] text-[11px]">Available: </span>
                    <strong class="text-white">{{ Number(ad.available_amount).toFixed(2) }}</strong> USDT
                  </div>
                  <div class="text-[11px] text-[#848e9c]">
                    <span>Limit: </span>
                    <span class="text-white">
                      <span v-if="ad.fiat === 'KES'">KSh </span>
                      <span v-else-if="ad.fiat === 'USDT' || ad.fiat === 'USD'">$ </span>
                      {{ Number(ad.min_limit).toLocaleString() }} - 
                      <span v-if="ad.fiat === 'KES'">KSh </span>
                      <span v-else-if="ad.fiat === 'USDT' || ad.fiat === 'USD'">$ </span>
                      {{ Number(ad.max_limit).toLocaleString() }} {{ ad.fiat }}
                    </span>
                  </div>
                </td>

                <!-- Payment methods -->
                <td class="py-4 px-4">
                  <div class="flex flex-wrap gap-1.5">
                    <span v-for="method in ad.payment_methods" :key="method"
                          :class="method === 'mpesa' ? 'bg-[#0ecb81]/10 text-[#0ecb81] border-[#0ecb81]/30' : 'bg-[#1e88e5]/10 text-[#64b5f6] border-[#1e88e5]/30'"
                          class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase font-mono">
                      {{ method === 'mpesa' ? '📱 M-Pesa' : '🏦 Bank' }}
                    </span>
                  </div>
                </td>

                <!-- Action Button -->
                <td class="py-4 px-4 md:px-6 text-right">
                  <!-- Dummy / Limited ad: Limited state with qualification requirement popup -->
                  <div v-if="ad.is_dummy || ad.is_limited" class="inline-flex flex-col items-end space-y-1">
                    <button
                      @click="openLimitedNotice(ad)"
                      type="button"
                      class="bg-[#2b3139]/80 hover:bg-[#323842] text-[#848e9c] hover:text-[#eaecef] border border-[#3b414d] font-bold px-4 py-2 rounded-xl transition text-xs flex items-center space-x-1.5 font-sans shadow"
                      title="Trading Limited: Merchant requirements not met">
                      <span>🔒</span>
                      <span>Limited</span>
                    </button>
                    <span class="text-[10px] text-[#848e9c]">Req: 30+ Trades</span>
                  </div>

                  <!-- Real User / Active ad: Clickable Buy/Sell button -->
                  <button
                    v-else
                    @click="openTradeModal(ad)"
                    type="button"
                    :class="activeType === 'sell' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e03a51] text-white'"
                    class="font-black px-5 py-2.5 rounded-xl transition text-xs shadow-lg font-sans flex items-center space-x-1 ml-auto">
                    <span>{{ activeType === 'sell' ? 'Buy USDT' : 'Sell USDT' }}</span>
                  </button>
                </td>
              </tr>

              <tr v-if="ads.data.length === 0">
                <td colspan="5" class="py-12 text-center text-[#848e9c]">
                  <div class="text-3xl mb-2">🔍</div>
                  <p class="text-sm font-semibold text-slate-300">No active P2P ads matching your current filters.</p>
                  <p class="text-xs mt-1 text-slate-400">
                    <span v-if="selectedFiat !== 'all'">Currently filtering by {{ selectedFiat }}.</span>
                    <span v-else>Looking for available market liquidity?</span>
                  </p>
                  <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <button v-if="selectedFiat !== 'all'" @click="setCurrency('all')" class="bg-[#f0b90b] text-[#1e2329] font-bold text-xs px-4 py-2 rounded-xl transition hover:bg-[#d4a30b]">
                      View All Currencies (USDT & KES)
                    </button>
                    <button v-if="activeType === 'sell' && (stats?.buyAdsCount || 0) > 0" @click="setTab('buy')" class="bg-slate-800 text-slate-200 hover:text-white border border-slate-700 text-xs px-4 py-2 rounded-xl transition font-semibold">
                      Switch to Sell USDT ({{ stats.buyAdsCount }} ads available) →
                    </button>
                    <button v-else-if="activeType === 'buy' && (stats?.sellAdsCount || 0) > 0" @click="setTab('sell')" class="bg-slate-800 text-slate-200 hover:text-white border border-slate-700 text-xs px-4 py-2 rounded-xl transition font-semibold">
                      Switch to Buy USDT ({{ stats.sellAdsCount }} ads available) →
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Table Pagination Bar -->
        <div v-if="ads.total > (ads.per_page || 25)" class="border-t border-[#2b3139] px-4 md:px-6 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 bg-[#14161a] text-xs">
          <div class="text-[#848e9c]">
            Showing <span class="text-white font-bold font-mono">{{ ads.from || 1 }}</span> to <span class="text-white font-bold font-mono">{{ ads.to || ads.data.length }}</span> of <span class="text-white font-bold font-mono">{{ ads.total }}</span> P2P advertisers
          </div>
          <div class="flex items-center space-x-1 flex-wrap gap-1">
            <button
              v-for="(link, i) in ads.links"
              :key="i"
              :disabled="!link.url || link.active"
              @click="link.url && router.visit(link.url, { preserveState: true })"
              v-html="link.label"
              :class="[
                link.active ? 'bg-[#f0b90b] text-[#1e2329] font-black shadow' : 'bg-[#0b0e11] text-[#848e9c] hover:text-white border border-[#2b3139]',
                !link.url ? 'opacity-30 cursor-not-allowed' : 'cursor-pointer hover:border-[#f0b90b]'
              ]"
              class="px-3 py-1.5 rounded-lg text-xs transition">
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- TRADE INITIATION MODAL -->
    <div v-if="selectedAd" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl text-xs space-y-4 p-6">
        <div class="flex justify-between items-center border-b border-[#2b3139] pb-3">
          <div class="flex items-center space-x-2">
            <span :class="selectedAd.type === 'sell' ? 'text-[#0ecb81]' : 'text-[#f6465d]'" class="font-bold text-base">
              {{ selectedAd.type === 'sell' ? '🟢 Buy USDT' : '🔴 Sell USDT' }}
            </span>
            <span class="text-[#848e9c]">with {{ selectedAd.user?.p2p_merchant_name || selectedAd.user?.name }}</span>
          </div>
          <button @click="selectedAd = null" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <div v-if="orderError" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] p-3 rounded-xl text-xs">
          {{ orderError }}
        </div>

        <!-- Ad Summary Details -->
        <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3 space-y-2 font-mono text-[11px]">
          <div class="flex justify-between items-center">
            <span class="text-[#848e9c]">Unit Price:</span>
            <span class="font-bold text-white text-xs">
              <span v-if="selectedAd.fiat === 'KES'" class="text-[#0ecb81]">KSh {{ Number(selectedAd.price).toFixed(2) }} KES</span>
              <span v-else-if="selectedAd.fiat === 'USDT' || selectedAd.fiat === 'USD'" class="text-[#f0b90b]">${{ Number(selectedAd.price).toFixed(2) }} {{ selectedAd.fiat }}</span>
              <span v-else>{{ Number(selectedAd.price).toFixed(2) }} {{ selectedAd.fiat }}</span>
            </span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[#848e9c]">Order Limits:</span>
            <span class="text-white">
              <span v-if="selectedAd.fiat === 'KES'">KSh {{ Number(selectedAd.min_limit).toLocaleString() }} - KSh {{ Number(selectedAd.max_limit).toLocaleString() }} KES</span>
              <span v-else-if="selectedAd.fiat === 'USDT' || selectedAd.fiat === 'USD'">${{ Number(selectedAd.min_limit).toLocaleString() }} - ${{ Number(selectedAd.max_limit).toLocaleString() }} {{ selectedAd.fiat }}</span>
              <span v-else>{{ Number(selectedAd.min_limit).toLocaleString() }} - {{ Number(selectedAd.max_limit).toLocaleString() }} {{ selectedAd.fiat }}</span>
            </span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-[#848e9c]">Payment Window:</span>
            <span class="text-[#f0b90b]">{{ selectedAd.time_limit_minutes || 15 }} Minutes</span>
          </div>
        </div>

        <!-- Inputs: Amount Calculator -->
        <div class="space-y-3">
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">I want to pay ({{ selectedAd.fiat }})</label>
            <div class="relative">
              <input
                v-model="tradeFiatInput"
                @input="calculateFromFiat"
                type="number"
                step="any"
                :min="selectedAd.min_limit"
                :max="selectedAd.max_limit"
                placeholder="0.00"
                class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-4 py-3 text-white font-mono text-sm font-bold focus:outline-none focus:border-[#f0b90b]" />
              <button @click="setMaxAmount" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-[#f0b90b] font-bold uppercase hover:underline">Max</button>
            </div>
          </div>

          <!-- Escrow & Fee Breakdown Box -->
          <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3.5 space-y-2 font-mono text-xs">
            <div class="flex justify-between items-center text-[11px]">
              <span class="text-[#848e9c]">Platform Escrow Fee:</span>
              <span v-if="computedFee === 0" class="text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/30">
                🎉 FREE ($0.00)
              </span>
              <span v-else class="text-amber-400 font-bold bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/30">
                0.50 USDT
              </span>
            </div>
            <div class="text-[10px] text-[#848e9c]">
              ✨ Free escrow for trades up to $50 · Flat 0.50 USDT fee for trades over $50
            </div>
            <div class="border-t border-[#2b3139] pt-2 flex justify-between items-center">
              <span class="text-white font-sans font-bold">Net Deposited into Wallet:</span>
              <span class="text-emerald-400 font-black text-base">{{ computedNetReceive.toFixed(4) }} USDT</span>
            </div>
          </div>

          <!-- Select Payment Method -->
          <div>
            <label class="block text-[#848e9c] mb-1 font-semibold">Payment Method</label>
            <select v-model="selectedOrderPaymentMethod" class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-xl px-3 py-2.5 text-white font-semibold focus:outline-none focus:border-[#f0b90b]">
              <option v-for="m in selectedAd.payment_methods" :key="m" :value="m">
                {{ m === 'mpesa' ? '📱 Safaricom M-Pesa' : '🏦 Bank Transfer' }}
              </option>
            </select>
          </div>

          <!-- Merchant Terms -->
          <div v-if="selectedAd.terms" class="bg-[#1e2329]/60 border border-[#2b3139] p-2.5 rounded-lg text-[11px] text-[#848e9c] space-y-1">
            <span class="text-white font-semibold">Merchant Terms:</span>
            <p>{{ selectedAd.terms }}</p>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center space-x-3 pt-2">
          <button @click="selectedAd = null" type="button" class="flex-1 border border-[#2b3139] hover:bg-[#2b3139]/40 text-[#848e9c] hover:text-white py-3 rounded-xl font-bold transition">
            Cancel
          </button>
          <button
            @click="submitOrder"
            :disabled="orderLoading || !tradeFiatInput || tradeFiatInput <= 0"
            type="button"
            :class="selectedAd.type === 'sell' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f6465d] hover:bg-[#e03a51] text-white'"
            class="flex-1 font-black py-3 rounded-xl transition shadow-lg disabled:opacity-50 flex items-center justify-center space-x-2">
            <span v-if="orderLoading">Creating Escrow...</span>
            <span v-else>{{ selectedAd.type === 'sell' ? 'Buy USDT (Escrow)' : 'Sell USDT' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ADVERTISER TRADING REQUIREMENT / LIMITED NOTICE MODAL -->
    <div v-if="limitedNoticeAd" class="fixed inset-0 z-50 bg-[#0b0e11]/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-[#181a20] border border-[#2b3139] rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4 text-xs">
        <div class="flex justify-between items-start">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-xl">
              🔒
            </div>
            <div>
              <h3 class="font-bold text-white text-base">Trading Limited</h3>
              <p class="text-xs text-[#848e9c]">Merchant: {{ limitedNoticeAd.user?.p2p_merchant_name || limitedNoticeAd.user?.name }}</p>
            </div>
          </div>
          <button @click="limitedNoticeAd = null" class="text-[#848e9c] hover:text-white text-base">✕</button>
        </div>

        <p class="text-slate-300 leading-relaxed text-xs">
          This merchant desk has restricted trading to experienced accounts with high trading histories. New or unverified accounts cannot place orders directly with this advertiser.
        </p>

        <div class="bg-[#0b0e11] border border-[#2b3139] rounded-xl p-3.5 space-y-2 text-xs">
          <div class="text-[#848e9c] font-semibold text-[11px] uppercase tracking-wider">Advertiser Qualifications:</div>
          <div class="flex items-center justify-between text-[11px]">
            <span class="text-slate-300">Min. Completed P2P Orders:</span>
            <span class="text-rose-400 font-bold font-mono">30 Trades Required</span>
          </div>
          <div class="flex items-center justify-between text-[11px]">
            <span class="text-slate-300">Account Registration Age:</span>
            <span class="text-rose-400 font-bold font-mono">> 30 Days Required</span>
          </div>
          <div class="flex items-center justify-between text-[11px]">
            <span class="text-slate-300">30-Day Completion Rate:</span>
            <span class="text-rose-400 font-bold font-mono">≥ 95.0% Required</span>
          </div>
        </div>

        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-3 text-xs text-amber-300 flex items-start space-x-2">
          <span>💡</span>
          <span class="leading-relaxed">
            You can trade freely with our <strong>Top Verified Traders</strong> shown at the top of the list!
          </span>
        </div>

        <button
          @click="limitedNoticeAd = null"
          type="button"
          class="w-full bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-black py-2.5 rounded-xl transition text-xs shadow-lg">
          Trade with Top Traders
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';
import P2PNotificationBanner from '@/Components/P2PNotificationBanner.vue';

const props = defineProps({
  ads: { type: Object, required: true },
  filters: { type: Object, default: () => ({ type: 'sell', fiat: 'KES', payment_method: 'all' }) },
  user: { type: Object, default: null },
  isNewUser: { type: Boolean, default: false },
  usdtBalance: { type: Number, default: 0.00 },
  myActiveOrders: { type: Array, default: () => [] },
  stats: { type: Object, default: () => ({}) },
});

const activeType = ref(props.filters.type || 'sell');
const selectedFiat = ref(props.filters.fiat || 'KES');
const selectedPayment = ref(props.filters.payment_method || 'all');

const selectedAd = ref(null);
const limitedNoticeAd = ref(null);
const tradeFiatInput = ref('');
const tradeGrossCrypto = ref(0);
const selectedOrderPaymentMethod = ref('mpesa');
const orderLoading = ref(false);
const orderError = ref('');

function openLimitedNotice(ad) {
  limitedNoticeAd.value = ad;
}

function getEffectivePrice(ad) {
  if (!ad) return 1.0;
  return parseFloat(ad.price) || 1.0;
}

const computedFee = computed(() => {
  const gross = parseFloat(tradeGrossCrypto.value) || 0;
  if (gross <= 0) return 0;
  return gross > 50.0 ? 0.50 : 0.0;
});

const computedNetReceive = computed(() => {
  const gross = parseFloat(tradeGrossCrypto.value) || 0;
  if (gross <= 0) return 0;
  return Math.max(0, gross - computedFee.value);
});

function setTab(type) {
  activeType.value = type;
  applyFilters();
}

function setCurrency(fiat) {
  selectedFiat.value = fiat;
  applyFilters();
}

function applyFilters() {
  router.get('/p2p', {
    type: activeType.value,
    fiat: selectedFiat.value,
    payment_method: selectedPayment.value,
  }, { preserveState: true });
}

function openTradeModal(ad) {
  if (!props.user) {
    window.location.href = '/login';
    return;
  }
  selectedAd.value = ad;
  selectedOrderPaymentMethod.value = ad.payment_methods[0] || 'mpesa';
  tradeFiatInput.value = ad.min_limit.toString();
  calculateFromFiat();
  orderError.value = '';
}

function calculateFromFiat() {
  if (!selectedAd.value) return;
  const fiat = parseFloat(tradeFiatInput.value) || 0;
  if (fiat <= 0) {
    tradeGrossCrypto.value = 0;
    return;
  }
  const price = getEffectivePrice(selectedAd.value);
  tradeGrossCrypto.value = fiat / price;
}

function setMaxAmount() {
  if (!selectedAd.value) return;
  const price = getEffectivePrice(selectedAd.value);
  const maxFiat = Math.min(selectedAd.value.max_limit, selectedAd.value.available_amount * price);
  tradeFiatInput.value = maxFiat.toString();
  calculateFromFiat();
}

function submitOrder() {
  if (!selectedAd.value) return;
  orderLoading.value = true;
  orderError.value = '';

  router.post('/p2p/orders', {
    ad_id: selectedAd.value.id,
    amount: tradeFiatInput.value,
    amount_type: 'fiat',
    payment_method: selectedOrderPaymentMethod.value,
  }, {
    onError: (errors) => {
      orderError.value = errors.message || Object.values(errors)[0] || 'Could not place P2P order.';
      orderLoading.value = false;
    },
    onFinish: () => {
      orderLoading.value = false;
    },
  });
}
</script>
