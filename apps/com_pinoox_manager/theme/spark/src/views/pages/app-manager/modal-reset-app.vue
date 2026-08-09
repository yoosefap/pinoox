<template>
  <SimpleModal :title="translate('are_you_sure_reset_app')" size="sm" class="modalAppReset">
    <div
        class="modalAppReset__body"
        :class="{
          'is-busy': isBusy,
          'is-done': isDone,
        }"
    >
      <div class="modalAppReset__hero" aria-hidden="true">
        <span class="modalAppReset__iconRing"/>
        <span class="modalAppReset__iconRing modalAppReset__iconRing--delayed"/>
        <span class="modalAppReset__iconWrap">
          <Icon :is="saxIcon.refresh" class="modalAppReset__icon"/>
        </span>
      </div>

      <p class="modalAppReset__lead">{{ translate('app_reset_lead') }}</p>
      <p class="modalAppReset__hint">{{ translate('app_reset_hint') }}</p>

      <div class="modalAppReset__preview">
        <AppIcon v-bind="appIconProps(app)" size="md"/>
        <div class="modalAppReset__previewText">
          <strong>{{ app?.name || packageName }}</strong>
          <span dir="ltr">{{ packageName }}</span>
        </div>
      </div>

      <label class="modalAppReset__option">
        <input v-model="purgeStorage" type="checkbox" :disabled="isBusy || isDone"/>
        <span>
          <strong>{{ translate('app_reset_purge_storage_title') }}</strong>
          <small>{{ translate('app_reset_purge_storage_hint') }}</small>
        </span>
      </label>

      <p v-if="isBusy" class="modalAppReset__status" role="status" aria-live="polite">
        <span class="modalAppReset__statusDot"/>
        {{ translate('app_reset_progress') }}
      </p>
    </div>

    <template #footer>
      <Button
          :label="translate('cancel')"
          variant="dark"
          outline
          :is-disabled="isBusy || isDone"
          @click="closeModal"
      />
      <Button
          :label="translate('app_reset_confirm')"
          variant="warning"
          :is-loading="isBusy"
          :is-disabled="isDone"
          @click="confirmReset"
      />
    </template>
  </SimpleModal>
</template>

<script setup>
defineOptions({modalGroup: 'default'});

import {nextTick, ref} from 'vue';
import {closeModal, useModalContext} from '@kolirt/vue-modal';
import {saxIcon} from '@/const/icons.js';
import Button from '@/views/components/widgets/Button.vue';
import Icon from '@/views/components/widgets/Icon.vue';
import SimpleModal from '@/views/components/commons/SimpleModal.vue';
import AppIcon from '@/views/components/widgets/AppIcon.vue';
import {appAPI} from '@api/app.js';
import {appIconProps} from '@utils/helpers/appIconProps.js';
import {translate} from '@utils/helpers/managerLang.js';
import {HTTP_ALERT_SILENT} from '@utils/helpers/alertHelper.js';
import {unwrapResponse} from '@utils/helpers/apiHelper.js';
import {resolveApiFailure} from '@utils/apiEnvelope.js';
import {toastError} from '@utils/helpers/toastHelper.js';

const props = defineProps({
  app: {
    type: Object,
    required: true,
  },
  packageName: {
    type: String,
    required: true,
  },
});

const {confirm} = useModalContext();

const isBusy = ref(false);
const isDone = ref(false);
const purgeStorage = ref(true);

const confirmReset = async () => {
  if (isBusy.value || isDone.value) {
    return;
  }

  isBusy.value = true;
  await nextTick();

  try {
    const response = await appAPI.reset(props.packageName, {
      purge_storage: purgeStorage.value,
    }, HTTP_ALERT_SILENT);
    unwrapResponse(response);
    isDone.value = true;
    await new Promise((resolve) => setTimeout(resolve, 420));
    confirm({reset: true});
  } catch (error) {
    toastError(resolveApiFailure(error));
  } finally {
    isBusy.value = false;
  }
};
</script>
