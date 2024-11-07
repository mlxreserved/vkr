<template>
    <div class="teacher-page">
  
      <!-- Заголовок страницы -->
      <h2 class="page-title" v-if="students.length > 0" >Таблица данных</h2>
  
      <!-- Сообщение об ошибке (если данные не удалось загрузить) -->
      <p v-if="error" class="error-message">Произошла ошибка при загрузке данных.</p>
  
      <!-- Таблица с данными -->
      <table v-if="students.length > 0" class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Фамилия</th>
            <th>Группа</th>
            <th>Тематика ВКР</th>
            <th>Тема ВКР</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in students" @click="openModal(item)" :key="index" class="table-row">
            <td>{{ item.student_id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.lastname }}</td>
            <td>{{ item.group_name }}</td>
            <td>{{ item.pretheme }}</td>
            <td>{{ item.theme }}</td>
          </tr>
        </tbody>
      </table>

        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
            <div class="modal-content">
                <h3>Информация о студенте</h3>
                <p><strong>Тематика:</strong> {{ selectedStudent.pretheme }}</p>
                <p><strong>Тема:</strong> {{ selectedStudent.theme }}</p>
                <button @click="closeModal">Закрыть</button>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    export default{
        data() {
            return {
                teacherName: "Иванов Иван Иванович", // Динамическое имя преподавателя
                students: [], // Данные для таблицы
                error: false, // Индикатор ошибки
                isModalOpen: false,   // Состояние модального окна
                selectedStudent: {},
            };
        },
        methods: {
            async fetchData() {
                try {
                    const response = await axios.get("http://localhost:8000/students", {
                        withCredentials: true, // Включаем отправку куки
                    }); // Запрос к API

                    this.students = response.data; // Заполняем таблицу данными
                    
                } catch (error) {
                    this.error = true;
                    console.error("Произошла ошибка:", error);
                }
            },
            openModal(student) {
                this.selectedStudent = student;
                this.isModalOpen = true;
            },
            closeModal() {
                this.isModalOpen = false;
            },
        },
        mounted() {
            this.fetchData();
        }
    }
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

.table-row {
  cursor: pointer;
}
.table-row:hover {
  background-color: #f0f0f0;
}

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

/* Таблица данных */
.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.data-table th, .data-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

.data-table th {
  background-color: #007bff;
  color: white;
}

.data-table tr:nth-child(even) {
  background-color: #f2f2f2;
}
</style>