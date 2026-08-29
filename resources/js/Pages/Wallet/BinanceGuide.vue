<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12">
    <!-- Top Navigation Header -->
    <TradingHeader 
      :user="user"
      :markets="markets"
      :wallets="wallets"
    />

    <!-- Main Workspace Container -->
    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-6">
      <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-800 pb-4 gap-3">
        <div>
          <h1 class="text-xl md:text-2xl font-bold text-slate-100 flex items-center space-x-2">
            <span>📖 Binance BEP20 Setup Guide</span>
          </h1>
          <p class="text-xs md:text-sm text-slate-400 mt-1">Step-by-step tutorial on how to locate and copy your Binance BEP20 (BNB Smart Chain) address.</p>
        </div>

        <Link href="/payments" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2 rounded-xl transition text-xs flex items-center space-x-1.5 self-start md:self-auto shadow-md">
          <span>← Back to Payments Settings</span>
        </Link>
      </div>

      <!-- Step-by-Step Instructions Grid -->
      <div class="grid grid-cols-1 gap-4">
        <!-- Step 1 -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-start space-x-4">
          <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 font-extrabold flex items-center justify-center text-base shrink-0 font-mono border border-amber-500/40">
            1
          </div>
          <div class="space-y-1.5 flex-1">
            <h3 class="font-bold text-slate-100 text-sm md:text-base">Open Binance & Navigate to Wallets</h3>
            <p class="text-xs text-slate-300">Open your official <strong class="text-amber-400">Binance Mobile App</strong> or log in at <a href="https://binance.com" target="_blank" class="text-amber-400 underline font-semibold">Binance.com</a>. Tap <strong class="text-slate-100">Wallets</strong> at the bottom right corner, then select <strong class="text-slate-100">Deposit</strong>.</p>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-start space-x-4">
          <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 font-extrabold flex items-center justify-center text-base shrink-0 font-mono border border-amber-500/40">
            2
          </div>
          <div class="space-y-1.5 flex-1">
            <h3 class="font-bold text-slate-100 text-sm md:text-base">Select Deposit USDT</h3>
            <p class="text-xs text-slate-300">Search for <strong class="text-amber-400 font-mono font-bold">USDT</strong> in the token list and choose it. This is the stablecoin currency that is automatically matched with your deposits.</p>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-start space-x-4">
          <div class="w-10 h-10 rounded-full bg-emerald-500/20 text-emerald-400 font-extrabold flex items-center justify-center text-base shrink-0 font-mono border border-emerald-500/40">
            3
          </div>
          <div class="space-y-1.5 flex-1">
            <h3 class="font-bold text-slate-100 text-sm md:text-base">Choose BNB Smart Chain (BEP20) Network</h3>
            <p class="text-xs text-slate-300">Binance will prompt you to choose a network. Choose <strong class="text-amber-400">BNB Smart Chain (BEP20)</strong>. Do not choose Ethereum (ERC20) or Tron (TRC20) networks.</p>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-start space-x-4">
          <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 font-extrabold flex items-center justify-center text-base shrink-0 font-mono border border-amber-500/40">
            4
          </div>
          <div class="space-y-1.5 flex-1">
            <h3 class="font-bold text-slate-100 text-sm md:text-base">Copy Your BEP20 Wallet Address</h3>
            <p class="text-xs text-slate-300">Your unique 42-character address starting with <code class="text-amber-400 font-bold font-mono">0x</code> will be displayed. Tap the <strong class="text-slate-100">Copy Icon</strong> to copy it to your clipboard.</p>
          </div>
        </div>

        <!-- Step 5 -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl flex items-start space-x-4">
          <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 font-extrabold flex items-center justify-center text-base shrink-0 font-mono border border-amber-500/40">
            5
          </div>
          <div class="space-y-1.5 flex-1">
            <h3 class="font-bold text-slate-100 text-sm md:text-base">Paste & Save on Tradex Pro</h3>
            <p class="text-xs text-slate-300">Return to the Tradex Pro <Link href="/payments" class="text-amber-400 underline font-semibold">Payments Settings Page</Link>, paste your copied address into the form, and click <strong class="text-slate-100">Save Binance BEP20 Address</strong>.</p>
          </div>
        </div>
      </div>

      <!-- Action CTA Footer -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 text-center space-y-3">
        <h3 class="font-bold text-slate-100 text-base">Ready to link your Binance BEP20 Wallet?</h3>
        <p class="text-xs text-slate-400">Once linked, all your trading profits and option payouts are transferred directly to your address.</p>
        <Link href="/payments" class="inline-block bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold px-6 py-2.5 rounded-xl transition text-xs shadow-lg">
          Link Binance BEP20 Wallet Now →
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import TradingHeader from '@/Components/TradingHeader.vue';

defineProps({
  user: Object,
  wallets: Array,
  markets: Array,
});
</script>
