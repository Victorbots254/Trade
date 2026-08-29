<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans select-none flex flex-col transition-colors duration-200 pb-12">
    <TradingHeader :user="user" />

    <div class="flex-1 max-w-5xl w-full mx-auto p-4 md:p-8 space-y-10">
      <div class="text-center space-y-4 py-8 border-b border-slate-800">
        <h1 class="text-3xl md:text-5xl font-black text-slate-100 tracking-tight">Trading <span class="text-emerald-400">Academy</span></h1>
        <p class="text-slate-400 max-w-2xl mx-auto text-sm md:text-base">Master the markets. Read our official guides to learn the most effective ways to earn yield, spot trade, and utilize quick options on the platform.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link v-for="article in articles" :key="article.slug" :href="`/learn/${article.slug}`" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden hover:border-emerald-500/50 transition group flex flex-col shadow-xl">
          <div class="h-48 w-full overflow-hidden relative">
            <div class="absolute inset-0 bg-slate-900/20 group-hover:bg-transparent transition z-10"></div>
            <img :src="article.image" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500" />
            <div class="absolute top-4 left-4 z-20 bg-slate-900/80 backdrop-blur-sm text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
              {{ article.category }}
            </div>
          </div>
          <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
            <div>
              <h2 class="text-lg font-bold text-slate-100 group-hover:text-emerald-400 transition leading-tight">{{ article.title }}</h2>
              <p class="text-xs text-slate-400 mt-3 line-clamp-3 leading-relaxed">{{ article.excerpt }}</p>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-slate-800/50 text-xs font-mono text-slate-500">
              <span>{{ article.read_time }}</span>
              <span class="text-emerald-400 group-hover:translate-x-1 transition transform inline-block">Read Guide &rarr;</span>
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
