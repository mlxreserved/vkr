<template>
    <div class="create-stream-page">
      <h2 class="page-title">Создание потока</h2>
  
      <!-- Ошибка при создании -->
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>
  
      <!-- Форма создания потока -->
      <div v-if="groups.length > 0">
        <form @submit.prevent="createStream" class="create-stream-form">
          <!-- Название потока -->
          <div class="form-group">
            <label for="stream-name">Название потока</label>
            <input
              type="text"
              id="stream-name"
              v-model="streamName"
              required
              placeholder="Введите название потока"
              class="form-control"
            />
          </div>
  
          <!-- Выбор групп -->
          <div class="form-group">
            <label>Выберите группы</label>
            <div v-for="group in groups" :key="group.group_id" class="group-option">
              <input
                type="checkbox"
                :id="'group-' + group.group_id"
                v-model="selectedGroups"
                :value="group.group_id"
                class="group-checkbox"
              />
              <label :for="'group-' + group.group_id" class="group-label">{{ group.group_name }}</label>
            </div>
          </div>
  
          <!-- Кнопка создания потока -->
          <button type="submit" class="btn btn-primary">Создать поток</button>
        </form>
      </div>
  
      <!-- Если нет доступных групп -->
      <div v-else>
        <p>Нет доступных групп для добавления в поток.</p>
      </div>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        streamName: "",
        selectedGroups: [], // Массив для выбранных групп
        groups: [], // Массив с группами, загруженными из localStorage
        errorMessage: "",
      };
    },
    created() {
      const groupsStr = localStorage.getItem("groups");
  
      if (groupsStr) {
        this.groups = JSON.parse(groupsStr);
      } else {
        this.errorMessage = "Нет доступных групп для создания потока.";
      }
    },
    methods: {
      async createStream() {
        if (this.selectedGroups.length === 0) {
          this.errorMessage = "Выберите хотя бы одну группу.";
          return;
        }
  
        // Приводим все ID групп к числовому типу
        const groups = this.selectedGroups.map(group => Number(group));
        
        const payload = {
          stream_name: this.streamName,
          groups: groups,
        };
  
        // Логирование payload перед отправкой
        console.log('Payload отправляется на сервер:', payload);
  
        try {
          const response = await axios.post(
            "http://localhost:8000/createStream",
            payload,
            {
              withCredentials: true,
            }
          );
  
          if (response.data.success) {
            // Очистить groups из localStorage после успешного создания потока
            localStorage.removeItem("groups");
  
            // Перенаправить на страницу потока
            this.$router.push("/streams");
          } else {
            this.errorMessage = response.data.error || "Ошибка при создании потока.";
          }
        } catch (error) {
          console.error(error);
          this.errorMessage = "Произошла ошибка при создании потока.";
        }
      },
    },
  };
  </script>
  
  <style scoped>
  .create-stream-page {
    max-width: 800px;
    margin: 40px auto;
    font-family: "Roboto", sans-serif;
    padding: 20px;
  }
  
  .page-title {
    text-align: center;
    color: #333;
    font-size: 2rem;
    margin-bottom: 20px;
  }
  
  .create-stream-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  
  .form-group {
    display: flex;
    flex-direction: column;
  }
  
  label {
    font-size: 1.1rem;
    margin-bottom: 8px;
  }
  
  .input,
  select {
    padding: 10px;
    font-size: 1rem;
    border-radius: 5px;
    border: 1px solid #ccc;
  }
  
  input[type="text"],
  select {
    width: 100%;
  }
  
  button {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.1rem;
    width: 100%;
    transition: background-color 0.3s ease;
  }
  
  button:hover {
    background-color: #218838;
  }
  
  .group-option {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }
  
  .group-checkbox {
    margin-right: 10px;
  }
  
  .group-label {
    font-size: 1rem;
  }
  
  .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    text-align: center;
    margin-bottom: 20px;
  }
  
  p {
    text-align: center;
    font-size: 1.2rem;
    color: #888;
  }
  </style>
  