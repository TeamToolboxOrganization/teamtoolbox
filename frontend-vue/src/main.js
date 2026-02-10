import { createApp } from 'vue'
import { createVuetify } from 'vuetify'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import App from './App.vue'

const vuetify = createVuetify({
  theme: {
    defaultTheme: 'teamtoolbox',
    themes: {
      teamtoolbox: {
        dark: false,
        colors: {
          primary: '#1E40AF',
          secondary: '#0F766E',
          accent: '#0284C7',
          surface: '#FFFFFF',
          background: '#F3F6FB',
          info: '#6366F1',
          success: '#16A34A',
          warning: '#D97706'
        }
      }
    }
  },
  defaults: {
    VCard: {
      rounded: 'xl',
      elevation: 2
    },
    VBtn: {
      rounded: 'lg'
    }
  }
})

createApp(App).use(vuetify).mount('#app')
