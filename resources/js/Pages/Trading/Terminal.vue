<template>
  <div class="min-h-screen lg:h-screen flex flex-col bg-slate-950 text-slate-100 overflow-y-auto lg:overflow-hidden font-sans select-none pb-28 sm:pb-24 lg:pb-12">
    <!-- Top Bar Navigation Header -->
    <TradingHeader 
      :user="$page.props.auth.user"
      :markets="marketsList"
      :wallets="walletsList"
      @select-market="changeMarket"
      @account-mode-changed="(mode) => accountMode = mode"
      @open-deposit="showDepositModal = true"
      @open-login="showAuthModal = 'login'"
      @open-register="showAuthModal = 'register'"
    />

    <!-- 1. DESKTOP WORKSPACE (Visible on screens >= lg) -->
    <div class="hidden lg:grid flex-1 grid-cols-12 gap-1.5 p-1.5 overflow-hidden transition-all duration-300">
      <!-- Column 1: Market Search & Selection (Collapsible) -->
      <div :class="isMarketCollapsed ? 'col-span-1' : 'col-span-2'" class="h-full overflow-hidden transition-all duration-300">
        <MarketList 
          :markets="marketsList"
          :currentSymbol="currentMarketState.symbol"
          @select-market="changeMarket"
          @toggle-collapse="(val) => isMarketCollapsed = val"
        />
      </div>

      <!-- Column 2: Center Interactive Chart & Bottom Orders -->
      <div :class="isMarketCollapsed ? 'col-span-7' : 'col-span-6'" class="flex flex-col space-y-1.5 h-full overflow-hidden transition-all duration-300">
        <!-- Candlestick Chart -->
        <div class="flex-1 min-h-[340px]">
          <TradingViewChart 
            :symbol="currentMarketState.symbol"
            :market="currentMarketState"
            :orderBook="orderBookState"
            :trades="tradesList"
          />
        </div>

        <!-- Open Orders / Positions / History Tabs -->
        <div class="h-44">
          <UserOrders 
            :orders="openOrdersList"
            :history="orderHistoryList"
            :wallets="walletsList"
            :markets="marketsList"
            :accountMode="accountMode"
            @order-cancelled="fetchUserData"
          />
        </div>
      </div>

      <!-- Column 3: Order Book & Recent Trades (2 Cols) -->
      <div class="col-span-2 flex flex-col space-y-1.5 h-full overflow-hidden">
        <div class="flex-1 min-h-[260px]">
          <OrderBook 
            :bids="orderBookState.bids"
            :asks="orderBookState.asks"
            :lastPrice="Number(currentMarketState.last_price || 0)"
            @select-price="onSelectPrice"
          />
        </div>
        <div class="h-44">
          <RecentTrades :trades="tradesList" />
        </div>
      </div>

      <!-- Column 4: Buy / Sell Order Terminal (2 Cols) -->
      <div class="col-span-2 h-full overflow-y-auto">
        <OrderTerminal 
          :market="currentMarketState"
          :wallets="walletsList"
          :selectedPrice="selectedTerminalPrice"
          :accountMode="accountMode"
          @order-placed="onOrderPlaced"
        />
      </div>
    </div>

    <!-- 2. MOBILE DEDICATED WORKSPACE (Visible on screens < lg) -->
    <div class="lg:hidden flex-1 flex flex-col overflow-y-auto space-y-2 pb-24">
      <!-- A. Swipeable Market Carousel & Pair Info Bar -->
      <MobileMarketBar 
        :markets="marketsList"
        :currentMarket="currentMarketState"
        :currentSymbol="currentMarketState.symbol"
        @select-market="changeMarket"
      />

      <!-- B. Responsive TradingView Chart -->
      <div class="h-[310px] sm:h-[350px] w-full bg-slate-900 border-b border-slate-800 shrink-0">
        <TradingViewChart 
          :symbol="currentMarketState.symbol"
          :market="currentMarketState"
          :orderBook="orderBookState"
          :trades="tradesList"
        />
      </div>

      <!-- C. Mobile Segmented Navigation Tabs -->
      <div class="px-2 pt-1">
        <div class="grid grid-cols-4 gap-1 bg-slate-900/90 border border-slate-800 p-1 rounded-xl text-xs font-semibold shadow-inner">
          <button @click="mobileTab = 'order'" 
                  :class="mobileTab === 'order' ? 'bg-[#f0b90b] text-[#1e2329] font-black shadow-sm' : 'text-slate-400 hover:text-white'"
                  class="py-2 rounded-lg transition flex items-center justify-center space-x-1 text-center">
            <span>⚡</span>
            <span>Trade</span>
          </button>
          <button @click="mobileTab = 'book'" 
                  :class="mobileTab === 'book' ? 'bg-slate-800 text-emerald-400 font-bold border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-white'"
                  class="py-2 rounded-lg transition flex items-center justify-center space-x-1 text-center">
            <span>📖</span>
            <span>Book</span>
          </button>
          <button @click="mobileTab = 'trades'" 
                  :class="mobileTab === 'trades' ? 'bg-slate-800 text-emerald-400 font-bold border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-white'"
                  class="py-2 rounded-lg transition flex items-center justify-center space-x-1 text-center">
            <span>🕒</span>
            <span>Trades</span>
          </button>
          <button @click="mobileTab = 'orders'" 
                  :class="mobileTab === 'orders' ? 'bg-slate-800 text-emerald-400 font-bold border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-white'"
                  class="py-2 rounded-lg transition flex items-center justify-center space-x-1 text-center">
            <span>📋</span>
            <span>Orders</span>
            <span v-if="openOrdersList.length > 0" class="bg-emerald-500 text-slate-950 text-[10px] px-1 rounded-full font-mono font-bold">{{ openOrdersList.length }}</span>
          </button>
        </div>
      </div>

      <!-- D. Mobile Tab Content Panels -->
      <!-- Panel 1: Order Terminal (Trade) -->
      <div v-show="mobileTab === 'order'" class="px-2">
        <OrderTerminal 
          :market="currentMarketState"
          :wallets="walletsList"
          :selectedPrice="selectedTerminalPrice"
          :accountMode="accountMode"
          :initialSide="mobileSide"
          @order-placed="onOrderPlaced"
        />
      </div>

      <!-- Panel 2: Order Book -->
      <div v-show="mobileTab === 'book'" class="px-2 space-y-2">
        <div class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-slate-400 flex items-center justify-between">
          <span>💡 Tap any price below to copy it into your trade form</span>
          <span class="text-[#f0b90b] font-bold">1-Tap Fill</span>
        </div>
        <div class="h-[430px] bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
          <OrderBook 
            :bids="orderBookState.bids"
            :asks="orderBookState.asks"
            :lastPrice="Number(currentMarketState.last_price || 0)"
            @select-price="handlePriceFromBook"
          />
        </div>
      </div>

      <!-- Panel 3: Recent Trades -->
      <div v-show="mobileTab === 'trades'" class="px-2">
        <div class="h-[430px] bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
          <RecentTrades :trades="tradesList" />
        </div>
      </div>

      <!-- Panel 4: My Orders & Positions -->
      <div v-show="mobileTab === 'orders'" class="px-2">
        <div class="min-h-[380px] bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
          <UserOrders 
            :orders="openOrdersList"
            :history="orderHistoryList"
            :wallets="walletsList"
            :markets="marketsList"
            :accountMode="accountMode"
            @order-cancelled="fetchUserData"
          />
        </div>
      </div>

      <!-- E. Sticky Floating Mobile Quick Action Bar (Above Marquee Ticker) -->
      <div class="fixed bottom-9 left-0 right-0 z-40 px-3 py-1.5 bg-slate-950/95 backdrop-blur-md border-t border-slate-800 flex items-center space-x-2 shadow-2xl">
        <button @click="openMobileTrade('buy')" 
                class="flex-1 bg-[#0ecb81] hover:bg-[#0bb573] active:scale-[0.98] text-[#1e2329] font-black py-2.5 px-3 rounded-xl text-xs uppercase tracking-wider flex items-center justify-center space-x-1.5 shadow-lg shadow-emerald-500/20 transition">
          <span>▲</span>
          <span>BUY {{ currentMarketState.base_currency }}</span>
        </button>
        <button @click="openMobileTrade('sell')" 
                class="flex-1 bg-[#f6465d] hover:bg-[#e0384f] active:scale-[0.98] text-white font-black py-2.5 px-3 rounded-xl text-xs uppercase tracking-wider flex items-center justify-center space-x-1.5 shadow-lg shadow-rose-500/20 transition">
          <span>▼</span>
          <span>SELL {{ currentMarketState.base_currency }}</span>
        </button>
      </div>
    </div>

    <!-- Modals -->
    <DepositModal 
      v-if="showDepositModal"
      :custodialAddress="custodialAddress"
      @close="showDepositModal = false"
      @deposit-submitted="fetchUserData"
    />

    <!-- Auth Modal (Login / Register) -->
    <div v-if="showAuthModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-slate-900 border border-slate-800 rounded-xl w-full max-w-sm p-5 space-y-4 shadow-2xl text-xs">
        <div class="flex justify-between items-center border-b border-slate-800 pb-2">
          <h3 class="font-bold text-slate-100 text-sm capitalize">{{ showAuthModal }} Account</h3>
          <button @click="showAuthModal = null" class="text-slate-500 hover:text-slate-300">✕</button>
        </div>

        <form @submit.prevent="handleAuthSubmit" class="space-y-3">
          <div v-if="showAuthModal === 'register'">
            <label class="block text-slate-400 mb-1">Full Name</label>
            <input v-model="authForm.name" type="text" required class="w-full bg-slate-950 border border-slate-800 rounded px-2.5 py-1.5 text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <div>
            <label class="block text-slate-400 mb-1">Email Address</label>
            <input v-model="authForm.email" type="email" required class="w-full bg-slate-950 border border-slate-800 rounded px-2.5 py-1.5 text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <div>
            <label class="block text-slate-400 mb-1">Password</label>
            <input v-model="authForm.password" type="password" required class="w-full bg-slate-950 border border-slate-800 rounded px-2.5 py-1.5 text-slate-100 focus:outline-none focus:border-emerald-500" />
          </div>

          <div v-if="showAuthModal === 'register'" class="flex items-center space-x-2 pt-1">
            <input v-model="authForm.accepted_terms" type="checkbox" required id="reg-terms" class="rounded bg-slate-950 border-slate-800 text-emerald-500" />
            <label for="reg-terms" class="text-slate-400 text-[11px]">I agree to the <a href="/terms" target="_blank" class="text-emerald-400 underline">Terms of Service</a></label>
          </div>

          <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded transition">
            {{ showAuthModal === 'login' ? 'Log In' : 'Register' }}
          </button>
        </form>
      </div>
    </div>
    <!-- Bottom Live Right-to-Left Ticker Bar -->
    <BottomMarketTicker :markets="marketsList" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

