<template>
  <div class="min-h-screen flex flex-col justify-center items-center p-4 text-[#eaecef] font-sans select-none relative overflow-hidden"
    style="background: radial-gradient(ellipse at 80% 20%, #0f1923 0%, #0b0e11 40%, #050608 100%);">

    <!-- Grid overlay -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.04]"
      style="background-image: linear-gradient(#f0b90b 1px, transparent 1px), linear-gradient(90deg, #f0b90b 1px, transparent 1px); background-size: 40px 40px;"></div>

    <!-- Decorative glow blobs -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 right-0 w-[500px] h-[500px] rounded-full"
        style="background: radial-gradient(circle, rgba(14,203,129,0.10) 0%, transparent 70%); filter: blur(50px);"></div>
      <div class="absolute bottom-0 -left-20 w-[400px] h-[400px] rounded-full"
        style="background: radial-gradient(circle, rgba(240,185,11,0.08) 0%, transparent 70%); filter: blur(40px);"></div>
    </div>

    <!-- Header Logo -->
    <a href="/" class="flex items-center space-x-2.5 mb-10 z-10">
      <div class="w-9 h-9 rounded-lg flex items-center justify-center font-black text-[#1e2329] text-sm shadow-lg shadow-[#f0b90b]/30"
        style="background: linear-gradient(135deg, #f0b90b 0%, #e6a800 100%);">T</div>
      <span class="font-black text-white text-xl tracking-tight">TRADE<span class="text-[#f0b90b]">PRO</span></span>
    </a>

    <!-- Register Form Card -->
    <div class="w-full max-w-[420px] z-10 rounded-2xl p-8 space-y-6 shadow-2xl"
      style="background: linear-gradient(145deg, rgba(30,35,41,0.95) 0%, rgba(20,24,30,0.95) 100%); border: 1px solid rgba(14,203,129,0.15); backdrop-filter: blur(20px);">

      <!-- Top accent bar -->
      <div class="h-0.5 w-full rounded-full mb-6" style="background: linear-gradient(90deg, #0ecb81, #f0b90b, transparent);"></div>

      <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white">Create Account</h2>
        <p class="text-xs text-[#848e9c]">Join the ultra-low latency institutional crypto exchange.</p>
      </div>

      <div v-if="errorMessage" class="bg-[#f6465d]/10 border border-[#f6465d]/30 text-[#f6465d] p-3 rounded-lg text-xs font-medium flex items-center space-x-2">
        <span class="text-base">⚠️</span>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4 text-xs">
        <div>
          <label class="block text-[#848e9c] mb-1.5 font-semibold uppercase tracking-wider text-[10px]">Full Name</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="John Doe"
            class="w-full border rounded-xl px-4 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none transition text-sm"
            style="background: rgba(11,14,17,0.8); border-color: rgba(43,49,57,0.8);"
            @focus="$event.target.style.borderColor='rgba(14,203,129,0.6)'"
            @blur="$event.target.style.borderColor='rgba(43,49,57,0.8)'"
          />
        </div>

        <div>
          <label class="block text-[#848e9c] mb-1.5 font-semibold uppercase tracking-wider text-[10px]">Email Address</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="trader@example.com"
            class="w-full border rounded-xl px-4 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none transition text-sm"
            style="background: rgba(11,14,17,0.8); border-color: rgba(43,49,57,0.8);"
            @focus="$event.target.style.borderColor='rgba(14,203,129,0.6)'"
            @blur="$event.target.style.borderColor='rgba(43,49,57,0.8)'"
          />
        </div>

        <div>
          <label class="block text-[#848e9c] mb-1.5 font-semibold uppercase tracking-wider text-[10px]">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="Minimum 8 characters"
            class="w-full border rounded-xl px-4 py-3 text-[#eaecef] placeholder-[#5e6673] focus:outline-none transition text-sm"
            style="background: rgba(11,14,17,0.8); border-color: rgba(43,49,57,0.8);"
            @focus="$event.target.style.borderColor='rgba(14,203,129,0.6)'"
            @blur="$event.target.style.borderColor='rgba(43,49,57,0.8)'"
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
            I agree to the <a href="/terms" target="_blank" class="text-[#0ecb81] underline font-semibold">Terms of Service</a> and acknowledge the <a href="/risk-disclosure" target="_blank" class="text-[#0ecb81] underline font-semibold">Financial Risk Disclosure</a>.
          </label>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full font-bold py-3.5 rounded-xl transition disabled:opacity-50 text-sm flex items-center justify-center space-x-2 mt-6 shadow-lg"
          style="background: linear-gradient(135deg, #0ecb81 0%, #0aaf6d 100%); color: #0b0e11; box-shadow: 0 8px 32px rgba(14,203,129,0.25);">
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span v-if="loading">Creating Account...</span>
          <span v-else">Create My Free Account →</span>
        </button>
      </form>

      <div class="border-t pt-5 text-center text-xs text-[#848e9c]" style="border-color: rgba(43,49,57,0.6);">
        Already have an account?
        <a href="/login" class="font-bold ml-1 hover:underline" style="color: #f0b90b;">Log In →</a>
      </div>
    </div>

    <!-- Trust badges -->
    <div class="flex items-center space-x-5 mt-8 z-10 text-[10px] text-[#5e6673]">
      <span class="flex items-center space-x-1"><span class="text-[#0ecb81]">✔</span><span>Free \$10K Demo</span></span>
      <span class="flex items-center space-x-1"><span class="text-[#0ecb81]">✔</span><span>No KYC</span></span>
      <span class="flex items-center space-x-1"><span class="text-[#0ecb81]">✔</span><span>Instant Access</span></span>
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
