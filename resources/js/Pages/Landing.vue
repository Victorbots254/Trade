<template>
  <div class="min-h-screen font-sans select-none flex flex-col pb-9" :class="isDark ? 'bg-[#0b0e11] text-white' : 'bg-white text-[#1e2329]'">

    <!-- ANNOUNCEMENT BAR -->
    <div class="bg-[#f0b90b] text-[#1e2329] text-[11px] font-semibold text-center py-2 px-4 flex items-center justify-center space-x-2">
      <span>🎉</span>
      <span>Binary Options now LIVE — up to 88% ROI on BTC, ETH, GOLD &amp; more. <a href="/trade/options/BTC_USDT" class="underline ml-1">Start Trading →</a></span>
    </div>

    <!-- TOP NAVBAR -->
    <header class="sticky top-0 z-50 border-b" :class="isDark ? 'bg-[#0b0e11] border-[#1e2329]' : 'bg-white border-gray-200'">
      <div class="max-w-[1400px] mx-auto px-4 h-14 flex items-center justify-between">
        <div class="flex items-center space-x-8">
          <a href="/" class="flex items-center space-x-2">
            <div class="w-7 h-7 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-sm">T</div>
            <span class="font-bold text-lg tracking-tight" :class="isDark ? 'text-[#f0b90b]' : 'text-[#1e2329]'">TRADE<span class="text-[#f0b90b]">PRO</span></span>
          </a>
          <nav class="hidden lg:flex items-center space-x-1 text-[13px] font-medium" :class="isDark ? 'text-[#b7bdc6]' : 'text-[#474d57]'">
            <div class="relative group">
              <button class="flex items-center space-x-1 hover:text-[#f0b90b] px-3 py-2 rounded transition"><span>Buy Crypto</span><span class="text-[10px]">▾</span></button>
              <div class="absolute top-full left-0 mt-1 w-48 rounded-xl shadow-2xl border py-2 z-50 hidden group-hover:block" :class="isDark ? 'bg-[#1e2329] border-[#2b3139]' : 'bg-white border-gray-100'">
                <a href="/payments" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">USDT Deposit</a>
                <a href="/payments/binance-guide" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Binance BEP20 Guide</a>
              </div>
            </div>
            <div class="relative group">
              <button class="flex items-center space-x-1 hover:text-[#f0b90b] px-3 py-2 rounded transition"><span>Markets</span><span class="text-[10px]">▾</span></button>
              <div class="absolute top-full left-0 mt-1 w-44 rounded-xl shadow-2xl border py-2 z-50 hidden group-hover:block" :class="isDark ? 'bg-[#1e2329] border-[#2b3139]' : 'bg-white border-gray-100'">
                <a href="#markets" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">All Markets</a>
                <a href="#markets" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Crypto Pairs</a>
                <a href="#markets" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Commodities</a>
                <a href="#markets" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Stock Equities</a>
              </div>
            </div>
            <div class="relative group">
              <button class="flex items-center space-x-1 hover:text-[#f0b90b] px-3 py-2 rounded transition"><span>Trade</span><span class="text-[10px]">▾</span></button>
              <div class="absolute top-full left-0 mt-1 w-44 rounded-xl shadow-2xl border py-2 z-50 hidden group-hover:block" :class="isDark ? 'bg-[#1e2329] border-[#2b3139]' : 'bg-white border-gray-100'">
                <a href="/trade/BTC_USDT" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Spot Trading</a>
                <a href="/trade/options/BTC_USDT" class="block px-4 py-2 text-xs hover:text-[#f0b90b] transition">Binary Options</a>
              </div>
            </div>
            <a href="#security" class="hover:text-[#f0b90b] px-3 py-2 rounded transition">Security</a>
            <a href="#withdrawals" class="hover:text-[#f0b90b] px-3 py-2 rounded transition">Community</a>
          </nav>
        </div>
        <div class="flex items-center space-x-2 text-[13px]">
          <button @click="isDark = !isDark" class="p-2 rounded-lg transition" :class="isDark ? 'hover:bg-[#1e2329] text-[#b7bdc6]' : 'hover:bg-gray-100 text-[#474d57]'">
            <span v-if="isDark">☀️</span><span v-else>🌙</span>
          </button>
          <template v-if="user">
            <a href="/trade/BTC_USDT" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-4 py-1.5 rounded-lg text-xs transition">Open Terminal</a>
          </template>
          <template v-else>
            <a href="/login" class="font-semibold hover:text-[#f0b90b] px-3 py-1.5 transition" :class="isDark ? 'text-[#b7bdc6]' : 'text-[#474d57]'">Log In</a>
            <a href="/register" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-4 py-1.5 rounded-lg transition">Register</a>
          </template>
        </div>
      </div>
    </header>

    <!-- LIVE PRICE TOP TICKER -->
    <div class="border-b overflow-hidden" :class="isDark ? 'bg-[#0b0e11] border-[#1e2329]' : 'bg-gray-50 border-gray-200'">
      <div class="max-w-[1400px] mx-auto px-4 py-2 flex items-center space-x-8 overflow-x-auto scrollbar-hide text-xs font-mono whitespace-nowrap">
        <div v-for="m in (markets || []).slice(0,8)" :key="m.symbol" class="flex items-center space-x-2 flex-shrink-0">
          <span class="font-bold" :class="isDark ? 'text-white' : 'text-[#1e2329]'">{{ m.symbol }}</span>
          <span :class="isDark ? 'text-white' : 'text-[#1e2329]'">${{ formatPrice(m.last_price) }}</span>
          <span class="font-bold" :class="(m.change_24h||0)>=0?'text-[#0ecb81]':'text-[#f6465d]'">{{ (m.change_24h||0)>=0?'+':'' }}{{ m.change_24h }}%</span>
        </div>
      </div>
    </div>

    <!-- HERO SECTION -->
    <section class="relative overflow-hidden" :class="isDark ? 'bg-[#0b0e11]' : 'bg-white'">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full opacity-[0.04]" style="background:radial-gradient(circle,#f0b90b,transparent)"></div>
        <div class="absolute top-20 right-0 w-[400px] h-[400px] rounded-full opacity-[0.04]" style="background:radial-gradient(circle,#0ecb81,transparent)"></div>
      </div>
      <div class="relative max-w-[1400px] mx-auto px-4 pt-16 pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="space-y-6">
          <div class="inline-flex items-center space-x-2 border rounded-full px-3.5 py-1.5 text-[11px] font-semibold" :class="isDark?'border-[#f0b90b]/30 bg-[#f0b90b]/10 text-[#f0b90b]':'border-[#f0b90b]/40 bg-[#f0b90b]/10 text-[#b07e00]'">
            <span class="w-2 h-2 bg-[#f0b90b] rounded-full animate-pulse"></span>
            <span>LIVE MARKETS · INSTITUTIONAL-GRADE EXCHANGE</span>
          </div>
          <h1 class="text-4xl sm:text-5xl xl:text-6xl font-extrabold leading-tight" :class="isDark?'text-white':'text-[#1e2329]'">
            Trade Crypto,<br>Gold &amp; Stocks<br><span class="text-[#f0b90b]">With Confidence.</span>
          </h1>
          <p class="text-[15px] leading-relaxed max-w-lg" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            Access 14+ spot markets with real-time pricing, binary options up to 88% ROI, and instant USDT deposits via BNB Smart Chain.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <a href="/register" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-8 py-3.5 rounded-xl text-sm transition shadow-xl flex items-center justify-center">Get Started — It's Free</a>
            <a href="/trade/BTC_USDT" class="border font-semibold px-8 py-3.5 rounded-xl text-sm transition flex items-center justify-center" :class="isDark?'border-[#2b3139] text-white hover:border-[#f0b90b]':'border-gray-200 text-[#1e2329] hover:border-[#f0b90b]'">📊 View Markets</a>
          </div>
          <div class="flex flex-wrap items-center gap-4 pt-2 text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            <div class="flex items-center space-x-1.5"><span class="text-[#0ecb81]">✔</span><span>No KYC Required</span></div>
            <div class="flex items-center space-x-1.5"><span class="text-[#0ecb81]">✔</span><span>USDT BEP20 Deposits</span></div>
            <div class="flex items-center space-x-1.5"><span class="text-[#0ecb81]">✔</span><span>Instant Settlements</span></div>
            <div class="flex items-center space-x-1.5"><span class="text-[#0ecb81]">✔</span><span>$10,000 Demo Account</span></div>
          </div>
        </div>
        <div class="hidden lg:block">
          <div class="rounded-2xl border p-5 space-y-4" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
            <div class="flex items-center justify-between">
              <div>
                <div class="font-bold text-lg" :class="isDark?'text-white':'text-[#1e2329]'">BTC/USDT</div>
                <div class="text-[12px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Bitcoin · Spot Market</div>
              </div>
              <div class="text-right">
                <div class="text-xl font-bold text-[#0ecb81]">${{ formatPrice(markets && markets[0] ? markets[0].last_price : 107540) }}</div>
                <div class="text-[12px] text-[#0ecb81]">+{{ markets && markets[0] ? markets[0].change_24h : 2.34 }}%</div>
              </div>
            </div>
            <div class="flex items-end space-x-1 h-20">
              <div v-for="(h,i) in chartBars" :key="i" class="flex-1 rounded-t-sm" :style="`height:${h}%;background:linear-gradient(to top,#f0b90b22,#f0b90b88)`"></div>
            </div>
            <div class="grid grid-cols-3 gap-3 text-[11px]">
              <div class="rounded-lg p-2.5" :class="isDark?'bg-[#0b0e11]':'bg-white border border-gray-200'">
                <div :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">24h High</div>
                <div class="font-bold text-[#0ecb81]">${{ formatPrice(markets && markets[0] ? markets[0].high_24h : 109200) }}</div>
              </div>
              <div class="rounded-lg p-2.5" :class="isDark?'bg-[#0b0e11]':'bg-white border border-gray-200'">
                <div :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">24h Low</div>
                <div class="font-bold text-[#f6465d]">${{ formatPrice(markets && markets[0] ? markets[0].low_24h : 104800) }}</div>
              </div>
              <div class="rounded-lg p-2.5" :class="isDark?'bg-[#0b0e11]':'bg-white border border-gray-200'">
                <div :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Volume</div>
                <div class="font-bold" :class="isDark?'text-white':'text-[#1e2329]'">$2.48B</div>
              </div>
            </div>
            <a href="/trade/BTC_USDT" class="block w-full bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold py-2.5 rounded-xl text-sm text-center transition">Trade BTC/USDT Now →</a>
          </div>
        </div>
      </div>
    </section>

    <!-- PLATFORM STATS -->
    <div class="border-y" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
      <div class="max-w-[1400px] mx-auto px-4 py-10 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div>
          <div class="text-2xl sm:text-3xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">$2.48B+</div>
          <div class="text-[12px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">24h Trading Volume</div>
        </div>
        <div>
          <div class="text-2xl sm:text-3xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">185,000+</div>
          <div class="text-[12px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Registered Users</div>
        </div>
        <div>
          <div class="text-2xl sm:text-3xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">14+</div>
          <div class="text-[12px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Spot Markets</div>
        </div>
        <div>
          <div class="text-2xl sm:text-3xl font-extrabold text-[#0ecb81]">&lt;0.5ms</div>
          <div class="text-[12px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Order Matching Speed</div>
        </div>
      </div>
    </div>

    <!-- MARKETS TABLE -->
    <section id="markets" class="py-16 px-4" :class="isDark?'bg-[#0b0e11]':'bg-white'">
      <div class="max-w-[1400px] mx-auto space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h2 class="text-2xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Market Overview</h2>
            <p class="text-[13px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Real-time prices across all spot pairs.</p>
          </div>
          <div class="flex items-center space-x-2 text-[12px] font-semibold">
            <button v-for="cat in ['All','Crypto','Commodities','Stocks']" :key="cat"
              @click="activeCategory=cat.toLowerCase()"
              class="px-4 py-1.5 rounded-lg transition"
              :class="activeCategory===cat.toLowerCase()?'bg-[#f0b90b] text-[#1e2329]':isDark?'bg-[#1e2329] text-[#848e9c] hover:text-white border border-[#2b3139]':'bg-gray-100 text-[#474d57] hover:bg-gray-200'">
              {{ cat }}
            </button>
          </div>
        </div>
        <div class="overflow-x-auto rounded-2xl border" :class="isDark?'border-[#2b3139]':'border-gray-200'">
          <table class="w-full text-[13px]">
            <thead>
              <tr class="border-b text-[11px] font-semibold uppercase tracking-wide" :class="isDark?'bg-[#181a20] border-[#2b3139] text-[#848e9c]':'bg-gray-50 border-gray-200 text-[#707a8a]'">
                <th class="px-4 py-3 text-left w-8">#</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-right">Last Price</th>
                <th class="px-4 py-3 text-right">24h Change</th>
                <th class="px-4 py-3 text-right hidden md:table-cell">24h High</th>
                <th class="px-4 py-3 text-right hidden md:table-cell">24h Low</th>
                <th class="px-4 py-3 text-right hidden lg:table-cell">Market Cap</th>
                <th class="px-4 py-3 text-center">Trade</th>
              </tr>
            </thead>
            <tbody class="divide-y" :class="isDark?'divide-[#2b3139]':'divide-gray-100'">
              <tr v-for="(m,i) in filteredMarkets" :key="m.symbol"
                class="transition cursor-pointer" :class="isDark?'hover:bg-[#181a20]':'hover:bg-gray-50'">
                <td class="px-4 py-4 text-[12px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ i+1 }}</td>
                <td class="px-4 py-4">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0" :style="getCoinColor(m.symbol)">{{ m.symbol.split('/')[0].charAt(0) }}</div>
                    <div>
                      <div class="font-bold" :class="isDark?'text-white':'text-[#1e2329]'">{{ m.symbol.split('/')[0] }}</div>
                      <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ getFullName(m.symbol) }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4 text-right font-bold font-mono" :class="isDark?'text-white':'text-[#1e2329]'">${{ formatPrice(m.last_price) }}</td>
                <td class="px-4 py-4 text-right font-bold" :class="(m.change_24h||0)>=0?'text-[#0ecb81]':'text-[#f6465d]'">
                  {{ (m.change_24h||0)>=0?'▲':'▼' }} {{ Math.abs(m.change_24h||0) }}%
                </td>
                <td class="px-4 py-4 text-right text-[#0ecb81] font-mono hidden md:table-cell">${{ formatPrice(m.high_24h) }}</td>
                <td class="px-4 py-4 text-right text-[#f6465d] font-mono hidden md:table-cell">${{ formatPrice(m.low_24h) }}</td>
                <td class="px-4 py-4 text-right font-mono hidden lg:table-cell" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ getMarketCap(m.symbol) }}</td>
                <td class="px-4 py-4 text-center">
                  <a :href="'/trade/'+m.symbol.replace('/','_')" class="inline-block bg-[#f0b90b]/10 hover:bg-[#f0b90b] text-[#f0b90b] hover:text-[#1e2329] border border-[#f0b90b]/40 hover:border-[#f0b90b] font-bold px-4 py-1.5 rounded-lg text-[11px] transition">Trade</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- RECENT WITHDRAWALS SLIDER -->
    <section id="withdrawals" class="py-16 px-4 border-y" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
      <div class="max-w-[1400px] mx-auto space-y-8">
        <div class="text-center space-y-2">
          <h2 class="text-2xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">🏆 Recent Community Payouts</h2>
          <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Real traders. Real withdrawals. Verified on-chain. Past 30 days.</p>
        </div>

        <!-- Slider Container -->
        <div class="relative" @mouseenter="pauseSlider" @mouseleave="resumeSlider">
          <!-- Prev Arrow -->
          <button @click="prevSlide"
            class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 z-10 w-9 h-9 rounded-full border flex items-center justify-center shadow-lg transition hover:border-[#f0b90b] hover:text-[#f0b90b]"
            :class="isDark?'bg-[#1e2329] border-[#2b3139] text-[#848e9c]':'bg-white border-gray-200 text-[#474d57]'">
            ‹
          </button>

          <!-- Cards Track -->
          <div class="overflow-hidden mx-4">
            <div class="flex transition-transform duration-500 ease-in-out gap-4"
              :style="`transform: translateX(calc(-${sliderIndex * (100 / visibleCards)}% - ${sliderIndex * 16 / visibleCards}px))`">
              <div v-for="w in recentWithdrawals" :key="w.name"
                class="rounded-xl p-4 border flex items-center space-x-3 flex-shrink-0 transition-all duration-300"
                :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-white border-gray-200'"
                :style="`width: calc(${100 / visibleCards}% - ${(visibleCards-1)*16/visibleCards}px)`">
                <!-- Avatar -->
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0 text-white shadow-md"
                  :style="`background:${w.color}`">
                  {{ w.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-[13px] truncate" :class="isDark?'text-white':'text-[#1e2329]'">{{ w.name }}</div>
                  <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ w.time }}</div>
                </div>
                <div class="text-right flex-shrink-0">
                  <div class="font-bold text-[#0ecb81] text-[13px]">+{{ w.amount }}</div>
                  <div class="text-[10px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Withdrawn</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Next Arrow -->
          <button @click="nextSlide"
            class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 z-10 w-9 h-9 rounded-full border flex items-center justify-center shadow-lg transition hover:border-[#f0b90b] hover:text-[#f0b90b]"
            :class="isDark?'bg-[#1e2329] border-[#2b3139] text-[#848e9c]':'bg-white border-gray-200 text-[#474d57]'">
            ›
          </button>
        </div>

        <!-- Dot Indicators -->
        <div class="flex items-center justify-center space-x-2 pt-2">
          <button v-for="(_, i) in dotCount" :key="i"
            @click="goToSlide(i)"
            class="rounded-full transition-all duration-300"
            :class="sliderIndex === i ? 'w-6 h-2 bg-[#f0b90b]' : isDark ? 'w-2 h-2 bg-[#2b3139] hover:bg-[#f0b90b]/50' : 'w-2 h-2 bg-gray-300 hover:bg-[#f0b90b]/50'">
          </button>
        </div>

        <div class="text-center text-[12px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          ✅ All payouts verified on BNB Smart Chain. Average withdrawal time: &lt;10 minutes.
        </div>
      </div>
    </section>


    <!-- FEATURES -->
    <section id="features" class="py-20 px-4" :class="isDark?'bg-[#0b0e11]':'bg-white'">
      <div class="max-w-[1400px] mx-auto space-y-12">
        <div class="text-center max-w-2xl mx-auto space-y-3">
          <h2 class="text-2xl sm:text-3xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Why Trade With Us?</h2>
          <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Institutional-grade infrastructure, made simple for every trader.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="f in features" :key="f.title" class="rounded-2xl p-6 border space-y-3 hover:border-[#f0b90b]/40 transition-all duration-200" :class="isDark?'bg-[#1e2329] border-[#2b3139]':'bg-gray-50 border-gray-200'">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl" :style="`background:${f.bg}22;border:1px solid ${f.bg}44`">{{ f.icon }}</div>
            <h3 class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ f.title }}</h3>
            <p class="text-[13px] leading-relaxed" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- HOW IT WORKS -->
    <section id="security" class="py-16 px-4 border-y" :class="isDark?'bg-[#181a20] border-[#2b3139]':'bg-gray-50 border-gray-200'">
      <div class="max-w-[1400px] mx-auto space-y-10">
        <div class="text-center space-y-2">
          <h2 class="text-2xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Get Started in 3 Steps</h2>
          <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Start trading in under 5 minutes.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(step,i) in steps" :key="i" class="text-center space-y-4">
            <div class="w-16 h-16 rounded-2xl border-2 border-[#f0b90b] bg-[#f0b90b]/10 flex items-center justify-center text-2xl mx-auto">{{ step.icon }}</div>
            <div>
              <div class="text-[11px] font-bold text-[#f0b90b] uppercase tracking-widest mb-1">Step {{ i+1 }}</div>
              <div class="font-bold text-[15px]" :class="isDark?'text-white':'text-[#1e2329]'">{{ step.title }}</div>
              <div class="text-[13px] mt-1" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">{{ step.desc }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="py-16 px-4" :class="isDark?'bg-[#0b0e11]':'bg-white'">
      <div class="max-w-3xl mx-auto space-y-6">
        <div class="text-center space-y-2">
          <h2 class="text-2xl font-bold" :class="isDark?'text-white':'text-[#1e2329]'">Frequently Asked Questions</h2>
          <p class="text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Everything you need to know before trading.</p>
        </div>
        <div v-for="(faq,i) in faqs" :key="i" class="rounded-xl border overflow-hidden" :class="isDark?'border-[#2b3139]':'border-gray-200'">
          <button @click="openFaq=openFaq===i?null:i" class="w-full text-left px-5 py-4 flex items-center justify-between font-semibold text-[14px] transition" :class="isDark?'bg-[#1e2329] text-white hover:bg-[#2b3139]':'bg-gray-50 text-[#1e2329] hover:bg-gray-100'">
            <span>{{ faq.q }}</span>
            <span class="text-[#f0b90b] text-xl leading-none transition-transform" :class="openFaq===i?'rotate-45':''">+</span>
          </button>
          <div v-if="openFaq===i" class="px-5 py-4 text-[13px] leading-relaxed border-t" :class="isDark?'bg-[#0b0e11] border-[#2b3139] text-[#848e9c]':'bg-white border-gray-200 text-[#707a8a]'">{{ faq.a }}</div>
        </div>
      </div>
    </section>

    <!-- CTA BANNER -->
    <section class="py-20 px-4" :class="isDark?'bg-[#181a20]':'bg-[#f8f9fa]'">
      <div class="max-w-3xl mx-auto text-center space-y-6">
        <div class="text-4xl">🚀</div>
        <h2 class="text-3xl font-extrabold" :class="isDark?'text-white':'text-[#1e2329]'">Ready to Start Trading?</h2>
        <p class="text-[14px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Join 185,000+ traders and access live spot markets, binary options, and real-time payouts.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-3">
          <a href="/register" class="bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-10 py-4 rounded-xl text-sm transition shadow-xl">Create Free Account →</a>
          <a href="/trade/BTC_USDT" class="border font-semibold px-10 py-4 rounded-xl text-sm transition" :class="isDark?'border-[#2b3139] text-white hover:border-[#f0b90b]':'border-gray-300 text-[#1e2329] hover:border-[#f0b90b]'">Demo Trade (No Signup)</a>
        </div>
        <div class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">🎮 Free $10,000 Demo · 💳 Min. Deposit $10 USDT · ⚡ Withdraw Anytime</div>
      </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t" :class="isDark?'bg-[#0b0e11] border-[#1e2329]':'bg-white border-gray-200'">
      <div class="max-w-[1400px] mx-auto px-4 py-14 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-8">
        <div class="col-span-2 sm:col-span-3 lg:col-span-2 space-y-4">
          <div class="flex items-center space-x-2">
            <div class="w-7 h-7 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-sm">T</div>
            <span class="font-bold text-lg" :class="isDark?'text-[#f0b90b]':'text-[#1e2329]'">TRADE<span class="text-[#f0b90b]">PRO</span></span>
          </div>
          <p class="text-[12px] leading-relaxed max-w-xs" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Institutional-grade cryptocurrency spot exchange with binary options, real-time settlements, and BEP20 USDT deposits. Built for serious traders worldwide.</p>
          <div class="flex items-center space-x-3">
            <a v-for="s in ['𝕏','📘','💬','📢']" :key="s" href="#" class="w-8 h-8 rounded-lg flex items-center justify-center text-sm transition border" :class="isDark?'bg-[#1e2329] border-[#2b3139] text-[#848e9c] hover:border-[#f0b90b] hover:text-[#f0b90b]':'bg-gray-100 border-gray-200 text-[#707a8a] hover:border-[#f0b90b] hover:text-[#b07e00]'">{{ s }}</a>
          </div>
          <div class="flex items-center space-x-2 text-[11px] text-[#0ecb81]">
            <span class="w-2 h-2 bg-[#0ecb81] rounded-full animate-pulse"></span>
            <span>All Systems Operational</span>
          </div>
        </div>
        <div class="space-y-3">
          <div class="text-[11px] font-bold uppercase tracking-widest" :class="isDark?'text-white':'text-[#1e2329]'">Products</div>
          <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            <li><a href="/trade/BTC_USDT" class="hover:text-[#f0b90b] transition">Spot Exchange</a></li>
            <li><a href="/trade/options/BTC_USDT" class="hover:text-[#f0b90b] transition">Binary Options</a></li>
            <li><a href="/payments" class="hover:text-[#f0b90b] transition">USDT Deposits</a></li>
            <li><a href="/payments" class="hover:text-[#f0b90b] transition">Withdrawals</a></li>
            <li><a href="/profile" class="hover:text-[#f0b90b] transition">Portfolio</a></li>
          </ul>
        </div>
        <div class="space-y-3">
          <div class="text-[11px] font-bold uppercase tracking-widest" :class="isDark?'text-white':'text-[#1e2329]'">Markets</div>
          <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            <li><a href="/trade/BTC_USDT" class="hover:text-[#f0b90b] transition">BTC/USDT</a></li>
            <li><a href="/trade/ETH_USDT" class="hover:text-[#f0b90b] transition">ETH/USDT</a></li>
            <li><a href="/trade/GOLD_USDT" class="hover:text-[#f0b90b] transition">GOLD/USDT (XAU)</a></li>
            <li><a href="/trade/NVDA_USDT" class="hover:text-[#f0b90b] transition">NVDA/USDT</a></li>
            <li><a href="#markets" class="hover:text-[#f0b90b] transition">View All →</a></li>
          </ul>
        </div>
        <div class="space-y-3">
          <div class="text-[11px] font-bold uppercase tracking-widest" :class="isDark?'text-white':'text-[#1e2329]'">Support</div>
          <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            <li><a href="/learn" class="hover:text-[#f0b90b] transition font-semibold">Trading Academy</a></li>
            <li><a href="/payments/binance-guide" class="hover:text-[#f0b90b] transition">Deposit Guide (Binance)</a></li>
            <li><a href="/faq" class="hover:text-[#f0b90b] transition">FAQ Center</a></li>
            <li><a href="/contact" class="hover:text-[#f0b90b] transition">Contact Support</a></li>
            <li><a href="/terms" class="hover:text-[#f0b90b] transition">Terms of Service</a></li>
            <li><a href="/privacy" class="hover:text-[#f0b90b] transition">Privacy Policy</a></li>
            <li><a href="/risk-disclosure" class="hover:text-[#f0b90b] transition">Risk Disclosure</a></li>
          </ul>
        </div>
        <div class="space-y-3">
          <div class="text-[11px] font-bold uppercase tracking-widest" :class="isDark?'text-white':'text-[#1e2329]'">Company</div>
          <ul class="space-y-2 text-[13px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
            <li><a href="/about" class="hover:text-[#f0b90b] transition">About Us</a></li>
            <li><a href="/blog" class="hover:text-[#f0b90b] transition">Blog &amp; News</a></li>
            <li><a href="/careers" class="hover:text-[#f0b90b] transition">Careers</a></li>
            <li><a href="/bug-bounty" class="hover:text-[#f0b90b] transition">Bug Bounty</a></li>
            <li><a href="/media-kit" class="hover:text-[#f0b90b] transition">Media Kit</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t" :class="isDark?'border-[#1e2329]':'border-gray-200'">
        <div class="max-w-[1400px] mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">
          <p>© 2026 TradePro Inc. All rights reserved. Trading involves risk. Please trade responsibly.</p>
          <div class="flex items-center space-x-4">
            <a href="/terms" class="hover:text-[#f0b90b] transition">Terms</a>
            <a href="/privacy" class="hover:text-[#f0b90b] transition">Privacy</a>
            <a href="/risk-disclosure" class="hover:text-[#f0b90b] transition">Risk</a>
            <a href="/contact" class="hover:text-[#f0b90b] transition">Support</a>
          </div>
        </div>
      </div>
      <div class="border-t" :class="isDark?'border-[#1e2329] bg-[#060a0f]':'border-gray-200 bg-gray-50'">
        <div class="max-w-[1400px] mx-auto px-4 py-5 text-[10px] leading-relaxed" :class="isDark?'text-[#636e80]':'text-[#9ea8b5]'">
          <strong class="text-[11px]" :class="isDark?'text-[#848e9c]':'text-[#707a8a]'">Risk Disclaimer:</strong>
          Cryptocurrency and derivative trading carries substantial risk of loss and is not suitable for all investors. The value of digital assets can fluctuate greatly, and you may lose some or all of your investment.
          TradePro does not provide investment, financial, tax, or legal advice. Past performance is not indicative of future results. Binary options are high-risk financial instruments. Please ensure compliance with local laws before trading.
        </div>
      </div>
    </footer>

    <!-- FIXED BOTTOM LIVE TICKER -->
    <div class="fixed bottom-0 left-0 right-0 z-50 h-9 flex items-center overflow-hidden border-t" :class="isDark?'bg-[#0b0e11] border-[#1e2329]':'bg-white border-gray-200'">
      <div class="flex items-center space-x-1 flex-shrink-0 px-3 border-r font-bold text-[10px] text-[#f0b90b]" :class="isDark?'border-[#1e2329]':'border-gray-200'">📊 LIVE</div>
      <div class="overflow-hidden flex-1">
        <div class="flex items-center animate-ticker whitespace-nowrap space-x-8 text-[11px] font-mono">
          <span v-for="(m,idx) in tickerMarkets" :key="idx" class="flex items-center space-x-1.5 flex-shrink-0">
            <span class="font-bold" :class="isDark?'text-white':'text-[#1e2329]'">{{ m.symbol }}</span>
            <span :class="isDark?'text-white':'text-[#1e2329]'">${{ formatPrice(m.last_price) }}</span>
            <span class="font-bold" :class="(m.change_24h||0)>=0?'text-[#0ecb81]':'text-[#f6465d]'">{{ (m.change_24h||0)>=0?'▲':'▼' }} {{ Math.abs(m.change_24h||0) }}%</span>
          </span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({ user: Object, markets: Array });

