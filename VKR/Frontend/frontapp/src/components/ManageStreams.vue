<template>
    <div class="streams-page">
        <h2 class="page-title">Список потоков</h2>
        <button @click="addStream" class="add-stream-btn">
            Добавить поток <span class="plus-icon">+</span>
        </button>

        <!-- Состояние загрузки -->
        <div v-if="isLoading" class="loading">Загрузка...</div>

        <!-- Ошибка при запросе данных -->
        <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
        </div>

        <!-- Выводим потоки, если данные есть -->
        <div v-if="streamsByGroups">
            <div
                v-for="(groups, streamName) in streamsByGroups"
                :key="streamName"
                class="stream-card"
            >
                <div class="stream-header">
                    <h3>{{ streamName }}</h3>

                    <div class="button-container">
                        <button
                            @click="editStream(streamName)"
                            class="edit-stream-btn"
                        >
                            Изменить поток
                        </button>

                        <button
                            @click="toggleGroupVisibility(streamName)"
                            class="show-stream-btn"
                        >
                            Показать группы
                        </button>
                        <button
                            @click="
                                deleteStream(
                                    streamsByGroups[streamName][0].stream_id
                                )
                            "
                            class="delete-stream-btn"
                        >
                            Удалить поток
                        </button>
                    </div>
                </div>

                <div v-if="isGroupVisible(streamName)" class="group-list">
                    <ul>
                        <li
                            v-for="group in groups"
                            :key="group.group_id"
                            class="group-card"
                        >
                            {{ group.group_name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { toRaw } from "vue";
export default {
    name: "StreamsPage",
    data() {
        return {
            streams: [], // Данные о потоках
            isLoading: true, // Состояние загрузки
            errorMessage: null, // Сообщение об ошибке
            groupVisibility: {}, // Состояние видимости групп
            groups: null,
        };
    },
    computed: {
        // Группируем потоки по имени потока
        streamsByGroups() {
            const grouped = {};
            this.streams.forEach((stream) => {
                if (!grouped[stream.stream_name]) {
                    grouped[stream.stream_name] = [];
                }
                grouped[stream.stream_name].push(stream);
            });
            return grouped;
        },
        // Перебираем все потоки и создаем объект видимости для каждого потока
        visibleGroups() {
            return this.streams.reduce((acc, stream) => {
                const streamName = stream.stream_name;
                if (!acc[streamName]) {
                    acc[streamName] = false; // По умолчанию группы скрыты
                }
                return acc;
            }, {});
        },
    },
    methods: {
        editStream(streamName) {
            // Находим id потока по его имени
            const streamId = this.streams.find(
                (stream) => stream.stream_name === streamName
            ).stream_id;

            // Переходим на страницу редактирования потока
            this.$router.push({
                name: "editStream", // Имя маршрута для редактирования потока
                params: {
                    streamId: streamId, // Передаем id потока
                },
            });
        },

        async fetchStreams() {
            try {
                const response = await axios.get(
                    "http://localhost:8000/streams",
                    {
                        withCredentials: true, // Отправляем куки с запросом
                    }
                );
                this.streams = response.data.streams;
                this.groups = response.data.groups;
                this.isLoading = false; // Завершаем загрузку
            } catch (error) {
                this.isLoading = false;
                this.errorMessage =
                    error.response?.data?.error ||
                    "Произошла ошибка при загрузке данных";
            }
        },
        addStream() {
            const plainGroups = toRaw(this.groups);
            localStorage.setItem("groups", JSON.stringify(plainGroups));
            this.$router.push("/createStream");
        },
        // Функция для управления видимостью групп
        toggleGroupVisibility(streamName) {
            this.groupVisibility[streamName] =
                !this.groupVisibility[streamName];
        },
        // Проверка видимости групп для потока
        isGroupVisible(streamName) {
            return this.groupVisibility[streamName] === true;
        },
        async deleteStream(streamId) {
            const confirmDelete = confirm(
                "Вы уверены, что хотите удалить этот поток?"
            );
            if (!confirmDelete) return;

            try {
                console.log(streamId);
                const response = await axios.post(
                    "http://localhost:8000/deleteStream",
                    {
                        stream_id: streamId,
                    },
                    {
                        withCredentials: true,
                    }
                );

                if (response.data.success) {
                    alert("Поток успешно удален");
                    this.$router.go(0); // Перезагружаем страницу
                } else {
                    alert("Ошибка при удалении потока");
                }
            } catch (error) {
                console.error(error);
                alert("Произошла ошибка при удалении потока");
            }
        },
    },
    created() {
        this.fetchStreams(); // Загружаем данные при создании компонента
    },
};
</script>

<style scoped>
.streams-page {
    max-width: 1000px;
    margin: 40px auto;
    font-family: "Roboto", sans-serif;
    padding: 20px;
}

.page-title {
    text-align: center;
    color: #333;
    font-size: 2rem;
    margin-bottom: 30px;
}

.loading,
.error-message {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

.stream-card {
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    padding: 20px;
}

.stream-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #ddd;
    padding-bottom: 10px;
}

.stream-header h3 {
    font-size: 1.5rem;
    color: #007bff;
}

.group-list {
    margin-top: 20px;
}

.group-card {
    background-color: #ffffff;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    font-size: 1.1rem;
    transition: transform 0.2s ease;
}

.group-card:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

ul {
    list-style-type: none;
    padding-left: 0;
}

ul li:last-child {
    margin-bottom: 0;
}

.add-stream-btn {
    display: inline-flex;
    align-items: center;
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    width: 300px;
    height: 50px;
    font-size: 1rem;
    margin-bottom: 20px;
    transition: background-color 0.3s ease;
}

.add-stream-btn:hover {
    background-color: #218838;
}

.plus-icon {
    font-size: 1.2rem;
    margin-left: 10px;
    font-weight: bold;
}

.delete-stream-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
    transition: background-color 0.3s ease;
}

.delete-stream-btn:hover {
    background-color: #c82333;
}

.button-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.edit-stream-btn,
.show-stream-btn,
.delete-stream-btn {
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    border: none;
}

.edit-stream-btn,
.show-stream-btn {
    background-color: #007bff;
    color: white;
}

.edit-stream-btn:hover,
.show-stream-btn:hover {
    background-color: #0056b3;
}

.stream-item {
    border: 1px solid #ddd;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
}

.groups-list {
    margin-top: 10px;
}
</style>
