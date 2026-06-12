import Cookies from "js-cookie";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

let echo: Echo | null = null;

export const initEcho = (): Echo | null => {
  if (typeof window === "undefined") return null;

  if (!echo) {
    (window as any).Pusher = Pusher;
    const token = Cookies.get("token");
    echo = new Echo({
      broadcaster: "pusher",
      key: "test",
      wsHost: process.env.NEXT_PUBLIC_HOST_SOCKET,
      wsPort: process.env.NEXT_PUBLIC_WSS_PORT
        ? process.env.NEXT_PUBLIC_WSS_PORT
        : 6010,
      wssPort: 443,
      forceTLS: false,
      cluster: "mt1",
      disableStats: true,
      enabledTransports: ["ws", "wss"],
      authEndpoint: `${process.env.NEXT_PUBLIC_BASE_URL}/broadcasting/auth`,
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    });
  }

  return echo;
};
