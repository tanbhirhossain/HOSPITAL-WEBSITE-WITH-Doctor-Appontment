<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

use App\Models\User;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\DepartmentCategory;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorExpertise;
use Modules\DOCTOR\Models\DoctorSchedule;
use Modules\DOCTOR\Models\PageSection;
use Modules\DOCTOR\Models\Symptom;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

/**
 * Covers the DOCTOR module: departments, categories, doctors (with nested
 * expertises), schedules, symptoms and the page sections that decorate the
 * public find-a-doctor screen.
 */
beforeEach(function (): void {
    $this->seed([
        \Modules\CORE\Database\Seeders\AccessControlSeeder::class,
        \Modules\DOCTOR\Database\Seeders\DepartmentCategorySeeder::class,
        \Modules\DOCTOR\Database\Seeders\DepartmentSeeder::class,
        \Modules\DOCTOR\Database\Seeders\DoctorSeeder::class,
        \Modules\DOCTOR\Database\Seeders\DoctorExpertiseSeeder::class,
        \Modules\DOCTOR\Database\Seeders\DoctorScheduleSeeder::class,
        \Modules\DOCTOR\Database\Seeders\SymptomSeeder::class,
        \Modules\DOCTOR\Database\Seeders\PageSectionSeeder::class,
    ]);

    $this->admin = User::query()->where('email', 'admin@amzhospital.test')->firstOrFail();
    $this->admin->assignRole('super-admin');
});

/* ------------------------------------------------------------------ */
/*  Department categories                                              */
/* ------------------------------------------------------------------ */

it('renders the department category screen', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/categories')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('DOCTOR::DepartmentCategory/Index', false)->has('categories.data'));
});

it('stores, updates and deletes a department category', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/categories', [
            'name' => 'Surgical Specialties',
            'description' => 'Operating-theatre led care.',
            'is_active' => true,
        ])
        ->assertRedirect();

    $category = DepartmentCategory::query()->where('name', 'Surgical Specialties')->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/doctors/categories/{$category->id}", [
            'name' => 'Surgical Care',
            'description' => 'Updated description.',
            'is_active' => false,
        ])
        ->assertRedirect();

    expect($category->refresh()->name)->toBe('Surgical Care')
        ->and($category->is_active)->toBeFalse();

    actingAs($this->admin)->delete("/admin/doctors/categories/{$category->id}")->assertRedirect();

    assertDatabaseMissing('department_categories', ['id' => $category->id]);
});

it('requires a name for a department category', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/categories', ['description' => 'No name supplied.'])
        ->assertSessionHasErrors('name');
});

/* ------------------------------------------------------------------ */
/*  Departments                                                        */
/* ------------------------------------------------------------------ */

it('renders the department screen with its category options', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/departments')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('DOCTOR::Department/Index', false)
                ->has('departments.data')
                ->has('categoryOptions'),
        );
});

it('stores a department and can toggle its featured flag', function (): void {
    $category = DepartmentCategory::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors/departments', [
            'title' => 'Neurosurgery',
            'department_category_id' => $category->id,
            'description' => 'Brain and spine surgery.',
            'is_featured' => false,
            'is_active' => true,
        ])
        ->assertRedirect();

    $department = Department::query()->where('title', 'Neurosurgery')->firstOrFail();

    expect($department->is_featured)->toBeFalse();

    actingAs($this->admin)
        ->patch("/admin/doctors/departments/{$department->id}/featured")
        ->assertRedirect();

    expect($department->refresh()->is_featured)->toBeTrue();
});

it('refuses a department without a valid category', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/departments', ['title' => 'Orphan Unit', 'department_category_id' => 999999])
        ->assertSessionHasErrors('department_category_id');
});

it('searches departments by title and by their category', function (): void {
    $category = DepartmentCategory::query()->firstOrFail();

    actingAs($this->admin)
        ->get('/admin/doctors/departments?search='.urlencode('cardio'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('departments.data'));

    actingAs($this->admin)
        ->get('/admin/doctors/departments?sort=category.title&direction=asc')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('departments.data'));

    expect($category->name)->not->toBeEmpty();
});

