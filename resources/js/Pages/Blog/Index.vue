<template>
  <div class="min-h-screen text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12"
    style="background: linear-gradient(180deg, #060a12 0%, #0b0f1a 30%, #0a0e17 100%);">
    <TradingHeader :user="user" />

    <!-- Hero Banner -->
    <div class="relative overflow-hidden border-b border-slate-800/60"
      style="background: linear-gradient(135deg, rgba(14,203,129,0.06) 0%, rgba(240,185,11,0.04) 50%, transparent 100%);">
      <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full"
          style="background: radial-gradient(circle, rgba(14,203,129,0.08) 0%, transparent 70%); filter: blur(60px);"></div>
        <div class="absolute top-0 left-1/4 w-[300px] h-[200px]"
          style="background: radial-gradient(ellipse, rgba(240,185,11,0.05) 0%, transparent 70%); filter: blur(40px);"></div>
      </div>
      <div class="relative max-w-5xl mx-auto px-4 py-14 text-center space-y-4">
        <div class="inline-flex items-center space-x-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">
          <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
          <span>TradePro Trading Academy</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-black tracking-tight">
          Master the <span style="background: linear-gradient(135deg, #0ecb81, #f0b90b); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Markets</span>
        </h1>
        <p class="text-slate-400 max-w-2xl mx-auto text-sm md:text-base leading-relaxed">
          Read our official guides to learn the most effective strategies for yield farming, spot trading, and quick options on TradePro.
        </p>
      </div>
    </div>

    <div class="flex-1 max-w-6xl w-full mx-auto p-4 md:p-8 space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link v-for="article in articles" :key="article.slug" :href="`/learn/${article.slug}`"
          class="group flex flex-col rounded-2xl overflow-hidden shadow-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-emerald-500/10"
          style="background: linear-gradient(145deg, rgba(22,27,35,0.95) 0%, rgba(15,19,26,0.95) 100%); border: 1px solid rgba(255,255,255,0.06);">

          <!-- Thumbnail -->
          <div class="h-48 w-full overflow-hidden relative">
            <div class="absolute inset-0 z-10 transition duration-500 group-hover:opacity-0"
              style="background: linear-gradient(180deg, transparent 40%, rgba(15,19,26,0.8) 100%);"></div>
            <img :src="article.image" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700" />
            <!-- Category Badge -->
            <div class="absolute top-3 left-3 z-20 text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full backdrop-blur-md"
              style="background: rgba(14,203,129,0.15); border: 1px solid rgba(14,203,129,0.3); color: #0ecb81;">
              {{ article.category }}
            </div>
          </div>

          <!-- Content -->
          <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <div>
              <h2 class="text-base font-bold text-slate-100 group-hover:text-emerald-400 transition leading-tight line-clamp-2">{{ article.title }}</h2>
              <p class="text-xs text-slate-500 mt-2.5 line-clamp-3 leading-relaxed">{{ article.excerpt }}</p>
            </div>
            <div class="flex items-center justify-between pt-3 border-t text-xs font-mono" style="border-color: rgba(255,255,255,0.06);">
              <span class="text-slate-600">{{ article.read_time }}</span>
              <span class="font-semibold group-hover:translate-x-1 transition transform inline-block" style="color: #0ecb81;">Read Guide &rarr;</span>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </div>
</template>


<script setup>
import { Link } from "@inertiajs/vue3";
import TradingHeader from "@/Components/TradingHeader.vue";

defineProps({
  user: Object,
  articles: Array
});
</script>
