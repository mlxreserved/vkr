import axios from "axios";
import { createStore } from "vuex";

export default createStore({
    state: {
        authToken: null,
        tokenRefreshTimer: null,
        isAuthorised: false,
        name: "",
        lastname: "",
    },
    actions: {
        // Действие для обновления токенаф
        refreshAccessToken({ commit }) {
            axios
                .post(
                    "http://localhost:8000/refresh-token",
                    {},
                    { withCredentials: true }
                )
                .then((response) => {
                    console.log(response);
                    console.log("Access token обновлен");
                })
                .catch((error) => {
                    console.error("Ошибка при обновлении токена", error);
                    commit("logout"); // Разлогиниваем пользователя в случае ошибки
                });
        },

        // Действие для логина (не отправляется запрос, только обновляем состояние)
        login({ commit }, user) {
            // Запускаем таймер для обновления токенов
            commit("startTokenRefreshTimer");
            // устанавливаем имя и фамилию
            commit("setUser", user);
            commit("changeAuthorised");
        },

        // Действие для разлогина (не отправляется запрос, только обновляем состояние)
        logout({ commit }) {
            // Очистить токены в состоянии
            commit("clearAuthToken");
            // Остановить таймер
            commit("stopTokenRefreshTimer");
            commit("changeAuthorised");
            // Перенаправить на страницу логина (это можно сделать в компоненте)
            console.log("Пользователь разлогинен");
        },
    },
    mutations: {
        // Устанавливаем токен в состояние
        setAuthToken(state, token) {
            state.authToken = token;
        },

        // Очищаем токен
        clearAuthToken(state) {
            state.authToken = null;
        },

        // Запуск таймера для обновления токенов
        startTokenRefreshTimer(state) {
            state.tokenRefreshTimer = setInterval(() => {
                this.dispatch("refreshAccessToken");
            }, 58 * 60 * 1000); // Обновляем токен каждые 58 минут
        },

        // Остановка таймера
        stopTokenRefreshTimer(state) {
            if (state.tokenRefreshTimer) {
                clearInterval(state.tokenRefreshTimer);
                state.tokenRefreshTimer = null;
            }
        },

        setUser(state, user) {
            state.name = user.name;
            state.lastname = user.lastname;
        },

        changeAuthorised(state) {
            state.isAuthorised = !state.isAuthorised;
        },
    },
    getters: {
        isAuthorised: (state) => state.isAuthorised,
        userName: (state) => state.name + " " + state.lastname,
    },
});
