const html = document.documentElement;
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const motionAllowed = () => !prefersReducedMotion.matches;

// Keep the skeleton on screen just long enough to read as an intentional preview,
// then remove it from the document so it can never block interaction.
const pageSkeleton = document.querySelector('#page-skeleton');
const loadingStartedAt = performance.now();
let pageReady = false;

const finishPageLoading = () => {
  if (pageReady) return;
  pageReady = true;

  const minimumPreviewTime = motionAllowed() ? 520 : 0;
  const elapsed = performance.now() - loadingStartedAt;

  window.setTimeout(() => {
    html.classList.add('page-ready');
    if (!pageSkeleton) return;

    pageSkeleton.classList.add('is-leaving');
    window.setTimeout(() => pageSkeleton.remove(), motionAllowed() ? 460 : 10);
  }, Math.max(0, minimumPreviewTime - elapsed));
};

if (document.readyState === 'complete') {
  finishPageLoading();
} else {
  window.addEventListener('load', finishPageLoading, { once: true });
}

// Failsafe for an unusually slow third-party font or image request.
window.setTimeout(finishPageLoading, 3600);

const menuToggle = document.querySelector('#menu-toggle');
const mobileMenu = document.querySelector('#mobile-menu');

if (menuToggle && mobileMenu) {
  const setMenuState = (isOpen) => {
    mobileMenu.classList.toggle('hidden', !isOpen);
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
  };

  menuToggle.addEventListener('click', () => {
    setMenuState(menuToggle.getAttribute('aria-expanded') !== 'true');
  });

  mobileMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenuState(false));
  });
}

const departmentGrid = document.querySelector('#department-grid');
const filterButtons = [...document.querySelectorAll('[data-department-filter]')];
const departmentCards = [...document.querySelectorAll('.department-card')];
let departmentAnimationTimer;

const animateDepartmentGrid = () => {
  if (!departmentGrid || !motionAllowed()) return;

  departmentGrid.classList.remove('is-filtering');
  // Restart the CSS animation without changing the resting state.
  void departmentGrid.offsetWidth;
  departmentGrid.classList.add('is-filtering');

  window.clearTimeout(departmentAnimationTimer);
  departmentAnimationTimer = window.setTimeout(() => {
    departmentGrid.classList.remove('is-filtering');
  }, 650);
};

const applyDepartmentFilter = (selectedFilter, shouldAnimate = true) => {
  filterButtons.forEach((filterButton) => {
    const isSelected = filterButton.dataset.departmentFilter === selectedFilter;
    filterButton.dataset.active = String(isSelected);
    filterButton.setAttribute('aria-pressed', String(isSelected));
  });

  departmentCards.forEach((card) => {
    const isVisible = selectedFilter === 'all' || card.dataset.category === selectedFilter;
    card.dataset.filterHidden = String(!isVisible);
  });

  if (shouldAnimate) animateDepartmentGrid();
};

filterButtons.forEach((button) => {
  button.addEventListener('click', () => {
    applyDepartmentFilter(button.dataset.departmentFilter);
  });
});

const packageTabs = [...document.querySelectorAll('[data-package-period]')];
const packageCarousel = document.querySelector('#package-carousel');
let packageAnimationTimer;

const animatePackageCards = () => {
  if (!packageCarousel || !motionAllowed()) return;

  packageCarousel.classList.remove('is-updating');
  void packageCarousel.offsetWidth;
  packageCarousel.classList.add('is-updating');

  window.clearTimeout(packageAnimationTimer);
  packageAnimationTimer = window.setTimeout(() => {
    packageCarousel.classList.remove('is-updating');
  }, 650);
};

packageTabs.forEach((tab) => {
  tab.addEventListener('click', () => {
    packageTabs.forEach((packageTab) => {
      const isSelected = packageTab === tab;
      packageTab.dataset.active = String(isSelected);
      packageTab.setAttribute('aria-selected', String(isSelected));
    });

    animatePackageCards();
  });
});

const focusableSelector = 'a, button, input, select, textarea, [tabindex]';

// The desktop Figma layouts show a complete first set of cards. We append inert
// copies after it so arrow controls can continue sliding without changing that first view.
const cloneCarouselSlides = (carousel) => {
  if (!carousel || !carousel.hasAttribute('data-clone-slides') || carousel.dataset.clonesReady === 'true') return;

  const originals = [...carousel.children].filter((item) => !item.dataset.carouselClone);
  if (originals.length < 2) return;

  const fragment = document.createDocumentFragment();
  originals.forEach((item) => {
    const clone = item.cloneNode(true);
    clone.dataset.carouselClone = 'true';
    clone.setAttribute('aria-hidden', 'true');
    clone.querySelectorAll(focusableSelector).forEach((element) => element.setAttribute('tabindex', '-1'));
    fragment.append(clone);
  });

  carousel.append(fragment);
  carousel.dataset.clonesReady = 'true';
};

document.querySelectorAll('[data-clone-slides]').forEach(cloneCarouselSlides);

const getCarouselGap = (carousel) => {
  const styles = window.getComputedStyle(carousel);
  const rawGap = styles.columnGap !== 'normal' ? styles.columnGap : styles.gap;
  const gap = Number.parseFloat(rawGap);
  return Number.isFinite(gap) ? gap : 0;
};

