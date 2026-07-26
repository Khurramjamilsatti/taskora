import { createApp } from 'vue'
import './assets/styles/main.css'
import './assets/styles/auth.css'
import './assets/styles/forms.css'
import './assets/styles/catalogue.css'
import './assets/styles/dashboard.css'
import './assets/styles/feedback.css'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')
