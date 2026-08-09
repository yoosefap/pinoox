<template>
  <div class="appDetails">
    <section class="appDetails__overview">
      <div class="appDetails__about">
        <div class="appDetails__aboutHead">
          <h3 class="appDetails__cardTitle">{{ translate('app_details_about') }}</h3>
          <div v-if="badges.length" class="appDetails__badges">
            <span v-for="badge in badges" :key="badge.label" class="appDetails__badge" :class="badge.class">
              {{ badge.label }}
            </span>
          </div>
        </div>
        <p v-if="app?.description" class="appDetails__description">{{ app.description }}</p>
        <p v-else class="appDetails__muted">{{ translate('app_details_no_description') }}</p>

        <dl class="appDetails__meta">
          <div v-for="item in statItems" :key="item.label" class="appDetails__metaItem">
            <dt>{{ item.label }}</dt>
            <dd :dir="item.ltr ? 'ltr' : undefined">
              {{ item.value }}<span v-if="item.hint" class="appDetails__metaHint">{{ item.hint }}</span>
            </dd>
          </div>
        </dl>
      </div>
    </section>

    <section class="appDetails__card appDetails__addresses">
      <header class="appDetails__cardHead">
        <div class="appDetails__cardHeadMain">
          <Icon :is="saxIcon.routes" size="sm"/>
          <h3 class="appDetails__cardTitle">{{ translate('app_details_addresses') }}</h3>
          <span class="appDetails__count">{{ routes.length }}</span>
        </div>
        <button type="button" class="appDetails__textLink" @click="goRoutes">
          {{ translate('app_details_manage_routes') }}
        </button>
      </header>

      <ul v-if="routes.length" class="appDetails__routeList">
        <li v-for="route in routes" :key="route.path" class="appDetails__route" dir="ltr">
          <span class="appDetails__live" aria-hidden="true"/>
          <a
              class="appDetails__url"
              dir="ltr"
              :href="buildRouteUrl(route)"
              target="_blank"
              rel="noopener noreferrer"
              :title="translate('route_url_open')"
          >
            <span class="appDetails__urlOrigin">{{ currentSite }}</span>
            <span class="appDetails__urlPath">{{ routeUrlSuffix(route.path) }}</span>
          </a>
          <span v-if="isHomeRoute(route)" class="appDetails__homeBadge">
            {{ translate('app_details_home_badge') }}
          </span>
          <div class="appDetails__routeActions">
            <span
                v-if="isCopied(route)"
                class="appDetails__copied"
                role="status"
                aria-live="polite"
            >
              {{ translate('route_url_copied') }}
            </span>
            <button
                type="button"
                class="appDetails__iconBtn"
                :class="{'is-copied': isCopied(route)}"
                :title="isCopied(route) ? translate('route_url_copied') : translate('route_url_copy')"
                @click="copyRouteUrl(route)"
            >
              <Icon :is="isCopied(route) ? saxIcon.notifySuccess : saxIcon.copy" size="xs"/>
            </button>
            <a
                class="appDetails__iconBtn"
                :href="buildRouteUrl(route)"
                target="_blank"
                rel="noopener noreferrer"
                :title="translate('route_url_open')"
            >
              <Icon :is="saxIcon.externalLink" size="xs"/>
            </a>
          </div>
        </li>
      </ul>

      <div v-else class="appDetails__empty">
        <Icon :is="saxIcon.routes" size="md"/>
        <p>{{ translate('app_details_addresses_empty') }}</p>
        <span>{{ translate('app_details_addresses_empty_hint') }}</span>
        <button type="button" class="appDetails__quickBtn appDetails__quickBtn--primary" @click="goRoutes">
          {{ translate('app_details_manage_routes') }}
        </button>
      </div>
    </section>
  </div>
</template>

<script setup>
import {computed, onUnmounted, ref} from 'vue';
import {getUrl} from '@/boot.js';
import {saxIcon} from '@/const/icons.js';
import Icon from '@/views/components/widgets/Icon.vue';
import {resolveRouterMode} from '@utils/helpers/appRoutePolicy.js';
import {useControlPanelNavigation} from '@/views/composables/useControlPanelNavigation.js';
import {translate} from '@utils/helpers/managerLang.js';
import {normalizeAppRoutes} from '@utils/appRoutes.js';
import {formatSiteOriginForDisplay} from '@utils/helpers/siteUrlHelper.js';

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

const {pushControlPath} = useControlPanelNavigation();

const siteUrl = String(getUrl().SITE ?? '').replace(/\/+$/, '');
const currentSite = formatSiteOriginForDisplay(siteUrl);
const copiedPath = ref(null);
let copiedTimer = null;

const isSystemApp = computed(() => !!(props.app?.sys_app ?? props.app?.['sys-app']));
const routes = computed(() => normalizeAppRoutes(props.app?.routes));

const routerModeLabel = computed(() => {
  const mode = resolveRouterMode(props.app);

  return mode === 'single'
      ? translate('app_routing_single')
      : translate('app_routing_multiple');
});

const statItems = computed(() => [
  {
    label: translate('app_stat_version'),
    value: props.app?.version || '—',
    hint: props.app?.version_code != null && props.app?.version_code !== '' ? `#${props.app.version_code}` : null,
    ltr: true,
  },
  {label: translate('app_stat_developer'), value: props.app?.developer || '—', ltr: false},
  {label: translate('app_stat_package'), value: props.packageName, ltr: true},
  {label: translate('app_stat_routing'), value: routerModeLabel.value, ltr: false},
]);

const badges = computed(() => {
  const list = [];

  if (isSystemApp.value) {
    list.push({label: translate('app_badge_system'), class: 'is-system'});
  }

  if (props.app?.hidden) {
    list.push({label: translate('app_badge_hidden'), class: 'is-muted'});
  }

  if (props.app?.dock === false) {
    list.push({label: translate('app_badge_no_dock'), class: 'is-muted'});
  }

  return list;
});

function isHomeRoute(route) {
  return route?.path === '/' || route?.path === '';
}

function routeUrlSuffix(path) {
  if (!path || path === '/') {
    return '/';
  }

  return path.startsWith('/') ? path : `/${path}`;
}

function buildRouteUrl(route) {
  const suffix = routeUrlSuffix(route?.path);

  if (!siteUrl) {
    return suffix;
  }

  return suffix === '/' ? `${siteUrl}/` : `${siteUrl}${suffix}`;
}

function isCopied(route) {
  return copiedPath.value === routeUrlSuffix(route?.path);
}

async function copyRouteUrl(route) {
  const url = buildRouteUrl(route);

  try {
    await navigator.clipboard.writeText(url);
    markCopied(route);
  } catch {
    try {
      const input = document.createElement('textarea');
      input.value = url;
      input.setAttribute('readonly', '');
      input.style.position = 'absolute';
      input.style.left = '-9999px';
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      markCopied(route);
    } catch {
      copiedPath.value = null;
    }
  }
}

function markCopied(route) {
  copiedPath.value = routeUrlSuffix(route?.path);
  clearTimeout(copiedTimer);
  copiedTimer = setTimeout(() => {
    copiedPath.value = null;
  }, 1800);
}

function goRoutes() {
  pushControlPath('/control/routes');
}

onUnmounted(() => {
  clearTimeout(copiedTimer);
});
</script>
