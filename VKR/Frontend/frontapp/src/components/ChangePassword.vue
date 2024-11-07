<template>
    <div class="change-password">
        <h1>СМЕНА ПАРОЛЯ</h1>
        <form @submit.prevent="handleChangePassword">
            <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
            <div>
                <label for="old-password">Старый пароль:</label>
                <input
                    type="password"
                    id="old-password"
                    v-model="oldPassword"
                    required
                />
            </div>
            <div>
                <label for="new-password">Новый пароль:</label>
                <input
                    type="password"
                    id="new-password"
                    v-model="newPassword"
                    required
                />
            </div>
            <div>
                <label for="confirm-password"
                    >Подтверждение нового пароля:</label
                >
                <input
                    type="password"
                    id="confirm-password"
                    v-model="confirmPassword"
                    required
                />
            </div>
            <button type="submit">Изменить пароль</button>
        </form>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            oldPassword: "",
            newPassword: "",
            confirmPassword: "",
            errorMessage: "",
        };
    },
    methods: {
        handleChangePassword() {
            this.errorMessage = "";

            // Проверяем, что новый пароль и подтверждение совпадают
            if (this.newPassword !== this.confirmPassword) {
                this.errorMessage =
                    "Новый пароль и подтверждение не совпадают.";
                return;
            }

            // Отправляем запрос на сервер для смены пароля
            axios
                .post(
                    "http://localhost:8000/changePassword",
                    {
                        oldPassword: this.oldPassword,
                        newPassword: this.newPassword,
                    },
                    {
                        withCredentials: true, // Это позволяет отправлять и получать куки с сервером
                    }
                )
                .then((response) => {
                    if (response.data.success) {
                        // Если пароль успешно изменен, можно перенаправить пользователя на страницу профиля
                        this.$router.push("/profile");
                    } else {
                        console.log(response.data);
                        this.errorMessage =
                            response.data.error || "Ошибка при смене пароля.";
                    }
                })
                .catch((error) => {
                    switch (error.status) {
                        case 401:
                            this.errorMessage = "Не авторизован";
                            break;
                        case 402:
                            this.errorMessage = "Просроченный токен";
                            break;
                        case 404:
                            this.errorMessage = "Нет пользователя";
                            break;
                        case 400:
                            this.errorMessage = "Неверный старый пароль";
                            break;
                        default:
                            this.errorMessage = "Непредвиденная ошибка";
                            break;
                    }
                });
        },
    },
    mounted() {
        // Действия при монтировании страницы (если нужно)
        console.log("Страница для смены пароля загружена");
    },
};
</script>

<style scoped>
.change-password {
    font-family: "Roboto", sans-serif;
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
    width: 100%;
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
    box-sizing: border-box;
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
    font-size: 18px;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
}
</style>
