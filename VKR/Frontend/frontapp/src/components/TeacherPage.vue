<template>
    <div class="teacher-page">
        <div>
            <!-- Заголовок страницы -->
            <div
                v-if="this.$store.state.students.length > 0"
                class="table-header"
            >
                <!-- Левая часть: чекбоксы и поиск -->
                <div class="filters-left">
                    <div class="filter-checkbox">
                        <label>
                            <input
                                type="checkbox"
                                :checked="filterWithoutTheme"
                                @change="toggleFilterWithout"
                            />
                            Показать без тем
                        </label>
                    </div>

                    <div class="filter-checkbox">
                        <label>
                            <input
                                type="checkbox"
                                :checked="filterWaiting"
                                @change="toggleFilterWait"
                            />
                            Показать ожидающих подтверждение
                        </label>
                    </div>

                    <div class="search-bar">
                        <input
                            type="text"
                            v-model="searchQuery"
                            @input="updateSearch"
                            placeholder="Поиск по всем столбцам"
                        />
                    </div>
                </div>

                <!-- Правая часть: выпадающий список -->
                <div class="flow-selector">
                    <label for="flowSelect">Поток:</label>
                    <select
                        id="flowSelect"
                        v-model="localStream"
                        @change="onStreamChange"
                    >
                        <option
                            v-for="stream in uniqueStreams"
                            :key="stream"
                            :value="stream"
                        >
                            {{ stream }}
                        </option>
                    </select>
                </div>
            </div>
            <p v-else class="page-title">Данных нет</p>

            <!-- Сообщение об ошибке (если данные не удалось загрузить) -->
            <p v-if="error" class="error-message">
                Произошла ошибка при загрузке данных.
            </p>

            <!-- Таблица с данными -->
            <table
                v-if="this.$store.state.students.length > 0"
                class="data-table"
            >
                <thead>
                    <tr>
                        <!-- <th rowspan="2">ID</th> -->
                        <th rowspan="2">Имя</th>
                        <th rowspan="2">Фамилия</th>
                        <th rowspan="2">Тема ВКР</th>
                        <th colspan="3" class="small_col">Подтверждение</th>
                    </tr>
                    <tr>
                        <th>Студент</th>
                        <th>Преподаватель</th>
                        <th>Администратор</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="(students, groupName) in groupedStudents"
                        :key="groupName"
                    >
                        <tr class="group-row">
                            <td colspan="6">{{ groupName }}</td>
                        </tr>
                        <tr v-for="(item, index) in students" :key="index">
                            <!-- <td class="small_col">{{ truncateText(item.vkr_id, 20) }}</td> -->
                            <td
                                :data-fulltext="
                                    item.name.length > 30 ? item.name : null
                                "
                                :style="{ maxWidth: '200px' }"
                            >
                                {{ truncateText(item.name, 30) }}
                            </td>
                            <td
                                :data-fulltext="
                                    item.lastname.length > 30
                                        ? item.lastname
                                        : null
                                "
                                :style="{ maxWidth: '200px' }"
                            >
                                {{ truncateText(item.lastname, 30) }}
                            </td>
                            <!-- <td
                :data-fulltext="item.group_name.length > 20 ? item.group_name : null"
                :style="{ maxWidth: '200px' }"
              >{{ truncateText(item.group_name, 20) }}</td> -->
                            <td
                                :data-fulltext="
                                    item.theme && item.theme.length > 30
                                        ? item.theme
                                        : null
                                "
                                :style="{ maxWidth: '200px' }"
                                v-if="
                                    item.confirmed_teacher ||
                                    item.confirmed_admin
                                "
                            >
                                {{ truncateText(item.theme, 30) }}
                            </td>
                            <td
                                :data-fulltext="
                                    item.theme && item.theme.length > 30
                                        ? item.theme
                                        : null
                                "
                                :style="{ maxWidth: '200px' }"
                                v-else
                                @click="openModal(item)"
                                class="table-cell"
                            >
                                {{ item.theme }}
                            </td>
                            <td class="small_col">
                                <span v-if="item.confirmed_student === null">
                                    <span v-if="this.$store.state.roleId == 3">
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_student',
                                                    true
                                                )
                                            "
                                        >
                                            ✔️
                                        </button>
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_student',
                                                    false
                                                )
                                            "
                                        >
                                            ❌
                                        </button>
                                    </span>
                                    <span v-else>{{ "🟡" }}</span>
                                </span>
                                <span v-else>{{
                                    item.confirmed_student ? "🟢" : "🔴"
                                }}</span>
                            </td>
                            <!-- Подтверждение преподавателя -->
                            <td class="small_col">
                                <span v-if="item.confirmed_teacher === null">
                                    <span v-if="this.$store.state.roleId == 2">
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_teacher',
                                                    true
                                                )
                                            "
                                        >
                                            ✔️
                                        </button>
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_teacher',
                                                    false
                                                )
                                            "
                                        >
                                            ❌
                                        </button>
                                    </span>
                                    <span v-else>{{ "🟡" }}</span>
                                </span>
                                <span v-else>{{
                                    item.confirmed_teacher ? "🟢" : "🔴"
                                }}</span>
                            </td>

                            <!-- Подтверждение администратора -->
                            <td class="small_col">
                                <span v-if="item.confirmed_admin === null">
                                    <span v-if="this.$store.state.roleId == 1">
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_admin',
                                                    true
                                                )
                                            "
                                        >
                                            ✔️
                                        </button>
                                        <button
                                            @click="
                                                changeConfirmation(
                                                    item,
                                                    'confirmed_admin',
                                                    false
                                                )
                                            "
                                        >
                                            ❌
                                        </button>
                                    </span>
                                    <span v-else>{{ "🟡" }}</span>
                                </span>
                                <span v-else>{{
                                    item.confirmed_admin ? "🟢" : "🔴"
                                }}</span>
                            </td>
                            <!-- <td>{{ item.confirmed_student == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td>
              <td>{{ item.confirmed_teacher == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td>
              <td>{{ item.confirmed_admin == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td> -->
                        </tr>
                    </template>
                </tbody>
            </table>

            <div
                v-if="isModalOpen"
                class="modal-overlay"
                @click.self="closeModal"
            >
                <div class="modal-content">
                    <h3>Информация о студенте</h3>
                    <div class="form-group">
                        <label for="description">Тема:</label>
                        <input type="text" v-model="selectedStudent.theme" />
                    </div>
                    <!-- <p><strong>Тематика:</strong> {{ selectedStudent.pretheme }}</p>
                  <p><strong>Тема:</strong> {{ selectedStudent.theme }}</p> -->
                    <!-- Кнопки отмены и сохранения -->
                    <div class="dialog-buttons">
                        <button @click="closeModal">Отменить</button>
                        <button @click="saveEdit">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { mapState, mapActions, mapGetters } from "vuex";

export default {
    data() {
        return {
            error: false, // Индикатор ошибки
            isModalOpen: false, // Состояние модального окна
            selectedStudent: {},
            searchQuery: "", // Локальное состояние для поиска
            localStream: "",
        };
    },
    computed: {
        ...mapState(["filterWithoutTheme", "filterWaiting", "selectedStream"]),
        ...mapGetters(["filteredStudents", "groupedStudents"]),
        uniqueStreams() {
            return Array.from(this.$store.state.uniqueStreams);
        },
    },
    methods: {
        ...mapActions([
            "fetchStudents",
            "toggleFilterWithoutTheme",
            "toggleFilterWaiting",
            "updateSearchQuery",
            "processUniqueStreams",
            "initializeStreams",
        ]),
        toggleFilterWithout() {
            this.toggleFilterWithoutTheme();
        },
        toggleFilterWait() {
            this.toggleFilterWaiting();
        },
        updateSearch() {
            this.updateSearchQuery(this.searchQuery);
        },
        onStreamChange() {
            console.log(this.localStream);
            this.$store.dispatch("selectStream", this.localStream);
        },
        openModal(student) {
            this.selectedStudent = { ...student };
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        },
        async saveEdit() {
            try {
                this.selectedStudent.confirmed_admin = null;
                this.selectedStudent.confirmed_student = null;
                this.selectedStudent.confirmed_teacher = true;

                this.isModalOpen = false; // Закрываем диалог после сохранения
                const response = await axios.post(
                    "http://localhost:8000/changeThemeTeacher",
                    {
                        vkr_id: this.selectedStudent.vkr_id,
                        theme: this.selectedStudent.theme,
                        confirmed_admin: this.selectedStudent.confirmed_admin,
                        confirmed_teacher:
                            this.selectedStudent.confirmed_teacher,
                        confirmed_student:
                            this.selectedStudent.confirmed_student,
                    },
                    {
                        withCredentials: true, // Это позволяет отправлять и получать куки с сервером
                    }
                );

                // Проверка успешного ответа от сервера
                if (response.data.message) {
                    this.$store.commit("updateStudent", this.selectedStudent);
                } else {
                    console.error(
                        "Ошибка при сохранении данных на сервере:",
                        response
                    );
                }
            } catch (error) {
                console.error("Ошибка при сохранении данных:", error);
            }
        },
        async changeConfirmation(student, field, value) {
            try {
                student.confirmed_admin =
                    field === "confirmed_admin"
                        ? value
                        : student.confirmed_admin;
                student.confirmed_student =
                    field === "confirmed_student"
                        ? value
                        : student.confirmed_student;
                student.confirmed_teacher =
                    field === "confirmed_teacher"
                        ? value
                        : student.confirmed_teacher;

                console.log(student.confirmed_admin);
                console.log(student.confirmed_student);
                console.log(student.confirmed_teacher);

                const response = await axios.post(
                    "http://localhost:8000/changeConfirmation",
                    {
                        vkr_id: student.vkr_id,
                        theme: student.theme,
                        confirmed_admin: student.confirmed_admin,
                        confirmed_teacher: student.confirmed_teacher,
                        confirmed_student: student.confirmed_student,
                    },
                    {
                        withCredentials: true, // Это позволяет отправлять и получать куки с сервером
                    }
                );
                console.log(response);
                if (response.data.message) {
                    this.$store.commit("updateStudent", student);
                } else {
                    console.error(
                        "Ошибка при сохранении данных на сервере:",
                        response
                    );
                }
            } catch (error) {
                console.error("Ошибка при сохранении данных:", error);
            }
        },
        truncateText(text, maxLength) {
            if (!text) return "";
            return text.length > maxLength
                ? text.slice(0, maxLength) + "..."
                : text;
        },
    },
    mounted() {
        this.fetchStudents();
        this.processUniqueStreams();
        this.initializeStreams(); // Устанавливаем стандартный поток
        this.localStream = this.selectedStream;
    },
};
</script>

<style scoped>
.teacher-page {
    font-family: Arial, sans-serif;
    padding: 20px;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
}

/* Стили для модального окна */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Навигационная панель */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #007bff;
    padding: 10px 20px;
    color: white;
}

.navbar-title {
    margin: 0;
}

.navbar-user {
    font-weight: bold;
}

.table-cell {
    cursor: pointer;
}
.table-cell:hover {
    background-color: #f0f0f0;
}

/* Стиль для поля поиска */
.search-bar {
    margin-bottom: 10px;
    margin-top: 10px;
}
/* .search-bar input {
  width: 25vw;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
} */

/* Заголовок страницы */
.page-title {
    font-size: 24px;
    color: #333;
    text-align: center;
    margin-top: 70px;
}

/* Сообщение об ошибке */
.error-message {
    color: red;
    text-align: center;
}

.small_col {
    width: 100px;
}

.form-group {
    margin-bottom: 10px;
}
.form-group label {
    display: block;
    margin-bottom: 5px;
}
.form-group input {
    width: 100%;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Таблица данных */
.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.data-table td,
.data-table th {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
    max-width: 200px; /* Максимальная ширина ячейки */
    white-space: nowrap; /* Запрещаем перенос строк */
    overflow: hidden; /* Прячем текст, который выходит за пределы */
    text-overflow: ellipsis; /* Добавляем многоточие */
    position: relative; /* Для всплывающей подсказки */
}

.data-table td:hover {
    overflow: visible; /* Разрешаем тексту выходить за пределы */
    white-space: normal; /* Разрешаем перенос строк */
    z-index: 10; /* Выводим текст поверх других элементов */
}

.data-table td::after,
.data-table th::after {
    content: attr(data-fulltext); /* Показываем текст в атрибуте */
    display: none; /* Прячем содержимое по умолчанию */
    position: absolute;
    top: 100%; /* Снизу ячейки */
    left: 0;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 4px;
    border-radius: 4px;
    white-space: nowrap;
    z-index: 100;
}

.data-table td[data-fulltext]:hover::after {
    display: block; /* Показываем подсказку только если есть data-fulltext */
}

.data-table th {
    background-color: #007bff;
    color: white;
}

.data-table th,
.data-table td {
    min-width: 50px; /* Минимальная ширина */
    max-width: 200px; /* Максимальная ширина */
}

.table-header {
    display: flex;
    justify-content: space-between; /* Левая и правая части по краям */
    align-items: flex-start; /* Выравнивание по верхнему краю */
    margin-bottom: 20px;
    margin-top: 70px;
}

.filters-container {
    display: flex;
    flex-direction: column; /* Размещаем элементы друг под другом */
    gap: 10px; /* Расстояние между чекбоксами и поиском */
}

.filter-checkbox label {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    gap: 4px;
}

.search-bar {
    margin-top: 10px;
    margin-bottom: 10px;
}

.search-bar input {
    width: 25vw;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.flow-selector {
    display: flex;
    align-items: center;
}

.flow-selector label {
    margin-right: 10px;
}

.flow-selector select {
    padding: 5px;
    width: 10vw;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.group-row {
    background-color: #f0f8ff; /* Светло-голубой фон */
    font-weight: bold;
    text-align: left;
}
</style>
