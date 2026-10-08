<template>
  <div>
    <!-- FLOATING INTERACTIVE TOAST POPUP (Triggered on new order, message, or payment) -->
    <transition
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-y-4 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0">
      <div
        v-if="activeToast"
        class="fixed top-5 right-5 z-50 w-full max-w-sm bg-[#181a20] border-2 shadow-2xl rounded-2xl p-4 text-xs font-sans overflow-hidden"
        :class="activeToast.type === 'paid' ? 'border-[#0ecb81] shadow-[#0ecb81]/20' : (activeToast.type === 'message' ? 'border-[#38bdf8] shadow-[#38bdf8]/20' : 'border-[#f0b90b] shadow-[#f0b90b]/20')">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-2 border-b border-[#2b3139]">
          <div class="flex items-center space-x-2">
            <span class="text-base animate-bounce">{{ activeToast.icon }}</span>
            <span class="font-bold text-white text-xs uppercase tracking-wider">{{ activeToast.title }}</span>
          </div>
          <button @click="activeToast = null" class="text-[#848e9c] hover:text-white text-sm font-bold leading-none p-1">✕</button>
        </div>

        <!-- Body -->
        <div class="py-2.5 space-y-1">
          <p class="text-white font-medium text-xs leading-snug">{{ activeToast.message }}</p>
          <div v-if="activeToast.subtext" class="text-[11px] text-[#848e9c] font-mono">
            {{ activeToast.subtext }}
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="pt-2 flex items-center space-x-2">
          <button
            @click="openOrder(activeToast.orderId)"
            type="button"
            :class="activeToast.type === 'paid' ? 'bg-[#0ecb81] hover:bg-[#0bb371] text-[#1e2329]' : 'bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329]'"
            class="flex-1 font-black py-2.5 px-3 rounded-xl transition text-xs shadow-lg uppercase tracking-wider flex items-center justify-center space-x-1.5">
            <span>Open Trade Room</span>
            <span>&rarr;</span>
          </button>
          <button
            @click="activeToast = null"
            type="button"
            class="border border-[#2b3139] hover:bg-[#2b3139]/60 text-[#848e9c] hover:text-white px-3 py-2.5 rounded-xl font-bold transition text-xs">
            Later
          </button>
        </div>
      </div>
    </transition>

    <!-- FLOATING DOCKED QUICK-BADGE (When user has active P2P trades) -->
    <div
      v-if="orders.length > 0 && !activeToast"
      class="fixed bottom-6 right-6 z-40">
      <button
        @click="showDrawer = !showDrawer"
        type="button"
        class="bg-[#181a20] hover:bg-[#1e2329] border border-[#f0b90b]/50 shadow-2xl rounded-full px-4 py-2.5 flex items-center space-x-2.5 group transition">
        <span class="relative flex h-2.5 w-2.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#f0b90b] opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#f0b90b]"></span>
        </span>
        <span class="text-xs font-bold text-white group-hover:text-[#f0b90b]">
          Active P2P Trades ({{ orders.length }})
        </span>
      </button>

      <!-- Mini Dropdown of Active Orders -->
      <div
        v-if="showDrawer"
        class="absolute bottom-12 right-0 w-80 bg-[#181a20] border border-[#2b3139] rounded-2xl shadow-2xl p-3 space-y-2 text-xs">
        <div class="flex justify-between items-center pb-2 border-b border-[#2b3139]">
          <span class="font-bold text-white text-xs">Your Live Escrow Trades</span>
          <button @click="showDrawer = false" class="text-[#848e9c] hover:text-white text-xs">✕</button>
        </div>

        <div class="max-h-64 overflow-y-auto space-y-2">
          <div
            v-for="order in orders"
            :key="order.id"
            @click="openOrder(order.id)"
            class="bg-[#0b0e11] hover:bg-[#1e2329] border border-[#2b3139] rounded-xl p-2.5 cursor-pointer transition space-y-1">
            <div class="flex justify-between items-center">
              <span class="font-bold text-white font-mono text-[11px]">#{{ order.order_number }}</span>
              <span
                :class="order.status === 'paid' ? 'bg-[#0ecb81]/20 text-[#0ecb81]' : 'bg-[#f0b90b]/20 text-[#f0b90b]'"
                class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase font-mono">
                {{ order.status }}
              </span>
            </div>
            <div class="flex justify-between text-[11px] text-[#848e9c] font-mono">
              <span>{{ order.is_seller ? 'Buyer: ' : 'Seller: ' }}<strong class="text-slate-300">{{ order.counterparty_name }}</strong></span>
              <span class="text-[#0ecb81] font-bold">{{ order.crypto_amount }} USDT</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  userId: { type: [Number, String], default: null },
});

