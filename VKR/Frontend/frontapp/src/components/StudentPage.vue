<template>
    <div class="student-profile">
        <h2>Профиль студента</h2>

        <!-- Состояние загрузки -->
        <div v-if="isLoading" class="loading">
            <p>Загрузка...</p>
        </div>

        <!-- Ошибка при запросе данных -->
        <div v-if="errorMessage" class="error-message">
            <p>{{ errorMessage }}</p>
        </div>

        <!-- Карточки с информацией о квалификационных работах -->
        <div
            v-if="graduationWorks && graduationWorks.length"
            class="graduation-cards"
        >
            <div
                v-for="(work, index) in graduationWorks"
                :key="index"
                class="card"
            >
                <div class="card-header">
                    <h3>Выпускная квалификационная работа</h3>
                </div>
                <div class="card-body">
                    <p><strong>Тип работы:</strong> {{ work.type_name }}</p>
                    <p><strong>Тематика:</strong> {{ work.pretheme }}</p>
                    <p><strong>Тема:</strong> {{ work.theme }}</p>

                    <!-- Оценка или статус -->
                    <p v-if="work.mark !== null">
                        <strong>Оценка:</strong> {{ work.mark }}
                    </p>
                    <p v-else><strong>Оценка:</strong> Оценка не выставлена</p>

                    <div class="work-state-container"
                        v-if="
                            work.confirmed_admin == false ||
                            work.confirmed_student == false ||
                            work.confirmed_teacher == false
                        "
                    >
                        <strong>Состояние:</strong>
                        <div
                            v-if="work.confirmed_student == false"
                            class="current-work-state"
                        >
                            Вы отклонили тему
                        </div>
                        <div
                            v-if="work.confirmed_teacher == false"
                            class="current-work-state"
                        >
                            Руководитель отклонил тему
                        </div>
                        <div
                            v-if="work.confirmed_admin == false"
                            class="current-work-state"
                        >
                            Администратор отклонил тему
                        </div>
                    </div>

                    <div
                        v-if="
                            work.confirmed_admin === null &&
                            work.confirmed_teacher === true &&
                            work.confirmed_student === null
                        "
                        class="confirmed-teacher-div"
                    >
                        <strong
                            >Тема подтверждена преподавателем, выберите
                            действие:</strong
                        >
                        <div class="buttons-container">
                            <button
                                v-if="
                                    work.confirmed_student !== true &&
                                    !work.isAccepted
                                "
                                @click="confirmTheme(work.vkr_id)"
                                class="accept-btn"
                            >
                                Принять
                            </button>
                            <button
                                v-if="
                                    work.confirmed_student !== true &&
                                    !work.isRejected
                                "
                                @click="rejectTheme(work.vkr_id)"
                                class="reject-btn"
                            >
                                Отклонить
                            </button>
                        </div>
                    </div>

                    <!-- Блок редактирования тематики и темы, если не выставлена оценка и администратор не подтвердил -->
                    <div
                        v-if="
                            work.mark === null && work.confirmed_admin === null
                        "
                    >
                        <label>Тематика:</label>
                        <input
                            v-model="work.pretheme"
                            :disabled="!work.isEditable"
                            @input="handleInputChange(work)"
                        />
                        <label>Тема:</label>
                        <input
                            v-model="work.theme"
                            :disabled="!work.isEditable"
                            @input="handleInputChange(work)"
                        />

                        <button
                            :disabled="!work.isModified"
                            @click="saveChanges(work.vkr_id)"
                        >
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "StudentPage",
    data() {
        return {
            graduationWorks: [], // Данные о выпускных работах
            isLoading: true, // Состояние загрузки
            errorMessage: null, // Сообщение об ошибке
        };
    },
    methods: {
        // Метод для получения данных о выпускной квалификационной работе
        fetchStudentProfile() {
            axios
                .get("http://localhost:8000/studentProfile", {
                    withCredentials: true, // Отправляем куки с запросом
                })
                .then((response) => {
                    this.graduationWorks = response.data;
                    this.graduationWorks = this.sortGraduationWorks(
                        this.graduationWorks
                    );
                    this.isLoading = false; // Завершаем загрузку

                    // Обновляем isEditable и isModified в зависимости от условий
                    this.graduationWorks.forEach((work) => {
                        work.isEditable =
                            work.mark === null && work.confirmed_admin === null;
                        work.isModified = false; // Сбрасываем флаг изменений для каждой карточки
                    });
                })
                .catch((error) => {
                    this.isLoading = false; // Завершаем загрузку
                    this.errorMessage =
                        error.response?.data?.error ||
                        "Произошла ошибка при загрузке данных";
                });
        },

        // Сохранение изменений
        saveChanges(vkrId) {
            const work = this.graduationWorks.find(
                (work) => work.vkr_id === vkrId
            );
            if (!work.isModified) return;

            // Отправляем данные в базу для сохранения изменений
            axios
                .post(
                    "http://localhost:8000/saveThemeChanges",
                    {
                        vkr_id: vkrId,
                        theme: work.theme,
                        pretheme: work.pretheme,
                    },
                    {
                        withCredentials: true,
                    }
                )
                .then(() => {
                    work.isModified = false; // Сбрасываем флаг изменений
                    this.$router.go(0);
                })
                .catch((error) => {
                    console.error(error);
                    alert("Ошибка при сохранении изменений");
                });
        },

        // Подтверждение темы
        confirmTheme(vkrId) {
            axios
                .post(
                    "http://localhost:8000/confirmTheme",
                    {
                        vkr_id: vkrId,
                    },
                    { withCredentials: true }
                )
                .then(() => {
                    this.$router.go(0);
                })
                .catch((error) => {
                    console.error(error);
                    alert("Ошибка при подтверждении темы");
                });
        },

        // Отклонение темы
        rejectTheme(vkrId) {
            axios
                .post(
                    "http://localhost:8000/rejectTheme",
                    {
                        vkr_id: vkrId,
                    },
                    { withCredentials: true }
                )
                .then(() => {
                    alert("Тема отклонена");
                    this.$router.go(0);
                })
                .catch((error) => {
                    console.error(error);
                    alert("Ошибка при отклонении темы");
                });
        },

        // Метод для обновления isModified, если пользователь изменил поля
        handleInputChange(work) {
            work.isModified = true; // Флаг изменений для текущей карточки
        },

        sortGraduationWorks(arr) {
            return arr.sort((a, b) => {
                // 1. Если a.mark === null и a.confirmed_admin !== true, то a должно идти первым
                if (a.mark === null && a.confirmed_admin !== true) {
                    return -1; // a раньше
                }

                // 2. Если b.mark === null и b.confirmed_admin !== true, то b должно идти первым
                if (b.mark === null && b.confirmed_admin !== true) {
                    return 1; // b раньше
                }

                // 3. Если a.mark === null и a.confirmed_admin === true, то a должно идти раньше, чем b
                if (
                    a.mark === null &&
                    a.confirmed_admin === true &&
                    b.mark !== null
                ) {
                    return -1; // a раньше
                }

                // 4. Если b.mark === null и b.confirmed_admin === true, то b должно идти раньше, чем a
                if (
                    b.mark === null &&
                    b.confirmed_admin === true &&
                    a.mark !== null
                ) {
                    return 1; // b раньше
                }

                // 5. Если mark не null, то такие объекты идут после всех остальных
                if (a.mark !== null && b.mark === null) {
                    return 1; // a позже
                }

                if (a.mark === null && b.mark !== null) {
                    return -1; // b позже
                }

                return 0; // если оба объекта одинаковы по этим критериям
            });
        },
    },
    created() {
        this.fetchStudentProfile(); // Загружаем данные при создании компонента
    },
};
</script>

