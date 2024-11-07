import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from './LoginPage.vue';
import ProfilePage from './ProfilePage.vue';
import TeacherPage from './TeacherPage.vue'

const routes = [

  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
  },
  {
    path: '/profile',
    name: 'Profile',
    component: ProfilePage,
  },
  {
    path: '/teacher',
    name: 'Teacher',
    component: TeacherPage,
  },
  {
    path: '/',
    name: 'BasePage',
    component: LoginPage,
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;