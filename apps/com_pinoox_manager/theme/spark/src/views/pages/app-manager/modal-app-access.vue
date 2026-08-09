<template>
  <SimpleModal :title="translate('app_users_access_title')" size="md" class="modalAppAccess">
    <p class="modalAppAccess__hint">{{ translate('app_users_access_hint') }}</p>

    <section v-if="meta.has_groups" class="modalAppAccess__block">
      <h3>{{ translate('app_users_groups_title') }}</h3>
      <ul class="modalAppAccess__list">
        <li v-for="group in meta.groups" :key="group.key">
          <strong>{{ group.label || group.key }}</strong>
          <PermissionGroups v-if="group.permissions?.length" :items="group.permissions"/>
        </li>
      </ul>
    </section>

    <section v-if="meta.has_roles" class="modalAppAccess__block">
      <h3>{{ translate('app_users_roles_title') }}</h3>
      <ul class="modalAppAccess__list">
        <li v-for="role in meta.roles" :key="role.role_id">
          <div class="modalAppAccess__row">
            <div>
              <strong>{{ role.name }}</strong>
              <small dir="ltr">{{ role.key }}</small>
            </div>
            <button
                v-if="meta.has_permissions"
                type="button"
                class="modalAppAccess__edit"
                @click="editRole(role)"
            >
              {{ translate('app_users_edit_role') }}
            </button>
          </div>
          <PermissionGroups v-if="role.permissions?.length" :items="role.permissions"/>
          <span v-else class="modalAppAccess__empty">{{ translate('app_users_no_permissions') }}</span>
        </li>
      </ul>
    </section>

    <section v-if="meta.has_permissions" class="modalAppAccess__block">
      <h3>{{ translate('app_users_permissions_title') }}</h3>
      <PermissionGroups :items="meta.permissions"/>
    </section>

    <p v-if="!meta.has_groups && !meta.has_roles && !meta.has_permissions" class="modalAppAccess__empty">
      {{ translate('app_users_access_empty') }}
    </p>

    <template #footer>
      <Button :label="translate('cancel')" variant="dark" outline @click="closeModal"/>
    </template>
  </SimpleModal>
</template>

<script setup>
defineOptions({modalGroup: 'default'});

import {closeModal, openModal} from '@kolirt/vue-modal';
import SimpleModal from '@/views/components/commons/SimpleModal.vue';
import Button from '@/views/components/widgets/Button.vue';
import {translate} from '@utils/helpers/managerLang.js';
import PermissionGroups from './PermissionGroups.vue';
import ModalAppRoleAccess from './modal-app-role-access.vue';

const props = defineProps({
  packageName: {type: String, required: true},
  meta: {type: Object, required: true},
  onUpdated: {type: Function, default: null},
});

function editRole(role) {
  openModal(ModalAppRoleAccess, {
    props: {
      packageName: props.packageName,
      role,
      permissions: props.meta.permissions || [],
    },
  }).then(() => props.onUpdated?.()).catch(() => {});
}
</script>