/* ------------------------------------------------------------------ */
/*  Doctors                                                            */
/* ------------------------------------------------------------------ */

it('renders the doctor screen with nested relations', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('DOCTOR::Doctor/Index', false)
                ->has('doctors.data')
                ->has('departmentOptions'),
        );
});

it('stores a doctor together with its expertises', function (): void {
    $department = Department::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors', [
            'name' => 'Dr. Rafiqul Islam',
            'department_id' => $department->id,
            'designation' => 'Senior Consultant',
            'specialty' => 'Neurology',
            'qualification' => 'MBBS, FCPS',
            'experience_years' => 12,
            'hospital_name' => 'AMZ Hospital, Barishal',
            'location' => 'Barishal',
            'rating' => 4.5,
            'is_featured' => true,
            'is_active' => true,
            'expertises' => [
                ['title' => 'Stroke care', 'description' => 'Acute stroke management', 'icon' => 'Brain', 'sort_order' => 1],
                ['title' => 'Epilepsy', 'description' => '', 'icon' => 'Activity', 'sort_order' => 2],
            ],
            'social_links' => ['facebook' => 'https://facebook.com/rafiqul'],
        ])
        ->assertRedirect();

    $doctor = Doctor::query()->where('name', 'Dr. Rafiqul Islam')->firstOrFail();

    expect($doctor->department_id)->toBe($department->id)
        ->and($doctor->is_featured)->toBeTrue()
        ->and($doctor->expertises()->count())->toBe(2);
});

it('replaces expertises when a doctor is updated', function (): void {
    $department = Department::query()->firstOrFail();

    actingAs($this->admin)->post('/admin/doctors', [
        'name' => 'Dr. Update Me',
        'department_id' => $department->id,
        'specialty' => 'General Medicine',
        'experience_years' => 5,
        'is_active' => true,
        'expertises' => [['title' => 'Old expertise', 'description' => '', 'icon' => 'Star', 'sort_order' => 1]],
    ]);

    $doctor = Doctor::query()->where('name', 'Dr. Update Me')->firstOrFail();

    actingAs($this->admin)->put("/admin/doctors/{$doctor->id}", [
        'name' => 'Dr. Updated Name',
        'department_id' => $department->id,
        'specialty' => 'General Medicine',
        'experience_years' => 6,
        'is_active' => true,
        'expertises' => [
            ['title' => 'New one', 'description' => '', 'icon' => 'Star', 'sort_order' => 1],
            ['title' => 'New two', 'description' => '', 'icon' => 'Star', 'sort_order' => 2],
        ],
    ])->assertRedirect();

    $doctor->refresh();

    expect($doctor->name)->toBe('Dr. Updated Name')
        ->and($doctor->expertises()->pluck('title')->all())
        ->toEqualCanonicalizing(['New one', 'New two']);
});

it('validates the doctor payload', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors', [
            'name' => 'No Department',
            'department_id' => 999999,
            'experience_years' => -3,
        ])
        ->assertSessionHasErrors(['department_id', 'specialty', 'experience_years']);
});

it('toggles a doctor status and deletes a doctor', function (): void {
    $doctor = Doctor::query()->firstOrFail();
    $before = (bool) $doctor->is_active;

    actingAs($this->admin)->patch("/admin/doctors/{$doctor->id}/status")->assertRedirect();

    expect((bool) $doctor->refresh()->is_active)->toBe(! $before);

    actingAs($this->admin)->delete("/admin/doctors/{$doctor->id}")->assertRedirect();

    assertDatabaseMissing('doctors', ['id' => $doctor->id]);
    assertDatabaseMissing('doctor_expertises', ['doctor_id' => $doctor->id]);
});

/* ------------------------------------------------------------------ */
/*  Schedules                                                          */
/* ------------------------------------------------------------------ */

it('renders the schedule screen', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/schedules')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('DOCTOR::DoctorSchedule/Index', false)->has('schedules.data'));
});