const isDark = ref(true);
const activeCategory = ref('all');
const openFaq = ref(null);

const chartBars = [40,55,45,70,60,80,65,90,75,88,72,95,78,85,100,88,92,78,85,90];

// ===== SLIDER =====
const sliderIndex = ref(0);
const sliderPaused = ref(false);
let sliderTimer = null;

// Responsive: 4 on lg, 2 on sm, 1 on xs
const visibleCards = ref(4);

function updateVisibleCards() {
  if (window.innerWidth < 640) visibleCards.value = 1;
  else if (window.innerWidth < 1024) visibleCards.value = 2;
  else visibleCards.value = 4;
}

const dotCount = computed(() => {
  const total = 12; // recentWithdrawals.length
  return Math.ceil(total / visibleCards.value);
});

const maxSlideIndex = computed(() => dotCount.value - 1);

function nextSlide() {
  sliderIndex.value = sliderIndex.value >= maxSlideIndex.value ? 0 : sliderIndex.value + 1;
}

function prevSlide() {
  sliderIndex.value = sliderIndex.value <= 0 ? maxSlideIndex.value : sliderIndex.value - 1;
}

function goToSlide(i) {
  sliderIndex.value = i;
}

function pauseSlider() { sliderPaused.value = true; }
function resumeSlider() { sliderPaused.value = false; }

