import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'


const app = createApp(App)

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

app.use(router)
window.Pusher = Pusher
// Pusher.logToConsole = true  
Pusher.logToConsole = import.meta.env.DEV


window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    enabledTransports: ['ws'],
        authEndpoint: 'http://localhost:8000/api/broadcasting/auth',  // ← Laravel, pas Vue

auth: {
        headers: {
            get Authorization() { 
                            return `Bearer ${localStorage.getItem('jwt_token')}`
}
            // Authorization: `Bearer ${localStorage.getItem('jwt_token')}`
        }
    }
})
app.mount('#app')