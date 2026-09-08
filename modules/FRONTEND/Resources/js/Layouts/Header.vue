<script setup>
import { ref } from 'vue'

const isMobileMenuOpen = ref(false)
const currentLang = ref('en')

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}
</script>

<template>
  <header class="relative z-40 bg-white">
    <nav
      class="mx-auto flex h-[72px] max-w-[1312px] items-center justify-between gap-5 px-5 lg:px-0"
      aria-label="Primary navigation"
    >
      <a class="shrink-0" href="/" aria-label="AMZ Hospital home">
        <img
          class="h-auto w-[205px] sm:w-[230px] lg:w-[244px]"
          src="/assets/images/amz-logo.png"
          alt="AMZ Hospital Ltd."
        />
      </a>

      <div class="hidden items-center gap-9 xl:flex" aria-label="Main menu">
        <a class="nav-link" href="/find-doctor">Doctors</a>
        <a class="nav-link" href="/departments">Departments</a>
        <a class="nav-link" href="/about">About</a>
        <a class="nav-link" href="/blogs">Blogs</a>
      </div>

      <div class="ml-auto hidden items-center gap-7 lg:flex">
        <button class="inline-flex items-center gap-2 text-[20px] font-normal" type="button" aria-label="Open site search">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="11" cy="11" r="6.2" />
            <path d="m16 16 4.2 4.2" />
          </svg>
          <span>Search</span>
        </button>

        <div class="inline-flex h-[42px] items-center rounded-full bg-[#fafafa] p-1 text-[13px] shadow-[0_2px_8px_rgba(0,0,0,0.08)]" aria-label="Language">
          <button 
            class="font-bengali rounded-full px-3 py-2" 
            :class="currentLang === 'bn' ? 'bg-white font-semibold text-[#020605] shadow-sm' : 'text-[#868686]'"
            type="button"
            @click="currentLang = 'bn'"
          >
            ?????
          </button>
          <button 
            class="rounded-full px-3 py-2" 
            :class="currentLang === 'en' ? 'bg-white font-semibold text-[#020605] shadow-sm' : 'text-[#868686]'"
            type="button"
            @click="currentLang = 'en'"
          >
            English
          </button>
        </div>
      </div>

      <a
        class="hidden h-[46px] shrink-0 items-center justify-center gap-2 rounded-[10px] bg-amz-red px-5 text-[16px] font-medium text-white transition hover:bg-[#c8102e] lg:inline-flex"
        href="tel:10699"
      >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path d="M21 16.9v2.6a1.8 1.8 0 0 1-2 1.8A17.8 17.8 0 0 1 2.7 5a1.8 1.8 0 0 1 1.8-2H7a1.8 1.8 0 0 1 1.8 1.5c.1 1 .4 2 .8 2.9a1.8 1.8 0 0 1-.4 1.9L8.1 10.4a14.4 14.4 0 0 0 5.5 5.5l1.1-1.1a1.8 1.8 0 0 1 1.9-.4c.9.4 1.9.7 2.9.8a1.8 1.8 0 0 1 1.5 1.7Z" />
        </svg>
        Hot Line-10699
      </a>

      <button
        id="menu-toggle"
        class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-[#e5e5e5] text-[#1e1e1e] lg:hidden"
        type="button"
        :aria-expanded="isMobileMenuOpen"
        aria-controls="mobile-menu"
        aria-label="Open navigation menu"
        @click="toggleMobileMenu"
      >
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
          <path d="M4 7h16M4 12h16M4 17h16" />
        </svg>
      </button>
    </nav>

    <div 
      id="mobile-menu" 
      v-show="isMobileMenuOpen" 
      class="border-t border-[#eeeeee] bg-white px-5 pb-5 pt-3 lg:hidden"
    >
      <div class="mx-auto flex max-w-[520px] flex-col gap-1">
        <a class="mobile-nav-link" href="/find-doctor">Doctors</a>
        <a class="mobile-nav-link" href="/departments">Departments</a>
        <a class="mobile-nav-link" href="/about">About</a>
        <a class="mobile-nav-link" href="/blogs">Blogs</a>
        <div class="mt-3 flex items-center justify-between gap-3">
          <button class="rounded-lg border border-[#e6e6e6] px-4 py-3 text-left" type="button">Search</button>
          <a class="rounded-lg bg-amz-red px-4 py-3 font-medium text-white" href="tel:10699">Hot Line-10699</a>
        </div>
      </div>
    </div>
  </header>
</template>