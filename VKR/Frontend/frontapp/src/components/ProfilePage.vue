<template>
    <div class="profile-container">
        <div class="user-profile">
            <h1>Профиль пользователя</h1>
            <div class="profile-info">
                <label for="username">Имя:</label>
                <input
                    id="username"
                    v-model="username"
                    placeholder="Введите ваше имя"
                />
            </div>
            <div class="profile-info">
                <label for="theme">Тема:</label>
                <input
                    id="theme"
                    v-model="theme"
                    placeholder="Введите вашу тему"
                />
            </div>
            <button @click="saveChanges">Сохранить изменения</button>
            <p v-if="message">{{ message }}</p>
        </div>
    </div>
</template>

<script>


export default {
    data() {
        return {
            username: "",
            theme: "",
            message: "",
        };
    },
    created() {
        // Загружаем данные из localStorage при создании компонента
        const savedUsername = localStorage.getItem("username");
        const savedTheme = localStorage.getItem("theme");

        if (savedUsername) {
            this.username = savedUsername;
        }
        if (savedTheme) {
            this.theme = savedTheme;
        }
    },
    methods: {
        saveChanges() {
            // Сохраняем данные в localStorage
            localStorage.setItem("username", this.username);
            localStorage.setItem("theme", this.theme);
            this.message = "Изменения сохранены!";
        },
    },
};
</script>

<style scoped>
.profile-container {
    display: flex;
    justify-content: center; /* Центрируем по горизонтали */
    align-items: center; /* Центрируем по вертикали */
    height: 100vh; /* Высота на всю страницу */
    background-color: #f0f0f0; /* Цвет фона страницы */
}

.user-profile {
    max-width: 400px;
    margin: auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #ffffff; /* Цвет фона профиля */
}

.profile-info {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
}

input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 10px 15px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #0056b3;
}

p {
    margin-top: 10px;
    color: green;
}
</style>
