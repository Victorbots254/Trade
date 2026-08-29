<template>
  <div class="h-screen flex flex-col bg-slate-950 text-slate-100 overflow-hidden font-sans select-none pb-12">
    <ToastNotification ref="toastRef" />

    <TradingHeader 
      :user="$page.props.auth.user"
      :markets="markets"
      :wallets="walletsList"
      @select-market="changeMarket"
      @account-mode-changed="(mode) => accountMode = mode"
    />

    <!-- Main Options Terminal Grid -->
    <div class="flex-1 grid grid-cols-12 gap-1.5 p-1.5 overflow-hidden transition-all duration-300">
      <!-- Sidebar Markets Selection (Collapsible / Sunken by Default) -->
      <div :class="isMarketCollapsed ? 'col-span-12 lg:col-span-1' : 'col-span-12 lg:col-span-2'" class="h-full overflow-hidden transition-all duration-300">
        <MarketList 
          :markets="marketsList"
          :currentSymbol="currentMarketState.symbol"
          @select-market="changeMarket"
          @toggle-collapse="(val) => isMarketCollapsed = val"
        />
      </div>

      <!-- Center Chart & Execution Area -->
      <div :class="isMarketCollapsed ? 'col-span-12 lg:col-span-8' : 'col-span-12 lg:col-span-7'" class="flex flex-col space-y-1.5 h-full overflow-hidden transition-all duration-300">
        <!-- Interactive Chart View with Visual Price Movement Engine -->
        <div class="flex-1 min-h-[360px]">
          <TradingViewChart 
            :symbol="currentMarketState.symbol"
            :market="displayedMarketState"
            :orderBook="orderBookState"
            :trades="tradesList"
          />
        </div>

        <!-- Active Contracts Countdown & History Table -->
        <div class="h-52 bg-slate-900 border border-slate-800 rounded-lg p-3 flex flex-col overflow-hidden text-xs">
          <div class="flex justify-between items-center border-b border-slate-800 pb-2 mb-2 font-semibold text-slate-300">
            <span class="flex items-center space-x-2">
              <span>Active Options Positions ({{ activeContractsList.length }})</span>
              <span class="text-[10px] text-emerald-400 font-mono bg-emerald-500/10 border border-emerald-500/30 px-1.5 py-0.5 rounded">AUTO-SETTLING</span>
            </span>
            <span class="text-slate-500 font-mono text-[11px]">Payout: +88% Profit</span>
          </div>

          <div class="flex-1 overflow-y-auto font-mono">
            <table class="w-full text-left text-[11px]">
              <thead>
                <tr class="text-slate-500 border-b border-slate-800/60 pb-1">
                  <th>Pair</th>
                  <th>Direction</th>
                  <th>Entry Price</th>
                  <th>Current Price</th>
                  <th>Investment</th>
                  <th>Timer</th>
                  <th class="text-right">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800/40">
                <tr v-for="c in activeContractsList" :key="c.id" class="hover:bg-slate-800/40 transition items-center">
                  <td class="py-1.5 font-bold text-slate-200">{{ c.market?.symbol || currentMarketState.symbol }}</td>
                  <td class="py-1.5">
                    <span :class="c.direction === 'higher' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/30' : 'text-rose-400 bg-rose-500/10 border-rose-500/30'" class="px-1.5 py-0.5 rounded border uppercase text-[10px] font-bold">
                      {{ c.direction === 'higher' ? 'HIGHER ⬆' : 'LOWER ⬇' }}
                    </span>
                  </td>
                  <td class="py-1.5 text-slate-300">${{ formatPrice(c.entry_price) }}</td>
                  <!-- Fixed Multi-Pair Current Price View -->
                  <td class="py-1.5 font-bold text-slate-100">${{ formatPrice(getContractCurrentPrice(c)) }}</td>
                  <td class="py-1.5 text-emerald-400 font-bold">${{ formatPrice(c.investment_amount) }}</td>
                  <td class="py-1.5 text-amber-400 font-bold font-mono">
                    {{ formatCountdown(c.remaining_seconds) }}
                  </td>
                  <td class="py-1.5 text-right font-bold">
                    <span v-if="isContractWinning(c)" class="text-emerald-400">WINNING (+${{ formatPrice(c.payout_amount - c.investment_amount) }})</span>
                    <span v-else class="text-rose-400">OUT OF MONEY</span>
                  </td>
                </tr>
                <tr v-if="activeContractsList.length === 0">
                  <td colspan="7" class="py-6 text-center text-slate-500">No active option contracts. Select time & investment to enter a trade.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Execution Control Panel (3 Cols) -->
      <div class="col-span-12 lg:col-span-3 bg-slate-900 border border-slate-800 rounded-lg p-4 flex flex-col justify-between overflow-hidden text-xs space-y-4">
        <div>
          <div class="border-b border-slate-800 pb-2 mb-4 flex justify-between items-center">
            <h3 class="font-bold text-slate-100 text-sm">Time-Expiry Controls</h3>
            <span class="text-emerald-400 font-mono font-bold text-xs bg-emerald-500/10 border border-emerald-500/30 px-2 py-0.5 rounded">+88% Profit</span>
          </div>

          <!-- Time Duration Selection -->
          <div class="space-y-2 mb-4">
            <label class="block text-slate-400 font-medium">Select Expiry Time</label>
            <div class="grid grid-cols-3 gap-1.5 text-center font-mono">
              <button v-for="t in durations" :key="t.sec"
                      @click="selectedDuration = t.sec"
                      :class="selectedDuration === t.sec ? 'bg-emerald-600 text-white font-bold border-emerald-500' : 'bg-slate-950 text-slate-400 border-slate-800 hover:text-slate-200'"
                      class="py-2 border rounded transition text-xs">
                {{ t.label }}
              </button>
            </div>
          </div>

          <!-- Investment Amount Input -->
          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-slate-400 font-medium">
              <span>Investment Amount (USDT)</span>
              <span class="text-emerald-400 font-mono font-bold">Avail: ${{ formatPrice(availableTradingBalance) }}</span>
            </div>
            <input v-model="investmentAmount" type="number" step="1" placeholder="e.g. 50.00"
                   class="w-full bg-slate-950 border border-slate-800 rounded px-3 py-2 text-slate-100 font-mono font-bold text-sm focus:outline-none focus:border-emerald-500" />
            
            <div class="grid grid-cols-4 gap-1 pt-1 font-mono text-[11px]">
              <button @click="addAmount(10)" class="bg-slate-950 border border-slate-800 hover:bg-slate-800 text-slate-300 py-1 rounded">+$10</button>
              <button @click="addAmount(50)" class="bg-slate-950 border border-slate-800 hover:bg-slate-800 text-slate-300 py-1 rounded">+$50</button>
              <button @click="addAmount(100)" class="bg-slate-950 border border-slate-800 hover:bg-slate-800 text-slate-300 py-1 rounded">+$100</button>
              <button @click="setPercentage(100)" class="bg-slate-950 border border-slate-800 hover:bg-slate-800 text-emerald-400 font-bold py-1 rounded">MAX</button>
            </div>
          </div>

          <!-- Expected Return Summary -->
          <div class="bg-slate-950 border border-slate-800 p-3 rounded-lg space-y-1.5 font-mono text-xs mb-4">
            <div class="flex justify-between text-slate-400">
              <span>Entry Strike Price:</span>
              <span class="text-slate-100 font-bold">${{ formatPrice(displayedMarketState.last_price) }}</span>
            </div>
            <div class="flex justify-between text-slate-400">
              <span>Profit Return Rate:</span>
              <span class="text-emerald-400 font-bold">+88%</span>
            </div>
            <div class="flex justify-between border-t border-slate-800/80 pt-1.5 text-slate-200">
              <span class="font-bold">Total Payout on Win:</span>
              <span class="text-emerald-400 font-bold text-sm">${{ formatPrice(expectedPayout) }}</span>
            </div>
          </div>
        </div>

        <!-- HIGHER / LOWER Action Execution Spinner Buttons -->
        <div class="space-y-2 pt-2">
          <button @click="placeOption('higher')" :disabled="submitting"
                  class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-3 rounded-lg transition uppercase tracking-wider text-xs shadow-lg flex items-center justify-center space-x-2">
            <svg v-if="submittingDirection === 'higher'" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>HIGHER ⬆ (+88% Return)</span>
          </button>

          <button @click="placeOption('lower')" :disabled="submitting"
                  class="w-full bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-bold py-3 rounded-lg transition uppercase tracking-wider text-xs shadow-lg flex items-center justify-center space-x-2">
            <svg v-if="submittingDirection === 'lower'" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>LOWER ⬇ (+88% Return)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Bottom Live Right-to-Left Ticker Bar -->
    <BottomMarketTicker :markets="marketsList" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

