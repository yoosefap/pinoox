<template>
  <SimpleModal :title="translate('app_users_delete_title')" size="sm" class="modalAppUserDelete">
    <div class="modalAppUserDelete__body">
      <p class="modalAppUserDelete__lead">{{ translate('app_users_delete_lead') }}</p>
      <p class="modalAppUserDelete__hint">{{ translate('app_users_delete_hint') }}</p>
      <div class="modalAppUserDelete__preview">
        <strong>{{ user.full_name || user.username || user.email }}</strong>
        <span v-if="user.email" dir="ltr">{{ user.email }}</span>
      </div>
    </div>

    <template #footer>
      <Button
          :label="translate('cancel')"
          variant="dark"
          outline
          :is-disabled="deleting"
          @click="closeModal"
      />
      <Button
          :label="translate('app_users_delete_confirm')"
          variant="danger"
          :is-loading="deleting"
          @click="remove"
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
  user: {type: Object, required: true},
});

const {confirm} = useModalContext();
const deleting = ref(false);

async function remove() {
  if (deleting.value) {
    return;
  }

  deleting.value = true;

  try {
    unwrapResponse(await userAPI.deleteUser(props.packageName, props.user.user_id));
    toastSuccess(translate('app_users_deleted'));
    confirm();
  } catch (error) {
    toastError(resolveApiFailure(error) || translate('app_users_delete_failed'));
  } finally {
    deleting.value = false;
  }
}
</script>
