// store/index.js
import axios from "axios";

export default {
    state: {
        // Состояние для хранения данных о токенах
        authToken: null,
    },
    actions: {
        refreshAccessToken({ commit }) {
            axios
                .post(
                    "http://localhost:8000/refresh-token",
                    {},
                    {
                        withCredentials: true, // Важно для отправки куков с запросами
                    }
                )
                // eslint-disable-next-line
                .then((response) => {
                    // Тут мы не получаем токен, т.к. сервер обновит токен в куки автоматически.
                    console.log("Access token обновлен");
                    // Если необходимо, можно обновить состояние, хотя мы этого делать не будем,
                    // потому что куки сами обновляются
                })
                .catch((error) => {
                    console.error("Ошибка при обновлении токена", error);
                    // В случае ошибки, например если refresh token тоже истек, нужно разлогинить пользователя
                    commit("logout");
                });
        },
    },
    mutations: {
        setAuthToken(state, token) {
            state.authToken = token;
        },
        logout(state) {
            state.authToken = null;
            // Очистить куки, перенаправить на страницу входа
            document.cookie =
                "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            document.cookie =
                "refresh_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/";
            window.location.href = "/login"; // Перенаправление на страницу логина
        },
    },
};
