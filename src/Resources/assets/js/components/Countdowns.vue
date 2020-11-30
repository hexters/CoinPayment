<template>
  <countdown :time="timeout">
      <template slot-scope="props">
          <strong>{{ props.minutes }}m {{ props.seconds }}s</strong>
      </template>
  </countdown>
</template>
<script>
export default {
  props: ['timeout'],
  mounted() {
    console.log(this.timeout);
  }
}
</script>