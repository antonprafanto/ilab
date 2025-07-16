/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_URL: string
  readonly VITE_APP_NAME: string
  readonly VITE_APP_VERSION: string
  // add more environment variables here as needed
}

interface ImportMeta {
  readonly env: ImportMetaEnv
}