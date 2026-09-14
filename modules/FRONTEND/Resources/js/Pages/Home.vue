<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import FrontendLayout from '../../js/Layouts/FrontendLayout.vue';

const heroIndex = ref(0)
const departmentFilter = ref('all')
const packagePeriod = ref('monthly')
let heroTimer

function handleMainClick(event) {
  const trigger = event.target.closest('[data-scroll-target]')
  if (!trigger) return
  const carousel = document.getElementById(trigger.dataset.scrollTarget)
  if (!carousel) return
  const direction = trigger.dataset.scrollDirection === 'left' ? -1 : 1
  const amount = Math.max(280, Math.round(carousel.clientWidth * 0.78))
  carousel.scrollBy({ left: direction * amount, behavior: 'smooth' })
}

onMounted(() => {
  heroTimer = window.setInterval(() => {
    heroIndex.value = (heroIndex.value + 1) % 3
  }, 6500)
})

onBeforeUnmount(() => window.clearInterval(heroTimer))
</script>

<template>
  <FrontendLayout>
    <div class="home-page">
      <SiteHeader />
      <main id="main-content" @click="handleMainClick">
        <!-- Hero -->
        <section id="home" class="hero-section relative isolate min-h-[760px] overflow-hidden lg:h-[852px] lg:min-h-0" aria-labelledby="hero-title" data-hero-carousel>
          <div class="hero-slides absolute inset-0 -z-20" aria-hidden="true">
            <div class="hero-slide hero-slide--hospital" :class="{ 'is-active': heroIndex === 0 }" data-hero-slide></div>
          <div class="hero-slide hero-slide--care" :class="{ 'is-active': heroIndex === 1 }" data-hero-slide></div>
          <div class="hero-slide hero-slide--services" :class="{ 'is-active': heroIndex === 2 }" data-hero-slide></div>
        </div>
        <div class="hero-scrim absolute inset-0 -z-10 bg-gradient-to-r from-white/20 via-transparent to-transparent" aria-hidden="true"></div>

        <div class="hero-content mx-auto max-w-[1280px] px-5 sm:px-10">
          <div class="pt-32 sm:pt-48 lg:pt-[316px]">
            <h1 id="hero-title" class="max-w-[820px] font-sans text-[42px] font-normal leading-[1.18] tracking-[-0.035em] sm:text-[52px] lg:text-[64px]">
              Advanced care, delivered<br class="hidden lg:block" />
              with <em class="font-semibold text-amz-red">a steady hand.</em>
            </h1>
            <div class="hero-actions mt-9 flex flex-col gap-3 sm:flex-row sm:gap-10 lg:mt-10">
              <a class="hero-button bg-amz-red hover:bg-[#c8102e]" href="appointment.html">Book An Appointment</a>
              <a class="hero-button bg-[#494949] hover:bg-[#2f2f2f]" href="/find-doctor">Find A Doctor</a>
            </div>
          </div>

          <div class="quick-actions mt-16 grid grid-cols-2 gap-3 pb-8 lg:absolute lg:bottom-[100px] lg:left-1/2 lg:mt-0 lg:w-[1200px] lg:-translate-x-1/2 lg:grid-cols-[300px_265px_286px_276px] lg:gap-6 lg:pb-0">
            <a class="quick-action-card" href="appointment.html">
              <span class="max-w-[150px] text-[18px] leading-[1.35]">Home Sample<br />Collection</span>
              <img class="h-11 w-11 object-contain" src="/assets/icons/quick-home-sample.svg" alt="" />
            </a>
            <a class="quick-action-card" href="heart-health-guide.html">
              <span class="text-[18px] leading-[1.35]">Report<br />Download</span>
              <img class="h-12 w-12 object-contain" src="/assets/icons/quick-report.svg" alt="" />
            </a>
            <a class="quick-action-card" href="critical-care.html">
              <span class="text-[18px] leading-[1.35]">Critical Care</span>
              <img class="h-12 w-12 object-contain" src="/assets/icons/quick-critical-care.svg" alt="" />
            </a>
            <a class="quick-action-card" href="tel:10699">
              <span class="text-[18px] leading-[1.35]">Emergency<br class="lg:hidden" /> Call</span>
              <img class="h-12 w-12 object-contain" src="/assets/icons/quick-emergency.svg" alt="" />
            </a>
          </div>
        </div>
      </section>

      <!-- Departments -->
      <section id="departments" class="relative bg-white py-20 sm:py-24 lg:h-[975px] lg:py-[70px]" aria-labelledby="departments-title">
        <div class="mx-auto max-w-[1280px] px-5 sm:px-8">
          <div class="mx-auto max-w-[650px] text-center">
            <p class="eyebrow">Explore care</p>
            <h2 id="departments-title" class="section-title mt-[11px]">Departments</h2>
            <p class="section-subtitle mt-[11px]">Precision medicine delivered with an unwavering commitment to craft.</p>
            <div class="mt-[17px] flex flex-wrap justify-center gap-2" role="group" aria-label="Filter departments">
              <button class="filter-button" type="button" data-department-filter="all" :data-active="departmentFilter === 'all'" :aria-pressed="departmentFilter === 'all'" @click="departmentFilter = 'all'">All</button>
              <button class="filter-button" type="button" data-department-filter="medicine" :data-active="departmentFilter === 'medicine'" :aria-pressed="departmentFilter === 'medicine'" @click="departmentFilter = 'medicine'">Medicine</button>
              <button class="filter-button" type="button" data-department-filter="surgery" :data-active="departmentFilter === 'surgery'" :aria-pressed="departmentFilter === 'surgery'" @click="departmentFilter = 'surgery'">Surgery</button>
              <button class="filter-button" type="button" data-department-filter="diagnostics" :data-active="departmentFilter === 'diagnostics'" :aria-pressed="departmentFilter === 'diagnostics'" @click="departmentFilter = 'diagnostics'">Diagnostics</button>
            </div>
          </div>

          <div class="relative mt-10 lg:mt-[37px]">
            <button class="carousel-arrow carousel-arrow-left hidden lg:flex" type="button" data-scroll-target="department-grid" data-scroll-direction="left" data-department-cycle="previous" aria-label="Previous departments">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" /></svg>
            </button>
            <div id="department-grid" class="department-grid grid gap-4 overflow-x-auto pb-1 lg:ml-4 lg:w-[1216px] lg:grid-cols-2 lg:overflow-visible">
              <a class="department-card" data-category="surgery" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'surgery'" href="/departments">
                <img class="department-icon" src="/assets/icons/department-gynaecology.svg" alt="" />
                <div>
                  <h3>Gynecology &amp;<br />Obstetrics</h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
              <a class="department-card" data-category="surgery" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'surgery'" href="/centers/plastic-aesthetic-laser-surgery">
                <img class="department-icon" src="/assets/icons/department-plastic.svg" alt="" />
                <div>
                  <h3>Plastic, Aesthetic &amp;<br />Laser Surgery</h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
              <a class="department-card" data-category="medicine" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'medicine'" href="/departments/cardiology">
                <img class="department-icon" src="/assets/icons/department-cardiology.svg" alt="" />
                <div>
                  <h3>Cardiology</h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
              <a class="department-card" data-category="diagnostics" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'diagnostics'" href="/departments">
                <img class="department-icon" src="/assets/icons/department-ent.svg" alt="" />
                <div>
                  <h3>ENT <span>(Ear, Nose, Throat)</span></h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
              <a class="department-card" data-category="medicine" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'medicine'" href="/departments">
                <img class="department-icon" src="/assets/icons/department-medicine.svg" alt="" />
                <div>
                  <h3>Medicine</h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
              <a class="department-card" data-category="surgery" :data-filter-hidden="departmentFilter !== 'all' && departmentFilter !== 'surgery'" href="/departments">
                <img class="department-icon" src="/assets/icons/department-surgery.svg" alt="" />
                <div>
                  <h3>General, Laparoscopic<br />&amp; Laser Surgery</h3>
                  <p>Expert diagnosis and treatment for ear, nose, and throat conditions.</p>
                </div>
              </a>
            </div>
            <button class="carousel-arrow carousel-arrow-right hidden lg:flex" type="button" data-scroll-target="department-grid" data-scroll-direction="right" data-department-cycle="next" aria-label="Next departments">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </button>
          </div>

          <div class="mt-10 text-center lg:mt-[42px]">
            <a class="quiet-button" href="/departments">See All</a>
          </div>
        </div>
      </section>

      <!-- Centres of excellence -->
      <section id="centres" class="bg-white py-20 lg:h-[952px] lg:py-[74px]" aria-labelledby="centres-title">
        <div class="mx-auto max-w-[1107px] px-5 sm:px-8 lg:px-0">
          <div class="text-center">
            <h2 id="centres-title" class="section-title">Centres of excellence</h2>
            <p class="section-subtitle mt-4">Precision medicine delivered with an unwavering commitment to craft.</p>
          </div>

          <div class="mt-11 grid gap-4 md:grid-cols-2">
            <article class="centre-card">
              <img src="/assets/images/center-cardiac.jpg" alt="A detailed cardiac health illustration" />
              <div class="centre-card-content"><h3>Cardiac Center</h3><p>Coordinated expertise for heart and cardiovascular care.</p><a href="/departments/cardiology">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="centre-card">
              <img src="/assets/images/center-aesthetic.jpg" alt="A patient receiving specialist care" />
              <div class="centre-card-content"><h3>Cardiac Center</h3><p>Coordinated expertise for heart and cardiovascular care.</p><a href="/centers/plastic-aesthetic-laser-surgery">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="centre-card">
              <img src="/assets/images/center-dental.jpg" alt="A child holding an adult hand" />
              <div class="centre-card-content"><h3>Mother &amp; Child Center</h3><p>Focused care for mothers, children and growing families.</p><a href="/centers">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="centre-card">
              <img src="/assets/images/center-oncology.jpg" alt="A patient receiving aesthetic care" />
              <div class="centre-card-content"><h3>Mother &amp; Child Center</h3><p>Focused care for mothers, children and growing families.</p><a href="/centers">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="centre-card">
              <img src="/assets/images/center-mother.jpg" alt="Medical specialist holding an oncology model" />
              <div class="centre-card-content"><h3>Oncology Center</h3><p>Specialized oncology information and care pathways.</p><a href="/centers">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
            <article class="centre-card">
              <img src="/assets/images/center-room.jpg" alt="A bright and comfortable hospital room" />
              <div class="centre-card-content"><h3>Oncology Center</h3><p>Specialized oncology information and care pathways.</p><a href="/centers">Explore Center <span aria-hidden="true">→</span></a></div>
            </article>
          </div>

          <div class="mt-10 text-center"><a class="quiet-button" href="/centers">See all Centers</a></div>
        </div>
      </section>

      <!-- Health packages -->
      <section id="packages" class="relative bg-[#f5f5f5] py-20 lg:h-[717px] lg:py-[74px]" aria-labelledby="packages-title">
        <div class="mx-auto max-w-[1280px] px-5 lg:px-0">
          <div class="text-center">
            <h2 id="packages-title" class="section-title">Health packages</h2>
            <p class="section-subtitle mt-4">Proactive plans designed for a lifetime of quiet confidence.</p>
            <div class="mt-7 inline-flex overflow-hidden" role="tablist" aria-label="Health package schedule">
              <button class="package-tab" type="button" data-package-period="weekly" :data-active="packagePeriod === 'weekly'" role="tab" :aria-selected="packagePeriod === 'weekly'" @click="packagePeriod = 'weekly'">Weekly</button>
              <button class="package-tab" type="button" data-package-period="monthly" :data-active="packagePeriod === 'monthly'" role="tab" :aria-selected="packagePeriod === 'monthly'" @click="packagePeriod = 'monthly'">Monthly</button>
              <button class="package-tab" type="button" data-package-period="yearly" :data-active="packagePeriod === 'yearly'" role="tab" :aria-selected="packagePeriod === 'yearly'" @click="packagePeriod = 'yearly'">Yearly</button>
            </div>
          </div>

          <div class="relative mt-7 lg:mt-[28px]">
            <div class="absolute right-0 top-[-38px] hidden gap-3 lg:flex">
              <button class="slider-control" type="button" data-scroll-target="package-carousel" data-scroll-direction="left" aria-label="Previous health packages"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 12H5m6-6-6 6 6 6" /></svg></button>
              <button class="slider-control" type="button" data-scroll-target="package-carousel" data-scroll-direction="right" aria-label="Next health packages"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14m-6-6 6 6-6 6" /></svg></button>
            </div>
            <div id="package-carousel" class="hide-scrollbar carousel-track flex snap-x gap-5 overflow-x-auto pb-3 sm:gap-8 lg:gap-10 lg:pb-0" data-loop-carousel data-clone-slides>
              <article class="package-card snap-start" data-package-card>
                <div class="package-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20c.8-3.3 3.2-5 7.5-5s6.7 1.7 7.5 5"/></svg></div>
                <h3>Basic Checkup</h3><p>General Physician Consultation, CBC, Blood Sugar, Urine R/E, And ECG.</p><strong>BDT 2,000</strong><a href="appointment.html">See Pakage</a>
              </article>
              <article class="package-card snap-start" data-package-card>
                <div class="package-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20c.8-3.3 3.2-5 7.5-5s6.7 1.7 7.5 5"/></svg></div>
                <h3>Women Checkup</h3><p>General Physician Consultation, CBC, Blood Sugar, Urine R/E, And ECG.</p><strong>BDT 2,000</strong><a href="appointment.html">See Pakage</a>
              </article>
              <article class="package-card is-featured snap-start" data-package-card>
                <div class="package-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20c.8-3.3 3.2-5 7.5-5s6.7 1.7 7.5 5"/></svg></div>
                <h3>Family Checkup</h3><p>General Physician Consultation, CBC, Blood Sugar, Urine R/E, And ECG.</p><strong>BDT 2,000</strong><a href="appointment.html">See Pakage</a>
              </article>
              <article class="package-card snap-start" data-package-card>
                <div class="package-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.2"/><path d="M4.5 20c.8-3.3 3.2-5 7.5-5s6.7 1.7 7.5 5"/></svg></div>
                <h3>Women Checkup</h3><p>General Physician Consultation, CBC, Blood Sugar, Urine R/E, And ECG.</p><strong>BDT 2,000</strong><a href="appointment.html">See Pakage</a>
              </article>
            </div>
          </div>
        </div>
      </section>

      <!-- The Figma layout intentionally leaves a quiet visual pause between packages and news. -->
      <div class="hidden h-[111px] bg-white lg:block" aria-hidden="true"></div>

      <!-- News -->
      <section id="news" class="bg-white py-20 lg:h-[733px] lg:py-0" aria-labelledby="news-title">
        <div class="mx-auto max-w-[1356px] px-5 lg:px-0">
          <h2 id="news-title" class="news-title">AMZ News And Media</h2>
          <div class="mt-12 grid gap-8 md:grid-cols-3 lg:mt-[52px] lg:gap-6">
            <article class="article-card"><img src="/assets/images/news-corridor.jpg" alt="Hospital staff moving a patient through a hospital corridor" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
            <article class="article-card"><img src="/assets/images/news-bed.jpg" alt="A nurse caring for a patient beside a hospital bed" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
            <article class="article-card"><img src="/assets/images/news-theatre.jpg" alt="A modern hospital surgical theatre" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
          </div>
          <div class="mt-7 text-center lg:mt-6"><a class="quiet-button" href="blogs.html">View All</a></div>
        </div>
      </section>

      <!-- Blogs -->
      <section id="blogs" class="bg-white py-20 lg:h-[747px] lg:py-0" aria-labelledby="blogs-title">
        <div class="mx-auto max-w-[1356px] px-5 lg:px-0">
          <h2 id="blogs-title" class="news-title">Recent Blogs</h2>
          <div class="mt-12 grid gap-8 md:grid-cols-3 lg:mt-[52px] lg:gap-6">
            <article class="article-card"><img src="/assets/images/blog-ward.jpg" alt="Patients and visitors in a hospital ward" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
            <article class="article-card"><img src="/assets/images/blog-room.jpg" alt="A neatly prepared hospital room" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
            <article class="article-card"><img src="/assets/images/blog-doctor.jpg" alt="A doctor attending to a patient in critical care" /><div><time datetime="2026-11-15">15 November 2026</time><h3>Amz Hospital Ltd Dhaka<br />Checkup Campaing In Gulshan</h3><a href="heart-health-guide.html">Read More</a></div></article>
          </div>
          <div class="mt-7 text-center lg:mt-6"><a class="quiet-button" href="blogs.html">View All</a></div>
        </div>
      </section>

      <!-- Corporate services -->
      <section id="about" class="relative bg-[#f5f5f5] py-20 lg:h-[648px] lg:py-[70px]" aria-labelledby="corporate-title">
        <div class="mx-auto max-w-[1344px] px-5 lg:px-0">
          <div class="mx-auto max-w-[1195px] text-center">
            <h2 id="corporate-title" class="corporate-title">AMZ Corporate Services</h2>
            <p class="corporate-copy mt-6">
              One-Stop Service Desk: For the convenience of corporate clients, AMZ Hospital Dhaka operates a dedicated One-Stop Service Desk in the hospital atrium. This service desk ensures prompt assistance and streamlined experiences for referral patients, International patients and corporate patients. Our operating hours: 9:00 AM to 5:00 PM (except government holidays). We offer comprehensive healthcare management to address customized needs promptly and efficiently.
            </p>
          </div>

          <div class="relative mt-10 lg:mt-[35px]">
            <button class="brand-arrow left-0 hidden lg:flex" type="button" data-scroll-target="partner-carousel" data-scroll-direction="left" aria-label="Previous partners"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 12H5m6-6-6 6 6 6" /></svg></button>
            <div id="partner-carousel" class="hide-scrollbar carousel-track mx-auto flex max-w-[1194px] snap-x justify-start gap-5 overflow-x-auto pb-2 sm:gap-7 lg:gap-[46px] lg:pb-0" data-loop-carousel data-clone-slides>
              <div class="partner-card snap-start"><img src="/assets/icons/partner-astro.svg" alt="Astro" /></div>
              <div class="partner-card snap-start"><img src="/assets/icons/partner-adobe.svg" alt="Adobe" /></div>
              <div class="partner-card snap-start"><img src="/assets/icons/partner-apple-pay.svg" alt="Apple Pay" /></div>
              <div class="partner-card snap-start"><img src="/assets/icons/partner-apptentive.svg" alt="Apptentive" /></div>
              <div class="partner-card snap-start"><img src="/assets/icons/partner-astro-2.svg" alt="Astro" /></div>
            </div>
            <button class="brand-arrow right-0 hidden lg:flex" type="button" data-scroll-target="partner-carousel" data-scroll-direction="right" aria-label="Next partners"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12h14m-6-6 6 6-6 6" /></svg></button>
          </div>
        </div>
      </section>

      <!-- Patient stories -->
      <section id="stories" class="bg-white py-20 lg:h-[538px] lg:py-[80px]" aria-labelledby="stories-title">
        <div class="mx-auto max-w-[1280px] px-5 lg:px-0">
          <div class="relative text-center">
            <h2 id="stories-title" class="section-title text-[38px] sm:text-[42px]">Patient <em class="font-semibold text-amz-red">stories</em></h2>
            <p class="mt-1 text-[13px]">The quiet truth of recovery, spoken by those who lived it.</p>
            <div class="absolute right-0 top-[46px] hidden gap-3 lg:flex">
              <button class="slider-control" type="button" data-scroll-target="testimonial-carousel" data-scroll-direction="left" aria-label="Previous patient stories"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M19 12H5m6-6-6 6 6 6" /></svg></button>
              <button class="slider-control" type="button" data-scroll-target="testimonial-carousel" data-scroll-direction="right" aria-label="Next patient stories"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 12h14m-6-6 6 6-6 6" /></svg></button>
            </div>
          </div>

          <div id="testimonial-carousel" class="testimonial-carousel hide-scrollbar mt-10 snap-x gap-0 overflow-x-auto lg:mt-[76px]" data-loop-carousel>
            <article class="testimonial-card border-b border-[#d8d8d8] pb-9 md:border-b-0 md:border-r md:pr-10 lg:min-h-[174px]">
              <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
              <blockquote>"They didn't just look at my scans. They looked at me. In a moment of absolute fear, I found a strange and profound calm in their hands."</blockquote>
              <div class="mt-6 flex items-center gap-3"><img src="/assets/images/patient-anika.jpg" alt="Anika Rahman" /><div><strong>Anika Rahman</strong><span>Cardiac patient, Dhaka</span></div></div>
            </article>
            <article class="testimonial-card pt-9 md:pl-10 md:pt-0 lg:min-h-[174px]">
              <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
              <blockquote>"Flying in from London, I expected bureaucracy. I found a concierge who handled everything, and a surgeon who drew me a picture of hope."</blockquote>
              <div class="mt-6 flex items-center gap-3"><img src="/assets/images/patient-david.jpg" alt="David Thompson" /><div><strong>David Thompson</strong><span>International patient, UK</span></div></div>
            </article>
            <article class="testimonial-card border-b border-[#d8d8d8] pb-9 md:border-b-0 md:border-r md:pr-10 lg:min-h-[174px]">
              <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
              <blockquote>"The silence of the ICU was the loudest kindness I have ever known. The nurse held my hand before the machine even beeped."</blockquote>
              <div class="mt-6 flex items-center gap-3"><img src="/assets/images/patient-anika.jpg" alt="Fatima Begum" /><div><strong>Fatima Begum</strong><span>Neurology patient, Chittagong</span></div></div>
            </article>
            <article class="testimonial-card pt-9 md:pl-10 md:pt-0 lg:min-h-[174px]">
              <div class="stars" aria-label="5 out of 5 stars">★★★★★</div>
              <blockquote>"My daughter walked again. The orthopedics team didn't just fix a bone. They rebuilt a young dancer's dream with titanium and tenderness."</blockquote>
              <div class="mt-6 flex items-center gap-3"><img src="/assets/images/patient-david.jpg" alt="Karim Hossain" /><div><strong>Karim Hossain</strong><span>Father of a patient, Sylhet</span></div></div>
            </article>
          </div>
        </div>
      </section>

      <!-- Statistics -->
      <section class="bg-black py-20 text-white lg:h-[363px] lg:py-[121px]" aria-label="AMZ Hospital statistics">
        <div class="mx-auto grid max-w-[1271px] grid-cols-2 gap-y-10 px-5 text-center sm:grid-cols-4 lg:px-0">
          <div class="stat"><strong>300K+</strong><span>Patients treated</span></div>
          <div class="stat"><strong>79+</strong><span>Specialist doctors</span></div>
          <div class="stat"><strong>100+</strong><span>Hospital beds</span></div>
          <div class="stat"><strong>7+</strong><span>Years of practice</span></div>
        </div>
      </section>

      <!-- Location map -->
      <section class="h-[340px] overflow-hidden sm:h-[420px] lg:h-[533px]" aria-label="AMZ Hospital location map">
        <!-- <img class="h-full w-full object-cover object-center" src="/assets/images/amz-map.png" alt="Map showing AMZ Hospital Ltd. in Dhaka" /> -->
        <iframe class="h-full w-full object-cover object-center" src="https://maps.google.com/maps?q=AMZ%Hospital%Ltd%Dhaka%Bangladesh&amp;t=m&amp;z=10&amp;output=embed&amp;iwloc=near" title="AMZ Hospital Ltd" aria-label="AMZ Hospital Ltd"></iframe>
        
      </section>

    </main>
    <SiteFooter />
  </div>
  </FrontendLayout>
</template>