it('stores a schedule and rejects one that ends before it starts', function (): void {
    $doctor = Doctor::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors/schedules', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Sun',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'consultation_type' => 'in_person',
            'availability_status' => 'available',
            'max_patients' => 20,
            'is_active' => true,
        ])
        ->assertRedirect();

    assertDatabaseHas('doctor_schedules', ['doctor_id' => $doctor->id, 'day_of_week' => 'Sun']);

    actingAs($this->admin)
        ->post('/admin/doctors/schedules', [
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Mon',
            'start_time' => '16:00',
            'end_time' => '09:00',
            'consultation_type' => 'in_person',
            'availability_status' => 'available',
            'max_patients' => 20,
            'is_active' => true,
        ])
        ->assertSessionHasErrors('end_time');
});

it('rejects a duplicate slot for the same doctor on the same weekday', function (): void {
    $doctor = Doctor::query()->firstOrFail();

    $payload = [
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Tue',
        'start_time' => '08:30',
        'end_time' => '12:30',
        'consultation_type' => 'in_person',
        'availability_status' => 'available',
        'max_patients' => 15,
        'is_active' => true,
    ];

    actingAs($this->admin)->post('/admin/doctors/schedules', $payload)->assertRedirect();

    actingAs($this->admin)->post('/admin/doctors/schedules', $payload)->assertSessionHasErrors('start_time');

    expect(
        DoctorSchedule::query()
            ->where('doctor_id', $doctor->id)
            ->where('day_of_week', 'Tue')
            ->where('start_time', '08:30')
            ->count(),
    )->toBe(1);
});

it('sorts schedules from Saturday to Friday', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/schedules?sort=day_of_week&direction=asc')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->has('schedules.data')
                ->where('query.sort', 'day_of_week'),
        );
});

/* ------------------------------------------------------------------ */
/*  Symptoms & page sections                                           */
/* ------------------------------------------------------------------ */

it('stores, updates and deletes a symptom', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/symptoms', [
            'title' => 'Persistent cough',
            'icon' => 'Wind',
            'link_url' => '/symptoms/cough',
            'is_active' => true,
        ])
        ->assertRedirect();

    $symptom = Symptom::query()->where('title', 'Persistent cough')->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/doctors/symptoms/{$symptom->id}", [
            'title' => 'Cough & cold',
            'icon' => 'Wind',
            'link_url' => '/symptoms/cough',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($symptom->refresh()->title)->toBe('Cough & cold');

    actingAs($this->admin)->delete("/admin/doctors/symptoms/{$symptom->id}")->assertRedirect();

    assertDatabaseMissing('symptoms', ['id' => $symptom->id]);
});

it('rejects a symptom without a title', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/symptoms', ['icon' => 'Wind'])
        ->assertSessionHasErrors('title');
});

it('keeps page section keys unique', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/page-sections', [
            'section_key' => 'hero',
            'badge' => 'Trusted care',
            'title' => 'Find a specialist',
            'subtitle' => 'Book in under a minute',
            'primary_button_text' => 'Find a doctor',
            'primary_button_url' => '/find-doctor',
            'secondary_button_text' => '',
            'secondary_button_url' => '',
            'image' => '',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('section_key');

    actingAs($this->admin)
        ->post('/admin/doctors/page-sections', [
            'section_key' => 'trust',
            'badge' => 'Why us',
            'title' => 'Nurse-guided follow up',
            'subtitle' => '',
            'primary_button_text' => '',
            'primary_button_url' => '',
            'secondary_button_text' => '',
            'secondary_button_url' => '',
            'image' => '',
            'is_active' => true,
        ])
        ->assertRedirect();

    assertDatabaseHas('page_sections', ['section_key' => 'trust']);
});

it('renders the page section screen', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/page-sections')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('DOCTOR::PageSection/Index', false)->has('sections.data'));

    expect(PageSection::query()->count())->toBeGreaterThan(0);
});

it('renders the expertises screen', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/expertises')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('DOCTOR::DoctorExpertise/Index', false)->has('expertises.data'));
});

/* ------------------------------------------------------------------ */
/*  Guards                                                             */
/* ------------------------------------------------------------------ */

