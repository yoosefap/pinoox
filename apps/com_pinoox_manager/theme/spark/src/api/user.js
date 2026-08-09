import {http} from "@global";
import {HTTP_ALERT_SUCCESS} from '@utils/helpers/alertHelper.js';

const BASE_URL = '/user';

export const userAPI = {
    get: () => http.get(`${BASE_URL}/get`, {alert: false}),
    getOptions: () => http.get(`${BASE_URL}/getOptions`, {alert: false}),
    deleteAvatar: () => http.get(`${BASE_URL}/deleteAvatar`, HTTP_ALERT_SUCCESS),
    changeAvatar: (formData) => http.postForm(`${BASE_URL}/changeAvatar`, formData, HTTP_ALERT_SUCCESS),
    changeInfo: (params) => http.post(`${BASE_URL}/changeInfo`, params, HTTP_ALERT_SUCCESS),
    changePassword: (params) => http.post(`${BASE_URL}/changePassword`, params, HTTP_ALERT_SUCCESS),
    getUsers: (packageName, params = {}) => http.get(`${BASE_URL}/getUsers/${packageName}`, {
        params,
        alert: false,
    }),
    createUser: (packageName, data) => http.post(`${BASE_URL}/create/${packageName}`, data, {alert: false}),
    updateUser: (packageName, userId, data) => http.post(`${BASE_URL}/update/${packageName}/${userId}`, data, {alert: false}),
    deleteUser: (packageName, userId) => http.post(`${BASE_URL}/delete/${packageName}/${userId}`, {}, {alert: false}),
    saveRolePermissions: (packageName, roleId, permissionIds) => http.post(
        `${BASE_URL}/rolePermissions/${packageName}/${roleId}`,
        {permission_ids: permissionIds},
        {alert: false},
    ),
};
