import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../components/MainLayout.vue'
import AdminLayout from '../components/AdminLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/awards',
    },
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: 'awards',
          name: 'awards',
          component: () => import('../views/AwardsView.vue'),
        },
        {
          path: 'engagement',
          name: 'engagement',
          component: () => import('../views/EngagementView.vue'),
        },
        {
          path: 'all',
          name: 'all',
          component: () => import('../views/AllView.vue'),
        },
        {
          path: 'settings',
          name: 'settings',
          component: () => import('../views/SettingsView.vue'),
        },
      ],
    },
    {
      path: '/admin',
      component: AdminLayout,
      children: [
        {
          path: 'tables',
          name: 'admin-tables',
          component: () => import('../views/AdminTablesView.vue'),
        },
        {
          path: 'statistics',
          name: 'admin-statistics',
          component: () => import('../views/AdminStatisticsView.vue'),
        },
      ],
    },
  ],
})

export default router
