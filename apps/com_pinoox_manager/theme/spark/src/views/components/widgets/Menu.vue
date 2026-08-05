<template>
  <div class="toolbarMenu">
    <Icon :is="iconComponent" class="toolbarMenu__icon" size="sm"/>
    <span class="toolbarMenu__label">{{ label }}</span>
  </div>
</template>

<script setup>
import { computed, markRaw } from 'vue';

const props = defineProps({
  icon: {
    type: [String, Object, Function],
    default: null,
  },
  label: {
    type: String,
    required: true,
  },
});

const iconComponent = computed(() => {
    if (!props.icon) {
        return null;
    }

    if (typeof props.icon === 'string') {
        return props.icon;
    }

    if (typeof props.icon === 'function' || typeof props.icon === 'object') {
        return markRaw(props.icon);
    }

    return props.icon;
});
</script>
