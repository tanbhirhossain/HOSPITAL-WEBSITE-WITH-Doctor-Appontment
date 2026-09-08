<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import FrontendLayout from '../../Layouts/FrontendLayout.vue'

// Read Inertia props with local fallback for static development
const props = defineProps({
  doctors: {
    type: Array,
    default: () => [
      {
        id: 1,
        name: 'Dr. Amara Okafor',
        degree: 'MBBS, MS, FRCP (Cardiology)',
        rating: 4.9,
        reviews: 321,
        department: 'Cardiology',
        specialty: 'Interventional',
        tags: ['Cardiology', 'Heart Center'],
        available: true,
        gender: 'Female',
        experience: 14,
        languages: ['EN', 'ES'],
        image: '/assets/images/pages/doctor-amara.jpg',
        bio: 'Interventional cardiologist with 14 years treating complex heart conditions, focused on preventive care and patient education.'
      },
      {
        id: 2,
        name: 'Dr. James Holloway',
        degree: 'MBBS, MS (Neurology)',
        rating: 4.7,
        reviews: 198,
        department: 'Neurology',
        specialty: 'Neurology',
        tags: ['Neurology', 'Neuro Dept'],
        available: false,
        gender: 'Male',
        experience: 19,
        languages: ['EN', 'FR'],
        image: '/assets/images/pages/doctor-james.jpg',
        bio: 'Specialist in stroke management and neurodegenerative disorders, combining research and compassionate bedside care.'
      },
      {
        id: 3,
        name: 'Dr. Zara Nwosu',
        degree: 'MBBS, DCH (Pediatrics)',
        rating: 5.0,
        reviews: 421,
        department: 'Pediatrics',
        specialty: 'Pediatrics',
        tags: ['Pediatrics', "Children's Wing"],
        available: true,
        gender: 'Female',
        experience: 11,
        languages: ['EN'],
        image: '/assets/images/pages/doctor-zara.jpg',
        bio: 'Warm, family-focused pediatrician dedicated to newborn and adolescent wellness with a gentle, reassuring approach.'
      },
      {
        id: 4,
        name: 'Dr. Robert Klein',
        degree: 'MBBS, MS, MCh (Orthopedics)',
        rating: 4.6,
        reviews: 166,
        department: 'Orthopedics',
        specialty: 'Orthopedics',
        tags: ['Orthopedics', 'Joint Center'],
        available: false,
        gender: 'Male',
        experience: 23,
        languages: ['EN', 'ES'],
        image: '/assets/images/pages/doctor-robert.jpg',
        bio: 'Senior orthopedic surgeon specializing in joint replacement and sports injuries with over two decades of experience.'
      },
      {
        id: 5,
        name: 'Dr. Daniel Osei',
        degree: 'MBBS, MRCGP (General)',
        rating: 4.8,
        reviews: 247,
        department: 'General Practice',
        specialty: 'General Practice',
        tags: ['General Practice', 'Family Care'],
        available: true,
        gender: 'Male',
        experience: 9,
        languages: ['EN', 'FR'],
        image: '/assets/images/pages/doctor-daniel.jpg',
        bio: 'General practitioner offering holistic primary care for all ages, with a focus on chronic disease management.'
      },
      {
        id: 6,
        name: 'Dr. Mei Lin',
        degree: 'MBBS, MS, FRCS (Cardiology)',
        rating: 4.9,
        reviews: 386,
        department: 'Cardiology',
        specialty: 'Interventional',
        tags: ['Cardiology', 'Heart Center'],
        available: true,
        gender: 'Female',
        experience: 17,
        languages: ['EN', 'ES', 'FR'],
        image: '/assets/images/pages/doctor-mei.jpg',
        bio: "Renowned interventional cardiologist leading the hospital's cardiac catheterization program with precision and care."
      }
    ]
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

const page = usePage()
const query = ref(page.props.query?.q || props.filters.q || '')
const mobileFiltersOpen = ref(false)
const advancedVisible = ref(true)
const availableOnly = ref(false)
const gender = ref('Any')
const viewMode = ref('grid')
const selectedDepartments = ref([])
const selectedSpecialties = ref([])
const selectedLanguages = ref([])
const experienceRange = ref('any')
const savedDoctors = ref([])
const toast = ref('')
let toastTimer = null

const groups = reactive({
  department: true,
  specialty: true,
  gender: true,
  availability: true,
  language: false,
  experience: false,
  location: false
})

const departmentOptions = ['Cardiology', 'Neurology', 'Orthopedics', 'Pediatrics', 'General Practice']
const specialtyOptions = ['General Practice', 'Interventional']
const languageOptions = ['EN', 'ES', 'FR']

const filteredDoctors = computed(() => {
  const term = query.value.trim().toLowerCase()
  return props.doctors.filter((doctor) => {
    const searchable = [
      doctor.name,
      doctor.degree,
      doctor.department,
      doctor.specialty,
      doctor.bio,
      ...(doctor.tags || [])
    ].join(' ').toLowerCase()

    if (term && !searchable.includes(term)) return false
    if (selectedDepartments.value.length && !selectedDepartments.value.includes(doctor.department)) return false
    if (selectedSpecialties.value.length && !selectedSpecialties.value.includes(doctor.specialty)) return false
    if (gender.value !== 'Any' && doctor.gender !== gender.value) return false
    if (availableOnly.value && !doctor.available) return false
    if (selectedLanguages.value.length && !selectedLanguages.value.every((lang) => doctor.languages?.includes(lang))) return false
    if (experienceRange.value === 'under10' && doctor.experience >= 10) return false
    if (experienceRange.value === '10plus' && doctor.experience < 10) return false
    if (experienceRange.value === '20plus' && doctor.experience < 20) return false

    return true
  })
})

function resetFilters() {
  query.value = ''
  availableOnly.value = false
  gender.value = 'Any'
  selectedDepartments.value = []
  selectedSpecialties.value = []
  selectedLanguages.value = []
  experienceRange.value = 'any'
}

function toggleSaved(id) {
  savedDoctors.value = savedDoctors.value.includes(id)
    ? savedDoctors.value.filter((doctorId) => doctorId !== id)
    : [...savedDoctors.value, id]
}

function showToast(message) {
  toast.value = message
  if (toastTimer) window.clearTimeout(toastTimer)
  toastTimer = window.setTimeout(() => { toast.value = '' }, 2800)
}

function bookDoctor(doctor) {
  // Uses Inertia navigation via named Ziggy route or relative path
  router.get('/appointments/create', { doctor_id: doctor.id }, {
    preserveState: true,
    preserveScroll: true
  })
}
</script>

<template>
  <FrontendLayout>
    <div class="find-doctor-page">
      <main id="main-content">
        <section class="fd-hero" aria-labelledby="find-doctor-title">
          <div class="trust-badge">
            <svg class="heart-icon" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
            </svg>
            Trusted by 40,000+ patients every year
          </div>

          <h1 id="find-doctor-title">Find the Right Doctor for Your<br />Healthcare Journey.</h1>
          <p class="sub">Connect with our expert consultants across every specialty. Patient-centered care, precise diagnoses, and a calm, seamless path to feeling your best.</p>

          <form class="search-wrap" role="search" @submit.prevent>
            <div class="search-icon-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.2" /><path d="m16 16 4.2 4.2" /></svg>
            </div>
            <input v-model="query" class="search-input" type="search" placeholder="Search by Doctor Name, Specialty, or Department" aria-label="Search doctors" />
            <button class="search-btn" type="submit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="6.2" /><path d="m16 16 4.2 4.2" /></svg>
              Search
            </button>
          </form>

          <div class="advanced-toggle-wrap">
            <button class="adv-link" type="button" :aria-expanded="advancedVisible" @click="advancedVisible = !advancedVisible">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M6 12h12M9 18h6" /></svg>
              {{ advancedVisible ? 'Hide Advanced Filters' : 'Advanced Filters' }}
            </button>
          </div>

          <template v-if="advancedVisible">
            <div class="filter-row" aria-label="Filter shortcuts">
              <button v-for="label in ['Department', 'Specialty', 'Location', 'Language', 'Experience']" :key="label" class="fpill" type="button" @click="mobileFiltersOpen = true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18M6 12h12M9 18h6" /></svg>
                {{ label }}
                <svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M6 9l6 6 6-6" /></svg>
              </button>
            </div>

            <div class="agrow">
              <div class="agbox">
                <div class="avail-label">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></svg>
                  Available Today
                </div>
                <label class="sw">
                  <input v-model="availableOnly" type="checkbox" aria-label="Available today only" />
                  <span class="sw-track"></span>
                </label>
                <div class="sep-v"></div>
                <div class="gender-grp" role="group" aria-label="Doctor gender">
                  <span class="g-label">Gender</span>
                  <button v-for="option in ['Any', 'Male', 'Female']" :key="option" class="gbtn" :class="{ active: gender === option }" type="button" @click="gender = option">{{ option }}</button>
                </div>
              </div>
            </div>
          </template>

          <div class="stats-strip">
            <div class="stat-block">
              <div class="stat-thumb"><img src="/assets/images/pages/find-doctor-stat-consultants.jpg" alt="" /></div>
              <div><div class="stat-num">320+</div><div class="stat-lbl">Expert Consultants</div></div>
            </div>
            <div class="stat-block">
              <div class="stat-thumb"><img src="/assets/images/pages/find-doctor-stat-specialties.jpg" alt="" /></div>
              <div><div class="stat-num">45</div><div class="stat-lbl">Specialties Covered</div></div>
            </div>
            <div class="stat-block">
              <div class="stat-thumb"><img src="/assets/images/pages/find-doctor-stat-rating.jpg" alt="" /></div>
              <div><div class="stat-num">4.9</div><div class="stat-lbl">Avg. Patient Rating</div></div>
            </div>
          </div>
        </section>

        <button class="mobile-filter-button" type="button" @click="mobileFiltersOpen = !mobileFiltersOpen">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M6 12h12M9 18h6" /></svg>
          {{ mobileFiltersOpen ? 'Hide filters' : 'Show filters' }}
        </button>

        <div class="page-body">
          <aside class="sidebar" :class="{ 'mobile-open': mobileFiltersOpen }" aria-label="Doctor filters">
            <div class="sb-top">
              <span class="sb-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M6 12h12M9 18h6" /></svg>Filters</span>
              <button class="sb-reset" type="button" @click="resetFilters">Reset</button>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.department }" type="button" @click="groups.department = !groups.department">Department<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.department" class="fg-body">
                <label v-for="option in departmentOptions" :key="option" class="fg-opt"><input v-model="selectedDepartments" type="checkbox" :value="option" />{{ option }}</label>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.specialty }" type="button" @click="groups.specialty = !groups.specialty">Specialty<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.specialty" class="fg-body">
                <label v-for="option in specialtyOptions" :key="option" class="fg-opt"><input v-model="selectedSpecialties" type="checkbox" :value="option" />{{ option }}</label>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.gender }" type="button" @click="groups.gender = !groups.gender">Gender<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.gender" class="fg-body">
                <label v-for="option in ['Any', 'Male', 'Female']" :key="option" class="fg-opt"><input v-model="gender" type="radio" name="gender" :value="option" />{{ option }}</label>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.availability }" type="button" @click="groups.availability = !groups.availability">Availability Today<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.availability" class="fg-body">
                <div class="sb-avail"><span>Available today only</span><label class="sw"><input v-model="availableOnly" type="checkbox" /><span class="sw-track"></span></label></div>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.language }" type="button" @click="groups.language = !groups.language">Language<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.language" class="fg-body">
                <label v-for="option in languageOptions" :key="option" class="fg-opt"><input v-model="selectedLanguages" type="checkbox" :value="option" />{{ option }}</label>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.experience }" type="button" @click="groups.experience = !groups.experience">Experience<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.experience" class="fg-body">
                <label class="fg-opt"><input v-model="experienceRange" type="radio" name="experience" value="any" />Any experience</label>
                <label class="fg-opt"><input v-model="experienceRange" type="radio" name="experience" value="under10" />Under 10 years</label>
                <label class="fg-opt"><input v-model="experienceRange" type="radio" name="experience" value="10plus" />10+ years</label>
                <label class="fg-opt"><input v-model="experienceRange" type="radio" name="experience" value="20plus" />20+ years</label>
              </div>
            </div>

            <div class="fg">
              <button class="fg-head" :class="{ 'is-collapsed': !groups.location }" type="button" @click="groups.location = !groups.location">Location<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M18 15l-6-6-6 6" /></svg></button>
              <div v-show="groups.location" class="fg-body"><p class="filter-note">AMZ Hospital, Dhaka</p></div>
            </div>
          </aside>

          <section class="results" aria-label="Doctor listings">
            <div class="results-hd">
              <div class="results-hd-row">
                <div>
                  <div class="res-eyebrow">AMZ HOSPITAL LTD.</div>
                  <div class="res-title">Featured Specialists</div>
                  <div class="res-sub">Showing {{ filteredDoctors.length }} highly-rated {{ filteredDoctors.length === 1 ? 'doctor' : 'doctors' }} matching your filters</div>
                </div>
                <div class="view-toggle" role="group" aria-label="Results view">
                  <button class="vbtn" :class="{ active: viewMode === 'grid' }" type="button" :aria-pressed="viewMode === 'grid'" @click="viewMode = 'grid'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></svg>Grid</button>
                  <button class="vbtn" :class="{ active: viewMode === 'list' }" type="button" :aria-pressed="viewMode === 'list'" @click="viewMode = 'list'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>List</button>
                </div>
              </div>
            </div>

            <div class="results-cards" :class="`${viewMode}-mode`">
              <article v-for="doctor in filteredDoctors" :key="doctor.id" class="dcard">
                <button class="wish-btn" :class="{ saved: savedDoctors.includes(doctor.id) }" type="button" :aria-label="savedDoctors.includes(doctor.id) ? `Remove ${doctor.name} from saved doctors` : `Save ${doctor.name}`" :aria-pressed="savedDoctors.includes(doctor.id)" @click="toggleSaved(doctor.id)">
                  <svg viewBox="0 0 24 24" :fill="savedDoctors.includes(doctor.id) ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" /></svg>
                </button>
                <div class="dcard-top">
                  <div class="dphoto"><img :src="doctor.image" :alt="doctor.name" /></div>
                  <div class="dinfo">
                    <div class="dname">{{ doctor.name }}</div>
                    <div class="ddeg">{{ doctor.degree }}</div>
                    <div class="drating"><span class="star">★</span><span class="score">{{ doctor.rating.toFixed(1) }}</span><span class="cnt">({{ doctor.reviews }})</span></div>
                    <div class="dtags"><span v-for="tag in doctor.tags" :key="tag" class="dtag">{{ tag }}</span><span v-if="doctor.available" class="dtag-avail">Available Today</span></div>
                  </div>
                </div>
                <p class="dbio">{{ doctor.bio }}</p>
                <div class="dmeta">
                  <span class="dmeta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></svg>{{ doctor.experience }} yrs</span>
                  <span class="dmeta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12.87 15.07l-2.54-2.51C12 10.6 13.2 8.4 14 6h3V4h-7V2H8v2H1v2h11c-.7 2-1.7 3.8-3 5.4C8.1 10.3 7.3 9.2 6.7 8h-2c.7 1.6 1.7 3.2 3 4.6L2.6 17.6 4 19l5-5 3.1 3.1.8-2zM18.5 10h-2L12 22h2l1.1-3h4.8l1.1 3h2l-4.5-12zm-2.6 7 1.6-4.3 1.6 4.3h-3.2z" /></svg>{{ doctor.languages ? doctor.languages.join(', ') : '' }}</span>
                </div>
                <div class="dactions">
                  <button class="btn-book" type="button" @click="bookDoctor(doctor)">Book Appointment</button>
                  <button class="btn-view" type="button" @click="showToast(`${doctor.name}'s full profile is coming soon`)">View Profile</button>
                </div>
              </article>
            </div>

            <div v-if="!filteredDoctors.length" class="empty-state">
              <strong>No doctors found</strong>
              <p>Try changing or resetting your filters.</p>
              <button type="button" @click="resetFilters">Reset filters</button>
            </div>
          </section>
        </div>
      </main>

      <Transition name="toast">
        <div v-if="toast" class="toast" role="status">{{ toast }}</div>
      </Transition>
    </div>
  </FrontendLayout>
</template>

<style scoped src="../../../../../../resources/css/find-doctor.css"></style>