function startSliderTimer() {
  sliderTimer = setInterval(() => {
    if (!sliderPaused.value) nextSlide();
  }, 3000);
}

onMounted(() => {
  updateVisibleCards();
  window.addEventListener('resize', updateVisibleCards);
  startSliderTimer();
});

onUnmounted(() => {
  window.removeEventListener('resize', updateVisibleCards);
  if (sliderTimer) clearInterval(sliderTimer);
});
// ===== END SLIDER =====


const cryptoSymbols = ['BTC/USDT','ETH/USDT','BNB/USDT','SOL/USDT','XRP/USDT','DOGE/USDT'];
const commoditySymbols = ['GOLD/USDT','SILVER/USDT','OIL/USDT'];
const stockSymbols = ['NVDA/USDT','AAPL/USDT','TSLA/USDT','MSFT/USDT','SPY/USDT'];

const filteredMarkets = computed(() => {
  let list = props.markets || [];
  if (activeCategory.value === 'crypto') return list.filter(m => cryptoSymbols.includes(m.symbol));
  if (activeCategory.value === 'commodities') return list.filter(m => commoditySymbols.includes(m.symbol));
  if (activeCategory.value === 'stocks') return list.filter(m => stockSymbols.includes(m.symbol));
  return list;
});

const tickerMarkets = computed(() => {
  const list = props.markets || [];
  return [...list, ...list];
});

