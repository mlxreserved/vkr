<template>
    <div>
        <nav>
            <!-- Надпись "ВКР" слева -->
            <div class="logo">ВКР</div>

            <!-- 1 admin 2 teacher 3 student -->
            <div v-if="isAuthorised" class="name-label">{{ userName }}</div>
            <button v-if="role_id == 2" @click="handleStudents">
                Студенты
            </button>
            <button v-if="role_id == 3" @click="handleEvents">
                Мероприятия
            </button>
            <button v-if="role_id == 3" @click="handleProfile">Профиль</button>
            <button v-if="role_id == 1" @click="handleWorkLoad">
                Занятость руководителей
            </button>
            <button v-if="role_id == 1" @click="handleTeachers">
                Распределение руководителей
            </button>
            <button v-if="role_id == 1" @click="handleThemes">
                Распределение тем
            </button>
            <button v-if="role_id == 1" @click="handleStreams">
                Управление потоками
            </button>
            <button v-if="isAuthorised" @click="handleChangePassword">
                Сменить пароль
            </button>
            <button v-if="isAuthorised" @click="handleLogout">Выйти</button>
            <button v-else @click="handleLogin">Логирование</button>
        </nav>
        <div>
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { mapGetters } from "vuex";
export default {
    name: "App",
    components: {},
    methods: {
        handleLogout() {
            axios
                .post("http://localhost:8000/logout", {
                    withCredentials: true,
                })
                .then((response) => {
                    console.log(response);
                    this.$store.dispatch("logout");
                    localStorage.clear();
                    this.$router.push("/login");
                })
                .catch((error) => {
                    console.error("Ошибка при выходе:", error);
                });
        },
        handleLogin() {
            this.$router.push("/login");
        },
        handleChangePassword() {
            this.$router.push("/changePassword");
        },
        handleStudents() {
            this.$router.push("/studentsTable");
        },
        handleEvents() {
            this.$router.push("/studentEvents");
        },
        handleProfile() {
            this.$router.push("/profile");
        },
        handleStreams() {
            this.$router.push("/streams");
        },
        handleWorkLoad() {
            this.$router.push("/workload");
        },
        handleTeachers() {
            this.$router.push("/worksTeachers");
        },
        handleThemes() {
            this.$router.push("/worksThemes");
        },
    },
    computed: {
        ...mapGetters(["isAuthorised", "userName", "role_id"]),
    },
};
</script>

<style scoped>
nav {
    font-family: "Roboto", sans-serif;
    display: flex;
    justify-content: flex-end; /* Выравнивание кнопок вправо */
    align-items: center;
    background-color: #007bff; /* Цвет фона навигации */
    padding: 10px 20px; /* Уменьшенные отступы */
    border-radius: 0 0 8px 8px; /* Скругление углов внизу */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Тень */
    position: fixed; /* Фиксированное положение */
    top: 0; /* Привязка к верхней части страницы */
    left: 0; /* Привязка к левому краю страницы */
    right: 0; /* Привязка к правому краю страницы */
    z-index: 1000; /* Обеспечение отображения поверх других элементов */
}

.logo {
    font-family: "Roboto", sans-serif;
    font-size: 24px; /* Размер шрифта */
    font-weight: bold; /* Жирный шрифт */
    color: white;
    margin-right: auto; /* Отодвигает логотип влево */
    padding-left: 10px; /* Отступ слева */
}

.name-label {
    color: white;
    display: flex;
    justify-content: center; /* Горизонтальное выравнивание */
    align-items: center;
}

body {
    margin-top: 60px; /* Отступ сверху для контента, чтобы не перекрывался шапкой */
}

button {
    background-color: #ffffff; /* Цвет фона кнопок */
    color: #007bff; /* Цвет текста кнопок */
    border: none; /* Убираем границы */
    border-radius: 4px; /* Скругление углов кнопок */
    padding: 8px 15px; /* Уменьшенные отступы внутри кнопок */
    margin-left: 15px; /* Отступ между кнопками */
    cursor: pointer; /* Указатель при наведении */
    transition: background-color 0.3s ease; /* Плавный переход цвета */
    font-size: 14px; /* Уменьшенный размер шрифта */
}

button:hover {
    background-color: #e7e7e7; /* Цвет фона при наведении */
}

button:focus {
    outline: none; /* Убираем контур при фокусе */
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5); /* Добавляем тень при фокусе */
}
</style>
