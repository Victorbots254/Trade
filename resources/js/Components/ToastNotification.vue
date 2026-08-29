<template>
  <Teleport to="body">
    <Transition name="toast">
      <div v-if="visible" 
           :class="type === 'success' ? 'bg-slate-900 border-emerald-500/50 text-emerald-400' : (type === 'error' ? 'bg-slate-900 border-rose-500/50 text-rose-400' : 'bg-slate-900 border-slate-700 text-slate-200')"
           class="fixed top-4 right-4 z-[9999] border px-4 py-3 rounded-xl shadow-2xl backdrop-blur-md flex items-center space-x-3 text-xs font-medium max-w-md select-none font-sans">
        <span v-if="type === 'success'" class="w-2 h-2 bg-emerald-400 rounded-full animate-ping"></span>
        <span v-else-if="type === 'error'" class="w-2 h-2 bg-rose-400 rounded-full animate-ping"></span>
        <span v-else class="w-2 h-2 bg-cyan-400 rounded-full"></span>
        
        <span class="flex-1 leading-snug">{{ message }}</span>
        
        <button @click="visible = false" class="text-slate-500 hover:text-slate-300 ml-2 font-bold">✕</button>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const visible = ref(false);
const message = ref('');
const type = ref('success');
let timer = null;

function show(msg, toastType = 'success', duration = 4000) {
  message.value = msg;
  type.value = toastType;
  visible.value = true;
  if (timer) clearTimeout(timer);
  timer = setTimeout(() => {
    visible.value = false;
  }, duration);
}

defineExpose({ show });
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-12px) scale(0.95);
}
</style>
