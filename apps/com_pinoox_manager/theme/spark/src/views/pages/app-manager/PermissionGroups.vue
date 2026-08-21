<template>
  <div class="permGroups">
    <section v-for="group in groups" :key="group.prefix || '_other'" class="permGroups__group">
      <header class="permGroups__head">
        <span class="permGroups__dot" aria-hidden="true"/>
        <strong class="permGroups__title" dir="ltr">{{ groupTitle(group.prefix) }}</strong>
      </header>

      <div v-if="mode === 'checks'" class="permGroups__checks">
        <label
            v-for="entry in group.items"
            :key="String(entry.id)"
            class="permGroups__check"
        >
          <input v-model="selected" type="checkbox" :value="entry.id"/>
          <span>
            <strong>{{ entry.label }}</strong>
            <small dir="ltr">{{ entry.key }}</small>
          </span>
        </label>
      </div>

      <div v-else class="permGroups__items">
        <span
            v-for="entry in group.items"
            :key="entry.key"
            class="permGroups__chip"
            dir="ltr"
            :title="entry.key"
        >
          {{ entry.label }}
        </span>
      </div>
    </section>
  </div>
</template>

<script setup>
import {computed} from 'vue';
import {groupPermissions} from '@utils/helpers/permissionGroups.js';
import {translate} from '@utils/helpers/managerLang.js';

const props = defineProps({
  items: {type: Array, default: () => []},
  mode: {
    type: String,
    default: 'chips',
    validator: (value) => ['chips', 'checks'].includes(value),
  },
  modelValue: {type: Array, default: () => []},
});

const emit = defineEmits(['update:modelValue']);

const groups = computed(() => groupPermissions(props.items));

const selected = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

function groupTitle(prefix) {
  return prefix || translate('app_users_perm_other');
}
</script>
