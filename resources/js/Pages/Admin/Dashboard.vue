<template>
  <AdminLayout>
    <Head title="Dashboard" />
    <div class="space-y-6">
      <!-- KPI Metrics -->
      <DashboardMetrics :stats="stats" />

      <!-- Quick Actions & Health/Categories -->
      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-5">
          <QuickActions />
        </div>
        <div class="col-span-12 lg:col-span-7">
          <SystemHealthCard :system-health="systemHealth" />
        </div>
      </div>

      <!-- Main Grid Layout -->
      <div class="grid grid-cols-12 gap-6">
        <!-- RFQ Trends -->
        <div class="col-span-12 lg:col-span-7">
          <TrendChart 
            title="RFQ Trend (Last 12 Months)"
            :labels="monthlyTrend.labels"
            :data="monthlyTrend.data"
            :color="monthlyTrend.color"
          />
        </div>

        <!-- Category Breakdown -->
        <div class="col-span-12 lg:col-span-5">
          <CategoryBreakdown :categories="categoryBreakdown" />
        </div>

        <!-- RFQ Status Breakdown -->
        <div class="col-span-12 lg:col-span-5">
          <RFQStatusChart 
            title="RFQ Status Breakdown"
            :labels="rfqStatusData.labels"
            :data="rfqStatusData.data"
            :colors="rfqStatusData.colors"
          />
        </div>

        <!-- Summary Stats -->
        <div class="col-span-12 lg:col-span-7">
          <SummaryStats
            title="RFQ Summary"
            :stats="summaryStats"
          />
        </div>

        <!-- Revenue Trends -->
        <div class="col-span-12 lg:col-span-7">
          <TrendChart 
            title="Revenue Trend (Last 12 Months)"
            :labels="monthlyRevenue.labels"
            :data="monthlyRevenue.data"
            :color="monthlyRevenue.color"
          />
        </div>

        <!-- Agent Activities -->
        <div class="col-span-12 lg:col-span-5">
          <AgentActivities :activities="agentActivities" />
        </div>

        <!-- Recent RFQs -->
        <div class="col-span-12 lg:col-span-7">
          <RecentRfqs :rfqs="recent_rfqs" />
        </div>

        <!-- Financial Overview -->
        <div class="col-span-12 lg:col-span-5">
          <FinancialOverview :stats="totalStats" :invoices="recent_invoices" />
        </div>

        <!-- Team Performance -->
        <div class="col-span-12">
          <TeamPerformance :agents="topAgents" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/components/layout/AdminLayout.vue'
import DashboardMetrics from '@/components/dashboard/DashboardMetrics.vue'
import RFQStatusChart from '@/components/dashboard/RFQStatusChart.vue'
import TrendChart from '@/components/dashboard/TrendChart.vue'
import SummaryStats from '@/components/dashboard/SummaryStats.vue'
import SystemHealthCard from '@/components/dashboard/SystemHealthCard.vue'
import CategoryBreakdown from '@/components/dashboard/CategoryBreakdown.vue'
import QuickActions from '@/components/dashboard/QuickActions.vue'
import RecentRfqs from '@/components/dashboard/RecentRfqs.vue'
import FinancialOverview from '@/components/dashboard/FinancialOverview.vue'
import TeamPerformance from '@/components/dashboard/TeamPerformance.vue'
import AgentActivities from '@/components/dashboard/AgentActivities.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
  stats: Array,
  summaryStats: Array,
  recent_rfqs: Array,
  recent_invoices: Array,
  rfqStatusData: Object,
  monthlyTrend: Object,
  monthlyRevenue: Object,
  topAgents: Array,
  agentActivities: Array,
  categoryBreakdown: Array,
  systemHealth: Object,
  totalStats: Object
})
</script>