it('denies a role that lacks the matching permission', function (): void {
    $viewer = User::factory()->create(['email' => 'viewer@amzhospital.test']);
    $viewer->assignRole('viewer');

    actingAs($viewer)->get('/admin/doctors')->assertOk();
    actingAs($viewer)->post('/admin/doctors', ['name' => 'Blocked'])->assertForbidden();

    assertDatabaseMissing('doctors', ['name' => 'Blocked']);
});

/* ------------------------------------------------------------------ */
/*  Remaining write paths                                              */
/* ------------------------------------------------------------------ */

it('toggles a doctor featured flag', function (): void {
    $doctor = Doctor::query()->firstOrFail();
    $before = (bool) $doctor->is_featured;

    actingAs($this->admin)->patch("/admin/doctors/{$doctor->id}/featured")->assertRedirect();

    expect((bool) $doctor->refresh()->is_featured)->toBe(! $before);
});

it('updates a department', function (): void {
    $department = Department::query()->firstOrFail();
    $category = DepartmentCategory::query()->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/doctors/departments/{$department->id}", [
            'title' => 'Interventional Cardiology',
            'department_category_id' => $category->id,
            'short_description' => 'Catheter-led procedures.',
            'is_active' => true,
        ])
        ->assertRedirect();

    $department->refresh();

    expect($department->title)->toBe('Interventional Cardiology')
        ->and($department->department_category_id)->toBe($category->id);
});

it('deletes a department', function (): void {
    $category = DepartmentCategory::query()->firstOrFail();

    actingAs($this->admin)->post('/admin/doctors/departments', [
        'title' => 'Short-lived Unit',
        'department_category_id' => $category->id,
    ]);

    $department = Department::query()->where('title', 'Short-lived Unit')->firstOrFail();

    actingAs($this->admin)
        ->delete("/admin/doctors/departments/{$department->id}")
        ->assertRedirect();

    assertDatabaseMissing('departments', ['id' => $department->id]);
});

it('stores, updates and deletes a doctor expertise', function (): void {
    $doctor = Doctor::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors/expertises', [
            'doctor_id' => $doctor->id,
            'title' => 'Paediatric echo',
            'description' => 'Echo in children.',
            'icon' => 'Activity',
            'sort_order' => 3,
        ])
        ->assertRedirect();

    $expertise = DoctorExpertise::query()->where('title', 'Paediatric echo')->firstOrFail();

    expect($expertise->doctor_id)->toBe($doctor->id);

    actingAs($this->admin)
        ->put("/admin/doctors/expertises/{$expertise->id}", [
            'doctor_id' => $doctor->id,
            'title' => 'Adult echo',
            'description' => 'Echo in adults.',
            'icon' => 'Activity',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect($expertise->refresh()->title)->toBe('Adult echo');

    actingAs($this->admin)
        ->delete("/admin/doctors/expertises/{$expertise->id}")
        ->assertRedirect();

    assertDatabaseMissing('doctor_expertises', ['id' => $expertise->id]);
});

it('requires a doctor and a title for an expertise', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/expertises', ['title' => 'No doctor'])
        ->assertSessionHasErrors('doctor_id');

    $doctor = Doctor::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors/expertises', ['doctor_id' => $doctor->id])
        ->assertSessionHasErrors('title');
});

it('updates and deletes a schedule', function (): void {
    $doctor = Doctor::query()->firstOrFail();

    actingAs($this->admin)->post('/admin/doctors/schedules', [
        'doctor_id' => $doctor->id,
        'day_of_week' => 'Thu',
        'start_time' => '09:00',
        'end_time' => '13:00',
        'consultation_type' => 'both',
        'availability_status' => 'limited',
        'max_patients' => 12,
        'is_active' => true,
    ]);

    $schedule = DoctorSchedule::query()
        ->where('doctor_id', $doctor->id)
        ->where('day_of_week', 'Thu')
        ->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/doctors/schedules/{$schedule->id}", [
            'doctor_id' => $doctor->id,
            'day_of_week' => 'Thu',
            'start_time' => '10:30',
            'end_time' => '15:30',
            'consultation_type' => 'online',
            'availability_status' => 'available',
            'max_patients' => 25,
            'is_active' => false,
        ])
        ->assertRedirect();

    $schedule->refresh();

    expect($schedule->start_time)->toBe('10:30')
        ->and($schedule->consultation_type)->toBe('online')
        ->and($schedule->is_active)->toBeFalse();

    actingAs($this->admin)
        ->delete("/admin/doctors/schedules/{$schedule->id}")
        ->assertRedirect();

    assertDatabaseMissing('doctor_schedules', ['id' => $schedule->id]);
});

