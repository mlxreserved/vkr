import axios from "axios";
import { createStore } from "vuex";
import createPersistedState from "vuex-persistedstate";

export default createStore({
    state: {
        tokenRefreshTimer: null,
        isAuthorised: false,
        name: "",
        lastname: "",
        role_id: "",

        filterWithoutTheme: false,
        filterWaiting: false,
        students: [],
        uniqueStreams: new Set(),
        searchQuery: "", // Новое состояние для поиска
        selectedStream: null,
    },
    actions: {
        // Действие для обновления токена
        refreshAccessToken({ commit }) {
            console.log("Отправляю запрос на получение нового токена");

            // Токен будет автоматически отправляться через куки
            axios
                .post(
                    "http://localhost:8000/updateToken",
                    {},
                    { withCredentials: true }
                )
                .then((response) => {
                    console.log(response);
                    console.log("Токен обновлен");
                    // Мы не храним новый токен в хранилище, так как он в куки
                })
                .catch((error) => {
                    console.error("Ошибка при обновлении токена", error);
                    commit("logout"); // Разлогиниваем пользователя в случае ошибки
                });
        },

        // Действие для логина
        login({ commit }, params) {
            let user = params.user;
            let role_id = params.role_id;

            // Устанавливаем состояние пользователя и авторизации
            commit("setUser", { user, role_id });

            // Запускаем таймер для обновления токенов
            commit("startTokenRefreshTimer");

            console.log("Пользователь авторизован");
        },

        // Действие для разлогина
        logout({ commit }) {
            commit("clearAuthToken"); // Очищаем токен
            commit("stopTokenRefreshTimer"); // Останавливаем таймер
            commit("clearState"); // Очищаем остальные данные состояния
            console.log("Пользователь разлогинен");
        },

        initializeStreams({ commit, state }) {
            if (state.uniqueStreams.size > 0 && !state.selectedStream) {
                commit("initializeDefaultStream");
            }
        },
        selectStream({ commit, dispatch }, stream) {
            commit("setSelectedStream", stream);
            dispatch("filterStudentsByStream");
        },
        filterStudentsByStream({ commit }) {
            commit("setFilteredStudentsByStream");
        },
        //
        async fetchStudents({ commit }) {
            try {
                const response = await axios.get(
                    "http://localhost:8000/studentForTeacher",
                    {
                        withCredentials: true,
                    }
                );
                if (response.data.error) {
                    console.log("error");
                } else {
                    commit("setStudents", response.data);
                }
            } catch (error) {
                console.error("Ошибка при загрузке данных студентов", error);
            }
        },
        toggleFilterWithoutTheme({ commit }) {
            commit("toggleFilterWithoutTheme");
        },
        toggleFilterWaiting({ commit }) {
            commit("toggleFilterWaiting");
        },
        updateSearchQuery({ commit }, query) {
            // Новый action для обновления запроса
            commit("setSearchQuery", query);
        },
        processUniqueStreams({ commit }) {
            commit("setUniqueStreams");
        },
    },
    mutations: {
        // Устанавливаем данные пользователя
        setUser(state, { user, role_id }) {
            state.name = user.name;
            state.lastname = user.lastname;
            state.role_id = role_id;
            state.isAuthorised = true;
        },

        // Очищаем токен (если он был в состоянии, например для редиректа)
        clearAuthToken(state) {
            state.authToken = null;
        },

        // Запускаем таймер для обновления токенов
        startTokenRefreshTimer(state, dispatch) {
            if (state.tokenRefreshTimer) return; // Если таймер уже работает, не запускаем новый

            state.tokenRefreshTimer = setInterval(() => {
                dispatch("refreshAccessToken");
            }, 58 * 60 * 1000); // Обновляем токен каждые 58 минут
        },

        // Останавливаем таймер
        stopTokenRefreshTimer(state) {
            if (state.tokenRefreshTimer) {
                clearInterval(state.tokenRefreshTimer);
                state.tokenRefreshTimer = null;
            }
        },

        // Очистка состояния
        clearState(state) {
            state.isAuthorised = false;
            state.name = "";
            state.lastname = "";
            state.role_id = "";
            state.tokenRefreshTimer = null;
        },

        setSelectedStream(state, stream) {
            state.selectedStream = stream;
        },
        initializeDefaultStream(state) {
            if (state.uniqueStreams.size > 0) {
                state.selectedStream = Array.from(state.uniqueStreams)[0]; // Установить первый поток
            }
        },
        setFilteredStudentsByStream(state) {
            const stream = state.selectedStream;
            let students = state.students;
            students = stream
                ? state.students.filter(
                      (student) => student.stream_name === stream
                  )
                : state.students;
            return students;
        },
        setStudents(state, students) {
            state.students = students;
        },
        toggleFilterWithoutTheme(state) {
            state.filterWithoutTheme = !state.filterWithoutTheme;
        },
        toggleFilterWaiting(state) {
            state.filterWaiting = !state.filterWaiting;
        },

        setUniqueStreams(state) {
            state.uniqueStreams = new Set();
            state.students.forEach((student) =>
                state.uniqueStreams.add(student.stream_name)
            );
        },

        updateStudent(state, updatedStudent) {
            const index = state.students.findIndex(
                (student) => student.vkr_id === updatedStudent.vkr_id
            );
            if (index !== -1) {
                state.students.splice(index, 1, { ...updatedStudent });
            }
        },
        setSearchQuery(state, query) {
            // Мутация для установки запроса
            state.searchQuery = query;
        },
        //
    },
    getters: {
        isAuthorised: (state) => state.isAuthorised,
        userName: (state) => `${state.name} ${state.lastname}`,
        role_id: (state) => state.role_id,

        filteredStudents: (state) => {
            let students = state.students;

            //console.log(Array.isArray(students) ? 1 : 0);
            // Применяем фильтр на основе checkbox
            if (state.filterWithoutTheme) {
                students = students.filter((student) => student.theme == null);
            }

            if (state.filterWaiting) {
                students = students.filter(
                    (student) => student.confirmed_teacher == null
                );
            }

            if (state.searchQuery) {
                const query = state.searchQuery.toLowerCase();
                students = students.filter((student) => {
                    // Проверяем только указанные поля
                    const searchFields = [
                        student.name,
                        student.lastname,
                        student.group_name,
                        student.theme,
                    ];

                    return searchFields.some((field) =>
                        String(field).toLowerCase().includes(query)
                    );
                });
            }
            if (state.selectedStream) {
                students = students.filter(
                    (student) => student.stream_name === state.selectedStream
                );
            }

            //Возвращаем отсортированные результаты
            return students.sort((a, b) =>
                a.lastname.localeCompare(b.lastname)
            );
        },

        groupedStudents: (state, getters) => {
            const students = getters.filteredStudents;

            // Группировка студентов по группам
            const grouped = students.reduce((acc, student) => {
                const groupName = student.group_name; // Группа по умолчанию
                if (!acc[groupName]) acc[groupName] = [];
                acc[groupName].push(student);
                return acc;
            }, {});

            // Сортировка групп и студентов
            const sortedGroups = Object.keys(grouped)
                .sort() // Сортируем группы по алфавиту
                .reduce((acc, groupName) => {
                    acc[groupName] = grouped[groupName];
                    return acc;
                }, {});

            return sortedGroups;
        },
    },
    plugins: [createPersistedState()],
});