const coinColors = {
  BTC:{bg:'#f7931a',text:'#fff'}, ETH:{bg:'#627eea',text:'#fff'}, BNB:{bg:'#f0b90b',text:'#1e2329'},
  SOL:{bg:'#9945ff',text:'#fff'}, XRP:{bg:'#00aae4',text:'#fff'}, DOGE:{bg:'#c2a633',text:'#fff'},
  GOLD:{bg:'#ffd700',text:'#1e2329'}, SILVER:{bg:'#a8a9ad',text:'#1e2329'}, OIL:{bg:'#444',text:'#fff'},
  NVDA:{bg:'#76b900',text:'#fff'}, AAPL:{bg:'#555',text:'#fff'}, TSLA:{bg:'#e31937',text:'#fff'},
  MSFT:{bg:'#00a4ef',text:'#fff'}, SPY:{bg:'#1e3a8a',text:'#fff'},
};

function getCoinColor(symbol) {
  const t = symbol.split('/')[0];
  const c = coinColors[t] || {bg:'#f0b90b',text:'#1e2329'};
  return `background:${c.bg};color:${c.text}`;
}

const fullNames = {
  BTC:'Bitcoin', ETH:'Ethereum', BNB:'BNB Smart Chain', SOL:'Solana', XRP:'Ripple',
  DOGE:'Dogecoin', GOLD:'Gold (XAU)', SILVER:'Silver (XAG)', OIL:'Crude Oil (WTI)',
  NVDA:'NVIDIA Corp.', AAPL:'Apple Inc.', TSLA:'Tesla Inc.', MSFT:'Microsoft Corp.', SPY:'S&P 500 ETF',
};