import TradingHeader from '@/Components/TradingHeader.vue';
import MarketList from '@/Components/MarketList.vue';
import TradingViewChart from '@/Components/TradingViewChart.vue';
import OrderBook from '@/Components/OrderBook.vue';
import RecentTrades from '@/Components/RecentTrades.vue';
import OrderTerminal from '@/Components/OrderTerminal.vue';
import UserOrders from '@/Components/UserOrders.vue';
import DepositModal from '@/Components/DepositModal.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';
import MobileMarketBar from '@/Components/MobileMarketBar.vue';

const props = defineProps({
  currentMarket: Object,
  markets: Array,
  orderBook: Object,
  recentTrades: Array,
  userWallets: Array,
  userOrders: Array,
  custodialAddress: String,
});

const page = usePage();
const accountMode = ref(localStorage.getItem('trade_account_mode') || 'demo');

const isMarketCollapsed = ref(true); // Sunken / Collapsed by default
const currentMarketState = ref(props.currentMarket || {});
const marketsList = ref(props.markets || []);
const orderBookState = ref(props.orderBook || { bids: [], asks: [] });
const tradesList = ref(props.recentTrades || []);
const walletsList = ref(props.userWallets || []);
const openOrdersList = ref(props.userOrders || []);
const orderHistoryList = ref([]);

