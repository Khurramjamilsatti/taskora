<script setup>
import AppNav from './AppNav.vue'
import PromoStrip from './PromoStrip.vue'
import HeroSection from './HeroSection.vue'
import StatsBar from './StatsBar.vue'
import CompanySection from './CompanySection.vue'
import FlowSection from './FlowSection.vue'
import ServicesSection from './ServicesSection.vue'
import GridSection from './GridSection.vue'
import CalculatorSection from './CalculatorSection.vue'
import DashboardSection from './DashboardSection.vue'
import ReviewsSection from './ReviewsSection.vue'
import CareerSection from './CareerSection.vue'
import SolutionsSection from './SolutionsSection.vue'
import TagBand from './TagBand.vue'
import TrustSection from './TrustSection.vue'
import BusinessModelSection from './BusinessModelSection.vue'
import ProfessionalSection from './ProfessionalSection.vue'
import VisionSection from './VisionSection.vue'
import CtaBanner from './CtaBanner.vue'
import AppFooter from './AppFooter.vue'
import { useSiteData } from '../composables/useSiteData'

const { siteData, loading, error } = useSiteData()
</script>

<template>
  <div v-if="loading" class="loading-state">Loading Taskora…</div>
  <div v-else-if="error" class="loading-state error">Failed to load site data. Is the Laravel API running?</div>
  <template v-else-if="siteData">
    <AppNav :nav="siteData.nav" />
    <PromoStrip :text="siteData.promo.text" />
    <HeroSection :hero="siteData.hero" />
    <StatsBar :stats="siteData.stats_bar" />
    <CompanySection :section="siteData.company" />
    <FlowSection :section="siteData.flow" />
    <ServicesSection :section="siteData.services" />
    <GridSection :section="siteData.knowledge" type="GUIDE" />
    <CalculatorSection :section="siteData.calculator" />
    <GridSection :section="siteData.tools" type="TOOL" />
    <DashboardSection :section="siteData.dashboard" />
    <ReviewsSection :section="siteData.reviews" />
    <CareerSection :section="siteData.career" />
    <SolutionsSection :section="siteData.solutions" />
    <TagBand :section="siteData.marketplace" />
    <TrustSection :section="siteData.trust" />
    <TagBand :section="siteData.customers" />
    <BusinessModelSection :section="siteData.business_model" />
    <ProfessionalSection :section="siteData.professional" />
    <VisionSection :section="siteData.vision" />
    <CtaBanner :section="siteData.cta" />
    <AppFooter :footer="siteData.footer" />
  </template>
</template>

<style scoped>
.loading-state {
  min-height: 100vh;
  display: grid;
  place-items: center;
  font-family: 'IBM Plex Mono', monospace;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--green);
}

.loading-state.error {
  color: #b42318;
  padding: 2rem;
  text-align: center;
}
</style>
