<template>
    <div>
        <nav>
            <div v-if="isAuthorised" class="name-label">{{ userName }}</div>
            <button v-if="isAuthorised" @click="handleStudents">Студенты</button> 
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
            this.$router.push("/teacher")
        },
    },
    computed: {
        ...mapGetters(["isAuthorised", "userName"]),
    },
};
</script>

<style scoped>
nav {
    font-family: "Finlandica", sans-serif;
    display: flex;
    justify-content: flex-end; /* Выравнивание кнопок вправо */
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
