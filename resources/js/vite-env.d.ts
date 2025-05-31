/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_API_URL: string;
    readonly VITE_BASE_PATH: string;
    readonly MODE: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
