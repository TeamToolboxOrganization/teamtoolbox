import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import App from './App.vue'

const vuetify = createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#0D47A1',
          secondary: '#1976D2',
          accent: '#00ACC1',
          background: '#F4F7FB'
        }
      }
    }
  }
})

createApp(App).use(vuetify).mount('#app')
