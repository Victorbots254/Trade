<template>
  <div class="min-h-screen flex flex-col justify-center items-center p-4 text-[#eaecef] font-sans select-none relative overflow-hidden"
    style="background: radial-gradient(ellipse at 20% 50%, #0f1923 0%, #0b0e11 40%, #050608 100%);">

    <!-- Grid overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
      style="background-image: linear-gradient(#f0b90b 1px, transparent 1px), linear-gradient(90deg, #f0b90b 1px, transparent 1px); background-size: 40px 40px;"></div>

    <!-- Decorative glow blobs -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -left-32 w-[500px] h-[500px] rounded-full"
        style="background: radial-gradient(circle, rgba(240,185,11,0.12) 0%, transparent 70%); filter: blur(40px);"></div>
      <div class="absolute bottom-0 right-0 w-[400px] h-[400px] rounded-full"
        style="background: radial-gradient(circle, rgba(14,203,129,0.08) 0%, transparent 70%); filter: blur(50px);"></div>
    </div>

    <!-- Header Logo -->
    <a href="/" class="flex items-center space-x-2.5 mb-8 z-10 group">
      <div class="w-9 h-9 rounded-lg flex items-center justify-center font-black text-[#1e2329] text-sm shadow-lg shadow-[#f0b90b]/30"
        style="background: linear-gradient(135deg, #f0b90b 0%, #e6a800 100%);">T</div>
      <span class="font-black text-white text-xl tracking-tight">TRADE<span class="text-[#f0b90b]">CO</span></span>
    </a>

    <!-- Card -->
    <div class="w-full max-w-[420px] z-10 rounded-2xl p-8 space-y-6 shadow-2xl"
      style="background: linear-gradient(145deg, rgba(30,35,41,0.95) 0%, rgba(20,24,30,0.95) 100%); border: 1px solid rgba(240,185,11,0.15); backdrop-filter: blur(20px);">

      <div class="h-0.5 w-full rounded-full mb-4" style="background: linear-gradient(90deg, #f0b90b, #0ecb81, transparent);"></div>

      <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white">Reset Password</h2>
        <p class="text-xs text-[#848e9c]">Enter your registered email address and we'll send you instructions to reset your password.</p>
      </div>

      <!-- Success message -->
      <div v-if="successMessage" class="bg-[#0ecb81]/10 border border-[#0ecb81]/30 text-[#0ecb81] p-3.5 rounded-xl text-xs font-medium space-y-2">
        <div class="flex items-center space-x-2">
          <span>✓</span>
          <span>{{ successMessage }}</span>
        </div>
        <div v-if="devResetUrl" class="mt-2 pt-2 border-t border-[#0ecb81]/20">
          <span class="text-[10px] text-slate-400 block mb-1">Direct link (Dev mode):</span>
          <a :href="devResetUrl" class="text-[#f0b90b] underline text-[11px] break-all">Click here to reset password</a>
        </div>
      </div>

      <!-- Error message -->
      <div v-if="errorMessage" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] p-3 rounded-lg text-xs font-medium flex items-center space-x-2">
        <span>⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4 text-xs">
        <div>
          <label class="block text-[#848e9c] mb-1.5 font-semibold uppercase tracking-wider text-[10px]">Email Address</label>
          <input
            v-model="email"
            type="email"
            required
            placeholder="trader@example.com"
            class="w-full border rounded-xl px-4 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none transition text-sm"
            style="background: rgba(11,14,17,0.8); border-color: rgba(43,49,57,0.8);"
            @focus="$event.target.style.borderColor='rgba(240,185,11,0.6)'"
            @blur="$event.target.style.borderColor='rgba(43,49,57,0.8)'"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full font-bold py-3.5 rounded-xl transition disabled:opacity-50 text-sm flex items-center justify-center space-x-2 mt-6 shadow-lg"
          style="background: linear-gradient(135deg, #f0b90b 0%, #e6a800 100%); color: #1e2329; box-shadow: 0 8px 32px rgba(240,185,11,0.25);">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-[#1e2329]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="loading">Sending Link...</span>
          <span v-else>Send Password Reset Link →</span>
        </button>
      </form>

      <div class="border-t pt-5 text-center text-xs text-[#848e9c]" style="border-color: rgba(43,49,57,0.6);">
        Remember your password?
        <a href="/login" class="font-bold ml-1 hover:underline" style="color: #f0b90b;">Back to Log In</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const email = ref('');
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const devResetUrl = ref('');

async function handleSubmit() {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  devResetUrl.value = '';

  try {
    const res = await axios.post('/api/forgot-password', { email: email.value });
    successMessage.value = res.data.message || 'Password reset link sent to your email!';
    if (res.data.dev_reset_url) {
      devResetUrl.value = res.data.dev_reset_url;
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Could not send reset link. Please check your email.';
  } finally {
    loading.value = false;
  }
}
</script>
