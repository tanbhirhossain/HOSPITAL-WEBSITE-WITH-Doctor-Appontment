<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import FrontendLayout from '../../Layouts/FrontendLayout.vue'
import UiIcon from '../../Components/UiIcon.vue'

defineOptions({
  layout: FrontendLayout
})

const saved = ref(false)
const consultationType = ref('In-Person')
const selectedDate = ref('MON 16')
const selectedTime = ref('10:00 AM')
const patientType = ref('New Patient')

const form = useForm({
  name: '',
  phone: '',
  email: '',
  consultation_type: consultationType.value,
  date: selectedDate.value,
  time: selectedTime.value,
  patient_type: patientType.value
})

const concerns = [
  { icon: 'heart-pulse', title: 'Chest Pain', copy: 'Evaluation of chest discomfort and related symptoms.' },
  { icon: 'activity', title: 'High Blood Pressure', copy: 'Diagnosis and long-term management of hypertension.' },
  { icon: 'waves', title: 'Heart Rhythm Problems', copy: 'Assessment of palpitations and irregular heartbeat.' },
  { icon: 'shield', title: 'Preventive Heart Care', copy: 'Risk screening and heart-healthy guidance.' }
]

const dates = [
  { day: 'SAT', date: '14' },
  { day: 'SUN', date: '15' },
  { day: 'MON', date: '16' },
  { day: 'TUE', date: '17' },
  { day: 'WED', date: '18' }
]

const times = ['10:00 AM', '11:00 AM', '12:00 PM', '12:30 PM']

const schedule = [
  { day: 'SAT', desktop: '10:00 AM – 1:00 PM', mobile: '10:00 AM – 1:00 PM', status: 'Available', time: '10:00 AM' },
  { day: 'SUN', desktop: '4:00 PM – 7:00 PM', mobile: '10:00 AM – 1:00 PM', status: 'Limited', time: '4:00 PM' },
  { day: 'MON', desktop: '4:00 PM – 7:00 PM', mobile: '4:00 PM – 7:00 PM', status: 'Limited', time: '4:00 PM' }
]

