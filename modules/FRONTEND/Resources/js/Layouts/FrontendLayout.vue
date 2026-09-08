<script setup>
import Header from '../Layouts/Header.vue'
import Footer from '../Layouts/Footer.vue'

</script>

<template>
  <div class="min-h-screen bg-white text-[#020605] antialiased">
    <a
      class="sr-only fixed left-4 top-4 z-[100] rounded-md bg-amz-red px-4 py-3 font-semibold text-white focus:not-sr-only focus:outline-none"
      href="#main-content"
    >
      Skip to content
    </a>

    <Header />

    <main id="main-content">
      <slot />
    </main>

    <Footer />
  </div>
</template>