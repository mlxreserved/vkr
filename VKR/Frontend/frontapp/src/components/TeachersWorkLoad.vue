<template>
    <div class="teacher-workload">
      <h2 class="header">Занятость учителей</h2>
      <table class="workload-table">
        <thead>
          <tr>
            <th>Учитель</th>
            <th v-for="(stream, index) in streams" :key="index">{{ stream }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="teacher in teacherStreams" :key="teacher.name">
            <td class="teacher-name">{{ teacher.name }}</td>
            <td v-for="(count, stream) in teacher.streams" :key="stream" class="stream-count">
              {{ count }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>
  
  <script>
  // Импортируем axios для работы с запросами
  import axios from "axios";
  
  export default {
    data() {
      return {
        teacherStreams: [],
        streams: [] // Массив всех уникальных потоков
      };
    },
    created() {
      this.fetchTeacherData();
    },
    methods: {
      async fetchTeacherData() {
        try {
          const response = await axios.get('http://localhost:8000/workload', {
            withCredentials: true
          });
          this.teachersData = response.data;
  
          // Обрабатываем данные
          this.processTeacherData();
        } catch (error) {
          console.error('Ошибка при получении данных:', error);
        }
      },
      processTeacherData() {
        const teacherStreams = {};
        const allStreams = new Set(); // Множество для всех потоков
  
        this.teachersData.forEach(item => {
          const teacherId = item.teacher_id;
          const streamName = item.stream_name;
  
          if (!teacherStreams[teacherId]) {
            teacherStreams[teacherId] = {
              name: `${item.name} ${item.lastname}`,
              streams: {}
            };
          }
  
          if (!teacherStreams[teacherId].streams[streamName]) {
            teacherStreams[teacherId].streams[streamName] = 0;
          }
          teacherStreams[teacherId].streams[streamName] += 1;
  
          // Добавляем поток в множество
          allStreams.add(streamName);
        });
  
        // Преобразуем объект в массив
        this.teacherStreams = Object.values(teacherStreams);
  
        // Получаем массив всех потоков и сортируем их
        this.streams = Array.from(allStreams).sort();
      }
    }
  };
  </script>
  
  <style scoped>
  .teacher-workload {
    padding: 20px;
    font-family: 'Arial', sans-serif;
    margin-top: 60px; /* Добавляем отступ сверху, чтобы не перекрывалось шапкой */
  }
  
  .header {
    text-align: center;
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
  }
  
  .workload-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
  
  .workload-table th,
  .workload-table td {
    padding: 12px 15px;
    text-align: center;
    border: 1px solid #ddd;
  }
  
  .workload-table th {
    background-color: #007bff;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
  }
  
  .workload-table td {
    background-color: #f9f9f9;
    font-size: 14px;
  }
  
  .workload-table tr:nth-child(even) td {
    background-color: #f1f1f1;
  }
  
  .workload-table tr:hover td {
    background-color: #e9ecef;
    cursor: pointer;
  }
  
  .teacher-name {
    font-weight: bold;
  }
  
  .stream-count {
    font-size: 16px;
    color: #555;
  }
  </style>
  