function scrollToBooking() {
  document.querySelector('#doctor-booking')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function scrollToSchedule() {
  document.querySelector('#consultation-schedule')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function chooseSchedule(item) {
  selectedDate.value = `${item.day} ${item.day === 'SAT' ? '14' : item.day === 'SUN' ? '15' : '16'}`
  selectedTime.value = item.time
  scrollToBooking()
}

function confirmAppointment() {
  form.consultation_type = consultationType.value
  form.date = selectedDate.value
  form.time = selectedTime.value
  form.patient_type = patientType.value

  form.post(route('appointments.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>

<template>
  <div class="min-h-screen bg-white text-[#212529] antialiased">
    <main id="main-content">
      <!-- Doctor Overview Section -->
      <section class="border-b border-gray-100 bg-white py-6 lg:py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
          <!-- Breadcrumb -->
          <nav class="mb-6 flex items-center gap-1.5 text-xs text-gray-500" aria-label="Breadcrumb">
            <Link :href="route('home')" class="hover:text-[#f80d1b]">Home</Link>
            <UiIcon name="chevron-right" class="h-2.5 w-2.5 text-gray-400" />
            <Link :href="route('find-doctor')" class="hover:text-[#f80d1b]">Doctors</Link>
            <UiIcon name="chevron-right" class="h-2.5 w-2.5 text-gray-400" />
            <span class="font-medium text-gray-900">Dr. Arifur Rahman</span>
          </nav>

          <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">
            <!-- Doctor Portrait -->
            <picture class="relative overflow-hidden rounded-3xl bg-gray-100 lg:col-span-5">
              <source media="(max-width: 700px)" srcset="/assets/images/pages/doctor-arifur-mobile.png" />
              <img src="/assets/images/pages/doctor-arifur.jpg" alt="Dr. Arifur Rahman" class="h-full w-full object-cover object-center" />
            </picture>

            <!-- Profile Info -->
            <div class="flex flex-col justify-between lg:col-span-7">
              <div>
                <!-- Social Links -->
                <div class="mb-4 flex items-center justify-end gap-2" aria-label="Social media">
                  <a class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#1877f2] text-xs font-bold text-white hover:opacity-90" href="https://facebook.com" target="_blank" rel="noreferrer">f</a>
                  <a class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#0a66c2] text-xs font-bold text-white hover:opacity-90" href="https://linkedin.com" target="_blank" rel="noreferrer">in</a>
                  <a class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#1da1f2] p-1.5 text-white hover:opacity-90" href="https://twitter.com" target="_blank" rel="noreferrer">
                    <svg viewBox="0 0 24 24" class="h-full w-full fill-current" aria-hidden="true">
                      <path d="M21 6.2c-.7.3-1.4.5-2.2.6.8-.5 1.4-1.2 1.7-2-.7.4-1.6.8-2.5.9A3.8 3.8 0 0 0 11.4 9c0 .3 0 .6.1.9-3.2-.2-6.1-1.7-8-4.1a3.8 3.8 0 0 0 1.2 5.1c-.6 0-1.2-.2-1.7-.5 0 1.9 1.3 3.4 3.1 3.8-.3.1-.7.1-1 .1-.2 0-.5 0-.7-.1.5 1.5 1.9 2.7 3.6 2.7A7.7 7.7 0 0 1 3.3 18H2.4a10.9 10.9 0 0 0 5.9 1.7c7.1 0 11-5.9 11-11v-.5c.7-.5 1.3-1.2 1.7-2Z" />
                    </svg>
                  </a>
                  <a class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#ff0000] p-1.5 text-white hover:opacity-90" href="https://youtube.com" target="_blank" rel="noreferrer">
                    <svg viewBox="0 0 24 24" class="h-full w-full fill-current" aria-hidden="true">
                      <path d="M21.6 7.2a2.7 2.7 0 0 0-1.9-1.9C18 4.8 12 4.8 12 4.8s-6 0-7.7.5a2.7 2.7 0 0 0-1.9 1.9A28 28 0 0 0 2 12a28 28 0 0 0 .4 4.8 2.7 2.7 0 0 0 1.9 1.9c1.7.5 7.7.5 7.7.5s6 0 7.7-.5a2.7 2.7 0 0 0 1.9-1.9A28 28 0 0 0 22 12a28 28 0 0 0-.4-4.8ZM10 15.2V8.8l5.5 3.2-5.5 3.2Z" />
                    </svg>
                  </a>
                </div>

                <p class="text-[10px] font-bold tracking-wider text-gray-500 uppercase">AMZ HOSPITAL</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-black sm:text-4xl">Dr. Arifur Rahman</h1>
                <h2 class="mt-1 text-sm font-bold text-gray-800">Consultant — Cardiology</h2>
                <p class="mt-0.5 text-xs text-gray-500">Department of Cardiology, AMZ Hospital Ltd.</p>

                <p class="mt-4 text-xs leading-relaxed text-gray-500">
                  Dr. Rahman provides specialist care for patients with heart and vascular conditions, focusing on clear guidance, early evaluation and personalised treatment plans.
                </p>

                <div class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
                  <span class="tracking-widest text-gray-300">☆☆☆☆☆</span>
                  <small class="text-[10px] text-gray-400">Verified reviews shown if available</small>
                </div>

                <!-- Meta Cards -->
                <div class="mt-5 grid grid-cols-3 gap-2.5">
                  <article class="rounded-xl border border-[#ffe1e3] bg-[#fff5f5] p-2.5 text-left">
                    <UiIcon name="stethoscope" class="mb-1 h-3.5 w-3.5 text-[#f80d1b]" />
                    <p class="text-[8px] font-bold tracking-wider text-gray-400 uppercase">SPECIALTY</p>
                    <b class="block text-[11px] font-semibold text-gray-800 leading-tight">Verified info required</b>
                  </article>
                  <article class="rounded-xl border border-[#ffe1e3] bg-[#fff5f5] p-2.5 text-left">
                    <UiIcon name="hospital" class="mb-1 h-3.5 w-3.5 text-[#f80d1b]" />
                    <p class="text-[8px] font-bold tracking-wider text-gray-400 uppercase">DEPARTMENT</p>
                    <b class="block text-[11px] font-semibold text-gray-800 leading-tight">Verified info required</b>
                  </article>
                  <article class="rounded-xl border border-[#ffe1e3] bg-[#fff5f5] p-2.5 text-left">
                    <UiIcon name="clock" class="mb-1 h-3.5 w-3.5 text-[#f80d1b]" />
                    <p class="text-[8px] font-bold tracking-wider text-gray-400 uppercase">EXPERIENCE</p>
                    <b class="block text-[11px] font-semibold text-gray-800 leading-tight">Verified info required</b>
                  </article>
                </div>
              </div>

              <!-- Primary Action Buttons -->
              <div class="mt-6 flex flex-wrap items-center gap-2.5">
                <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[#f80d1b] px-5 py-2.5 text-[11px] font-bold tracking-wide text-white hover:bg-red-700 active:scale-[0.98]" @click="scrollToBooking">
                  <UiIcon name="calendar-add" class="h-3.5 w-3.5" /> BOOK APPOINTMENT
                </button>
                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-5 py-2.5 text-[11px] font-bold tracking-wide text-gray-700 hover:bg-gray-50 active:scale-[0.98]" @click="scrollToSchedule">
                  <UiIcon name="calendar" class="h-3.5 w-3.5 text-gray-400" /> VIEW SCHEDULE
                </button>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-400 hover:border-red-200 hover:text-[#f80d1b]" :class="{ 'text-[#f80d1b] border-red-200 bg-rose-50': saved }" aria-label="Save doctor" @click="saved = !saved">
                  <UiIcon name="heart" class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Is This the Right Doctor section -->
      <section class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="lg:col-span-8">
              <h2 class="text-xl font-bold text-black">Is This the Right Doctor for You?</h2>
              <p class="mt-0.5 text-xs text-gray-400">Conditions and concerns this specialist commonly helps patients with.</p>
              
              <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <article v-for="item in concerns" :key="item.title" class="flex gap-3.5 rounded-2xl border border-gray-100 bg-white p-3.5 shadow-xs">
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-600">
                    <UiIcon :name="item.icon" class="h-4 w-4" />
                  </div>
                  <div>
                    <h3 class="text-xs font-bold text-gray-900">{{ item.title }}</h3>
                    <p class="mt-0.5 text-[11px] text-gray-400 leading-normal">{{ item.copy }}</p>
                  </div>
                </article>
              </div>

              <Link :href="route('departments', { slug: 'cardiology' })" class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-black hover:text-[#f80d1b]">
                View Full Expertise <UiIcon name="arrow-right" class="h-3 w-3" />
              </Link>
            </div>

            <!-- Side Card -->
            <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs lg:col-span-4">
              <h3 class="text-xs font-bold text-black">Before You Book</h3>
              <div class="mt-3 space-y-2">
                <article class="flex items-center gap-2.5 rounded-xl bg-gray-50/70 p-2.5">
                  <UiIcon name="stethoscope" class="h-3.5 w-3.5 text-gray-500" />
                  <span class="flex flex-col"><small class="text-[8px] font-bold text-gray-400 uppercase">SPECIALTY</small><b class="text-[11px] font-semibold text-gray-800">Cardiology</b></span>
                </article>
                <article class="flex items-center gap-2.5 rounded-xl bg-gray-50/70 p-2.5">
                  <UiIcon name="users" class="h-3.5 w-3.5 text-gray-500" />
                  <span class="flex flex-col"><small class="text-[8px] font-bold text-gray-400 uppercase">CONSULTATION</small><b class="text-[11px] font-semibold text-gray-800">In-person</b></span>
                </article>
                <article class="flex items-center gap-2.5 rounded-xl bg-gray-50/70 p-2.5">
                  <UiIcon name="map-pin" class="h-3.5 w-3.5 text-gray-500" />
                  <span class="flex flex-col"><small class="text-[8px] font-bold text-gray-400 uppercase">LOCATION</small><b class="text-[11px] font-semibold text-gray-800">AMZ Hospital, Dhaka</b></span>
                </article>
                <article class="flex items-center gap-2.5 rounded-xl bg-gray-50/70 p-2.5">
                  <UiIcon name="calendar-add" class="h-3.5 w-3.5 text-gray-500" />
                  <span class="flex flex-col"><small class="text-[8px] font-bold text-gray-400 uppercase">NEXT AVAILABLE</small><b class="text-[11px] font-semibold text-gray-800">Sat, 14 — 10:00 AM</b></span>
                </article>
              </div>
              <button type="button" class="mt-5 w-full rounded-full bg-[#f80d1b] py-2.5 text-[11px] font-bold tracking-wide text-white hover:bg-red-700" @click="scrollToBooking">
                BOOK APPOINTMENT
              </button>
            </aside>
          </div>
        </div>
      </section>

      <!-- Booking Form Section -->
      <section id="doctor-booking" class="bg-[#f8f9fa] py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
          <div class="text-center">
            <h2 class="text-2xl font-bold tracking-tight text-black">Choose a Convenient Time</h2>
            <p class="mt-1 text-xs text-gray-400">A calm, four-step booking experience — only the essentials.</p>
          </div>

          <form class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8" @submit.prevent="confirmAppointment">
            <!-- Step 1 -->
            <fieldset class="border-b border-gray-100 pb-5">
              <legend class="mb-3 text-xs font-bold text-black flex items-center gap-2">
                <b class="flex h-5 w-5 items-center justify-center rounded-full bg-black text-[9px] text-white">01</b> Select Consultation Type
              </legend>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border p-3.5 transition" :class="consultationType === 'In-Person' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                  <input v-model="consultationType" type="radio" value="In-Person" class="sr-only" />
                  <UiIcon name="hospital" class="h-4 w-4 text-gray-700" />
                  <span class="flex flex-col">
                    <strong class="text-xs font-bold text-black">In-Person</strong>
                    <small class="text-[10px] text-gray-400">Visit AMZ Hospital</small>
                  </span>
                </label>
                <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border p-3.5 transition" :class="consultationType === 'Online' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                  <input v-model="consultationType" type="radio" value="Online" class="sr-only" />
                  <UiIcon name="video" class="h-4 w-4 text-gray-700" />
                  <span class="flex flex-col">
                    <strong class="text-xs font-bold text-black">Online</strong>
                    <small class="text-[10px] text-gray-400">Available if verified</small>
                  </span>
                </label>
              </div>
            </fieldset>

            <!-- Step 2 -->
            <fieldset class="border-b border-gray-100 py-5">
              <legend class="mb-3 text-xs font-bold text-black flex items-center gap-2">
                <b class="flex h-5 w-5 items-center justify-center rounded-full bg-black text-[9px] text-white">02</b> Choose Date
              </legend>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="date in dates"
                  :key="date.date"
                  type="button"
                  class="flex flex-col items-center justify-center min-w-[62px] rounded-2xl border px-3 py-2 transition"
                  :class="selectedDate === `${date.day} ${date.date}` ? 'border-black bg-white text-black ring-1 ring-black' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'"
                  @click="selectedDate = `${date.day} ${date.date}`"
                >
                  <small class="text-[8px] font-bold uppercase tracking-wider text-gray-400">{{ date.day }}</small>
                  <strong class="text-xs font-extrabold">{{ date.date }}</strong>
                </button>
              </div>
            </fieldset>

            <!-- Step 3 -->
            <fieldset class="border-b border-gray-100 py-5">
              <legend class="mb-3 text-xs font-bold text-black flex items-center gap-2">
                <b class="flex h-5 w-5 items-center justify-center rounded-full bg-black text-[9px] text-white">03</b> Available Time
              </legend>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="time in times"
                  :key="time"
                  type="button"
                  class="rounded-full border px-3.5 py-1.5 text-xs font-semibold transition"
                  :class="[
                    time === '11:00 AM' ? 'opacity-40 cursor-not-allowed bg-gray-100 border-gray-200 text-gray-400' : '',
                    selectedTime === time && time !== '11:00 AM' ? 'border-black bg-white text-black ring-1 ring-black' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                  ]"
                  @click="selectedTime = time"
                >
                  {{ time }}
                </button>
              </div>
            </fieldset>

            <!-- Step 4 -->
            <fieldset class="py-5">
              <legend class="mb-3 text-xs font-bold text-black flex items-center gap-2">
                <b class="flex h-5 w-5 items-center justify-center rounded-full bg-black text-[9px] text-white">04</b> Patient Information
              </legend>
              <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
                <label class="flex flex-col gap-1">
                  <span class="text-[10px] font-semibold text-gray-600">Name</span>
                  <input v-model="form.name" required type="text" placeholder="Full name" class="rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 focus:border-[#f80d1b] focus:outline-none" />
                </label>
                <label class="flex flex-col gap-1">
                  <span class="text-[10px] font-semibold text-gray-600">Phone</span>
                  <input v-model="form.phone" required type="tel" placeholder="+880" class="rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 focus:border-[#f80d1b] focus:outline-none" />
                </label>
                <label class="flex flex-col gap-1">
                  <span class="text-[10px] font-semibold text-gray-600">Email</span>
                  <input v-model="form.email" required type="email" placeholder="you@email.com" class="rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 focus:border-[#f80d1b] focus:outline-none" />
                </label>
                <label class="flex flex-col gap-1">
                  <span class="text-[10px] font-semibold text-gray-600">Patient Type</span>
                  <select v-model="patientType" class="rounded-xl border border-gray-200 px-3 py-2 text-xs text-gray-900 focus:border-[#f80d1b] focus:outline-none">
                    <option>New Patient</option>
                    <option>Returning Patient</option>
                  </select>
                </label>
              </div>
            </fieldset>

            <button class="mt-3 w-full rounded-full bg-[#f80d1b] py-3 text-xs font-bold tracking-wide text-white hover:bg-red-700 disabled:opacity-50" type="submit" :disabled="form.processing">
              CONFIRM APPOINTMENT
            </button>
          </form>
        </div>
      </section>

      <!-- Before Visit & Schedule Section -->
      <section class="py-12">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs">
              <h2 class="text-base font-bold text-black">Before You Visit</h2>
              <p class="mt-0.5 text-xs text-gray-400">A short checklist to make your appointment smooth.</p>
              <div class="mt-5">
                <ul class="space-y-2.5 text-xs text-gray-600">
                  <li class="flex items-center gap-2"><UiIcon name="clipboard" class="h-3.5 w-3.5 text-gray-400" />Bring previous medical reports</li>
                  <li class="flex items-center gap-2"><UiIcon name="pill" class="h-3.5 w-3.5 text-gray-400" />Bring your current medication list</li>
                  <li class="flex items-center gap-2"><UiIcon name="clock" class="h-3.5 w-3.5 text-gray-400" />Arrive a little early</li>
                  <li class="flex items-center gap-2"><UiIcon name="id-card" class="h-3.5 w-3.5 text-gray-400" />Bring identification / documents required by AMZ</li>
                </ul>
                <Link :href="route('appointments.guide')" class="mt-5 inline-flex items-center gap-1 text-xs font-bold text-black hover:text-[#f80d1b]">
                  View Appointment Guide <UiIcon name="arrow-right" class="h-3 w-3" />
                </Link>
              </div>
            </article>

            <article id="consultation-schedule" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-xs">
              <h2 class="text-base font-bold text-black">Consultation Schedule</h2>
              <p class="mt-0.5 text-xs text-gray-400">Verified weekly availability.</p>
              <div class="mt-5 space-y-2.5">
                <article v-for="item in schedule" :key="item.day" class="flex items-center justify-between rounded-xl bg-gray-50/60 p-2.5">
                  <span class="flex items-center gap-3">
                    <b class="text-xs font-bold text-black">{{ item.day }}</b>
                    <small class="text-xs text-gray-400">{{ item.desktop }}</small>
                  </span>
                  <div class="flex items-center gap-2.5">
                    <i class="text-[10px] font-medium not-italic text-gray-400">{{ item.status }}</i>
                    <button type="button" class="rounded-full bg-[#f80d1b] px-3 py-1 text-[10px] font-bold text-white hover:bg-red-700" @click="chooseSchedule(item)">
                      Book This Time
                    </button>
                  </div>
                </article>
              </div>
            </article>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>