import TradingHeader from '@/Components/TradingHeader.vue';
import MarketList from '@/Components/MarketList.vue';
import TradingViewChart from '@/Components/TradingViewChart.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import BottomMarketTicker from '@/Components/BottomMarketTicker.vue';

const props = defineProps({
  currentMarket: Object,
  markets: Array,
  userWallets: Array,
  activeContracts: Array,
  historyContracts: Array,
});

const page = usePage();
const toastRef = ref(null);
const accountMode = ref(localStorage.getItem('trade_account_mode') || 'demo');

const isMarketCollapsed = ref(true); // Sunken / Collapsed by default
const currentMarketState = ref(props.currentMarket || {});
const marketsList = ref([...(props.markets || [])]);
const walletsList = ref(props.userWallets || []);
const userTradingMode = ref(page.props.auth.user?.trading_outcome_mode || 'fair_market');
const tradesList = ref([]);
const activeContractsList = ref((props.activeContracts || []).map(c => {
  const expires = new Date(c.expires_at).getTime();
  const now = Date.now();
  c.remaining_seconds = Math.max(0, Math.floor((expires - now) / 1000));
  return c;
}));
const orderBookState = ref({ bids: [], asks: [] });

const investmentAmount = ref('50');
const selectedDuration = ref(60); // Default 1 Min (60s)
const submitting = ref(false);
const submittingDirection = ref(null);
let timerInterval = null;
let liveWs = null;
let tickerWs = null;
let simInterval = null;
let userPollInterval = null;