const orders = ref([]);
const activeToast = ref(null);
const showDrawer = ref(false);

let pollTimer = null;
let lastKnownOrderIds = new Set();
let lastKnownStatusMap = new Map();
let lastSeenMessageId = null;
let isFirstCheck = true;

function playNotificationChime() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return;
    const ctx = new AudioCtx();
    const now = ctx.currentTime;

    // Chime Note 1
    const osc1 = ctx.createOscillator();
    const gain1 = ctx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(659.25, now); // E5
    gain1.gain.setValueAtTime(0.18, now);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
    osc1.connect(gain1);
    gain1.connect(ctx.destination);
    osc1.start(now);
    osc1.stop(now + 0.3);

    // Chime Note 2
    const osc2 = ctx.createOscillator();
    const gain2 = ctx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(880, now + 0.12); // A5
    gain2.gain.setValueAtTime(0.2, now + 0.12);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
    osc2.connect(gain2);
    gain2.connect(ctx.destination);
    osc2.start(now + 0.12);
    osc2.stop(now + 0.55);
  } catch (e) {
    // Audio context may be restricted by browser until user interaction
  }
}

async function checkNotifications() {
  try {
    const res = await axios.get('/api/p2p/notifications/poll');
    const incomingOrders = res.data.orders || [];
    const latestMessage = res.data.latest_message || null;

    orders.value = incomingOrders;

    if (isFirstCheck) {
      // First load: seed state without alerting for historical orders
      incomingOrders.forEach(o => {
        lastKnownOrderIds.add(o.id);
        lastKnownStatusMap.set(o.id, o.status);
      });
      if (latestMessage) {
        lastSeenMessageId = latestMessage.id;
      }
      isFirstCheck = false;
      return;
    }

    // 1. Check for newly created orders
    for (const o of incomingOrders) {
      if (!lastKnownOrderIds.has(o.id)) {
        lastKnownOrderIds.add(o.id);
        lastKnownStatusMap.set(o.id, o.status);

        playNotificationChime();
        activeToast.value = {
          type: 'new_order',
          icon: '⚡',
          title: o.is_seller ? 'New P2P Trade Initiated!' : 'P2P Trade Started',
          message: `${o.counterparty_name} placed an order for ${o.crypto_amount} USDT (${o.fiat_amount} ${o.fiat}).`,
          subtext: `Order #${o.order_number}`,
          orderId: o.id,
        };
        return; // Prioritize one popup at a time
      }

      // 2. Check for order status transitions (e.g., buyer marked payment sent)
      const prevStatus = lastKnownStatusMap.get(o.id);
      if (prevStatus && prevStatus !== o.status) {
        lastKnownStatusMap.set(o.id, o.status);

        if (o.status === 'paid' && o.is_seller) {
          playNotificationChime();
          activeToast.value = {
            type: 'paid',
            icon: '💸',
            title: 'Buyer Marked Payment Sent!',
            message: `${o.counterparty_name} sent ${o.fiat_amount} ${o.fiat}. Please confirm receipt and release crypto.`,
            subtext: `Order #${o.order_number}`,
            orderId: o.id,
          };
          return;
        }
      }
    }

    // 3. Check for new messages from the counterparty
    if (latestMessage && latestMessage.id !== lastSeenMessageId) {
      lastSeenMessageId = latestMessage.id;

      // Don't show toast if user is already on this exact order page
      const currentPath = window.location.pathname;
      if (!currentPath.includes(`/p2p/orders/${latestMessage.order_id}`)) {
        playNotificationChime();
        activeToast.value = {
          type: 'message',
          icon: '💬',
          title: `New Message from ${latestMessage.sender_name}`,
          message: `"${latestMessage.message}"`,
          subtext: `Order #${latestMessage.order_number}`,
          orderId: latestMessage.order_id,
        };
      }
    }
  } catch (e) {
    // Silent fail on polling error
  }
}

function openOrder(orderId) {
  activeToast.value = null;
  window.location.href = `/p2p/orders/${orderId}`;
}

onMounted(() => {
  checkNotifications();
  // Poll every 5 seconds
  pollTimer = setInterval(checkNotifications, 5000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
});
</script>
