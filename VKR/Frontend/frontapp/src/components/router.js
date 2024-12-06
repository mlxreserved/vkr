import { createRouter, createWebHistory } from "vue-router";
import LoginPage from "./LoginPage.vue";
import ChangePassword from "./ChangePassword.vue";
import TeacherPage from "./TeacherPage.vue";
import StudentPage from "./StudentPage.vue";
import ManageStreams from "./ManageStreams.vue";
import CreateStream from "./CreateStream.vue";
import TeachersWorkLoad from "./TeachersWorkLoad.vue";
import WorksTeachers from "./WorksTeachers.vue";
import WorksThemes from "./WorksThemes.vue";
import EditStream from "./EditStream.vue";

const routes = [
    {
        path: "/login",
        name: "Login",
        component: LoginPage,
    },
    {
        path: "/profile",
        name: "Profile",
        component: StudentPage,
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
    {
        path: "/studentEvents",
        name: "StudentEvents",
        component: StudentPage,
    },
    {
        path: "/streams",
        name: "streams",
        component: ManageStreams,
    },
    {
        path: "/createStream",
        name: "createStream",
        component: CreateStream,
    },
    {
        path: "/workload",
        name: "workload",
        component: TeachersWorkLoad,
    },
    {
        path: "/worksTeachers",
        name: "worksTeachers",
        component: WorksTeachers,
    },
    {
        path: "/worksThemes",
        name: "worksThemes",
        component: WorksThemes,
    },
    {
        path: "/editStream/:streamId", // Путь для редактирования потока
        name: "editStream",
        component: EditStream, // Компонент для редактирования потока
        props: true, // Передаем параметры как props в компонент
    },

    {
        path: "/studentsTable",
        name: "TeacherPage",
        component: TeacherPage,
    },
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});

export default router;