const showDepositModal = ref(false);
const showAuthModal = ref(null);
const selectedTerminalPrice = ref(null);

const mobileTab = ref('order'); // 'order', 'book', 'trades', 'orders'
const mobileSide = ref('buy');

function openMobileTrade(side) {
  mobileSide.value = side;
  mobileTab.value = 'order';
}

function handlePriceFromBook(price) {
  selectedTerminalPrice.value = price;
  mobileTab.value = 'order';
}

let liveWs = null;
let tickerWs = null;
let simInterval = null;

const authForm = ref({
  name: '',
  email: '',
  password: '',
  accepted_terms: false,
});

function changeMarket(symbol) {
  const formatted = symbol.replace('/', '_');
  router.visit(`/trade/${formatted}`, { preserveState: false });
}

function onSelectPrice(price) {
  selectedTerminalPrice.value = price;
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

async function fetchUserData() {
  if (!page.props.auth.user) return;
  try {
    const resOrders = await axios.get('/api/orders');
    openOrdersList.value = resOrders.data.open_orders || [];
    orderHistoryList.value = resOrders.data.order_history || [];

    const resDep = await axios.get('/api/deposits');
    walletsList.value = resDep.data.wallets || [];
  } catch (e) {}
}

function onOrderPlaced() {
  fetchUserData();
}

async function handleAuthSubmit() {
  const url = showAuthModal.value === 'login' ? '/api/login' : '/api/register';
  try {
    await axios.post(url, authForm.value);
    window.location.reload();
  } catch (err) {
    alert(err.response?.data?.message || 'Authentication error.');
  }
}

// Direct Public WebSocket Live Streamer for Real-Time Trades & Tickers
function startLiveMarketStream(symbol) {
  const cleanSym = symbol.replace('/', '').toLowerCase();
  const base = symbol.split('/')[0];

  // 1. Connect to Binance Public Combined Stream for Trades + Depth
  if (['BTC', 'ETH', 'BNB', 'SOL', 'XRP', 'DOGE'].includes(base)) {
    try {
      if (liveWs) liveWs.close();
      liveWs = new WebSocket(`wss://stream.binance.com:9443/stream?streams=${cleanSym}@trade/${cleanSym}@depth10@100ms`);

      liveWs.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        const data = msg.data || msg;

        // Trade execution event
        if (data.e === 'trade') {
          const price = parseFloat(data.p);
          const qty = parseFloat(data.q);

          const newTrade = {
            id: data.t,
            price,
            quantity: qty,
            side: data.m ? 'sell' : 'buy',
            timestamp: new Date(data.T).toISOString(),
          };

          tradesList.value.unshift(newTrade);
          if (tradesList.value.length > 100) tradesList.value.pop();

          currentMarketState.value.last_price = price;
          if (price > (currentMarketState.value.high_24h || 0)) currentMarketState.value.high_24h = price;
          if (price < (currentMarketState.value.low_24h || 0) || currentMarketState.value.low_24h == 0) currentMarketState.value.low_24h = price;

          const idx = marketsList.value.findIndex(m => m.symbol === symbol);
          if (idx !== -1) {
            marketsList.value[idx].last_price = price;
          }
        }

        // Depth update event
        if (data.bids && data.asks) {
          orderBookState.value = {
            bids: data.bids.map(b => ({ price: parseFloat(b[0]), quantity: parseFloat(b[1]) })),
            asks: data.asks.map(a => ({ price: parseFloat(a[0]), quantity: parseFloat(a[1]) })),
          };
        }
      };
    } catch (e) {}
  } else {
    // Non-crypto live price simulator (Gold, Stocks, Oil)
    if (simInterval) clearInterval(simInterval);
    simInterval = setInterval(() => {
      const currentPrice = currentMarketState.value.last_price || 100;
      const variation = (Math.random() - 0.49) * (currentPrice * 0.001);
      const price = parseFloat((currentPrice + variation).toFixed(2));
      const qty = parseFloat((Math.random() * 5 + 0.1).toFixed(4));
      const side = variation >= 0 ? 'buy' : 'sell';

      const newTrade = {
        id: Date.now(),
        price,
        quantity: qty,
        side,
        timestamp: new Date().toISOString(),
      };

      tradesList.value.unshift(newTrade);
      if (tradesList.value.length > 100) tradesList.value.pop();

      currentMarketState.value.last_price = price;
      const idx = marketsList.value.findIndex(m => m.symbol === symbol);
      if (idx !== -1) {
        marketsList.value[idx].last_price = price;
      }
    }, 1500);
  }

  // 2. Connect to Global Array Tickers Stream for Sidebar Live Updates
  try {
    if (!tickerWs) {
      tickerWs = new WebSocket(`wss://stream.binance.com:9443/ws/!ticker@arr`);
      tickerWs.onmessage = (event) => {
        const arr = JSON.parse(event.data);
        const updates = new Map();
        for (let i = 0; i < arr.length; i++) {
          updates.set(arr[i].s, arr[i]);
        }
        
        marketsList.value.forEach((m) => {
          const clean = m.symbol.replace('/', '');
          const t = updates.get(clean);
          if (t) {
            m.last_price = parseFloat(t.c);
            m.change_24h = parseFloat(t.P);
            m.high_24h = parseFloat(t.h);
            m.low_24h = parseFloat(t.l);
          }
        });
      };
    }
  } catch (e) {}
}

