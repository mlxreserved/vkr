<template>
    <div class="login">
        <h1>АВТОРИЗАЦИЯ</h1>
        <form @submit.prevent="handleLogin">
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
            <div>
                <label for="username">Логин:</label>
                <input id="username" v-model="username" required />
            </div>
            <div>
                <label for="password">Пароль:</label>
                <input
                    type="password"
                    id="password"
                    v-model="password"
                    required
                />
            </div>
            <button type="submit">Войти</button>
        </form>
    </div>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            username: "",
            password: "",
            errorMessage: "",
        };
    },
    methods: {
        handleLogin() {
            this.errorMessage = "";

            // Используем axios для отправки POST-запроса
            axios
                .post(
                    "http://localhost:8000/login",
                    {
                        login: this.username,
                        password: this.password,
                    },
                    {
                        withCredentials: true, // Это позволяет отправлять и получать куки с сервером
                    }
                )
                .then((response) => {
                    // Проверяем, если ответ успешный и есть данные
                    if (response.data) {
                        console.log(response);
                        this.$router.push("/profile"); // Перенаправляем на страницу профиля
                    } else if (response.data.error) {
                        console.log(response.data.error);
                        this.errorMessage = response.data.error;
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    this.errorMessage = "An unexpected error occurred.";
                });
        },
    },
    mounted() {
        this.$router.push("/login");
    },
};
</script>

<style scoped>
.login {
    font-family: "Finlandica", sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    width: 300px;
    margin: auto;
}

h1 {
    margin-bottom: 20px;
    color: #333;
    font-weight: normal;
}

form {
    width: 100%; /* Обеспечивает полную ширину */
}

label {
    display: block;
    margin-bottom: 5px;
    color: #555;
}

input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
    box-sizing: border-box; /* Учитываем паддинги и границы */
}

input:focus {
    border-color: #0d3c6e;
    outline: none;
}

.error {
    color: red;
    margin-bottom: 15px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 18x;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
}
</style>
