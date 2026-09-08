<script setup>
defineProps({ name: { type: String, required: true } })
</script>

<template>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <template v-if="name === 'calendar'">
      <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" />
    </template>
    <template v-else-if="name === 'calendar-add'">
      <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18M12 13v5M9.5 15.5h5" />
    </template>
    <template v-else-if="name === 'check'">
      <path d="m5 12 4 4L19 6" />
    </template>
    <template v-else-if="name === 'check-circle'">
      <circle cx="12" cy="12" r="9" /><path d="m8 12 2.7 2.7L16.5 9" />
    </template>
    <template v-else-if="name === 'heart-pulse'">
      <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21.2l7.8-7.8a5.5 5.5 0 0 0 1-8.8Z" /><path d="M4.5 12h4l1.4-3.2 3.1 7 1.7-3.8h4.8" />
    </template>
    <template v-else-if="name === 'brain'">
      <path d="M9.5 4.5A3 3 0 0 0 4 6.2 3.5 3.5 0 0 0 3 12a3.5 3.5 0 0 0 2 5.9A3 3 0 0 0 10 20V4.5ZM14.5 4.5A3 3 0 0 1 20 6.2a3.5 3.5 0 0 1 1 5.8 3.5 3.5 0 0 1-2 5.9A3 3 0 0 1 14 20V4.5Z" /><path d="M7 8.5c1.7 0 3 1.3 3 3M17 8.5c-1.7 0-3 1.3-3 3M7 15c1.7 0 3-1.3 3-3M17 15c-1.7 0-3-1.3-3-3" />
    </template>
    <template v-else-if="name === 'bone'">
      <path d="M17.5 3a2.5 2.5 0 0 0-2.4 3.2L6.2 15A2.5 2.5 0 1 0 4 19.5 2.5 2.5 0 0 0 8.8 18l8.9-8.9A2.5 2.5 0 1 0 17.5 3Z" />
    </template>
    <template v-else-if="name === 'baby'">
      <circle cx="12" cy="13" r="7" /><path d="M9 10h.01M15 10h.01M9.5 15c1.5 1.3 3.5 1.3 5 0M12 6c0-2 1.2-3 3-3M5 13H3M21 13h-2" />
    </template>
    <template v-else-if="name === 'stethoscope'">
      <path d="M6 3v6a5 5 0 0 0 10 0V3M4 3h4M14 3h4M11 16v1a4 4 0 0 0 8 0v-3" /><circle cx="19" cy="12" r="2" />
    </template>
    <template v-else-if="name === 'sparkles'">
      <path d="m12 3 1.4 4.2L18 9l-4.6 1.8L12 15l-1.4-4.2L6 9l4.6-1.8L12 3ZM5 15l.8 2.2L8 18l-2.2.8L5 21l-.8-2.2L2 18l2.2-.8L5 15ZM19 14l.7 1.8 1.8.7-1.8.7L19 19l-.7-1.8-1.8-.7 1.8-.7L19 14Z" />
    </template>
    <template v-else-if="name === 'shield'">
      <path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z" /><path d="m9 12 2 2 4-4" />
    </template>
    <template v-else-if="name === 'bolt'">
      <path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z" />
    </template>
    <template v-else-if="name === 'users'">
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8" />
    </template>
    <template v-else-if="name === 'phone'">
      <path d="M21 16.8v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.2 5.2 2 2 0 0 1 4.2 3h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 3a2 2 0 0 1-.4 2.1l-1.3 1.3a16 16 0 0 0 4.8 4.8l1.3-1.3a2 2 0 0 1 2.1-.4c1 .4 2 .6 3 .7a2 2 0 0 1 1.6 2Z" />
    </template>
    <template v-else-if="name === 'search'">
      <circle cx="11" cy="11" r="7" /><path d="m20 20-4-4" />
    </template>
    <template v-else-if="name === 'filter'">
      <path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z" />
    </template>
    <template v-else-if="name === 'headphones'">
      <path d="M4 14v-2a8 8 0 0 1 16 0v2M18 19h1a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-1v6ZM6 19H5a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h1v6Z" />
    </template>
    <template v-else-if="name === 'chevron-left'"><path d="m15 18-6-6 6-6" /></template>
    <template v-else-if="name === 'chevron-right'"><path d="m9 18 6-6-6-6" /></template>
    <template v-else-if="name === 'chevron-down'"><path d="m6 9 6 6 6-6" /></template>
    <template v-else-if="name === 'arrow-right'"><path d="M5 12h14M14 7l5 5-5 5" /></template>
    <template v-else-if="name === 'heart'"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21.2l7.8-7.8a5.5 5.5 0 0 0 1-8.8Z" /></template>
    <template v-else-if="name === 'clock'"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></template>
    <template v-else-if="name === 'upload'"><path d="M12 16V4M7 9l5-5 5 5M5 15v4h14v-4" /></template>
    <template v-else-if="name === 'user'"><circle cx="12" cy="8" r="4" /><path d="M4 21c.8-4.4 3.5-7 8-7s7.2 2.6 8 7" /></template>
    <template v-else-if="name === 'mail'"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></template>
    <template v-else-if="name === 'hash'"><path d="M5 9h14M4 15h14M10 3 8 21M16 3l-2 18" /></template>
    <template v-else-if="name === 'download'"><path d="M12 3v12M7 10l5 5 5-5M5 21h14" /></template>
    <template v-else-if="name === 'message'"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z" /></template>
    <template v-else-if="name === 'radio'"><circle cx="12" cy="12" r="8" /><circle cx="12" cy="12" r="3" /></template>
    <template v-else-if="name === 'info'"><circle cx="12" cy="12" r="9" /><path d="M12 11v5M12 8h.01" /></template>
    <template v-else-if="name === 'map-pin'"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" /><circle cx="12" cy="10" r="2.5" /></template>
    <template v-else-if="name === 'arrow-up-right'"><path d="M7 17 17 7M8 7h9v9" /></template>
    <template v-else-if="name === 'ear'"><path d="M6 10a6 6 0 1 1 10.7 3.7c-1.5 1.7-1.8 2.2-2.2 3.8a3.2 3.2 0 0 1-6.2-.8M9 10a3 3 0 1 1 5 2.2c-1.1 1-1.5 1.6-1.7 2.8" /></template>
    <template v-else-if="name === 'drops'"><path d="M8 3S4 8 4 11a4 4 0 0 0 8 0C12 8 8 3 8 3ZM17 8s-3 4-3 6.5a3.5 3.5 0 0 0 7 0C21 12 17 8 17 8Z" /></template>
    <template v-else-if="name === 'hand-heart'"><path d="M4 14h3l3 3h5l5-5a2 2 0 0 0-3-2l-3 2M7 14l4-4a2 2 0 0 1 3 0l1 1" /><path d="M12 6.5 10.5 5a2.2 2.2 0 0 1 3-3L15 3.5 16.5 2a2.2 2.2 0 0 1 3 3L15 9l-3-2.5Z" /></template>
    <template v-else-if="name === 'flower'"><circle cx="12" cy="12" r="2" /><path d="M12 10c-4-1-4-6 0-7 4 1 4 6 0 7ZM14 12c1-4 6-4 7 0-1 4-6 4-7 0ZM12 14c4 1 4 6 0 7-4-1-4-6 0-7ZM10 12c-1 4-6 4-7 0 1-4 6-4 7 0Z" /></template>
    <template v-else-if="name === 'activity'"><path d="M3 12h4l2-6 4 12 2-6h6" /></template>
    <template v-else-if="name === 'refresh'"><path d="M20 7v5h-5M4 17v-5h5" /><path d="M6.1 8a7 7 0 0 1 11.4-2.2L20 8M4 16l2.5 2.2A7 7 0 0 0 17.9 16" /></template>
    <template v-else-if="name === 'life-buoy'"><circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="3" /><path d="m5.6 5.6 4.3 4.3M14.1 14.1l4.3 4.3M18.4 5.6l-4.3 4.3M9.9 14.1l-4.3 4.3" /></template>
    <template v-else-if="name === 'microscope'"><path d="m9 3 5 2-1 3-5-2 1-3ZM11 7l-2 5M6 21h12M5 17h7a5 5 0 0 0 5-5v-1M8 12a4 4 0 0 0 4 5" /></template>
    <template v-else-if="name === 'scan'"><path d="M8 3H4a1 1 0 0 0-1 1v4M16 3h4a1 1 0 0 1 1 1v4M8 21H4a1 1 0 0 1-1-1v-4M16 21h4a1 1 0 0 0 1-1v-4" /><circle cx="12" cy="12" r="3" /></template>
    <template v-else-if="name === 'flask'"><path d="M9 3h6M10 3v6l-5 9a2 2 0 0 0 2 3h10a2 2 0 0 0 2-3l-5-9V3M7.5 16h9" /></template>
    <template v-else-if="name === 'send'"><path d="m22 2-7 20-4-9-9-4 20-7Z" /><path d="M22 2 11 13" /></template>
    <template v-else-if="name === 'grid'"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><rect x="14" y="14" width="7" height="7" /></template>
    <template v-else-if="name === 'clipboard'"><rect x="5" y="4" width="14" height="17" rx="2" /><path d="M9 4a3 3 0 0 1 6 0v2H9V4ZM9 12h6M9 16h4" /></template>
    <template v-else-if="name === 'target'"><circle cx="12" cy="12" r="9" /><circle cx="12" cy="12" r="5" /><circle cx="12" cy="12" r="1.5" /></template>
    <template v-else-if="name === 'sliders'"><path d="M4 7h8M16 7h4M4 17h4M12 17h8" /><circle cx="14" cy="7" r="2" /><circle cx="10" cy="17" r="2" /></template>
    <template v-else-if="name === 'layers'"><path d="m12 3 9 5-9 5-9-5 9-5Z" /><path d="m3 12 9 5 9-5M3 16l9 5 9-5" /></template>
    <template v-else-if="name === 'image'"><rect x="3" y="4" width="18" height="16" rx="2" /><circle cx="9" cy="10" r="2" /><path d="m21 15-5-5L5 20" /></template>
    <template v-else-if="name === 'waves'"><path d="M3 8c2.2-2 4.2 2 6.4 0s4.2 2 6.4 0 4.2 2 5.2 0M3 12c2.2-2 4.2 2 6.4 0s4.2 2 6.4 0 4.2 2 5.2 0M3 16c2.2-2 4.2 2 6.4 0s4.2 2 6.4 0 4.2 2 5.2 0" /></template>
    <template v-else-if="name === 'menu'"><path d="M4 7h16M4 12h16M4 17h16" /></template>
    <template v-else-if="name === 'video'"><rect x="3" y="6" width="13" height="12" rx="2" /><path d="m16 10 5-3v10l-5-3" /></template>
    <template v-else-if="name === 'hospital'"><path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 9h3a1 1 0 0 1 1 1v11M2 21h20M8 7h4M10 5v4M8 12h2M12 12h2M8 16h2M12 16h2M8 21v-2h4v2" /></template>
    <template v-else-if="name === 'pill'"><path d="m10.5 20.5-7-7a4.2 4.2 0 0 1 0-6l4-4a4.2 4.2 0 0 1 6 0l7 7a4.2 4.2 0 0 1 0 6l-4 4a4.2 4.2 0 0 1-6 0ZM7 17l10-10" /></template>
    <template v-else-if="name === 'id-card'"><rect x="3" y="5" width="18" height="14" rx="2" /><circle cx="8" cy="11" r="2" /><path d="M5.5 16c.6-1.5 1.4-2 2.5-2s1.9.5 2.5 2M13 10h5M13 14h4" /></template>
    <template v-else-if="name === 'eye'"><path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12Z" /><circle cx="12" cy="12" r="2.8" /></template>
    <template v-else-if="name === 'book'"><path d="M3 5.5A2.5 2.5 0 0 1 5.5 3H11v16H5.5A2.5 2.5 0 0 0 3 21.5v-16ZM21 5.5A2.5 2.5 0 0 0 18.5 3H13v16h5.5a2.5 2.5 0 0 1 2.5 2.5v-16Z" /></template>
    <template v-else-if="name === 'minus'"><path d="M5 12h14" /></template>
    <template v-else-if="name === 'plus'"><path d="M12 5v14M5 12h14" /></template>
    <template v-else-if="name === 'navigation'"><path d="m21 3-8 18-2.2-7.8L3 11l18-8Z" /></template>
    <template v-else-if="name === 'file-check'"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z" /><path d="M14 3v5h5M9.5 14.5l1.8 1.8 3.4-3.6" /></template>
    <template v-else-if="name === 'clipboard-check'"><rect x="5" y="4" width="14" height="17" rx="2" /><path d="M9 4a3 3 0 0 1 6 0v2H9V4ZM9 13.5l2 2 4-4" /></template>
    <template v-else-if="name === 'arrow-down'"><path d="M12 5v14M7 14l5 5 5-5" /></template>
    <template v-else><circle cx="12" cy="12" r="9" /></template>
  </svg>
</template>