let userPollTimer = null;

onMounted(() => {
  fetchUserData();
  startLiveMarketStream(currentMarketState.value.symbol);

  if (page.props.auth.user) {
    userPollTimer = setInterval(fetchUserData, 15000);
  }

  // Laravel Reverb Private Channels for User Balance & Admin Alerts
  const channelName = 'market.' + currentMarketState.value.symbol.replace('/', '_');
  if (window.Echo) {
    try {
      window.Echo.channel(channelName)
        .listen('OrderBookUpdated', (e) => {
          orderBookState.value = { bids: e.bids, asks: e.asks };
        })
        .listen('TradeExecuted', (e) => {
          tradesList.value.unshift(e);
          if (tradesList.value.length > 100) tradesList.value.pop();
          currentMarketState.value.last_price = e.price;
        });

      if (page.props.auth.user) {
        window.Echo.private(`user.${page.props.auth.user.id}`)
          .listen('DepositApprovedEvent', (e) => {
            alert(`🎉 ${e.message}`);
            fetchUserData();
          });
      }
    } catch (err) {}
  }
});

onUnmounted(() => {
  if (liveWs) liveWs.close();
  if (tickerWs) tickerWs.close();
  if (simInterval) clearInterval(simInterval);
  if (userPollTimer) clearInterval(userPollTimer);
});
</script>
