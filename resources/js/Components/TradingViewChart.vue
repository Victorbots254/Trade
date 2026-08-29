<template>
  <div class="bg-slate-900 border border-slate-800 rounded-lg flex flex-col h-full overflow-hidden relative">
    <!-- Chart Header Bar -->
    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-800 text-xs select-none">
      <div class="flex items-center space-x-3">
        <span class="font-bold text-slate-200 text-sm">{{ symbol }}</span>
        <span class="font-mono text-emerald-400 font-bold text-sm">${{ formatPrice(market?.last_price) }}</span>
        <span :class="market?.change_24h >= 0 ? 'text-emerald-400' : 'text-rose-400'" class="font-mono font-medium">
          {{ market?.change_24h >= 0 ? '+' : '' }}{{ market?.change_24h }}%
        </span>
        <span class="hidden md:inline text-slate-500">High: <span class="text-slate-300 font-mono">${{ formatPrice(market?.high_24h) }}</span></span>
        <span class="hidden md:inline text-slate-500">Low: <span class="text-slate-300 font-mono">${{ formatPrice(market?.low_24h) }}</span></span>
      </div>

      <div class="flex items-center space-x-2">
        <div class="flex items-center space-x-1 bg-slate-950 p-0.5 rounded border border-slate-800 text-[11px]">
          <button @click="activeTab = 'tv_widget'" :class="activeTab === 'tv_widget' ? 'bg-slate-800 text-emerald-400 font-semibold shadow' : 'text-slate-400 hover:text-slate-200'" class="px-2.5 py-0.5 rounded transition">TradingView Technical Chart</button>
          <button @click="activeTab = 'candles'" :class="activeTab === 'candles' ? 'bg-slate-800 text-emerald-400 font-semibold shadow' : 'text-slate-400 hover:text-slate-200'" class="px-2.5 py-0.5 rounded transition">Engine Feed</button>
          <button @click="activeTab = 'depth'" :class="activeTab === 'depth' ? 'bg-slate-800 text-emerald-400 font-semibold shadow' : 'text-slate-400 hover:text-slate-200'" class="px-2.5 py-0.5 rounded transition">Depth Chart</button>
        </div>

        <!-- 1-Click Expand / Fullscreen Chart Button -->
        <button @click="toggleFullscreen" 
                title="Expand TradingView Chart to Fullscreen"
                class="bg-slate-950 hover:bg-slate-800 text-slate-300 hover:text-emerald-400 border border-slate-800 px-2 py-1 rounded text-xs font-bold transition flex items-center space-x-1">
          <span>⛶ Expand</span>
        </button>
      </div>
    </div>

    <!-- Mode 1: OFFICIAL TRADINGVIEW ADVANCED PRO TECHNICAL CHART -->
    <div v-show="activeTab === 'tv_widget'" class="flex-1 w-full h-full bg-slate-950 relative overflow-hidden">
      <div class="tradingview-widget-container h-full w-full" ref="tvWidgetContainer">
        <div class="tradingview-widget-container__widget h-full w-full"></div>
      </div>
    </div>

    <!-- Mode 2: Interactive Native Lightweight Charts Container -->
    <div v-show="activeTab === 'candles'" class="flex-1 w-full h-full relative min-h-[340px]" ref="chartContainer"></div>

    <!-- Mode 3: Depth Chart View -->
    <div v-show="activeTab === 'depth'" class="flex-1 p-4 flex flex-col justify-center items-center text-slate-400 text-xs font-mono">
      <div class="w-full max-w-md bg-slate-950 border border-slate-800 p-4 rounded-lg space-y-3 shadow-lg">
        <div class="text-slate-300 font-semibold border-b border-slate-800 pb-2 flex justify-between">
          <span>Depth Liquidity Overview</span>
          <span class="text-emerald-400">{{ symbol }}</span>
        </div>
        <div class="flex justify-between text-slate-400">
          <span>Bid Support (Bids Total):</span>
          <span class="text-emerald-400 font-bold">{{ totalBidsQty }}</span>
        </div>
        <div class="flex justify-between text-slate-400">
          <span>Ask Resistance (Asks Total):</span>
          <span class="text-rose-400 font-bold">{{ totalAsksQty }}</span>
        </div>
        <div class="w-full bg-slate-900 h-3 rounded-full overflow-hidden flex border border-slate-800">
          <div class="bg-emerald-500 h-full transition-all duration-300" :style="{ width: bidRatio + '%' }"></div>
          <div class="bg-rose-500 h-full transition-all duration-300" :style="{ width: askRatio + '%' }"></div>
        </div>
      </div>
    </div>

    <!-- FULLSCREEN EXPANDED CHART OVERLAY MODAL -->
    <Teleport to="body">
      <div v-if="isFullscreen" class="fixed inset-0 z-[99999] bg-slate-950 flex flex-col select-none">
        <div class="bg-slate-900 border-b border-slate-800 px-4 py-2 flex justify-between items-center text-xs">
          <div class="flex items-center space-x-4">
            <span class="font-bold text-slate-100 text-base">{{ symbol }} Technical Analysis</span>
            <span class="text-emerald-400 font-mono font-bold text-base">${{ formatPrice(market?.last_price) }}</span>
            <span class="text-slate-400 text-xs">Expanded Technical View</span>
          </div>

          <button @click="isFullscreen = false" 
                  class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition shadow flex items-center space-x-1">
            <span>✕ Close Fullscreen</span>
          </button>
        </div>

        <div class="flex-1 w-full h-full bg-slate-950">
          <div class="tradingview-widget-container h-full w-full" ref="fullscreenTvContainer">
            <div class="tradingview-widget-container__widget h-full w-full"></div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue';
