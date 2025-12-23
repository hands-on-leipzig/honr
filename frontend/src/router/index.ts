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
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { public: true },
    },
    {
      path: '/wizard',
      name: 'wizard',
      component: () => import('../views/WizardView.vue'),
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
          path: 'all',
          name: 'all',
          component: () => import('../views/AllView.vue'),
        },
        {
          path: 'people',
          name: 'people',
          component: () => import('../views/PeopleView.vue'),
        },
        {
          path: 'user/:id',
          name: 'user-detail',
          component: () => import('../views/UserDetailView.vue'),
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

// Navigation guard
router.beforeEach(async (to, _from, next) => {
  const token = localStorage.getItem('auth_token')
  const isAuthenticated = !!token

  if (to.meta.public) {
    // Public route - redirect to awards if already authenticated
    if (isAuthenticated && to.name === 'login') {
      // Check wizard status before redirecting
      const { useUserStore } = await import('../stores/user')
      const userStore = useUserStore()
      if (!userStore.user) {
        await userStore.fetchUser()
      }
      if (userStore.user && !userStore.user.wizard_completed) {
        next('/wizard')
      } else {
        next('/awards')
      }
    } else {
      next()
    }
  } else if (to.name === 'wizard') {
    // Wizard route - allow if authenticated
    if (!isAuthenticated) {
      next('/login')
    } else {
      // If wizard is already completed, redirect to awards
      const { useUserStore } = await import('../stores/user')
      const userStore = useUserStore()
      if (!userStore.user) {
        await userStore.fetchUser()
      }
      if (userStore.user && userStore.user.wizard_completed) {
        next('/awards')
      } else {
        next()
      }
    }
  } else {
    // Protected route - redirect to login if not authenticated
    if (!isAuthenticated) {
      next('/login')
    } else {
      // Check if wizard needs to be completed
      const { useUserStore } = await import('../stores/user')
      const userStore = useUserStore()
      
      // Fetch user if not already loaded
      if (!userStore.user) {
        await userStore.fetchUser()
      }
      
      // Redirect to wizard if not completed
      if (userStore.user && !userStore.user.wizard_completed) {
        next('/wizard')
      } else {
        next()
      }
    }
  }
})

export default router