const getCarouselStep = (carousel) => {
  if (carousel.id === 'testimonial-carousel') {
    return window.matchMedia('(min-width: 768px)').matches ? carousel.clientWidth / 2 : carousel.clientWidth;
  }

  const firstSlide = [...carousel.children].find((item) => !item.dataset.carouselClone) || carousel.firstElementChild;
  if (!firstSlide) return carousel.clientWidth;

  return Math.max(180, firstSlide.getBoundingClientRect().width + getCarouselGap(carousel));
};

const moveCarousel = (targetId, direction) => {
  const carousel = document.getElementById(targetId);
  if (!carousel) return;

  const maxScroll = Math.max(0, carousel.scrollWidth - carousel.clientWidth);
  if (maxScroll < 2) return;

  const currentScroll = carousel.scrollLeft;
  const step = getCarouselStep(carousel);
  const behavior = motionAllowed() ? 'smooth' : 'auto';

  if (carousel.hasAttribute('data-loop-carousel') && direction === 'right' && currentScroll >= maxScroll - 3) {
    carousel.scrollTo({ left: 0, behavior });
    return;
  }

  if (carousel.hasAttribute('data-loop-carousel') && direction === 'left' && currentScroll <= 3) {
    carousel.scrollTo({ left: maxScroll, behavior });
    return;
  }

  carousel.scrollBy({
    left: direction === 'left' ? -step : step,
    behavior,
  });
};

const cycleDepartmentFilter = (direction) => {
  if (!filterButtons.length) return;

  const activeIndex = Math.max(0, filterButtons.findIndex((button) => button.dataset.active === 'true'));
  const offset = direction === 'previous' ? -1 : 1;
  const nextIndex = (activeIndex + offset + filterButtons.length) % filterButtons.length;
  applyDepartmentFilter(filterButtons[nextIndex].dataset.departmentFilter);
};

document.querySelectorAll('[data-scroll-target]').forEach((control) => {
  control.addEventListener('click', () => {
    if (control.dataset.departmentCycle) {
      cycleDepartmentFilter(control.dataset.departmentCycle);
      return;
    }

    moveCarousel(control.dataset.scrollTarget, control.dataset.scrollDirection);
  });
});

const heroCarousel = document.querySelector('[data-hero-carousel]');

if (heroCarousel) {
  const heroSlides = [...heroCarousel.querySelectorAll('[data-hero-slide]')];
  const heroContent = heroCarousel.querySelector('.hero-content');
  const heroImageUrls = ['assets/images/hero-care.jpg', 'assets/images/hero-services.jpg'];
  let activeHeroSlide = Math.max(0, heroSlides.findIndex((slide) => slide.classList.contains('is-active')));
  let heroTimer;

  // Warm the next backgrounds before their first fade-in.
  heroImageUrls.forEach((source) => {
    const image = new Image();
    image.src = source;
  });

  const showHeroSlide = (nextIndex) => {
    if (heroSlides.length < 2 || nextIndex === activeHeroSlide) return;

    heroSlides[activeHeroSlide].classList.remove('is-active');
    heroSlides[nextIndex].classList.add('is-active');
    activeHeroSlide = nextIndex;

    if (heroContent && motionAllowed()) {
      heroContent.classList.remove('is-animating');
      void heroContent.offsetWidth;
      heroContent.classList.add('is-animating');
    }
  };

  const advanceHeroSlide = () => showHeroSlide((activeHeroSlide + 1) % heroSlides.length);
  const pauseHeroAutoplay = () => window.clearInterval(heroTimer);
  const startHeroAutoplay = () => {
    pauseHeroAutoplay();
    if (!motionAllowed() || document.hidden) return;
    heroTimer = window.setInterval(advanceHeroSlide, 7000);
  };

  if (heroSlides.length > 1) {
    startHeroAutoplay();
    heroCarousel.addEventListener('mouseenter', pauseHeroAutoplay);
    heroCarousel.addEventListener('mouseleave', startHeroAutoplay);
    heroCarousel.addEventListener('focusin', pauseHeroAutoplay);
    heroCarousel.addEventListener('focusout', (event) => {
      if (!heroCarousel.contains(event.relatedTarget)) startHeroAutoplay();
    });
    document.addEventListener('visibilitychange', () => (document.hidden ? pauseHeroAutoplay() : startHeroAutoplay()));
    prefersReducedMotion.addEventListener?.('change', startHeroAutoplay);
  }
}

// Keep fragment navigation visibly tied to the section currently in view.
// Route links are normal page navigations and must not be passed to querySelector().
const navigationLinks = [...document.querySelectorAll('.nav-link')]
  .filter((link) => link.getAttribute('href')?.startsWith('#'));
const observedSections = navigationLinks
  .map((link) => document.querySelector(link.getAttribute('href')))
  .filter(Boolean);

if ('IntersectionObserver' in window && observedSections.length) {
  const observer = new IntersectionObserver(
    (entries) => {
      const activeEntry = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

      if (!activeEntry) return;

      navigationLinks.forEach((link) => {
        link.dataset.current = String(link.getAttribute('href') === `#${activeEntry.target.id}`);
      });
    },
    { rootMargin: '-20% 0px -65% 0px', threshold: [0.05, 0.25, 0.5] },
  );

  observedSections.forEach((section) => observer.observe(section));
}
