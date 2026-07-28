<template>
  <PublicLayout>
    <Head title="Profil Instansi - Dinas Dukcapil Kabupaten Dompu" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <template v-for="sec in sections" :key="sec.id">
        <component
          v-if="sec.is_enabled && sectionComponents[sec.key]"
          :is="sectionComponents[sec.key]"
          :setting="sec.setting"
          :orgTree="orgTree"
          :officials="officials"
          :recentNews="recentNews"
        />
      </template>
    </div>
  </PublicLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import HeroSection from '@/Components/Profile/Sections/HeroSection.vue'
import AboutSection from '@/Components/Profile/Sections/AboutSection.vue'
import SpeechSection from '@/Components/Profile/Sections/SpeechSection.vue'
import VisionMissionSection from '@/Components/Profile/Sections/VisionMissionSection.vue'
import DutiesSection from '@/Components/Profile/Sections/DutiesSection.vue'
import OrgChartSection from '@/Components/Profile/Sections/OrgChartSection.vue'
import OfficialsSection from '@/Components/Profile/Sections/OfficialsSection.vue'
import AchievementsSection from '@/Components/Profile/Sections/AchievementsSection.vue'
import TimelineSection from '@/Components/Profile/Sections/TimelineSection.vue'
import GallerySection from '@/Components/Profile/Sections/GallerySection.vue'
import MaklumatSection from '@/Components/Profile/Sections/MaklumatSection.vue'
import ContactSection from '@/Components/Profile/Sections/ContactSection.vue'

defineProps({
  sections: Array,
  orgTree: Array,
  officials: Object,
  recentNews: Array,
})

const sectionComponents = {
  hero: HeroSection,
  about: AboutSection,
  speech: SpeechSection,
  vision_mission: VisionMissionSection,
  duties: DutiesSection,
  org_chart: OrgChartSection,
  officials: OfficialsSection,
  achievements: AchievementsSection,
  timeline: TimelineSection,
  gallery: GallerySection,
  maklumat: MaklumatSection,
  contact: ContactSection,
}
</script>