function getFullName(symbol) { return fullNames[symbol.split('/')[0]] || symbol; }

const mcaps = {
  BTC:'$2.13T', ETH:'$364B', BNB:'$93B', SOL:'$82B', XRP:'$71B', DOGE:'$28B',
  GOLD:'$16.8T', SILVER:'$1.9T', OIL:'Commodity', NVDA:'$3.3T', AAPL:'$3.1T',
  TSLA:'$920B', MSFT:'$3.2T', SPY:'$580B',
};

function getMarketCap(symbol) { return mcaps[symbol.split('/')[0]] || '-'; }

function formatPrice(val) {
  return Number(val||0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

const recentWithdrawals = [
  {name:'alex_m94', amount:'$14,820', time:'2 hours ago', color:'#f7931a'},
  {name:'cryptoJade', amount:'$50,000', time:'5 hours ago', color:'#627eea'},
  {name:'traderKing_X', amount:'$11,500', time:'8 hours ago', color:'#0ecb81'},
  {name:'Maria_T', amount:'$28,200', time:'12 hours ago', color:'#9945ff'},
  {name:'SunilFX', amount:'$10,100', time:'1 day ago', color:'#f0b90b'},
  {name:'GoldHands88', amount:'$16,750', time:'1 day ago', color:'#ffd700'},
  {name:'neo_block', amount:'$42,000', time:'2 days ago', color:'#00aae4'},
  {name:'LunaRise', amount:'$12,300', time:'2 days ago', color:'#e31937'},
  {name:'BearKiller99', amount:'$18,900', time:'3 days ago', color:'#76b900'},
  {name:'CryptoElena', amount:'$10,400', time:'3 days ago', color:'#c2a633'},
  {name:'tradeXpert', amount:'$31,000', time:'4 days ago', color:'#00a4ef'},
  {name:'moonWalker', amount:'$22,500', time:'5 days ago', color:'#ff6b35'},
];

const features = [
  {icon:'⚡', title:'Sub-Millisecond Matching', desc:'Redis in-memory order books with atomic Lua scripts guarantee price-time priority matching at under 0.5ms latency.', bg:'#f0b90b'},
  {icon:'🔐', title:'Cold Wallet Security', desc:'Funds secured with industry-standard custody practices. Only a minimal hot wallet is maintained for daily operations.', bg:'#0ecb81'},
  {icon:'📊', title:'Double-Entry Accounting', desc:'Every debit and credit is immutably logged in our financial ledger, preventing negative balances with row-level locks.', bg:'#3b82f6'},
  {icon:'💸', title:'Instant USDT Withdrawals', desc:'Withdraw profits to any BEP20 wallet within minutes. No delays, no arbitrary limits on your earnings.', bg:'#a855f7'},
  {icon:'🎯', title:'Binary Options — 88% ROI', desc:'Trade short-term price direction on BTC, ETH, Gold, and more. Fixed risk, fixed reward, transparent settlement.', bg:'#f43f5e'},
  {icon:'🎮', title:'Free $10,000 Demo Account', desc:'Practice every strategy risk-free with $10,000 virtual balance. Reset anytime — no commitment required.', bg:'#10b981'},
];

const steps = [
  {icon:'📝', title:'Create Your Account', desc:'Register with an email address. No KYC required to start trading instantly.'},
  {icon:'💳', title:'Deposit USDT', desc:'Send USDT via BNB Smart Chain (BEP20) to your unique deposit address. Minimum: $10.'},
  {icon:'📈', title:'Start Trading', desc:'Choose Spot Trading or Binary Options. Place your first trade and track profits in real time.'},
];

const faqs = [
  {q:'What is the minimum deposit?', a:'The minimum deposit is $10 USDT via BNB Smart Chain (BEP20). Deposits are credited after 1 network confirmation, usually within 1-3 minutes.'},
  {q:'How do I deposit? Do I need Binance?', a:'You can send USDT (BEP20) from any exchange or wallet supporting BNB Smart Chain — including Binance, Trust Wallet, MetaMask (BSC), and others. We provide a detailed step-by-step Binance guide.'},
  {q:'What is the difference between Spot and Binary Options?', a:'Spot Trading lets you buy and sell asset pairs (e.g., BTC/USDT) at market prices. Binary Options are short-term contracts where you predict UP or DOWN within a fixed time, with up to 88% payout on a correct call.'},
  {q:'Can I use a demo account before depositing real funds?', a:'Yes! Every account comes with a free $10,000 demo balance. Practice Spot and Options trading with no real money at risk. Demo and real balances are completely isolated from each other.'},
  {q:'How fast are withdrawals processed?', a:'Withdrawals are typically processed within 10 minutes after admin approval. Funds are sent directly to your BEP20 USDT wallet on BNB Smart Chain with full transaction hash confirmation.'},
  {q:'Is my data and funds safe?', a:'Security is our top priority. User funds are secured with cold wallet storage, double-entry accounting ledgers, and encrypted data practices. We never store private keys on our servers.'},
];
</script>

<style scoped>
@keyframes ticker {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.animate-ticker {
  animation: ticker 45s linear infinite;
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>