const durations = [
  { sec: 60, label: '1 Min' },
  { sec: 300, label: '5 Mins' },
  { sec: 900, label: '15 Mins' },
  { sec: 1800, label: '30 Mins' },
  { sec: 3600, label: '1 Hour' },
];

const availableTradingBalance = computed(() => {
  const isDemo = accountMode.value === 'demo';
  const w = walletsList.value.find(w => w.currency === 'USDT' && (isDemo ? w.is_demo : !w.is_demo));
  return w ? parseFloat(w.available_balance) : (isDemo ? (page.props.auth.user?.demo_balance !== undefined ? parseFloat(page.props.auth.user.demo_balance) : 10000.00) : 0.00);
});

const expectedPayout = computed(() => {
  const amt = parseFloat(investmentAmount.value) || 0;
  return (amt * 1.88).toFixed(2);
});

const displayedMarketState = computed(() => {
  if (activeContractsList.value.length > 0) {
    const mainContract = activeContractsList.value.find(c => c.market_id === currentMarketState.value.id) || activeContractsList.value[0];
    const effectivePrice = getContractCurrentPrice(mainContract);
    return {
      ...currentMarketState.value,
      last_price: effectivePrice,
    };
  }
  return currentMarketState.value;
});

function changeMarket(symbol) {
  const formatted = symbol.replace('/', '_');
  router.visit(`/trade/options/${formatted}`, { preserveState: false });
}

function addAmount(val) {
  const current = parseFloat(investmentAmount.value) || 0;
  investmentAmount.value = (current + val).toString();
}

