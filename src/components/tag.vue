<template>
  <transition :name="closeTransition ? '' : 'el-zoom-in-center'">
    <span
      class="el-tag"
      @click="handleClick"
      :class="[type ? 'el-tag--' + type : '', {'is-hit': hit}]"
      :style="{backgroundColor: color}">
      <slot></slot>
      <i class="el-tag__close el-icon-close"
        v-if="closable"
        @click.stop="handleClose"></i>
    </span>
  </transition>
</template>
<script>
  export default {
    name: 'slTag',
    props: {
      text: String,
      closable: Boolean,
      type: String,
      hit: Boolean,
      closeTransition: Boolean,
      color: String
    },
    methods: {
      handleClick(event){
        this.$emit('click', event);
      },
      handleClose(event) {
        this.$emit('close', event);
      }
    }
  };
</script>