it('renders the symptom screen', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/symptoms')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('DOCTOR::Symptom/Index', false)
                ->has('symptoms.data'),
        );
});

it('updates and deletes a page section', function (): void {
    $section = PageSection::query()->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/doctors/page-sections/{$section->id}", [
            'section_key' => $section->section_key,
            'badge' => 'Updated badge',
            'title' => 'Updated headline',
            'subtitle' => 'Updated supporting copy',
            'primary_button_text' => 'Book now',
            'primary_button_url' => '/appointments',
            'is_active' => true,
        ])
        ->assertRedirect();

    $section->refresh();

    expect($section->title)->toBe('Updated headline')
        ->and($section->badge)->toBe('Updated badge');

    actingAs($this->admin)
        ->delete("/admin/doctors/page-sections/{$section->id}")
        ->assertRedirect();

    assertDatabaseMissing('page_sections', ['id' => $section->id]);
});

/* ------------------------------------------------------------------ */
/*  Filter validation                                                  */
/* ------------------------------------------------------------------ */

it('filters symptoms by active state', function (): void {
    Symptom::query()->create(['title' => 'Visible symptom', 'icon' => 'Wind', 'is_active' => true]);
    Symptom::query()->create(['title' => 'Hidden symptom', 'icon' => 'Wind', 'is_active' => false]);

    $active = actingAs($this->admin)
        ->get('/admin/doctors/symptoms?filters[is_active][]=1')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('symptoms.data'));

    expect(collect($active->viewData('page')['props']['symptoms']['data'])->pluck('title'))
        ->toContain('Visible symptom')
        ->not->toContain('Hidden symptom');

    $hidden = actingAs($this->admin)->get('/admin/doctors/symptoms?filters[is_active][]=0');

    expect(collect($hidden->viewData('page')['props']['symptoms']['data'])->pluck('title'))
        ->toContain('Hidden symptom')
        ->not->toContain('Visible symptom');
});

it('rejects a malformed filter value', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/symptoms?filters[is_active][]=not-a-boolean')
        ->assertSessionHasErrors('filters.is_active.0');
});

/* ------------------------------------------------------------------ */
/*  Department create / edit pages                                     */
/* ------------------------------------------------------------------ */

it('renders the department create page with its options', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/departments/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('DOCTOR::Department/Form', false)
            ->where('department', null)
            ->has('categoryOptions')
            ->has('symptomOptions')
            ->has('sectionKeyOptions'));
});

it('renders the department edit page with its page sections, symptoms and seo', function (): void {
    $department = Department::query()->firstOrFail();

    $symptom = Symptom::query()->where('is_active', true)->firstOrFail();

    $department->symptoms()->sync([$symptom->id => ['sort_order' => 0]]);
    $department->pageSections()->create([
        'section_key' => 'hero',
        'title' => 'Hero',
        'is_active' => true,
    ]);

    actingAs($this->admin)
        ->get("/admin/doctors/departments/{$department->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('DOCTOR::Department/Form', false)
            ->where('department.id', $department->id)
            ->where('department.symptoms', [$symptom->id])
            ->has('department.page_sections', 1)
            ->has('department.seo.meta_title'));
});

