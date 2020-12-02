<template>
  <div>
    <slot></slot>

    <license />
  </div>
</template>
<script>
import License from './License';
export default {
  components: {
    License
  }
}
</script>