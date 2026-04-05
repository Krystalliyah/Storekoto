// resources/js/types/echo.d.ts
import type Echo from 'laravel-echo';

declare global {
    interface Window {
        Echo: Echo;
        Pusher: any;
        axios: any;
    }
}

export {};