it('stores a department with its page sections, symptoms and seo', function (): void {
    $symptom = Symptom::query()->where('is_active', true)->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors/departments', [
            'title' => 'Interventional Cardiology',
            'sort_order' => 3,
            'is_active' => true,
            'page_sections' => [
                [
                    'section_key' => 'hero',
                    'title' => 'Heart care',
                    'subtitle' => 'Catheter-led procedures.',
                    'is_active' => true,
                ],
                [
                    'section_key' => 'bottom_cta',
                    'title' => 'Book now',
                    'is_active' => true,
                ],
            ],
            'symptoms' => [$symptom->id],
            'seo' => [
                'meta_title' => 'Cardiology department',
                'robots' => 'index, follow',
            ],
        ])
        ->assertRedirect();

    $department = Department::query()->where('title', 'Interventional Cardiology')->firstOrFail();

    expect($department->pageSections()->pluck('section_key')->sort()->values()->all())
        ->toBe(['bottom_cta', 'hero'])
        ->and($department->pageSections()->first()->department_id)->toBe($department->id)
        ->and($department->symptoms()->pluck('symptoms.id')->all())->toBe([$symptom->id])
        ->and($department->seo)->not->toBeNull()
        ->and($department->seo->meta_title)->toBe('Cardiology department');
});

it('replaces page sections and symptoms when a department is updated', function (): void {
    $department = Department::query()->firstOrFail();

    $first = Symptom::query()->where('is_active', true)->firstOrFail();
    $second = Symptom::query()->where('is_active', true)->skip(1)->firstOrFail();

    $department->pageSections()->create(['section_key' => 'hero', 'title' => 'Hero', 'is_active' => true]);
    $department->symptoms()->sync([$first->id => ['sort_order' => 0]]);

    actingAs($this->admin)
        ->put("/admin/doctors/departments/{$department->id}", [
            'title' => $department->title,
            'page_sections' => [
                ['section_key' => 'urgent_care', 'title' => 'Urgent care', 'is_active' => true],
            ],
            'symptoms' => [$second->id],
            'seo' => ['meta_title' => 'Updated title'],
        ])
        ->assertRedirect();

    $department->refresh();

    expect($department->pageSections()->pluck('section_key')->all())->toBe(['urgent_care'])
        ->and($department->symptoms()->pluck('symptoms.id')->all())->toBe([$second->id])
        ->and($department->seo->meta_title)->toBe('Updated title');
});

it('validates the page sections of a department', function (): void {
    actingAs($this->admin)
        ->post('/admin/doctors/departments', [
            'title' => 'Broken department',
            'page_sections' => [
                ['title' => 'No key provided'],
            ],
            'symptoms' => [999999],
        ])
        ->assertSessionHasErrors(['page_sections.0.section_key', 'symptoms.0']);
});

it('allows the same section key on two different departments', function (): void {
    $payload = static fn (string $title): array => [
        'title' => $title,
        'page_sections' => [['section_key' => 'hero', 'title' => 'Hero', 'is_active' => true]],
    ];

    actingAs($this->admin)->post('/admin/doctors/departments', $payload('Alpha unit'))->assertRedirect();
    actingAs($this->admin)->post('/admin/doctors/departments', $payload('Beta unit'))->assertRedirect();

    expect(PageSection::query()->where('section_key', 'hero')->count())->toBeGreaterThanOrEqual(2);
});

it('cascades department deletion to its page sections and symptom links', function (): void {
    // Every seeded department has doctors, and those block deletion.
    $department = Department::query()->create([
        'title' => 'Doomed department',
        'is_active' => true,
    ]);

    $symptom = Symptom::query()->where('is_active', true)->firstOrFail();

    $department->pageSections()->create(['section_key' => 'hero', 'title' => 'Hero', 'is_active' => true]);
    $department->symptoms()->sync([$symptom->id => ['sort_order' => 0]]);

    $sectionId = $department->pageSections()->firstOrFail()->id;

    actingAs($this->admin)->delete("/admin/doctors/departments/{$department->id}")->assertRedirect();

    assertDatabaseMissing('page_sections', ['id' => $sectionId]);
    assertDatabaseMissing('department_symptom', ['department_id' => $department->id]);
});

/* ------------------------------------------------------------------ */
/*  Doctor create / edit pages                                         */
/* ------------------------------------------------------------------ */

