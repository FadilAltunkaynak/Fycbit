import axios from "axios";
import Cookie from "js-cookie";

const evmRequest = axios.create({
  baseURL: process.env.NEXT_PUBLIC_EVM_APP_HOST + "/",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

evmRequest.interceptors.request.use((config: any) => {
  const token = Cookie.get("evm_access_token");
  const lang = global.window && window.location.href.split("/")[3];

  if (token) {
    config.headers.token = `${token}`;
  }
  config.headers.lang = lang ? lang : "en";
  config.headers.evmapisecret = process.env.NEXT_PUBLIC_EVM_APP_SECRET;
  return config;
});
evmRequest.interceptors.response.use((response: any) => {
  return response;
});

export function apievmRequest(base: any, query: any | null) {
  if (query === null) {
    return evmRequest(base);
  } else {
    return axios.get(base + query);
  }
}
export default evmRequest;
