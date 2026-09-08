<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import FrontendLayout from '../../Layouts/FrontendLayout.vue'

// Import custom sub-components if needed in your project scope:
// import SiteHeader from '@/Components/SiteHeader.vue'
import UiIcon from '../../Components/UiIcon.vue'

const searchQuery = ref('')
const activeCategory = ref('All')

const needs = [
  { label: 'Heart & Chest', icon: 'heart-pulse', query: 'Cardiology' },
  { label: 'Brain & Nerves', icon: 'brain', query: 'Neurology' },
  { label: 'Bones & Joints', icon: 'bone', query: 'Orthopedics' },
  { label: "Women's Health", icon: 'flower', query: 'Gynecology' },
  { label: "Children's Health", icon: 'baby', query: 'Pediatrics' },
  { label: 'Skin & Hair', icon: 'sparkles', query: 'Dermatology' },
  { label: 'Ear, Nose & Throat', icon: 'ear', query: 'ENT' },
  { label: 'Digestive Health', icon: 'drops', query: 'Medicine' },
  { label: 'Urinary Health', icon: 'drops', query: 'Urology' },
  { label: 'Burns & Reconstruction', icon: 'hand-heart', query: 'Plastic Surgery' }
]

const categories = ['All', 'Medicine', 'Surgery', "Women's Health", "Children's Health", 'Diagnostics', 'Specialized Care']
const departments = [
  { name: 'Gynecology & Obstetrics', category: "Women's Health", icon: '/assets/icons/department-gynaecology.svg' },
  { name: 'ENT', detail: '(Ear, Nose, Throat)', category: 'Specialized Care', icon: '/assets/icons/department-plastic.svg' },
  { name: 'Cardiology', category: 'Medicine', icon: '/assets/icons/department-cardiology.svg', route: '/departments/cardiology' },
  { name: 'ENT', detail: '(Ear, Nose, Throat)', category: 'Diagnostics', icon: '/assets/icons/department-ent.svg', monochrome: true },
  { name: 'Medicine', category: 'Medicine', icon: '/assets/icons/department-medicine.svg' },
  { name: 'General, Laparoscopic & Laser Surgery', category: 'Surgery', icon: '/assets/icons/department-surgery.svg' }
]

const visibleDepartments = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return departments.filter((department) => {
    const categoryMatches = activeCategory.value === 'All' || department.category === activeCategory.value
    const queryMatches = !query || `${department.name} ${department.detail || ''} ${department.category}`.toLowerCase().includes(query)
    return categoryMatches && queryMatches
  })
})

function selectNeed(need) {
  searchQuery.value = need.query
  document.querySelector('#department-search')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}
</script>

