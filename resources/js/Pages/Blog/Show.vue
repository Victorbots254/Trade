<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 font-sans flex flex-col transition-colors duration-200 pb-12">
    <TradingHeader :user="user" />

    <div class="flex-1 max-w-4xl w-full mx-auto p-4 md:p-8 space-y-10">
      <Link href="/learn" class="inline-flex items-center space-x-2 text-slate-400 hover:text-emerald-400 transition text-sm font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        <span>Back to Academy</span>
      </Link>

      <article class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="h-64 md:h-96 w-full relative">
          <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent z-10"></div>
          <img :src="article.image" class="w-full h-full object-cover" />
          <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 z-20 space-y-3">
            <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider backdrop-blur-sm">
              {{ article.category }}
            </span>
            <h1 class="text-2xl md:text-4xl font-black text-white leading-tight max-w-2xl">{{ article.title }}</h1>
            <div class="flex items-center space-x-4 text-xs font-mono text-slate-300">
              <span>{{ article.read_time }}</span>
            </div>
          </div>
        </div>

        <div class="p-6 md:p-10 prose prose-invert prose-emerald max-w-none">
          <div v-html="article.content"></div>
        </div>
      </article>

      <!-- Read More Section -->
      <div v-if="moreArticles && moreArticles.length > 0" class="pt-8 border-t border-slate-800">
        <h3 class="text-xl font-bold text-slate-100 mb-6">Keep Learning</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <Link v-for="more in moreArticles" :key="more.slug" :href="`/learn/${more.slug}`" class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center space-x-4 hover:border-emerald-500/50 transition group">
            <img :src="more.image" class="w-20 h-20 rounded-lg object-cover" />
            <div>
              <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider mb-1">{{ more.category }}</div>
              <h4 class="font-bold text-slate-200 text-sm group-hover:text-emerald-400 transition leading-snug">{{ more.title }}</h4>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import TradingHeader from "@/Components/TradingHeader.vue";

defineProps({
  user: Object,
  article: Object,
  moreArticles: Array
});
</script>

<style>
/* Custom styling for injected HTML content */
.prose h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #f1f5f9;
  margin-top: 2rem;
  margin-bottom: 1rem;
}
.prose p {
  color: #94a3b8;
  line-height: 1.75;
  margin-bottom: 1rem;
}
.prose strong {
  color: #e2e8f0;
}
.prose ol, .prose ul {
  color: #94a3b8;
  margin-left: 1.25rem;
  margin-bottom: 1rem;
}
.prose ol {
  list-style-type: decimal;
}
.prose ul {
  list-style-type: disc;
}
.prose li {
  margin-bottom: 0.5rem;
}
.prose li strong {
  color: #34d399; /* emerald-400 */
}
</style>
