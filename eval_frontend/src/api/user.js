import request from "@/utils/request";
import axios from "axios";
import { getToken } from "@/utils/auth";

export function login(data) {
  return request({
    url: "/login",
    method: "post",
    data
  });
}

export function getInfo(token) {
  return request({
    url: "/me",
    method: "get"
  });
}

export function logout() {
  return request({
    url: "/logout",
    method: "post"
  });
}
export function logoutWithWebsocket(data) {
  return request({
    url: "/logout",
    method: "post",
    data
  });
}



export function refreshTokenFn() {
  return new Promise((resolve, reject) => {
    axios({
      url: `${process.env.VUE_APP_BASE_API}/refresh`,
      method: "post",
      headers: {
        Authorization: "Bearer " + getToken()
      }
    })
      .then((response) => {
        // return response;
        resolve(response);
      })
      .catch((error) => {
        resolve(error.response);
      });
  });
}

// websocket中绑定个人信息
export function bindUser(data) {
  return request({
    url: `/user/bind`,
    method: "post",
    data
  });
}
