<template>
    <div class="edit-stream-page">
        <h2>Редактирование потока: {{ streamName }}</h2>

        <!-- Список групп, которые уже в потоке -->
        <div v-if="existingGroups.length > 0">
            <h3>Группы в потоке:</h3>
            <ul>
                <li v-for="group in existingGroups" :key="group.group_id">
                    {{ group.group_name }}
                    <button
                        @click="removeGroupFromStream(group.group_id)"
                        class="remove-button"
                    >
                        Удалить
                    </button>
                </li>
            </ul>
        </div>

        <!-- Кнопка для добавления групп в поток -->
        <button @click="openAddGroupsModal">Добавить группы в поток</button>

        <!-- Модальное окно для выбора групп -->
        <div v-if="isModalOpen" class="modal">
            <div class="modal-content">
                <h3>Выберите группы для добавления</h3>
                <ul>
                    <li v-for="group in availableGroups" :key="group.group_id">
                        <input
                            type="checkbox"
                            :id="'group-' + group.group_id"
                            :value="group.group_id"
                            v-model="selectedGroups"
                        />
                        <label :for="'group-' + group.group_id">{{
                            group.group_name
                        }}</label>
                    </li>
                </ul>
                <button @click="addGroupsToStream">
                    Добавить выбранные группы
                </button>
                <button @click="closeAddGroupsModal">Закрыть</button>
            </div>
        </div>

        <!-- Сообщения об ошибках или успехе -->
        <div v-if="message" class="message" :class="messageType">
            {{ message }}
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            streamId: this.$route.params.streamId, // ID потока из маршрута
            streamName: "", // Название потока
            existingGroups: [], // Группы, уже связанные с потоком
            availableGroups: [], // Группы, не привязанные к потоку
            selectedGroups: [], // Выбранные группы для добавления в поток
            isModalOpen: false, // Флаг открытия модального окна
            message: "", // Сообщение об ошибке или успехе
            messageType: "", // Тип сообщения ('success' или 'error')
        };
    },
    created() {
        this.fetchStreamData();
        this.fetchAvailableGroups();
    },
    methods: {
        // Получаем данные о потоке и группах
        async fetchStreamData() {
            try {
                const response = await axios.get(
                    `http://localhost:8000/getStream?streamId=${this.streamId}`,
                    {
                        withCredentials: true,
                    }
                );
                const streamData = response.data;
                this.streamName = streamData[0].stream_name;
                this.existingGroups = streamData.map((group) => ({
                    group_id: group.group_id,
                    group_name: group.group_name,
                }));
            } catch (error) {
                console.log(error);
                this.message = "Ошибка при загрузке потока.";
                this.messageType = "error";
            }
        },

        // Получаем группы, которые не привязаны к потоку
        async fetchAvailableGroups() {
            try {
                const response = await axios.get(
                    "http://localhost:8000/getGroupsWithoutStream",
                    {
                        withCredentials: true,
                    }
                );
                this.availableGroups = response.data.groups || [];
            } catch (error) {
                console.log(error);
                this.message = "Ошибка при загрузке доступных групп.";
                this.messageType = "error";
            }
        },

        // Открыть модальное окно для добавления групп
        openAddGroupsModal() {
            this.isModalOpen = true;
        },

        // Закрыть модальное окно
        closeAddGroupsModal() {
            this.isModalOpen = false;
            this.selectedGroups = []; // Сбросить выбранные группы
        },

        // Добавить выбранные группы в поток
        async addGroupsToStream() {
            if (this.selectedGroups.length === 0) {
                this.message = "Пожалуйста, выберите хотя бы одну группу.";
                this.messageType = "error";
                return;
            }

            try {
                console.log(this.selectedGroups);
                const response = await axios.post(
                    "http://localhost:8000/addGroupsToStream",
                    {
                        streamId: this.streamId,
                        groupIds: this.selectedGroups,
                    },
                    { withCredentials: true }
                );

                if (response.data.success) {
                    this.message = "Группы успешно добавлены!";
                    this.messageType = "success";
                    this.fetchStreamData(); // Обновляем данные потока
                    this.fetchAvailableGroups(); // Обновляем доступные группы
                    this.closeAddGroupsModal(); // Закрываем модальное окно
                } else {
                    this.message = "Ошибка при добавлении групп.";
                    this.messageType = "error";
                }
            } catch (error) {
                console.log(error);
                this.message = "Произошла ошибка при добавлении групп.";
                this.messageType = "error";
            }
        },

        // Удалить группу из потока
        async removeGroupFromStream(groupId) {
            try {
                const response = await axios.post(
                    "http://localhost:8000/deleteGroupFromStream",
                    {
                        streamId: this.streamId,
                        groupId: groupId,
                    },
                    { withCredentials: true }
                );

                if (response.data.success) {
                    this.message = "Группа успешно удалена!";
                    this.messageType = "success";
                    this.fetchStreamData(); // Обновляем данные потока
                } else {
                    this.message = "Ошибка при удалении группы.";
                    this.messageType = "error";
                }
            } catch (error) {
                console.log(error);
                this.message = "Произошла ошибка при удалении группы.";
                this.messageType = "error";
            }
        },
    },
};
</script>

<style scoped>
.edit-stream-page {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
    font-family: "Roboto", sans-serif;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

button {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin: 10px;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #218838;
}

.add-button {
    background-color: #007bff;
}

.add-button:hover {
    background-color: #0056b3;
}

.remove-button {
    background-color: #dc3545;
}

.remove-button:hover {
    background-color: #c82333;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    width: 400px;
    max-height: 70%;
    overflow-y: auto;
}

.group-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.group-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px solid #ddd;
}

.group-name {
    flex-grow: 1;
    font-size: 16px;
}

.message {
    text-align: center;
    padding: 10px;
    margin-top: 20px;
    border-radius: 5px;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
}

input[type="checkbox"] {
    margin-right: 10px;
}
</style>