import { createChart, ColorType, CandlestickSeries } from 'lightweight-charts';

const props = defineProps({
  symbol: { type: String, default: 'BTC/USDT' },
  market: Object,
  orderBook: Object,
  trades: Array,
});

const activeTab = ref('tv_widget');
const isFullscreen = ref(false);
const chartContainer = ref(null);
const tvWidgetContainer = ref(null);
const fullscreenTvContainer = ref(null);
let chart = null;
let candlestickSeries = null;
let resizeObserver = null;

const tvWidgetSymbol = computed(() => {
  const sym = props.symbol.replace('/', '');
  const map = {
    'BTCUSDT': 'BINANCE:BTCUSDT',
    'ETHUSDT': 'BINANCE:ETHUSDT',
    'BNBUSDT': 'BINANCE:BNBUSDT',
    'SOLUSDT': 'BINANCE:SOLUSDT',
    'XRPUSDT': 'BINANCE:XRPUSDT',
    'DOGEUSDT': 'BINANCE:DOGEUSDT',
    'GOLDUSDT': 'TVC:GOLD',
    'SILVERUSDT': 'TVC:SILVER',
    'OILUSDT': 'TVC:USOIL',
    'NVDAUSDT': 'NASDAQ:NVDA',
    'AAPLUSDT': 'NASDAQ:AAPL',
    'TSLAUSDT': 'NASDAQ:TSLA',
    'MSFTUSDT': 'NASDAQ:MSFT',
    'SPYUSDT': 'AMEX:SPY',
  };

  return map[sym] || `BINANCE:${sym}`;
});

const totalBidsQty = computed(() => {
  return (props.orderBook?.bids || []).reduce((acc, b) => acc + (b.quantity || 0), 0).toFixed(4);
});

const totalAsksQty = computed(() => {
  return (props.orderBook?.asks || []).reduce((acc, a) => acc + (a.quantity || 0), 0).toFixed(4);
});

const bidRatio = computed(() => {
  const b = parseFloat(totalBidsQty.value) || 0;
  const a = parseFloat(totalAsksQty.value) || 0;
  if (b + a === 0) return 50;
  return Math.round((b / (b + a)) * 100);
});

const askRatio = computed(() => 100 - bidRatio.value);

