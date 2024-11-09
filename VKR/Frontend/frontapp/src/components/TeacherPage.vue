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
            <th>Тема ВКР</th>
            <th class="small_col">Подтв. студента</th>
            <th class="small_col">Подтв. преподавателя</th>
            <th class="small_col">Подтв. администратора</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in students" @click="openModal(item)" :key="index" class="table-row">
            <td>{{ item.student_id }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.lastname }}</td>
            <td>{{ item.group_name }}</td>
            <td>{{ item.theme }}</td>
            <td>{{ item.confirmed_student == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td>
            <td>{{ item.confirmed_teacher == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td>
            <td>{{ item.confirmed_admin == true ? 'Подтверждено' : item.confirmed_student == false ? 'Отклонено' : 'Ожидание' }}</td>
          </tr>
        </tbody>
      </table>

        <div v-if="isModalOpen" class="modal-overlay" @click.self="closeModal">
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
</template>

<script>
    import axios from "axios";
    export default{
        data() {
            return {
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
                    console.log(response);
                } catch (error) {
                    this.error = true;
                    console.error("Произошла ошибка:", error);
                }
            },
            openModal(student) {
                this.selectedStudent = {...student};
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
                const response = await axios
                .post("http://localhost:8000/update_state", 
                    {
                      vkr_id: this.selectedStudent.vkr_id,
                      pretheme: this.selectedStudent.pretheme,
                      theme: this.selectedStudent.theme,
                      confirmed_admin: this.selectedStudent.confirmed_admin,
                      confirmed_teacher: this.selectedStudent.confirmed_teacher,
                      confirmed_student: this.selectedStudent.confirmed_student,
                    },
                    {
                        withCredentials: true, // Это позволяет отправлять и получать куки с сервером
                    }
                );

                // Проверка успешного ответа от сервера
                if (response.data.message) {
                  const index = this.students.findIndex(i => i.student_id === this.selectedStudent.student_id);
                  console.log(index);
                  if (index !== -1) {
                    this.students.splice(index, 1, { ...this.selectedStudent }); // Обновляем данные в таблице
                  }
                  this.isModalOpen = false; // Закрываем диалог после сохранения
                } else {
                  console.error("Ошибка при сохранении данных на сервере:", response);
                }
              } catch (error) {
                console.error("Ошибка при сохранении данных:", error);
              }
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