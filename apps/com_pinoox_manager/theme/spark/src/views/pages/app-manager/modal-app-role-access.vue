<template>
  <SimpleModal :title="translate('app_users_role_access_title')" size="md" class="modalAppRoleAccess">
    <p class="appRoleAccess__hint">{{ translate('app_users_role_access_hint') }}</p>
    <strong class="appRoleAccess__role">{{ role.name }}</strong>

    <div v-if="permissions.length" class="appRoleAccess__list">
      <label v-for="permission in permissions" :key="permission.permission_id" class="appRoleAccess__check">
        <input v-model="selected" type="checkbox" :value="permission.permission_id"/>
        <span>
          <strong>{{ permission.name }}</strong>
          <small v-if="permission.key !== permission.name" dir="ltr">{{ permission.key }}</small>
        </span>
      </label>
    </div>
    <p v-else class="appRoleAccess__empty">{{ translate('app_users_no_permissions') }}</p>

    <template #footer>
      <Button
          :label="translate('cancel')"
          variant="dark"
          outline
          :is-disabled="saving"
          @click="closeModal"
      />
      <Button
          :label="translate('app_users_form_save')"
          variant="primary"
          :is-loading="saving"
          :is-disabled="!permissions.length"
          @click="save"
      />
    </template>
  </SimpleModal>
</template>

<script setup>
defineOptions({modalGroup: 'default'});

import {ref} from 'vue';
import {closeModal, useModalContext} from '@kolirt/vue-modal';
import SimpleModal from '@/views/components/commons/SimpleModal.vue';
import Button from '@/views/components/widgets/Button.vue';
import {userAPI} from '@api/user.js';
import {unwrapResponse} from '@utils/helpers/apiHelper.js';
import {resolveApiFailure} from '@utils/apiEnvelope.js';
import {toastError, toastSuccess} from '@utils/helpers/toastHelper.js';
import {translate} from '@utils/helpers/managerLang.js';

const props = defineProps({
  packageName: {type: String, required: true},
  role: {type: Object, required: true},
  permissions: {type: Array, default: () => []},
});

const {confirm} = useModalContext();
const saving = ref(false);
const selected = ref((props.role.permissions || []).map((permission) => permission.permission_id));

async function save() {
  if (saving.value) {
    return;
  }

  saving.value = true;

  try {
    unwrapResponse(await userAPI.saveRolePermissions(
        props.packageName,
        props.role.role_id,
        selected.value.map((id) => Number(id)),
    ));
    toastSuccess(translate('app_users_role_access_saved'));
    confirm();
  } catch (error) {
    toastError(resolveApiFailure(error) || translate('app_users_save_failed'));
  } finally {
    saving.value = false;
  }
}
</script>
