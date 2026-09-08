<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const menuOpen = ref(false)
const searchOpen = ref(false)
const searchTerm = ref('')
const language = ref('English')

// Ziggy helper 'route()' support thakle direct route name use kora jabe, othoba fallback URL path
const currentUrl = computed(() => page.url)

const doctorsCurrent = computed(() => 
  typeof route === 'function' ? route().current('find-doctor') : currentUrl.value.startsWith('/find-doctor')
)
const departmentsCurrent = computed(() => 
  typeof route === 'function' ? route().current('departments.*') : currentUrl.value.startsWith('/departments')
)
const aboutCurrent = computed(() => 
  typeof route === 'function' ? route().current('about') : currentUrl.value.startsWith('/about')
)
const blogsCurrent = computed(() => 
  typeof route === 'function' ? route().current('blogs.*') : currentUrl.value.startsWith('/blogs')
)

function submitSearch() {
  const term = searchTerm.value.trim()
  if (!term) return
  searchOpen.value = false

  // Inertia Navigation with Ziggy or direct URL path
  const targetUrl = typeof route === 'function' ? route('find-doctor') : '/find-doctor'
  router.get(targetUrl, { q: term }, { preserveState: true })
}

function closeMenu() {
  menuOpen.value = false
}
</script>

<template>
  <a
    class="sr-only fixed left-4 top-4 z-[100] rounded-md bg-amz-red px-4 py-3 font-semibold text-white focus:not-sr-only focus:outline-none"
    href="#main-content"
  >Skip to content</a>

  <header class="relative z-40 bg-white site-header">
    <nav
      class="mx-auto flex h-[72px] max-w-[1312px] items-center justify-between gap-5 px-5 lg:px-0"
      aria-label="Primary navigation"
    >
      <Link class="shrink-0" :href="typeof route === 'function' ? route('home') : '/'" aria-label="AMZ Hospital home" @click="closeMenu">
        <img
          class="h-auto w-[205px] sm:w-[230px] lg:w-[244px]"
          src="/assets/images/amz-logo.png"
          alt="AMZ Hospital Ltd."
        />
      </Link>

      <div class="hidden items-center gap-9 xl:flex" aria-label="Main menu">
        <Link class="nav-link" :href="typeof route === 'function' ? route('find-doctor') : '/find-doctor'" :data-current="doctorsCurrent">Doctors</Link>
        <Link class="nav-link" :href="typeof route === 'function' ? route('departments') : '/departments'" :data-current="departmentsCurrent">Departments</Link>
        <Link class="nav-link" :href="typeof route === 'function' ? route('about') : '/about'" :data-current="aboutCurrent">About</Link>
        <Link class="nav-link" :href="typeof route === 'function' ? route('blogs') : '/blogs'" :data-current="blogsCurrent">Blogs</Link>
      </div>

      <div class="ml-auto hidden items-center gap-7 lg:flex">
        <button
          class="inline-flex items-center gap-2 text-[20px] font-normal"
          type="button"
          aria-label="Open site search"
          :aria-expanded="searchOpen"
          @click="searchOpen = !searchOpen"
        >
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="11" cy="11" r="6.2" />
            <path d="m16 16 4.2 4.2" />
          </svg>
          <span>Search</span>
        </button>

        <div class="inline-flex h-[42px] items-center rounded-full bg-[#fafafa] p-1 text-[13px] shadow-[0_2px_8px_rgba(0,0,0,0.08)]" aria-label="Language">
          <button
            class="font-bengali rounded-full px-3 py-2"
            :class="language === 'বাংলা' ? 'bg-white font-semibold text-[#020605] shadow-sm' : 'text-[#868686]'"
            type="button"
            @click="language = 'বাংলা'"
          >বাংলা</button>
          <button
            class="rounded-full px-3 py-2"
            :class="language === 'English' ? 'bg-white font-semibold text-[#020605] shadow-sm' : 'text-[#868686]'"
            type="button"
            @click="language = 'English'"
          >English</button>
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
        class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-[#e5e5e5] text-[#1e1e1e] lg:hidden"
        type="button"
        aria-controls="mobile-menu"
        :aria-expanded="menuOpen"
        :aria-label="menuOpen ? 'Close navigation menu' : 'Open navigation menu'"
        @click="menuOpen = !menuOpen"
      >
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
          <path v-if="!menuOpen" d="M4 7h16M4 12h16M4 17h16" />
          <path v-else d="M6 6l12 12M18 6 6 18" />
        </svg>
      </button>
    </nav>

    <form v-if="searchOpen" class="site-search" role="search" @submit.prevent="submitSearch">
      <label class="sr-only" for="site-search-input">Search doctors or specialties</label>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.2" /><path d="m16 16 4.2 4.2" /></svg>
      <input id="site-search-input" v-model="searchTerm" type="search" placeholder="Search doctors or specialties" autofocus />
      <button type="submit">Search</button>
    </form>

    <div v-show="menuOpen" id="mobile-menu" class="border-t border-[#eeeeee] bg-white px-5 pb-5 pt-3 lg:hidden">
      <div class="mx-auto flex max-w-[520px] flex-col gap-1">
        <Link class="mobile-nav-link" :href="typeof route === 'function' ? route('find-doctor') : '/find-doctor'" @click="closeMenu">Doctors</Link>
        <Link class="mobile-nav-link" :href="typeof route === 'function' ? route('departments') : '/departments'" @click="closeMenu">Departments</Link>
        <Link class="mobile-nav-link" :href="typeof route === 'function' ? route('about') : '/about'" @click="closeMenu">About</Link>
        <Link class="mobile-nav-link" :href="typeof route === 'function' ? route('blogs') : '/blogs'" @click="closeMenu">Blogs</Link>
        <div class="mt-3 flex items-center justify-between gap-3">
          <button class="rounded-lg border border-[#e6e6e6] px-4 py-3 text-left" type="button" @click="searchOpen = !searchOpen; closeMenu()">Search</button>
          <a class="rounded-lg bg-amz-red px-4 py-3 font-medium text-white" href="tel:10699">Hot Line-10699</a>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.site-header { box-shadow: 0 1px 0 rgba(0, 0, 0, .05); }
.site-search {
  align-items: center;
  background: #fff;
  border-top: 1px solid #eee;
  display: flex;
  gap: 12px;
  margin: 0 auto;
  max-width: 1312px;
  padding: 14px 20px;
}
.site-search svg { color: #8b8b8b; height: 20px; width: 20px; }
.site-search input { border: 1px solid #dedede; border-radius: 8px; flex: 1; min-width: 0; outline: none; padding: 10px 12px; }
.site-search input:focus { border-color: #e01f29; box-shadow: 0 0 0 3px rgba(224, 31, 41, .1); }
.site-search button { background: #e01f29; border-radius: 8px; color: #fff; font-weight: 600; padding: 10px 18px; }
</style>