<template>
  <SimpleModal :title="title" size="md" class="modalAppUser">
    <form class="appUserForm" @submit.prevent="save">
      <div class="appUserForm__row">
        <Input v-model="form.fname" variant="glass" :label="translate('app_users_form_fname')"/>
        <Input v-model="form.lname" variant="glass" :label="translate('app_users_form_lname')"/>
      </div>
      <Input
          v-model="form.username"
          variant="glass"
          :label="translate('app_users_form_username')"
          direction="ltr"
          autocomplete="off"
      />
      <Input
          v-model="form.email"
          type="email"
          variant="glass"
          :label="translate('app_users_form_email')"
          direction="ltr"
          autocomplete="off"
      />
      <Input
          v-model="form.mobile"
          variant="glass"
          :label="translate('app_users_form_mobile')"
          direction="ltr"
      />
      <Input
          v-model="form.password"
          type="password"
          variant="glass"
          :label="translate('app_users_form_password')"
          direction="ltr"
          autocomplete="new-password"
          show-password-toggle
      />
      <p v-if="isEdit" class="appUserForm__hint">{{ translate('app_users_form_password_hint') }}</p>

      <DarkSelect
          v-model="form.status"
          :label="translate('app_users_form_status')"
          :options="statusOptions"
          direction="rtl"
      />

      <DarkSelect
          v-if="hasGroups"
          v-model="form.group_key"
          :label="translate('app_users_form_group')"
          :options="groupOptions"
          direction="rtl"
      />

      <div v-if="hasRoles" class="appUserForm__checks">
        <span class="appUserForm__checksLabel">{{ translate('app_users_form_roles') }}</span>
        <label v-for="role in meta.roles" :key="role.role_id" class="appUserForm__check">
          <input v-model="form.role_ids" type="checkbox" :value="role.role_id"/>
          <span>{{ role.name }}</span>
        </label>
      </div>
    </form>

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
          @click="save"
      />
    </template>
  </SimpleModal>
</template>

<script setup>
defineOptions({modalGroup: 'default'});

import {computed, reactive, ref} from 'vue';
import {closeModal, useModalContext} from '@kolirt/vue-modal';
import SimpleModal from '@/views/components/commons/SimpleModal.vue';
import Input from '@/views/components/form/Input.vue';
import DarkSelect from '@/views/components/form/DarkSelect.vue';
import Button from '@/views/components/widgets/Button.vue';
import {userAPI} from '@api/user.js';
import {unwrapResponse} from '@utils/helpers/apiHelper.js';
import {resolveApiFailure} from '@utils/apiEnvelope.js';
import {toastError, toastSuccess} from '@utils/helpers/toastHelper.js';
import {translate} from '@utils/helpers/managerLang.js';

const props = defineProps({
  packageName: {type: String, required: true},
  user: {type: Object, default: null},
  meta: {type: Object, default: () => ({})},
});

const {confirm} = useModalContext();
const saving = ref(false);
const isEdit = computed(() => Boolean(props.user?.user_id));
const title = computed(() => translate(isEdit.value ? 'app_users_form_edit_title' : 'app_users_form_add_title'));
const hasGroups = computed(() => Boolean(props.meta?.has_groups));
const hasRoles = computed(() => Boolean(props.meta?.has_roles));

const statusOptions = computed(() => (props.meta?.statuses || ['active', 'inactive', 'suspend', 'pending']).map((value) => ({
  value,
  label: translate(`app_users_status_${value}`),
})));

const groupOptions = computed(() => [
  {value: '', label: translate('app_users_form_group_none')},
  ...(props.meta?.groups || []).map((group) => ({
    value: group.key,
    label: group.label || group.key,
  })),
]);

const form = reactive({
  fname: props.user?.fname || '',
  lname: props.user?.lname || '',
  username: props.user?.username || '',
  email: props.user?.email || '',
  mobile: props.user?.mobile || '',
  password: '',
  status: props.user?.status || 'active',
  group_key: props.user?.group_key || '',
  role_ids: (props.user?.roles || []).map((role) => role.role_id),
});

async function save() {
  if (saving.value) {
    return;
  }

  saving.value = true;

  const payload = {
    fname: form.fname,
    lname: form.lname,
    username: form.username,
    email: form.email,
    mobile: form.mobile,
    status: form.status,
    group_key: form.group_key,
    role_ids: form.role_ids.map((id) => Number(id)),
  };

  if (form.password) {
    payload.password = form.password;
  }

  try {
    if (isEdit.value) {
      unwrapResponse(await userAPI.updateUser(props.packageName, props.user.user_id, payload));
    } else {
      unwrapResponse(await userAPI.createUser(props.packageName, payload));
    }

    toastSuccess(translate('app_users_saved'));
    confirm();
  } catch (error) {
    toastError(resolveApiFailure(error) || translate('app_users_save_failed'));
  } finally {
    saving.value = false;
  }
}
</script>