<template>
  <Head title="Departments | AMZ Hospital" />

  <FrontendLayout>
    <div class="departments-page">
      <SiteHeader />
      <main id="main-content" class="departments-main">
        <nav class="department-breadcrumb" aria-label="Breadcrumb">
          <Link href="/">Home</Link>
          <UiIcon name="chevron-right" />
          <span>Departments</span>
        </nav>

        <section class="departments-hero" aria-labelledby="departments-page-title">
          <div class="departments-hero-copy">
            <p class="department-kicker"><i></i>AMZ MEDICAL DEPARTMENTS</p>
            <h1 id="departments-page-title">Find the Right<br />Department for<br />Your Care.</h1>
            <p class="departments-hero-lead">Explore AMZ Hospital's medical specialties and find the right<br />team for your healthcare needs.</p>
            <div class="departments-trust">
              <span><UiIcon name="shield" />Patient-first guidance</span>
              <span><UiIcon name="map-pin" />Dhaka, Bangladesh</span>
            </div>
          </div>
          <img src="/assets/images/pages/department-consultation.jpg" alt="Doctor discussing a care plan with a patient" />
        </section>

        <section class="needs-section" aria-labelledby="needs-heading">
          <h2 id="needs-heading">What Brings You Here?</h2>
          <p>Start with what you need help with. We'll help you find the appropriate medical<br />specialty.</p>
          <div class="needs-grid">
            <button v-for="need in needs" :key="need.label" type="button" @click="selectNeed(need)">
              <UiIcon :name="need.icon" />
              <strong>{{ need.label }}</strong>
              <UiIcon name="arrow-up-right" />
            </button>
          </div>
          <p class="medical-note">
            <UiIcon name="info" />Information provided here is for navigation and education, not medical diagnosis.
          </p>
        </section>

        <section id="department-search" class="department-search-panel" aria-labelledby="specialty-search-title">
          <div class="search-panel-heading">
            <div>
              <p>START YOUR SEARCH</p>
              <h2 id="specialty-search-title">Or Search by Specialty</h2>
            </div>
            <span>Search by a department, specialty, or doctor<br />name to find your next step.</span>
          </div>
          <label>
            <UiIcon name="search" />
            <input v-model="searchQuery" type="search" placeholder="Search departments, specialties or doctors..." />
          </label>
          <div class="popular-searches">
            <span>Popular:</span>
            <button v-for="term in ['Cardiology','Neurology','Orthopedics','ENT','Pediatrics','Gynecology']" :key="term" type="button" @click="searchQuery = term">{{ term }}</button>
          </div>
        </section>

        <section class="all-departments" aria-labelledby="all-departments-title">
          <div class="department-section-heading">
            <div>
              <p>EXPLORE CARE</p>
              <h2 id="all-departments-title">All Departments</h2>
            </div>
            <span>Find the care that fits your needs.</span>
          </div>
          <div class="department-tabs" role="tablist" aria-label="Department categories">
            <button v-for="category in categories" :key="category" type="button" role="tab" :aria-selected="activeCategory === category" :class="{ active: activeCategory === category }" @click="activeCategory = category">{{ category }}</button>
          </div>
          <div class="all-department-grid">
            <Link v-for="department in visibleDepartments" :key="`${department.name}-${department.category}`" :href="department.route || '/departments'" class="all-department-card">
              <img :class="{ monochrome: department.monochrome }" :src="department.icon" alt="" />
              <div>
                <h3>{{ department.name }} <small v-if="department.detail">{{ department.detail }}</small></h3>
                <p>Expert diagnosis and treatment for ear, nose, and<br />throat conditions.</p>
              </div>
            </Link>
          </div>
          <div v-if="!visibleDepartments.length" class="department-empty">No departments match your current search.</div>
        </section>

        <section class="centers-section" aria-labelledby="centers-title">
          <div class="department-section-heading">
            <div>
              <p>FOCUSED EXPERTISE</p>
              <h2 id="centers-title">Centers of Excellence</h2>
              <em>Focused expertise for specialized healthcare needs.</em>
            </div>
            <Link href="/centers">Explore all centers <UiIcon name="arrow-up-right" /></Link>
          </div>
          <div class="department-centers-grid">
            <article class="department-center-card center-with-image">
              <img src="/assets/images/pages/department-consultation.jpg" alt="Cardiology consultation" />
              <div>
                <h3>Cardiology</h3>
                <p>Integrated care for heart and cardiovascular health.</p>
                <Link href="/departments/cardiology">Explore <UiIcon name="arrow-up-right" /></Link>
              </div>
            </article>
            <article class="department-center-card">
              <UiIcon name="sparkles" />
              <div>
                <h3>Plastic, Aesthetic & Laser<br />Surgery</h3>
                <p>Specialized surgical and reconstructive care with a<br />patient-first approach.</p>
                <Link href="/centers/plastic-aesthetic-laser-surgery">Explore <UiIcon name="arrow-up-right" /></Link>
              </div>
            </article>
            <article class="department-center-card">
              <UiIcon name="flower" />
              <div>
                <h3>Women's Health</h3>
                <p>Connected care across pregnancy, gynecology and<br />wellbeing.</p>
                <Link href="/centers">Explore <UiIcon name="arrow-up-right" /></Link>
              </div>
            </article>
          </div>
        </section>

        <aside class="departments-emergency">
          <div>
            <UiIcon name="activity" />
            <span>
              <strong>Need Urgent Medical Care?</strong>
              <small>For emergencies, contact AMZ Hospital's emergency service immediately.</small>
            </span>
          </div>
          <div>
            <a href="tel:10699"><UiIcon name="phone" />Call Emergency</a>
            <a href="https://maps.google.com" target="_blank" rel="noreferrer"><UiIcon name="send" />Get Directions</a>
          </div>
        </aside>

        <section class="departments-final-cta">
          <h2>Your Care Starts With<br />the Right Specialist.</h2>
          <p>Find the department and doctor that best match your healthcare<br />needs.</p>
          <div>
            <Link href="/find-doctor">Find a Doctor <UiIcon name="arrow-up-right" /></Link>
            <Link href="/appointment">Book Appointment</Link>
          </div>
        </section>
      </main>
    </div>
  </FrontendLayout>
</template>

<style scoped src="../../../../../../resources/css/departments.css"></style>