<style scoped>
.student-profile {
    max-width: 800px;
    margin: 40px auto 0; /* Увеличен отступ сверху, без изменения отступов снизу */
    font-family: "Roboto", sans-serif;
    padding: 20px; /* Добавлен внутренний отступ */
}

.confirmed-teacher-div {
    margin-bottom: 20px;
}
.graduation-cards {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

.card-header {
    background-color: #007bff;
    color: white;
    padding: 15px;
    border-radius: 8px 8px 0 0;
    text-align: center;
}

.card-body {
    padding: 20px;
}

.card p {
    margin: 10px 0;
    font-size: 16px;
}

.error-message {
    color: red;
    text-align: center;
}

.current-work-state {
    color: red;
}

.work-state-container {
    display: flex;
    gap: 5px;
}

.loading {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 8px;
    margin: 8px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

button {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    border-radius: 4px;
    margin-top: 10px;
}

button:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.buttons-container {
    display: flex;
    gap: 15px;
}

.accept-btn {
    background-color: #4caf50; /* Зеленый */
    color: white;
    border: none;
}

.reject-btn {
    background-color: #f44336; /* Красный */
    color: white;
}

.accept-btn:hover {
    background-color: #45a049;
}

.reject-btn:hover {
    background-color: #e53935;
}

.accept-btn:disabled,
.reject-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
</style>