it('renders the doctor create page with its options', function (): void {
    actingAs($this->admin)
        ->get('/admin/doctors/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('DOCTOR::Doctor/Form', false)
            ->where('doctor', null)
            ->has('departmentOptions')
            ->has('dayOptions')
            ->has('consultationTypeOptions')
            ->has('availabilityOptions'));
});

it('renders the doctor edit page with its timetable, expertises and seo', function (): void {
    $doctor = Doctor::query()->has('schedules')->firstOrFail();

    actingAs($this->admin)
        ->get("/admin/doctors/{$doctor->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('DOCTOR::Doctor/Form', false)
            ->where('doctor.id', $doctor->id)
            ->has('doctor.schedules')
            ->has('doctor.expertises')
            ->has('doctor.seo.meta_title'));
});

it('stores a doctor with a timetable and seo metadata', function (): void {
    $department = Department::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors', [
            'department_id' => $department->id,
            'name' => 'Dr. Timetable Test',
            'specialty' => 'Neurology',
            'is_active' => true,
            'schedules' => [
                [
                    'day_of_week' => 'Sat',
                    'start_time' => '09:00',
                    'end_time' => '13:00',
                    'consultation_type' => 'both',
                    'availability_status' => 'available',
                    'max_patients' => 15,
                    'is_active' => true,
                ],
                [
                    'day_of_week' => 'Mon',
                    'start_time' => '14:00',
                    'end_time' => '18:00',
                    'consultation_type' => 'in_person',
                    'availability_status' => 'limited',
                    'max_patients' => 10,
                    'is_active' => true,
                ],
            ],
            'seo' => [
                'meta_title' => 'Dr. Timetable Test — Neurology',
                'og_type' => 'profile',
            ],
        ])
        ->assertRedirect();

    $doctor = Doctor::query()->where('name', 'Dr. Timetable Test')->firstOrFail();

    expect($doctor->schedules()->count())->toBe(2)
        ->and($doctor->schedules()->pluck('day_of_week')->sort()->values()->all())->toBe(['Mon', 'Sat'])
        ->and($doctor->schedules()->where('day_of_week', 'Sat')->first()->max_patients)->toBe(15)
        ->and($doctor->seo)->not->toBeNull()
        ->and($doctor->seo->meta_title)->toBe('Dr. Timetable Test — Neurology');
});

it('replaces the timetable when a doctor is updated', function (): void {
    $doctor = Doctor::query()->has('schedules')->firstOrFail();

    $before = $doctor->schedules()->count();

    actingAs($this->admin)
        ->put("/admin/doctors/{$doctor->id}", [
            'department_id' => $doctor->department_id,
            'name' => $doctor->name,
            'specialty' => $doctor->specialty,
            'schedules' => [
                [
                    'day_of_week' => 'Wed',
                    'start_time' => '10:00',
                    'end_time' => '12:00',
                    'consultation_type' => 'online',
                    'availability_status' => 'available',
                    'max_patients' => 8,
                    'is_active' => true,
                ],
            ],
            'seo' => ['meta_title' => 'Replaced schedule'],
        ])
        ->assertRedirect();

    $doctor->refresh();

    expect($doctor->schedules()->count())->toBe(1)
        ->and($before)->toBeGreaterThan(1)
        ->and($doctor->schedules()->first()->day_of_week)->toBe('Wed')
        ->and($doctor->seo->meta_title)->toBe('Replaced schedule');
});

it('validates the timetable rows of a doctor', function (): void {
    $department = Department::query()->firstOrFail();

    actingAs($this->admin)
        ->post('/admin/doctors', [
            'department_id' => $department->id,
            'name' => 'Dr. Bad Schedule',
            'specialty' => 'Neurology',
            'schedules' => [
                [
                    'day_of_week' => 'NotADay',
                    'start_time' => '18:00',
                    'end_time' => '09:00',
                    'consultation_type' => 'telepathy',
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'schedules.0.day_of_week',
            'schedules.0.end_time',
            'schedules.0.consultation_type',
        ]);
});
