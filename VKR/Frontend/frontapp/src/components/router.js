import { createRouter, createWebHistory } from "vue-router";
import LoginPage from "./LoginPage.vue";
import ProfilePage from "./ProfilePage.vue";
import ChangePassword from "./ChangePassword.vue";
import TeacherPage from "./TeacherPage.vue";

const routes = [
    {
        path: "/login",
        name: "Login",
        component: LoginPage,
    },
    {
        path: "/profile",
        name: "Profile",
        component: ProfilePage,
    },
    {
        path: "/",
        name: "BasePage",
        component: LoginPage,
    },
    {
      path: "/teacher",
      name: "TeacherPage",
      component: TeacherPage,
    },
    {
        path: "",
        name: "BasePage",
        component: LoginPage,
    },
    {
      path: "/changePassword",
      name: "ChangePassword",
      component: ChangePassword,
  },
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});

export default router;
