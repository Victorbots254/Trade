<template>
  <div class="min-h-screen bg-[#0b0e11] flex flex-col justify-center items-center p-4 text-[#eaecef] font-sans select-none relative overflow-hidden">
    <!-- Decorative background blobs -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full opacity-5" style="background: radial-gradient(circle, #f0b90b, transparent)"></div>
      <div class="absolute top-20 right-0 w-[400px] h-[400px] rounded-full opacity-5" style="background: radial-gradient(circle, #0ecb81, transparent)"></div>
    </div>

    <!-- Header Logo -->
    <a href="/" class="flex items-center space-x-2 font-bold text-2xl tracking-wider mb-8 z-10">
      <div class="w-8 h-8 bg-[#f0b90b] rounded-sm flex items-center justify-center font-black text-[#1e2329] text-base">T</div>
      <span class="font-bold text-white text-xl">TRADE<span class="text-[#f0b90b]">PRO</span></span>
    </a>

    <!-- Register Form Card -->
    <div class="w-full max-w-[420px] bg-[#181a20] border border-[#2b3139] rounded-2xl p-8 space-y-6 shadow-2xl z-10">
      <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white">Create Account</h2>
        <p class="text-xs text-[#848e9c]">Join the ultra-low latency FinTech spot exchange.</p>
      </div>

      <div v-if="errorMessage" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] p-3 rounded-lg text-xs font-medium">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4 text-xs">
        <div>
          <label class="block text-[#848e9c] mb-1.5 font-medium">Full Name</label>
          <input 
            v-model="form.name" 
            type="text" 
            required 
            placeholder="John Doe"
            class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-lg px-3.5 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none focus:border-[#f0b90b] transition text-sm" 
          />
        </div>

        <div>
          <label class="block text-[#848e9c] mb-1.5 font-medium">Email Address</label>
          <input 
            v-model="form.email" 
            type="email" 
            required 
            placeholder="trader@example.com"
            class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-lg px-3.5 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none focus:border-[#f0b90b] transition text-sm" 
          />
        </div>

        <div>
          <label class="block text-[#848e9c] mb-1.5 font-medium">Password</label>
          <input 
            v-model="form.password" 
            type="password" 
            required 
            placeholder="Minimum 8 characters"
            class="w-full bg-[#0b0e11] border border-[#2b3139] rounded-lg px-3.5 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none focus:border-[#f0b90b] transition text-sm" 
          />
        </div>

        <div class="flex items-start space-x-2.5 pt-1">
          <input 
            v-model="form.accepted_terms" 
            type="checkbox" 
            required 
            id="reg-terms" 
            class="mt-0.5 rounded bg-[#0b0e11] border-[#2b3139] text-[#f0b90b] focus:ring-0" 
          />
          <label for="reg-terms" class="text-[#848e9c] text-[11px] leading-relaxed">
            I agree to the <a href="/terms" target="_blank" class="text-[#f0b90b] underline font-semibold">Terms of Service</a> and acknowledge the <a href="/risk-disclosure" target="_blank" class="text-[#f0b90b] underline font-semibold">Financial Risk Disclosure</a>.
          </label>
        </div>

        <button 
          type="submit" 
          :disabled="loading"
          class="w-full bg-[#f0b90b] hover:bg-[#d4a30b] text-[#1e2329] font-bold py-3.5 rounded-xl transition shadow-lg shadow-[#f0b90b]/10 disabled:opacity-50 text-sm flex items-center justify-center space-x-2 mt-6">
          <svg v-if="loading" class="animate-spin h-4 w-4 text-[#1e2329]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="loading">Creating Account...</span>
          <span v-else>Register Account</span>
        </button>
      </form>

      <div class="border-t border-[#2b3139] pt-4 text-center text-xs text-[#848e9c]">
        Already have an account? 
        <a href="/login" class="text-[#f0b90b] font-semibold hover:underline ml-1">Log In</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
  name: '',
  email: '',
  password: '',
  accepted_terms: false,
});

const loading = ref(false);
const errorMessage = ref('');

async function handleRegister() {
  loading.value = true;
  errorMessage.value = '';
  try {
    await axios.post('/api/register', form.value);
    window.location.href = '/terminal';
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Registration failed.';
  } finally {
    loading.value = false;
  }
}
</script>
