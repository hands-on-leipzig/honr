import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../components/MainLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/me',
    },
    {
      path: '/',
      component: MainLayout,
      children: [
        {
          path: 'me',
          name: 'me',
          component: () => import('../views/MeView.vue'),
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
  ],
})

export default router
