import DefaultTheme from 'vitepress/theme'
import './custom.css'
import Landing from './Landing.vue'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('Landing', Landing)
  },
}