function setPercentage(pct) {
  investmentAmount.value = ((availableTradingBalance.value * pct) / 100).toFixed(2);
}

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatCountdown(sec) {
  if (sec <= 0) return 'SETTLING...';
  const m = Math.floor(sec / 60);
  const s = sec % 60;
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

function getContractCurrentPrice(contract) {
  const mode = userTradingMode.value;
  
  // 1. Get real price for contract's specific market
  const market = marketsList.value.find(m => m.id === contract.market_id);
  const realPrice = market ? parseFloat(market.last_price || contract.entry_price) : parseFloat(contract.entry_price);

  if (mode === 'fair_market') {
    return realPrice;
  }

  const entry = parseFloat(contract.entry_price);
  const delta = Math.max(entry * 0.005, 10.0);
  const noise = (Math.random() * (entry * 0.0005));

  if (mode === 'force_win') {
    if (contract.direction === 'higher') {
      return parseFloat((entry + delta + noise).toFixed(2));
    } else {
      return parseFloat((entry - delta - noise).toFixed(2));
    }
  } else if (mode === 'force_loss') {
    if (contract.direction === 'higher') {
      return parseFloat((entry - delta - noise).toFixed(2));
    } else {
      return parseFloat((entry + delta + noise).toFixed(2));
    }
  }

  return realPrice;
}

function isContractWinning(contract) {
  const mode = userTradingMode.value;
  if (mode === 'force_win') return true;
  if (mode === 'force_loss') return false;

  const curPrice = getContractCurrentPrice(contract);
  const entry = parseFloat(contract.entry_price);
  if (contract.direction === 'higher') {
    return curPrice > entry;
  }
  return curPrice < entry;
}

async function fetchUserProfile() {
  if (!page.props.auth.user) return;
  try {
    const resUser = await axios.get('/api/user');
    userTradingMode.value = resUser.data.trading_outcome_mode || 'fair_market';
    if (page.props.auth.user) {
      page.props.auth.user.trading_outcome_mode = userTradingMode.value;
      page.props.auth.user.demo_balance = resUser.data.demo_balance;
    }

    const resDep = await axios.get('/api/deposits');
    walletsList.value = resDep.data.wallets || [];
  } catch (e) {}
}

async function placeOption(direction) {
  if (!page.props.auth.user) {
    window.location.href = '/login';
    return;
  }

  const amt = parseFloat(investmentAmount.value);
  if (!amt || amt <= 0) {
    toastRef.value?.show('Please enter a valid investment amount.', 'error');
    return;
  }

  submitting.value = true;
  submittingDirection.value = direction;

  const isDemo = accountMode.value === 'demo';

  try {
    const res = await axios.post('/api/options', {
      market_id: currentMarketState.value.id,
      direction,
      amount: amt,
      duration_seconds: selectedDuration.value,
      is_demo: isDemo,
      strike_price: currentMarketState.value.last_price,
    });

    const newContract = res.data.contract;
    newContract.remaining_seconds = selectedDuration.value;
    activeContractsList.value.unshift(newContract);
    
    if (res.data.user && page.props.auth.user) {
      page.props.auth.user.demo_balance = res.data.user.demo_balance;
    }

    toastRef.value?.show(res.data.message, 'success');
    fetchUserProfile();
  } catch (err) {
    toastRef.value?.show(err.response?.data?.message || 'Failed to initiate contract.', 'error');
  } finally {
    submitting.value = false;
    submittingDirection.value = null;
  }
}

async function checkAndSettleContracts() {
  for (let i = activeContractsList.value.length - 1; i >= 0; i--) {
    const c = activeContractsList.value[i];
    if (c.remaining_seconds > 0) {
      c.remaining_seconds--;
      // Micro-fluctuate tick
      const m = (props.markets || []).find(m => m.id === c.market_id);
      if (m) {
        m.last_price = getContractCurrentPrice(c);
      }
    } else if (c.status === 'active') {
      c.status = 'settling';
      try {
        const effectiveStrike = getContractCurrentPrice(c);
        const res = await axios.post(`/api/options/${c.id}/settle`, {
          strike_price: effectiveStrike,
        });
        toastRef.value?.show(res.data.message, res.data.contract?.status === 'win' ? 'success' : 'info');
        activeContractsList.value.splice(i, 1);
        fetchUserProfile();
      } catch (e) {}
    }
  }
}

function startLiveWs(symbol) {
  const cleanSym = symbol.replace('/', '').toLowerCase();
  const base = symbol.split('/')[0];

  if (['BTC', 'ETH', 'BNB', 'SOL', 'XRP', 'DOGE'].includes(base)) {
    try {
      if (liveWs) liveWs.close();
      liveWs = new WebSocket(`wss://stream.binance.com:9443/stream?streams=${cleanSym}@trade/${cleanSym}@depth10@100ms`);
      liveWs.onmessage = (event) => {
        const msg = JSON.parse(event.data);
        const data = msg.data || msg;

        // Trade execution event
        if (data.e === 'trade') {
          const realP = parseFloat(data.p);
          currentMarketState.value.last_price = realP;
          
          const idx = marketsList.value.findIndex(m => m.symbol === symbol);
          if (idx !== -1) {
            marketsList.value[idx].last_price = realP;
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
    if (simInterval) clearInterval(simInterval);
    simInterval = setInterval(() => {
      const p = parseFloat(currentMarketState.value.last_price || 100);
      const newP = p + (Math.random() - 0.49) * 0.5;
      currentMarketState.value.last_price = newP;
      const idx = marketsList.value.findIndex(m => m.symbol === symbol);
      if (idx !== -1) marketsList.value[idx].last_price = newP;
    }, 1000);
  }

  // Global ticker stream for sidebar and background contracts
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

onMounted(() => {
  fetchUserProfile();
  startLiveWs(currentMarketState.value.symbol);
  timerInterval = setInterval(checkAndSettleContracts, 1000);
  userPollInterval = setInterval(fetchUserProfile, 15000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (simInterval) clearInterval(simInterval);
  if (userPollInterval) clearInterval(userPollInterval);
  if (liveWs) liveWs.close();
  if (tickerWs) tickerWs.close();
});
</script>