function formatPrice(val) {
  return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function toggleFullscreen() {
  isFullscreen.value = !isFullscreen.value;
  if (isFullscreen.value) {
    nextTick(() => {
      renderFullscreenWidget();
    });
  }
}

function renderTvScriptWidget() {
  if (!tvWidgetContainer.value) return;

  tvWidgetContainer.value.innerHTML = '<div class="tradingview-widget-container__widget" style="height:100%;width:100%"></div>';

  const script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
  script.async = true;
  script.innerHTML = JSON.stringify({
    autosize: true,
    symbol: tvWidgetSymbol.value,
    interval: "1",
    timezone: "Etc/UTC",
    theme: "dark",
    style: "1",
    locale: "en",
    enable_publishing: true,
    allow_symbol_change: true,
    hide_side_toolbar: false,
    details: true,
    hotlist: true,
    calendar: true,
    watchlist: [
      "BINANCE:BTCUSDT",
      "BINANCE:ETHUSDT",
      "BINANCE:BNBUSDT",
      "BINANCE:SOLUSDT",
      "TVC:GOLD",
      "TVC:USOIL",
      "FX:EURUSD",
      "NASDAQ:NVDA"
    ],
    show_popup_button: true,
    popup_width: "1000",
    popup_height: "650",
    support_host: "https://www.tradingview.com"
  });

  tvWidgetContainer.value.appendChild(script);
}

function renderFullscreenWidget() {
  if (!fullscreenTvContainer.value) return;

  fullscreenTvContainer.value.innerHTML = '<div class="tradingview-widget-container__widget" style="height:100%;width:100%"></div>';

  const script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
  script.async = true;
  script.innerHTML = JSON.stringify({
    autosize: true,
    symbol: tvWidgetSymbol.value,
    interval: "1",
    timezone: "Etc/UTC",
    theme: "dark",
    style: "1",
    locale: "en",
    enable_publishing: true,
    allow_symbol_change: true,
    hide_side_toolbar: false,
    details: true,
    hotlist: true,
    calendar: true,
    watchlist: [
      "BINANCE:BTCUSDT",
      "BINANCE:ETHUSDT",
      "BINANCE:BNBUSDT",
      "BINANCE:SOLUSDT",
      "TVC:GOLD",
      "TVC:USOIL",
      "FX:EURUSD",
      "NASDAQ:NVDA"
    ],
    support_host: "https://www.tradingview.com"
  });

  fullscreenTvContainer.value.appendChild(script);
}

async function initLightweightChart() {
  if (!chartContainer.value) return;

  const width = chartContainer.value.clientWidth || 600;
  const height = chartContainer.value.clientHeight || 360;

  if (chart) {
    try { chart.remove(); } catch (e) {}
    chart = null;
  }

  chart = createChart(chartContainer.value, {
    width,
    height,
    layout: {
      background: { type: ColorType.Solid, color: '#090d16' },
      textColor: '#94a3b8',
    },
    grid: {
      vertLines: { color: '#1e293b' },
      horzLines: { color: '#1e293b' },
    },
    rightPriceScale: {
      borderColor: '#1e293b',
    },
    timeScale: {
      borderColor: '#1e293b',
      timeVisible: true,
      secondsVisible: false,
    },
  });

  const seriesOptions = {
    upColor: '#10b981',
    downColor: '#ef4444',
    borderVisible: false,
    wickUpColor: '#10b981',
    wickDownColor: '#ef4444',
  };

  if (typeof chart.addCandlestickSeries === 'function') {
    candlestickSeries = chart.addCandlestickSeries(seriesOptions);
  } else if (typeof chart.addSeries === 'function' && CandlestickSeries) {
    candlestickSeries = chart.addSeries(CandlestickSeries, seriesOptions);
  }

  const basePrice = props.market?.last_price || 64500;
  const cleanSym = props.symbol.replace('/', '');
  let fetchedData = null;

  try {
    const res = await fetch(`https://api.binance.com/api/v3/klines?symbol=${cleanSym}&interval=1h&limit=100`);
    if (res.ok) {
      const klines = await res.json();
      fetchedData = klines.map(k => ({
        time: Math.floor(k[0] / 1000),
        open: parseFloat(k[1]),
        high: parseFloat(k[2]),
        low: parseFloat(k[3]),
        close: parseFloat(k[4]),
      }));
    }
  } catch (e) {}

  if (candlestickSeries) {
    if (fetchedData && fetchedData.length > 0) {
      candlestickSeries.setData(fetchedData);
    } else {
      candlestickSeries.setData(generateCandleData(basePrice));
    }
  }
}

function generateCandleData(basePrice) {
  const data = [];
  const now = Math.floor(Date.now() / 1000);
  let price = basePrice * 0.96;

  for (let i = 80; i >= 0; i--) {
    const time = now - i * 3600;
    const change = (Math.random() - 0.49) * (basePrice * 0.012);
    const open = price;
    const close = open + change;
    const high = Math.max(open, close) + Math.random() * (basePrice * 0.004);
    const low = Math.min(open, close) - Math.random() * (basePrice * 0.004);

    data.push({ time, open, high, low, close });
    price = close;
  }

  if (data.length > 0) {
    data[data.length - 1].close = basePrice;
  }

  return data;
}

watch(activeTab, (newTab) => {
  if (newTab === 'candles') {
    nextTick(() => {
      initLightweightChart();
    });
  } else if (newTab === 'tv_widget') {
    nextTick(() => {
      renderTvScriptWidget();
    });
  }
});

watch(() => props.symbol, () => {
  if (activeTab.value === 'candles') {
    nextTick(() => {
      initLightweightChart();
    });
  } else if (activeTab.value === 'tv_widget') {
    nextTick(() => {
      renderTvScriptWidget();
    });
  }
});

let lastBar = null;

watch(() => props.market?.last_price, (newPrice) => {
  if (candlestickSeries && newPrice) {
    const now = Math.floor(Date.now() / 1000);
    const barTime = Math.floor(now / 3600) * 3600;

    if (lastBar && lastBar.time === barTime) {
      lastBar.close = newPrice;
      lastBar.high = Math.max(lastBar.high, newPrice);
      lastBar.low = Math.min(lastBar.low, newPrice);
    } else {
      lastBar = {
        time: barTime,
        open: newPrice,
        high: newPrice,
        low: newPrice,
        close: newPrice,
      };
    }

    try {
      candlestickSeries.update(lastBar);
    } catch (e) {}
  }
});

onMounted(() => {
  nextTick(() => {
    renderTvScriptWidget();

    if (chartContainer.value) {
      resizeObserver = new ResizeObserver((entries) => {
        if (!entries || !entries.length) return;
        const { width, height } = entries[0].contentRect;
        if (chart && width > 0 && height > 0) {
          try { chart.applyOptions({ width, height }); } catch (e) {}
        }
      });
      resizeObserver.observe(chartContainer.value);
    }
  });
});

onUnmounted(() => {
  if (resizeObserver && chartContainer.value) {
    resizeObserver.unobserve(chartContainer.value);
  }
  if (chart) {
    try { chart.remove(); } catch (e) {}
  }
});
</script>
